<?php

namespace App\Command;

use App\Entity\Pokemon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;

#[AsCommand(
    name: 'app:download-pictures',
    description: 'Download pictures for normal and shiny Pokemons'
)]
class DownloadPicturesCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->getPicturesShiny();
        $this->getPicturesNormal();

        $this->io->success('Done !');

        return Command::SUCCESS;
    }

    private function getPicturesNormal(): void
    {
        $pokemonRepository = $this->entityManager->getRepository(Pokemon::class)->findAll();
        $progressBar = new ProgressBar($this->io, count($pokemonRepository));
        $progressBar->start();

        foreach ($pokemonRepository as $pokemon) {
            $progressBar->advance();

            if (file_exists("public/images/normal/" . $pokemon->getNumber() . ".png")) {
                continue;
            }

            $url = 'https://www.pokebip.com/pages/icones/poke/GO/' . $pokemon->getNumber() . '.png';
            $image = file_get_contents($url);
            $fp = fopen("public/images/normal/" . $pokemon->getNumber() . ".png", "w");
            if ($fp && $image) {
                fwrite($fp, $image);
                fclose($fp);
            }
        }
        $progressBar->finish();
    }

    private function getPicturesShiny(): void
    {
        $pokemonRepository = $this->entityManager->getRepository(Pokemon::class)->findBy(['isShiny' => true]);
        $progressBar = new ProgressBar($this->io, count($pokemonRepository));
        $progressBar->start();

        foreach ($pokemonRepository as $pokemon) {
            $progressBar->advance();

            if (file_exists("public/images/shiny/" . $pokemon->getNumber() . ".png")) {
                continue;
            }

            $endUrl = match ($pokemon->getNumber()) {
                201 => 'f.png',
                327 => 'p1.png',
                710, 711 => 'fe.png',
                default => '.png',
            };
            $url = 'https://www.pokebip.com/pages/icones/pokechroma/GO/' . $pokemon->getNumber() . $endUrl;

            $image = file_get_contents($url);
            $fp = fopen("public/images/shiny/" . $pokemon->getNumber() . ".png", "w");
            if ($fp && $image) {
                fwrite($fp, $image);
                fclose($fp);
            }
        }
        $progressBar->finish();
    }
}
