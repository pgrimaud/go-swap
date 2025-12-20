<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Pokemon;
use App\Repository\PokemonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:update:pictures',
    description: 'Update all pokémon pictures',
)]
class UpdatePicturesCommand extends Command
{
    private const string POKEMON_PICTURE_URL = 'https://db.pokemongohub.net/images/ingame/normal/pm%s.icon.png';

    private const string FOLDER_NORMAL = 'normal';
    // private const string FOLDER_SHINY = 'shiny';

    public function __construct(
        private readonly PokemonRepository $pokemonRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly HttpClientInterface $httpClient,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $allPokemon = $this->pokemonRepository->findAllWithoutPictures();

        $progressBar = new ProgressBar($output, count($allPokemon));
        $progressBar->start();

        foreach ($allPokemon as $pokemon) {
            if (!$pokemon->getSlug()) {
                $io->error(sprintf('Pokémon %s has no slug, skipping.', $pokemon->getName()));

                return Command::FAILURE;
            }

            $pictureUrl = $this->getPictureUrl($pokemon);
            $picturePath = $this->downloadPicture($io, $pictureUrl, $pokemon->getSlug(), self::FOLDER_NORMAL);

            $pokemon->setPicture($picturePath);

            $this->entityManager->flush();
            $progressBar->advance();
        }

        $progressBar->finish();

        $io->success('Pokémon pictures imported successfully.');

        return Command::SUCCESS;
    }

    private function downloadPicture(SymfonyStyle $io, string $url, string $pokemonSlug, string $type): string
    {
        try {
            $content = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3',
                ],
            ]);

            $picturePath = 'images/pokemon/' . $type . '/' . $pokemonSlug . '.png';

            file_put_contents(__DIR__ . '/../../public/' . $picturePath, $content->getContent());

            $fullPath = __DIR__ . '/../../public/' . $picturePath;
            $imageData = file_get_contents($fullPath);
            $srcImage = @imagecreatefromstring((string) $imageData);
            if (false !== $srcImage) {
                $srcWidth = imagesx($srcImage);
                $srcHeight = imagesy($srcImage);
                $dstSize = 150;
                $ratio = min($dstSize / $srcWidth, $dstSize / $srcHeight);
                $newWidth = (int) ($srcWidth * $ratio);
                $newHeight = (int) ($srcHeight * $ratio);
                $dstImage = imagecreatetruecolor($dstSize, $dstSize);
                imagealphablending($dstImage, false);
                imagesavealpha($dstImage, true);
                $transparent = imagecolorallocatealpha($dstImage, 0, 0, 0, 127);
                imagefilledrectangle($dstImage, 0, 0, $dstSize, $dstSize, (int) $transparent);
                $dstX = (int) (($dstSize - $newWidth) / 2);
                $dstY = (int) (($dstSize - $newHeight) / 2);
                imagecopyresampled($dstImage, $srcImage, $dstX, $dstY, 0, 0, $newWidth, $newHeight, $srcWidth, $srcHeight);
                imagepng($dstImage, $fullPath);
                imagedestroy($srcImage);
                imagedestroy($dstImage);
            } else {
                $io->warning('Error resizing image for ' . $pokemonSlug);
            }

            return $pokemonSlug . '.png';
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            exit;
        }
    }

    /**
     * This is fucking ugly, refactor this later. Or never. ¯\_(ツ)_/¯.
     */
    private function getPictureUrl(Pokemon $pokemon): string
    {
        if (!$pokemon->getSlug()) {
            throw new \Exception('Pokémon slug is empty for ' . $pokemon->getName());
        }

        if (201 === $pokemon->getNumber()) {
            return 'https://www.media.pokekalos.fr/img/pokemon/pokego/zarbi-lettre-f.png';
        }

        if (327 === $pokemon->getNumber()) {
            return 'https://www.media.pokekalos.fr/img/pokemon/pokego/spinda-1.png';
        }

        if (412 === $pokemon->getNumber()) {
            return match (true) {
                str_contains($pokemon->getSlug(), '_plant') => 'https://www.media.pokekalos.fr/img/pokemon/pokego/cheniti-forme-plante.png',
                str_contains($pokemon->getSlug(), '_trash') => 'https://www.media.pokekalos.fr/img/pokemon/pokego/cheniti-forme-dechet.png',
                str_contains($pokemon->getSlug(), '_sandy') => 'https://www.media.pokekalos.fr/img/pokemon/pokego/cheniti-forme-sable.png',
                default => throw new \Exception('Error'),
            };
        }

        if (413 === $pokemon->getNumber()) {
            return match (true) {
                str_contains($pokemon->getSlug(), '_plant') => 'https://www.media.pokekalos.fr/img/pokemon/pokego/cheniselle-forme-plante.png',
                str_contains($pokemon->getSlug(), '_trash') => 'https://www.media.pokekalos.fr/img/pokemon/pokego/cheniselle-forme-dechet.png',
                str_contains($pokemon->getSlug(), '_sandy') => 'https://www.media.pokekalos.fr/img/pokemon/pokego/cheniselle-forme-sable.png',
                default => throw new \Exception('Error'),
            };
        }

        if (550 === $pokemon->getNumber()) {
            return 'https://db.pokemongohub.net/images/ingame/normal/pm550.fBLUE_STRIPED.icon.png';
        }

        if ('darmanitan_standard' === $pokemon->getSlug()) {
            return 'https://www.media.pokekalos.fr/img/pokemon/pokego/darumacho.png';
        }

        if ('darmanitan_galarian_standard' === $pokemon->getSlug()) {
            return 'https://www.media.pokekalos.fr/img/pokemon/pokego/darumacho-g.png';
        }

        if (585 === $pokemon->getNumber()) {
            return 'https://db.pokemongohub.net/images/ingame/normal/pm585.fAUTUMN.icon.png';
        }

        if (586 === $pokemon->getNumber()) {
            return 'https://db.pokemongohub.net/images/ingame/normal/pm586.fAUTUMN.icon.png';
        }

        if ('kyurem' === $pokemon->getSlug()) {
            return 'https://www.media.pokekalos.fr/img/pokemon/pokego/kyurem.png';
        }

        if (666 === $pokemon->getNumber()) {
            return 'https://www.media.pokekalos.fr/img/pokemon/pokego/prismillon.png';
        }

        if (669 === $pokemon->getNumber()) {
            return 'https://www.media.pokekalos.fr/img/pokemon/pokego/flabebe-fleur-rouge.png';
        }

        if (670 === $pokemon->getNumber()) {
            return 'https://www.media.pokekalos.fr/img/pokemon/pokego/floette-fleur-rouge.png';
        }

        if (671 === $pokemon->getNumber()) {
            return 'https://www.media.pokekalos.fr/img/pokemon/pokego/florges-fleur-rouge.png';
        }

        if (676 === $pokemon->getNumber()) {
            return 'https://www.media.pokekalos.fr/img/pokemon/pokego/couafarel-forme-sauvage.png';
        }

        if (710 === $pokemon->getNumber()) {
            return match (true) {
                'pumpkaboo_small' === $pokemon->getSlug() => 'https://www.media.pokekalos.fr/img/pokemon/pokego/pitrouille-taille-mini.png',
                'pumpkaboo_average' === $pokemon->getSlug() => 'https://www.media.pokekalos.fr/img/pokemon/pokego/pitrouille-taille-normale.png',
                'pumpkaboo_large' === $pokemon->getSlug() => 'https://www.media.pokekalos.fr/img/pokemon/pokego/pitrouille-taille-maxi.png',
                'pumpkaboo_super' === $pokemon->getSlug() => 'https://www.media.pokekalos.fr/img/pokemon/pokego/pitrouille-taille-ultra.png',
                default => throw new \Exception('Error'),
            };
        }

        if (711 === $pokemon->getNumber()) {
            return match (true) {
                'gourgeist_small' === $pokemon->getSlug() => 'https://www.media.pokekalos.fr/img/pokemon/pokego/banshitrouye-taille-mini.png',
                'gourgeist_average' === $pokemon->getSlug() => 'https://www.media.pokekalos.fr/img/pokemon/pokego/banshitrouye-taille-normale.png',
                'gourgeist_large' === $pokemon->getSlug() => 'https://www.media.pokekalos.fr/img/pokemon/pokego/banshitrouye-taille-maxi.png',
                'gourgeist_super' === $pokemon->getSlug() => 'https://www.media.pokekalos.fr/img/pokemon/pokego/banshitrouye-taille-ultra.png',
                default => throw new \Exception('Error'),
            };
        }

        if (745 === $pokemon->getNumber()) {
            return match (true) {
                'lycanroc_midday' === $pokemon->getSlug() => 'https://www.media.pokekalos.fr/img/pokemon/pokego/lougaroc-forme-diurne.png',
                'lycanroc_dusk' === $pokemon->getSlug() => 'https://www.media.pokekalos.fr/img/pokemon/pokego/lougaroc-forme-crepusculaire.png',
                'lycanroc_midnight' === $pokemon->getSlug() => 'https://www.media.pokekalos.fr/img/pokemon/pokego/lougaroc-forme-nocturne.png',
                default => throw new \Exception('Error'),
            };
        }

        $partialPath = match (true) {
            str_contains($pokemon->getSlug(), '_alolan') => $pokemon->getNumber() . '.fALOLA',
            str_contains($pokemon->getSlug(), '_galarian') => $pokemon->getNumber() . '.fGALARIAN',
            str_contains($pokemon->getSlug(), '_hisuian') => $pokemon->getNumber() . '.fHISUIAN',
            str_contains($pokemon->getSlug(), 'tauros_aqua') => $pokemon->getNumber() . '.fPALDEA_AQUA',
            str_contains($pokemon->getSlug(), 'tauros_blaze') => $pokemon->getNumber() . '.fPALDEA_BLAZE',
            str_contains($pokemon->getSlug(), 'tauros_combat') => $pokemon->getNumber() . '.fPALDEA_COMBAT',
            str_contains($pokemon->getSlug(), '_armored') => $pokemon->getNumber() . '.fA',
            str_contains($pokemon->getSlug(), '_paldean') => $pokemon->getNumber() . '.fPALDEA',
            str_contains($pokemon->getSlug(), 'castform_rainy') => $pokemon->getNumber() . '.fRAINY',
            str_contains($pokemon->getSlug(), 'castform_snowy') => $pokemon->getNumber() . '.fSNOWY',
            str_contains($pokemon->getSlug(), 'castform_sunny') => $pokemon->getNumber() . '.fSUNNY',
            str_contains($pokemon->getSlug(), 'deoxys_attack') => $pokemon->getNumber() . '.fATTACK',
            str_contains($pokemon->getSlug(), 'deoxys_defense') => $pokemon->getNumber() . '.fDEFENSE',
            str_contains($pokemon->getSlug(), 'deoxys_speed') => $pokemon->getNumber() . '.fSPEED',
            str_contains($pokemon->getSlug(), 'cherrim_overcast') => $pokemon->getNumber() . '.fOVERCAST',
            str_contains($pokemon->getSlug(), 'cherrim_sunny') => $pokemon->getNumber() . '.fSUNNY',
            str_contains($pokemon->getSlug(), 'shellos') => $pokemon->getNumber() . '.fEAST_SEA',
            str_contains($pokemon->getSlug(), 'gastrodon') => $pokemon->getNumber() . '.fEAST_SEA',
            str_contains($pokemon->getSlug(), 'rotom_frost') => $pokemon->getNumber() . '.fFROST',
            str_contains($pokemon->getSlug(), 'rotom_heat') => $pokemon->getNumber() . '.fHEAT',
            str_contains($pokemon->getSlug(), 'rotom_MOW') => $pokemon->getNumber() . '.fMOW',
            str_contains($pokemon->getSlug(), 'rotom_wash') => $pokemon->getNumber() . '.fWASH',
            str_contains($pokemon->getSlug(), '_origin') => $pokemon->getNumber() . '.fORIGIN',
            str_contains($pokemon->getSlug(), '_altered') => $pokemon->getNumber() . '.fALTERED',
            str_contains($pokemon->getSlug(), 'shaymin_sky') => $pokemon->getNumber() . '.fSKY',
            str_contains($pokemon->getSlug(), '_incarnate') => $pokemon->getNumber() . '.fINCARNATE',
            str_contains($pokemon->getSlug(), '_therian') => $pokemon->getNumber() . '.fTHERIAN',
            str_contains($pokemon->getSlug(), '_black') => $pokemon->getNumber() . '.fBLACK',
            str_contains($pokemon->getSlug(), '_white') => $pokemon->getNumber() . '.fWHITE',
            str_contains($pokemon->getSlug(), '_ordinary') => $pokemon->getNumber() . '.fORDINARY',
            'genesect' === $pokemon->getSlug() => $pokemon->getNumber() . '.fNORMAL',
            str_contains($pokemon->getSlug(), '_burn') => $pokemon->getNumber() . '.fBURN',
            str_contains($pokemon->getSlug(), '_chill') => $pokemon->getNumber() . '.fCHILL',
            str_contains($pokemon->getSlug(), '_douse') => $pokemon->getNumber() . '.fDOUSE',
            str_contains($pokemon->getSlug(), '_shock') => $pokemon->getNumber() . '.fSHOCK',
            str_contains($pokemon->getSlug(), 'meowstic_female') => $pokemon->getNumber() . '.fFEMALE',
            str_contains($pokemon->getSlug(), 'zygarde_10') => $pokemon->getNumber() . '.fTEN_PERCENT',
            'zygarde' === $pokemon->getSlug() => $pokemon->getNumber() . '.fFIFTY_PERCENT',
            str_contains($pokemon->getSlug(), 'zygarde_complete') => $pokemon->getNumber() . '.fCOMPLETE',
            str_contains($pokemon->getSlug(), '_unbound') => $pokemon->getNumber() . '.fUNBOUND',
            str_contains($pokemon->getSlug(), 'oricorio_baile') => $pokemon->getNumber() . '.fBAILE',
            str_contains($pokemon->getSlug(), 'oricorio_pau') => $pokemon->getNumber() . '.fPAU',
            str_contains($pokemon->getSlug(), 'oricorio_pom_pom') => $pokemon->getNumber() . '.fPOMPOM',
            str_contains($pokemon->getSlug(), 'oricorio_sensu') => $pokemon->getNumber() . '.fSENSU',
            str_contains($pokemon->getSlug(), '_dawn_wings') => $pokemon->getNumber() . '.fDAWN_WINGS',
            str_contains($pokemon->getSlug(), '_dusk_mane') => $pokemon->getNumber() . '.fDUSK_MANE',
            'toxtricity' === $pokemon->getSlug() => $pokemon->getNumber() . '.fAMPED',
            str_contains($pokemon->getSlug(), '_crowned_sword') => $pokemon->getNumber() . '.fCROWNED_SWORD',
            str_contains($pokemon->getSlug(), '_crowned_shield') => $pokemon->getNumber() . '.fCROWNED_SHIELD',
            str_contains($pokemon->getSlug(), '_rapid_strike') => $pokemon->getNumber() . '.fRAPID_STRIKE',
            str_contains($pokemon->getSlug(), '_single_strike') => $pokemon->getNumber() . '.fSINGLE_STRIKE',
            str_contains($pokemon->getSlug(), 'oinkologne_female') => $pokemon->getNumber() . '.fFEMALE',
            'maushold' === $pokemon->getSlug() => $pokemon->getNumber() . '.fFAMILY_OF_FOUR',
            default => (string) $pokemon->getNumber(),
        };

        return sprintf(self::POKEMON_PICTURE_URL, $partialPath);
    }
}
