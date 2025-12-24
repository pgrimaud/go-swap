<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Move;
use App\Entity\Pokemon;
use App\Entity\PokemonMove;
use App\Entity\Type;
use App\Helper\GenerationHelper;
use App\Helper\HashHelper;
use App\Repository\MoveRepository;
use App\Repository\PokemonRepository;
use App\Repository\TypeRepository;
use App\Service\GameMasterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update:pokemon',
    description: 'Update all pokémon data',
)]
final class UpdatePokemonCommand extends AbstractSuggestCommand
{
    private const array FORM_MAPPING = [
        // Unown
        'unown' => 'fUNOWN_A',

        // Spinda
        'spinda' => 'f01',

        // Darmanitan (BEFORE generic galarian pattern!)
        'darmanitan_galarian_standard' => 'fGALARIAN_STANDARD',
        'darmanitan_standard' => 'fSTANDARD',

        // Alolan forms
        'alolan' => 'fALOLA',

        // Galarian forms (generic - must be AFTER specific cases)
        'galarian' => 'fGALARIAN',

        // Hisuian forms
        'hisuian' => 'fHISUIAN',

        // Paldean forms
        'tauros_aqua' => 'fPALDEA_AQUA',
        'tauros_blaze' => 'fPALDEA_BLAZE',
        'tauros_combat' => 'fPALDEA_COMBAT',
        'paldean' => 'fPALDEA',

        // Armored
        'armored' => 'fA',

        // Castform
        'castform_rainy' => 'fRAINY',
        'castform_snowy' => 'fSNOWY',
        'castform_sunny' => 'fSUNNY',

        // Deoxys
        'deoxys_attack' => 'fATTACK',
        'deoxys_defense' => 'fDEFENSE',
        'deoxys_speed' => 'fSPEED',

        // Cherrim
        'cherrim_overcast' => 'fOVERCAST',
        'cherrim_sunny' => 'fSUNNY',

        // Shellos/Gastrodon
        'shellos' => 'fEAST_SEA',
        'gastrodon' => 'fEAST_SEA',

        // Rotom
        'rotom_frost' => 'fFROST',
        'rotom_heat' => 'fHEAT',
        'rotom_mow' => 'fMOW',
        'rotom_wash' => 'fWASH',

        // Giratina
        'origin' => 'fORIGIN',
        'altered' => 'fALTERED',

        // Shaymin
        'shaymin_sky' => 'fSKY',

        // Therian forms
        'incarnate' => 'fINCARNATE',
        'therian' => 'fTHERIAN',

        // Kyurem
        'kyurem_black' => 'fBLACK',
        'kyurem_white' => 'fWHITE',
        'kyurem' => 'fNORMAL',

        // Keldeo
        'keldeo_ordinary' => 'fORDINARY',
        'keldeo_resolute' => 'fRESOLUTE',

        // Genesect
        'genesect' => 'fNORMAL',
        'burn' => 'fBURN',
        'chill' => 'fCHILL',
        'douse' => 'fDOUSE',
        'shock' => 'fSHOCK',

        // Meowstic
        'meowstic_female' => 'fFEMALE',

        // Zygarde
        'zygarde_10' => 'fTEN_PERCENT',
        'zygarde' => 'fFIFTY_PERCENT',
        'zygarde_complete' => 'fCOMPLETE',

        // Vivillon
        'vivillon' => 'fMEADOW',

        // Flabébé, Floette, Florges (Red flower)
        'flabebe' => 'fRED',
        'floette' => 'fRED',
        'florges' => 'fRED',

        // Furfrou
        'furfrou' => 'fNATURAL',

        // Hoopa
        'unbound' => 'fUNBOUND',

        // Oricorio
        'oricorio_baile' => 'fBAILE',
        'oricorio_pau' => 'fPAU',
        'oricorio_pom_pom' => 'fPOMPOM',
        'oricorio_sensu' => 'fSENSU',

        // Necrozma
        'dawn_wings' => 'fDAWN_WINGS',
        'dusk_mane' => 'fDUSK_MANE',

        // Lycanroc
        'lycanroc_midday' => 'fMIDDAY',
        'lycanroc_dusk' => 'fDUSK',
        'lycanroc_midnight' => 'fMIDNIGHT',

        // Toxtricity
        'toxtricity' => 'fAMPED',

        // Zacian/Zamazenta
        'crowned_sword' => 'fCROWNED_SWORD',
        'crowned_shield' => 'fCROWNED_SHIELD',

        // Urshifu
        'rapid_strike' => 'fRAPID_STRIKE',
        'single_strike' => 'fSINGLE_STRIKE',

        // Oinkologne
        'oinkologne_female' => 'fFEMALE',

        // Maushold
        'maushold' => 'fFAMILY_OF_FOUR',

        // Basculin
        'basculin' => 'fBLUE_STRIPED',

        // Burmy
        'burmy_plant' => 'fBURMY_PLANT',
        'burmy_sandy' => 'fBURMY_SANDY',
        'burmy_trash' => 'fBURMY_TRASH',

        // Wormadam
        'wormadam_plant' => 'fWORMADAM_PLANT',
        'wormadam_sandy' => 'fWORMADAM_SANDY',
        'wormadam_trash' => 'fWORMADAM_TRASH',

        // Deerling/Sawsbuck
        'deerling' => 'fAUTUMN',
        'sawsbuck' => 'fAUTUMN',

        // Aegislash
        'aegislash_shield' => 'fSHIELD',

        // Indeedee
        'indeedee_male' => 'fMALE',
        'indeedee_female' => 'fFEMALE',

        // Tatsugiri
        'tatsugiri_curly' => 'fCURLY',
        'tatsugiri_droopy' => 'fDROOPY',
        'tatsugiri_stretchy' => 'fSTRETCHY',
    ];

    /** @var array<Type> */
    private array $types = [];

    /** @var array<Move> */
    private array $moves = [];

    public function __construct(
        private readonly GameMasterService $gameMasterService,
        private readonly PokemonRepository $pokemonRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TypeRepository $typeRepository,
        private readonly MoveRepository $moveRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->writeln('');
        $io->writeln('<fg=cyan>═══════════════════════════════════════</>');
        $io->writeln('<fg=cyan;options=bold>  🦕 Updating Pokémon</>');
        $io->writeln('<fg=cyan>═══════════════════════════════════════</>');
        $io->writeln('');

        $pokemons = $this->gameMasterService->getPokemons();
        $progressBar = $io->createProgressBar(count($pokemons));
        $progressBar->start();

        foreach ($pokemons as $pokemon) {
            // avoid unreleased pokémon
            if (false === $pokemon['released'] && !in_array($pokemon['speciesId'], [
                'spewpa', 'ditto', 'shedinja', 'mudbray', 'mudsdale', 'cosmog', 'cosmoem',
            ])) {
                continue;
            }

            // avoid shadow, mega and special forms
            if (
                str_contains($pokemon['speciesId'], '_shadow')
                || str_contains($pokemon['speciesId'], '_mega')
                || str_contains($pokemon['speciesId'], 'pikachu_')
                || str_contains($pokemon['speciesId'], '_primal')
                || in_array('duplicate', $pokemon['tags'] ?? [])
            ) {
                continue;
            }

            $slug = $pokemon['speciesId'];

            $pokemonEntity = $this->pokemonRepository->findOneBy(['slug' => $slug]);

            if (!$pokemonEntity instanceof Pokemon || $pokemonEntity->getHash() !== HashHelper::fromPokemon($pokemon)) {
                $pokemonEntity = $pokemonEntity ?? new Pokemon();
                $pokemonEntity->setNumber($pokemon['dex']);
                $pokemonEntity->setName($pokemon['speciesName']);
                $pokemonEntity->setSlug($pokemon['speciesId']);
                $pokemonEntity->setAttack($pokemon['baseStats']['atk']);
                $pokemonEntity->setDefense($pokemon['baseStats']['def']);
                $pokemonEntity->setStamina($pokemon['baseStats']['hp']);
                $pokemonEntity->setShadow(in_array('shadoweligible', $pokemon['tags'] ?? []));
                $pokemonEntity->setGeneration(GenerationHelper::get($pokemon['dex']));
                $pokemonEntity->setForm($this->extractForm($slug));
                $pokemonEntity->setHash(HashHelper::fromPokemon($pokemon));

                // manage types
                foreach ($pokemon['types'] as $type) {
                    if ('none' === $type) {
                        continue;
                    }
                    $pokemonEntity->addType($this->getType($io, $progressBar, $type, $pokemon['speciesName']));
                }

                // manage fast moves
                foreach (array_merge($pokemon['fastMoves'], $pokemon['chargedMoves']) as $move) {
                    // check if move is already linked to the Pokémon
                    $existingMove = $pokemonEntity->getPokemonMoves()->filter(
                        fn (PokemonMove $pokemonMove) => $pokemonMove->getMove()?->getSlug() === mb_strtolower($move)
                    )->first();

                    if ($existingMove) {
                        $existingMove->setElite(in_array($move, $pokemon['eliteMoves'] ?? []));
                        continue;
                    }

                    $moveEntity = $this->getMove($io, $progressBar, mb_strtolower($move), $pokemon['speciesName']);

                    $pokemonMove = new PokemonMove();
                    $pokemonMove->setMove($moveEntity);
                    $pokemonMove->setPokemon($pokemonEntity);
                    $pokemonMove->setElite(in_array($move, $pokemon['eliteMoves'] ?? []));

                    $pokemonEntity->addPokemonMove($pokemonMove);
                }

                $this->entityManager->persist($pokemonEntity);
                $this->entityManager->flush();
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $io->newLine(2);

        $io->success('Pokémon data updated successfully.');

        return Command::SUCCESS;
    }

    private function extractForm(string $slug): ?string
    {
        foreach (self::FORM_MAPPING as $pattern => $suffix) {
            if (str_contains($slug, $pattern)) {
                return $suffix;
            }
        }

        return null;
    }

    private function getType(
        SymfonyStyle $io,
        ProgressBar $progressBar,
        string $typeAsString,
        string $pokemonName,
    ): Type {
        if (array_key_exists($typeAsString, $this->types)) {
            return $this->types[$typeAsString];
        }

        $type = $this->typeRepository->findOneBy(['slug' => $typeAsString]);

        if (!$type instanceof Type) {
            $progressBar->clear();

            $io->error(sprintf(
                'Type not found in %s: %s',
                $pokemonName,
                $typeAsString
            ));
            $this->runParentCommand($io, 'app:update:types');

            return $this->getType($io, $progressBar, $typeAsString, $pokemonName);
        }

        return $type;
    }

    private function getMove(
        SymfonyStyle $io,
        ProgressBar $progressBar,
        string $moveAsString,
        string $pokemonName,
    ): Move {
        if (array_key_exists($moveAsString, $this->moves)) {
            return $this->moves[$moveAsString];
        }

        $move = $this->moveRepository->findOneBy(['slug' => $moveAsString]);

        if (!$move instanceof Move) {
            $progressBar->clear();

            $io->error(sprintf(
                'Move not found in %s: %s',
                $pokemonName,
                $moveAsString
            ));
            $this->runParentCommand($io, 'app:update:moves');

            return $this->getMove($io, $progressBar, $moveAsString, $pokemonName);
        }

        return $move;
    }
}
