<?php

namespace App\Command;

use App\Entity\Pokemon;
use App\Repository\PokemonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;


#[AsCommand(
    name: 'app:import-pokemons',
    description: 'Import pokemons to database',
)]
class ImportPokemonsCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly PokemonRepository $pokemonRepository, private readonly HttpClientInterface $httpClient)
    {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addOption(
            'only-current',
            null,
            InputOption::VALUE_OPTIONAL,
            'Import only current pokemons',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->importCurrentPokemons($io);

        if ($input->getOption('only-current') === 'true') {
            $io->success('Done ! - ' . date('Y-m-d H:i:s'));
            return Command::SUCCESS;
        }

        $this->importPokemons();
        $this->importShiny();
        $this->importEnglishName();
        $this->importCurrentPokemons($io);
        $this->importSlugNames();

        $io->success('Done ! - ' . date('Y-m-d H:i:s'));

        return Command::SUCCESS;
    }

    private function importPokemons(): void
    {
        $browser = new HttpBrowser(HttpClient::create());

        foreach (range(1, 10) as $generation) {

            if ($generation === 1) {
                $url = 'https://www.pokebip.com/page/jeuxvideo/pokemon_go/pokemon';
            } else if ($generation === 10) {
                $url = 'https://www.pokebip.com/page/jeuxvideo/pokemon_go/pokemon/autres';
            } else {
                $url = 'https://www.pokebip.com/page/jeuxvideo/pokemon_go/pokemon/' . $generation . 'g';
            }

            $browser->request('GET', $url)
                ->filter('.bipcode tbody tr td:nth-child(1)')
                ->each(function (Crawler $node) use ($generation) {

                    if ($node->filter('em')->count() >= 1 && $node->filter('em')->text() !== 'Possède l\'apparence d\'un autre Pokémon') {
                        return;
                    }

                    if ($node->filter('span')->count() >= 1) {
                        $tdValue = str_replace($node->filter('span')->text(), '', $node->text());
                    } else {
                        $tdValue = $node->text();
                    }

                    if ($node->text() === '#132 MétamorphPossède l\'apparence d\'un autre Pokémon') {
                        $tdValue = str_replace($node->filter('em')->text(), '', $node->text());;
                    }

                    if (str_contains($node->text(), 'Flamoutan')) {
                        $tdValue = str_replace($node->filter('span')->text(), '', '#514 Flamoutan');
                    }

                    preg_match('#([0-9]{3,4}) (.*)#', $tdValue, $matches);
                    $pokemon = $this->entityManager->getRepository(Pokemon::class)->findOneBy(['number' => $matches[1]]);

                    if (!$pokemon) {
                        $pokemon = new Pokemon();
                        $pokemon->setNumber(intval($matches[1]));
                        $pokemon->setFrenchName($matches[2]);
                        $pokemon->setGeneration($generation . 'G');
                        $pokemon->setIsShiny(false);

                        $this->entityManager->persist($pokemon);
                        $this->entityManager->flush();
                    }
                });

        }

    }

    private function importShiny(): void
    {
        $browser = new HttpBrowser(HttpClient::create());

        foreach (range(1, 9) as $generation) {

            if ($generation === 1) {
                $url = 'https://www.pokebip.com/page/jeuxvideo/pokemon_go/pokemon_chromatiques';
            } else if ($generation === 9) {
                $url = 'https://www.pokebip.com/page/jeuxvideo/pokemon_go/chromatiques/autres';
            } else {
                $url = 'https://www.pokebip.com/page/jeuxvideo/pokemon_go/chromatiques/' . $generation . 'g';
            }

            $browser->request('GET', $url)
                ->filter('.bipcode tr td:nth-child(1)')
                ->each(function (Crawler $node) {

                    if (str_contains($node->text(), '#')) {
                        preg_match('#([0-9]{3,4}) (.*)#', $node->text(), $matches);
                        $pokemon = $this->entityManager->getRepository(Pokemon::class)->findOneBy(['number' => $matches[1]]);

                        if ($pokemon) {
                            $pokemon->setIsShiny(true);

                            $this->entityManager->persist($pokemon);
                            $this->entityManager->flush();
                        }
                    }
                });
        }
    }

    private function importEnglishName(): void
    {
        $client = HttpClient::create();
        $response = $client->request(
            'GET',
            'https://pokeapi.co/api/v2/pokemon?limit=10000'
        );

        foreach ($response->toArray()['results'] as $englishPokemon) {
            $id = explode('/', $englishPokemon['url'])[6];
            $pokemon = $this->entityManager->getRepository(Pokemon::class)->findOneBy(['number' => $id]);

            if ($pokemon && ($pokemon->getEnglishName() === null || $pokemon->getEnglishName() === '')) {
                $pokemon->setEnglishName(ucfirst($englishPokemon['name']));

                $this->entityManager->persist($pokemon);
                $this->entityManager->flush();
            }

        }

    }

    private function importCurrentPokemons(SymfonyStyle $io): void
    {
        $io->success('Updating actual pokemons...');

        $isActualPokemons = $this->entityManager->getRepository(Pokemon::class)->findBy(['isActual' => true]);
        foreach ($isActualPokemons as $pokemon) {
            $pokemon->setIsActual(false);

            $this->entityManager->persist($pokemon);
            $this->entityManager->flush();
        }

        $client = new Client();
        /** @var array<string, string> $pokemons */
        $pokemons = json_decode((string) $client->get('all_pokemons'), true);

        foreach ($pokemons as $pokemon) {
            $pokemonFound = $this->entityManager->getRepository(Pokemon::class)->findOneBy(['number' => $pokemon]);

            if ($pokemonFound) {
                $pokemonFound->setIsActual(true);

                $this->entityManager->persist($pokemonFound);
                $this->entityManager->flush();
            }
        }
    }

    private function importSlugNames(): void
    {
        /** @var Pokemon[] $pokemons */
        $pokemons = $this->pokemonRepository->getPokemonWithoutSlug();

        foreach($pokemons as $pokemon) {
            $request = $this->httpClient->request(
                'GET',
                sprintf('https://pokemon-go-api.github.io/pokemon-go-api/api/pokedex/id/%s.json', $pokemon->getNumber()),
            );
            $response = $request->toArray();

            $pokemon->setSlug(strtolower($response['formId']));

            $this->entityManager->flush();
        }
    }
}
