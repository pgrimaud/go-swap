<?php

namespace App\Command;

use App\Entity\Pokemon;
use App\Helper\StringHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:download-pictures',
    description: 'Download pictures for normal and shiny Pokemons'
)]
class DownloadPicturesCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly HttpClientInterface $httpClient
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->getPicturesShiny();
        $this->getPicturesNormal();

        $this->io->success('Done ! - ' . date('Y-m-d H:i:s'));

        return Command::SUCCESS;
    }

    private function getPicturesNormal(): void
    {
        $pokemonRepository = $this->entityManager->getRepository(Pokemon::class)->findAll();
        $progressBar = new ProgressBar($this->io, count($pokemonRepository));
        $progressBar->start();

        $notFound = [];

        foreach ($pokemonRepository as $pokemon) {
            $progressBar->advance();

            if (file_exists(__DIR__ . '/../../public/images/normal/' . $pokemon->getNumber() . '.png')
                || $pokemon->getNormalPicture() !== null
            ) {
                continue;
            }

            $url = 'https://www.media.pokekalos.fr/img/pokemon/pokego/' . mb_strtolower(StringHelper::cleanAccents($pokemon->getFrenchName())) . '.png';

            try{
                $request = $this->httpClient->request('GET', $url);
                $image = $request->getContent();
            } catch (\Exception $e) {
                $notFound[] = [$pokemon->getNumber(), $pokemon->getFrenchName()];
                continue;
            }

            $fp = fopen(__DIR__ . '/../../public/images/normal/' . $pokemon->getNumber() . '.png', 'w');
            if ($fp && $image) {
                fwrite($fp, $image);
                fclose($fp);
            }
            $pokemon->setNormalPicture($pokemon->getNumber() . '.png');
            $this->entityManager->persist($pokemon);
            $this->entityManager->flush();
        }
        $progressBar->finish();

        $this->io->newLine(2);

        $table = new Table($this->io);
        $table
            ->setHeaders(['Number', 'Name'])
            ->setRows($notFound)
        ;
        $table->render();
    }

    private function getPicturesShiny(): void
    {
        $pokemonRepository = $this->entityManager->getRepository(Pokemon::class)->findBy(['isShiny' => true]);
        $progressBar = new ProgressBar($this->io, count($pokemonRepository));
        $progressBar->start();

        $notFound = [];

        foreach ($pokemonRepository as $pokemon) {
            $progressBar->advance();

            if (file_exists(__DIR__ . '/../../public/images/shiny/' . $pokemon->getNumber() . '.png')
                || $pokemon->getShinyPicture() !== null
            ) {
                continue;
            }

            $url = 'https://www.media.pokekalos.fr/img/pokemon/pokego/' . mb_strtolower(StringHelper::cleanAccents($pokemon->getFrenchName())) . '-s.png';

            try{
                $request = $this->httpClient->request('GET', $url);
                $image = $request->getContent();
            } catch (\Exception $e) {
                $notFound[] = [$pokemon->getNumber(), $pokemon->getFrenchName()];
                continue;
            }

            $fp = fopen(__DIR__ . '/../../public/images/shiny/' . $pokemon->getNumber() . '.png', 'w');
            if ($fp && $image) {
                fwrite($fp, $image);
                fclose($fp);
            }
            $pokemon->setShinyPicture($pokemon->getNumber() . '.png');
            $this->entityManager->persist($pokemon);
            $this->entityManager->flush();
        }
        $progressBar->finish();

        $this->io->newLine(2);

        $table = new Table($this->io);
        $table
            ->setHeaders(['Number', 'Name'])
            ->setRows($notFound)
        ;
        $table->render();
    }
}
