<?php

namespace App\Command;

use App\Entity\Type;
use App\Repository\PokemonRepository;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:import-types',
    description: 'Update pokemon types',
)]
class ImportTypesCommand extends Command
{
    private const MULTI_FORM_ALLOWED = [
        'Unown',
        'Furfrou',
        'Pumpkaboo',
        'Gourgeist',
        'Flabebe',
        'Floette',
        'Florges',
        'Spinda',
        'Vivillon',
        'Burmy',
        'Wormadam',
        'Zygarde',
        'Cherrim',
        'Maushold',
        'Deerling',
        'Sawsbuck',
        'Shellos',
        'Gastrodon',
        'Lycanroc',
        'Basculin',
        'Deoxys',
        'Frillish',
        'Jellicent',
        'Pyroar',
        'Meowstic',
        'Oinkologne',
        'Morpeko',
        'Toxtricity',
        'Tornadus',
        'Thundurus',
        'Landorus',
        'Enamorus',
        'Dialga',
        'Palkia',
        'Giratina',
        'Genesect',
        'Sinistea',
        'Polteageist',
    ];

    private const API_FORM_ALLOWED = [
        'Normal',
        '00', // Spinda
        'Meadow', // Vivillon
        'La_reine', // Furfrou
        'Large', // Pumpkaboo & Gourgeist
        'Blue', // Flabebe, Floette & Florges
        'Exclamation_point', // Unown
        'Trash', // Burmy & Wormadam
        'Complete_ten_percent', // Zygarde
        'Overcast', // Cherrim
        'Family_of_four', // Maushold
        'Winter', // Deerling & Sawsbuck
        'West_sea', // Shellos & Gastrodon
        'Midday', // Lycanroc
        'Blue_striped', // Basculin
        'Speed', // Deoxys
        'Female', // Frillish, Jellicent, Pyroar, Meowstic & Oinkologne
        'Full_belly', // Morpeko
        'Amped', // Toxtricity
        'Incarnate', // Tornadus, Thundurus, Landorus & Enamorus
        'Origin', // Dialga, Palkia & Giratina
        'Alola',
        'Hisuian',
        'Galarian',
        'Paldea',
        'Chill', // Genesect
        'Antique', // Sinistea & Polteageist
    ];

    /**
     * @var Type[]
     */
    private array $types;

    public function __construct(
        private readonly TypeRepository         $typeRepository,
        private readonly PokemonRepository      $pokemonRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly HttpClientInterface    $client,
        private readonly SluggerInterface       $slugger,
    )
    {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addOption(
            'force',
            null,
            InputOption::VALUE_NONE,
            'Re-import all types',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->types = $this->typeRepository->findAll();

        // get pokémon without type
        $allPokemon = $this->pokemonRepository->findAll();
        $pokemonWithoutType = 0;
        $pokemonWithoutTypeList = [];
        foreach ($allPokemon as $pokemon) {
            if (count($pokemon->getTypes()) === 0) {
                $pokemonWithoutType++;
                $pokemonWithoutTypeList[] = $pokemon;
            }
        }

        if ($pokemonWithoutType === 0 && $input->getOption('force') === false) {
            $io->success('All pokemons have at least one type.');
            return Command::SUCCESS;
        } else if ($input->getOption('force') === false) {
            foreach ($pokemonWithoutTypeList as $pokemon) {
                $io->writeln(sprintf('Pokemon %s has no type.', $pokemon->getEnglishName()));
            }
            $io->note(sprintf('Found %d pokemons without type.', $pokemonWithoutType));
        }

        if ($input->getOption('force') === true) {
            $this->truncateTable();
        }


        $apiData = $this->client->request('GET', 'https://pogoapi.net/api/v1/pokemon_types.json');

        /**
         * @var array{pokemon_id: int, pokemon_name: string, form: string, type: string[]} $pokemon
         */
        foreach ($apiData->toArray() as $pokemon) {

            if (!in_array($pokemon['form'], self::API_FORM_ALLOWED)) {
                continue;
            }

            // find associated pokémon
            $pokemonEntities = $this->pokemonRepository->findBy(['number' => $pokemon['pokemon_id']]);

            if (count($pokemonEntities) > 1 && $pokemon['form'] === 'Alola') {
                $newPokemonEntities = [];
                foreach ($pokemonEntities as $pokemonEntity) {
                    if (str_contains((string) $pokemonEntity->getEnglishName(), 'Alolan')) {
                        $newPokemonEntities[] = $pokemonEntity;
                    }
                }
                $pokemonEntities = $newPokemonEntities;
            } else if (count($pokemonEntities) > 1 && $pokemon['form'] === 'Hisuian') {
                $newPokemonEntities = [];
                foreach ($pokemonEntities as $pokemonEntity) {
                    if (str_contains((string) $pokemonEntity->getEnglishName(), 'Hisuian')) {
                        $newPokemonEntities[] = $pokemonEntity;
                    }
                }
                $pokemonEntities = $newPokemonEntities;
            } else if (count($pokemonEntities) > 1 && $pokemon['form'] === 'Galarian') {
                $newPokemonEntities = [];
                foreach ($pokemonEntities as $pokemonEntity) {
                    if (str_contains((string) $pokemonEntity->getEnglishName(), 'Galarian')) {
                        $newPokemonEntities[] = $pokemonEntity;
                    }
                }
                $pokemonEntities = $newPokemonEntities;
            } else if (count($pokemonEntities) > 1 && $pokemon['form'] === 'Paldea') {
                $newPokemonEntities = [];
                foreach ($pokemonEntities as $pokemonEntity) {
                    if (str_contains((string) $pokemonEntity->getEnglishName(), 'Paldean')) {
                        $newPokemonEntities[] = $pokemonEntity;
                    }
                }
                $pokemonEntities = $newPokemonEntities;
            } else if (count($pokemonEntities) > 1 && $pokemon['form'] === 'Normal' && $this->hasOtherForm($pokemonEntities)) {
                // filter Alolan, Galarian, Hisuian forms
                $newPokemonEntities = [];
                foreach ($pokemonEntities as $pokemonEntity) {
                    if (!str_contains((string) $pokemonEntity->getEnglishName(), 'Alolan')
                        && !str_contains((string) $pokemonEntity->getEnglishName(), 'Galarian')
                        && !str_contains((string) $pokemonEntity->getEnglishName(), 'Hisuian')
                        && !str_contains((string) $pokemonEntity->getEnglishName(), 'Paldean')
                    ) {
                        $newPokemonEntities[] = $pokemonEntity;
                    }
                }
                $pokemonEntities = $newPokemonEntities;
            } else if (
                count($pokemonEntities) > 1
                && !$this->isMultiFormAllowed($pokemon['pokemon_name'])
            ) {
                continue;
            } elseif (count($pokemonEntities) === 0) {
                // pokémon not released yet
                continue;
            }

            foreach ($pokemonEntities as $pokemonEntity) {
                foreach ($pokemon['type'] as $type) {
                    $type = $this->getTypeOrCreate($type);
                    $pokemonEntity->addType($type);
                }
            }

            $this->entityManager->flush();
        }

        $io->success('Done.');

        return Command::SUCCESS;
    }

    private function getTypeOrCreate(string $pokemonType): Type
    {
        foreach ($this->types as $type) {
            if ($type->getName() === $pokemonType) {
                return $type;
            }
        }

        // not found, so we create it
        $newType = new Type();
        $newType->setName($pokemonType);
        $newType->setSlug(
            $this->slugger->slug($pokemonType)->lower()
        );

        $this->entityManager->persist($newType);
        $this->entityManager->flush();

        $this->types[] = $newType;

        return $newType;
    }

    private function isMultiFormAllowed(string $pokemonName): bool
    {
        foreach (self::MULTI_FORM_ALLOWED as $allowed) {
            if (str_contains($pokemonName, $allowed)) {
                return true;
            }
        }

        return false;
    }

    public function truncateTable(): void
    {
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeStatement($platform->getTruncateTableSQL('pokemon_type', true));
    }

    private function hasOtherForm(array $pokemonEntities): bool
    {
        foreach ($pokemonEntities as $pokemonEntity) {
            if (str_contains($pokemonEntity->getEnglishName(), 'Alolan')
                || str_contains($pokemonEntity->getEnglishName(), 'Galarian')
                || str_contains($pokemonEntity->getEnglishName(), 'Hisuian')
                || str_contains($pokemonEntity->getEnglishName(), 'Paldean')
            ) {
                return true;
            }
        }

        return false;
    }
}
