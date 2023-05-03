<?php

namespace App\Command;

use App\Entity\Pokemon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

#[AsCommand(
    name: 'app:import-pokemons',
    description: 'Import pokemons to database',
)]
class ImportPokemonsCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->importPokemons();
        $this->importShiny();
        $io->success('Done !');

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
                ->filter(".bipcode tbody tr td:nth-child(1)")
                ->each(function (Crawler $node) use ($generation) {
                    if ($node->filter("em")->count() >= 1) {
                        return;
                    }
                    if ($node->filter("span")->count() >= 1) {
                        $tdValue = str_replace($node->filter("span")->text(), "", $node->text());
                    } else {
                        $tdValue = $node->text();
                    }

                    preg_match('#([0-9]{3,4}) (.*)#', $tdValue, $matches);
                    $pokemon = $this->entityManager->getRepository(Pokemon::class)->findOneBy(['number' => $matches[1]]);
                    if ($pokemon) {
                        return;
                    } else {
                        $pokemon = new Pokemon();
                        $pokemon->setNumber(intval($matches[1]));
                        $pokemon->setFrenchName($matches[2]);
                        $pokemon->setGeneration($generation . "G");
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
                ->filter(".bipcode tr td:nth-child(1)")
                ->each(function (Crawler $node) {

                    if (str_contains($node->text(), "#")) {
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
}
