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

    private const array FORM_MAPPING = [
        // Unown
        'unown' => '.fUNOWN_F',

        // Spinda
        'spinda' => '.f01',

        // Darmanitan (BEFORE generic galarian pattern!)
        'darmanitan_galarian_standard' => '.fGALARIAN_STANDARD',
        'darmanitan_standard' => '.fSTANDARD',

        // Alolan forms
        'alolan' => '.fALOLA',

        // Galarian forms (generic - must be AFTER specific cases)
        'galarian' => '.fGALARIAN',

        // Hisuian forms
        'hisuian' => '.fHISUIAN',

        // Paldean forms
        'tauros_aqua' => '.fPALDEA_AQUA',
        'tauros_blaze' => '.fPALDEA_BLAZE',
        'tauros_combat' => '.fPALDEA_COMBAT',
        'paldean' => '.fPALDEA',

        // Armored
        'armored' => '.fA',

        // Castform
        'castform_rainy' => '.fRAINY',
        'castform_snowy' => '.fSNOWY',
        'castform_sunny' => '.fSUNNY',

        // Deoxys
        'deoxys_attack' => '.fATTACK',
        'deoxys_defense' => '.fDEFENSE',
        'deoxys_speed' => '.fSPEED',

        // Cherrim
        'cherrim_overcast' => '.fOVERCAST',
        'cherrim_sunny' => '.fSUNNY',

        // Shellos/Gastrodon
        'shellos' => '.fEAST_SEA',
        'gastrodon' => '.fEAST_SEA',

        // Rotom
        'rotom_frost' => '.fFROST',
        'rotom_heat' => '.fHEAT',
        'rotom_mow' => '.fMOW',
        'rotom_wash' => '.fWASH',

        // Giratina
        'origin' => '.fORIGIN',
        'altered' => '.fALTERED',

        // Shaymin
        'shaymin_sky' => '.fSKY',

        // Therian forms
        'incarnate' => '.fINCARNATE',
        'therian' => '.fTHERIAN',

        // Kyurem
        'kyurem_black' => '.fBLACK',
        'kyurem_white' => '.fWHITE',
        'kyurem' => '.fNORMAL',

        // Keldeo
        'keldeo_ordinary' => '.fORDINARY',
        'keldeo_resolute' => '.fRESOLUTE',

        // Genesect
        'genesect' => '.fNORMAL',
        'burn' => '.fBURN',
        'chill' => '.fCHILL',
        'douse' => '.fDOUSE',
        'shock' => '.fSHOCK',

        // Meowstic
        'meowstic_female' => '.fFEMALE',

        // Zygarde
        'zygarde_10' => '.fTEN_PERCENT',
        'zygarde' => '.fFIFTY_PERCENT',
        'zygarde_complete' => '.fCOMPLETE',

        // Vivillon
        'vivillon' => '.fMEADOW',

        // Flabébé, Floette, Florges (Red flower)
        'flabebe' => '.fRED',
        'floette' => '.fRED',
        'florges' => '.fRED',

        // Furfrou
        'furfrou' => '.fNATURAL',

        // Hoopa
        'unbound' => '.fUNBOUND',

        // Oricorio
        'oricorio_baile' => '.fBAILE',
        'oricorio_pau' => '.fPAU',
        'oricorio_pom_pom' => '.fPOMPOM',
        'oricorio_sensu' => '.fSENSU',

        // Necrozma
        'dawn_wings' => '.fDAWN_WINGS',
        'dusk_mane' => '.fDUSK_MANE',

        // Lycanroc
        'lycanroc_midday' => '.fMIDDAY',
        'lycanroc_dusk' => '.fDUSK',
        'lycanroc_midnight' => '.fMIDNIGHT',

        // Toxtricity
        'toxtricity' => '.fAMPED',

        // Zacian/Zamazenta
        'crowned_sword' => '.fCROWNED_SWORD',
        'crowned_shield' => '.fCROWNED_SHIELD',

        // Urshifu
        'rapid_strike' => '.fRAPID_STRIKE',
        'single_strike' => '.fSINGLE_STRIKE',

        // Oinkologne
        'oinkologne_female' => '.fFEMALE',

        // Maushold
        'maushold' => '.fFAMILY_OF_FOUR',

        // Basculin
        'basculin' => '.fBLUE_STRIPED',

        // Burmy
        'burmy_plant' => '.fBURMY_PLANT',
        'burmy_sandy' => '.fBURMY_SANDY',
        'burmy_trash' => '.fBURMY_TRASH',

        // Wormadam
        'wormadam_plant' => '.fWORMADAM_PLANT',
        'wormadam_sandy' => '.fWORMADAM_SANDY',
        'wormadam_trash' => '.fWORMADAM_TRASH',

        // Deerling/Sawsbuck
        'deerling' => '.fAUTUMN',
        'sawsbuck' => '.fAUTUMN',

        // Aegislash
        'aegislash_shield' => '.fSHIELD',

        // Indeedee
        'indeedee_male' => '.fMALE',
        'indeedee_female' => '.fFEMALE',

        // Tatsugiri
        'tatsugiri_curly' => '.fCURLY',
        'tatsugiri_droopy' => '.fDROOPY',
        'tatsugiri_stretchy' => '.fSTRETCHY',
    ];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $publicDir,
    ) {
    }

    public function downloadAndSavePicture(Pokemon $pokemon, SymfonyStyle $io, bool $strictMode = false): ?string
    {
        $slug = $pokemon->getSlug();
        if (null === $slug) {
            $io->warning(sprintf('Pokémon %s has no slug, skipping.', $pokemon->getName()));

            return null;
        }

        $url = $this->getImageUrl($pokemon, $strictMode);

        try {
            $content = $this->downloadImage($url);
            $filename = $this->savePicture($slug, $content);

            return $filename;
        } catch (\Exception $e) {
            $io->warning(sprintf('Failed to download image for %s: %s', $pokemon->getName(), $e->getMessage()));

            return null;
        }
    }

    private function getImageUrl(Pokemon $pokemon, bool $strictMode = false): string
    {
        // Build URL with form suffix (PoGo Hub only in strict mode)
        $formSuffix = $this->getFormSuffix($pokemon->getSlug() ?? '');
        $identifier = $pokemon->getNumber() . $formSuffix;

        return sprintf(self::BASE_URL, $identifier);
    }

    private function getFormSuffix(string $slug): string
    {
        foreach (self::FORM_MAPPING as $pattern => $suffix) {
            if (str_contains($slug, $pattern)) {
                return $suffix;
            }
        }

        return '';
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
