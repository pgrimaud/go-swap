<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Pokemon;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PokemonImageService
{
    private const string BASE_URL = 'https://db.pokemongohub.net/images/ingame/normal/pm%s.icon.png';

    private const int IMAGE_SIZE = 150;
    private const string OUTPUT_FORMAT = 'png';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $publicDir,
    ) {
    }

    public function downloadAndSavePicture(Pokemon $pokemon, SymfonyStyle $io): ?string
    {
        $slug = $pokemon->getSlug();
        if (null === $slug) {
            $io->warning(sprintf('Pokémon %s has no slug, skipping.', $pokemon->getName()));

            return null;
        }

        $url = $this->getImageUrl($pokemon);

        try {
            $content = $this->downloadImage($url);
            $filename = $this->savePicture($slug, $content);

            return $filename;
        } catch (\Exception $e) {
            $io->warning(sprintf('Failed to download image for %s: %s', $pokemon->getName(), $e->getMessage()));

            return null;
        }
    }

    private function getImageUrl(Pokemon $pokemon): string
    {
        $identifier = $pokemon->getNumber() . ($pokemon->getForm() ? '.' . $pokemon->getForm() : '');

        return sprintf(self::BASE_URL, $identifier);
    }

    private function downloadImage(string $url): string
    {
        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ],
        ]);

        return $response->getContent();
    }

    private function savePicture(string $slug, string $content): string
    {
        $directory = $this->publicDir . '/images/pokemon/normal';

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = $slug . '.' . self::OUTPUT_FORMAT;
        $fullPath = $directory . '/' . $filename;

        file_put_contents($fullPath, $content);

        $this->resizeImage($fullPath);

        return $filename;
    }

    private function resizeImage(string $path): void
    {
        $imageData = file_get_contents($path);
        $srcImage = @imagecreatefromstring((string) $imageData);

        if (false === $srcImage) {
            return;
        }

        $srcWidth = imagesx($srcImage);
        $srcHeight = imagesy($srcImage);

        $ratio = min(self::IMAGE_SIZE / $srcWidth, self::IMAGE_SIZE / $srcHeight);
        $newWidth = (int) ($srcWidth * $ratio);
        $newHeight = (int) ($srcHeight * $ratio);

        $dstImage = imagecreatetruecolor(self::IMAGE_SIZE, self::IMAGE_SIZE);

        if (false === $dstImage) {
            imagedestroy($srcImage);

            return;
        }

        imagealphablending($dstImage, false);
        imagesavealpha($dstImage, true);

        $transparent = imagecolorallocatealpha($dstImage, 0, 0, 0, 127);
        if (false !== $transparent) {
            imagefilledrectangle($dstImage, 0, 0, self::IMAGE_SIZE, self::IMAGE_SIZE, $transparent);
        }

        $dstX = (int) ((self::IMAGE_SIZE - $newWidth) / 2);
        $dstY = (int) ((self::IMAGE_SIZE - $newHeight) / 2);

        imagecopyresampled(
            $dstImage,
            $srcImage,
            $dstX,
            $dstY,
            0,
            0,
            $newWidth,
            $newHeight,
            $srcWidth,
            $srcHeight
        );

        imagepng($dstImage, $path);

        imagedestroy($srcImage);
        imagedestroy($dstImage);
    }
}
