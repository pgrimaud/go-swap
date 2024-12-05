<?php

namespace App\Command;

use App\Entity\TypeEffectiveness;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:import-types-effectiveness',
    description: 'Import types effectiveness',
)]
class ImportTypesEffectivenessCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly HttpClientInterface    $httpClient,
        private readonly TypeRepository         $typeRepository
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->resetTable();
        $this->importTypesEffectiveness();

        $io->success('Done.');

        return Command::SUCCESS;
    }

    private function resetTable(): void
    {
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeStatement($platform->getTruncateTableSQL('type_effectiveness', true));
    }

    private function importTypesEffectiveness(): void
    {
        $response = $this->httpClient->request('GET', 'https://pogoapi.net/api/v1/type_effectiveness.json');

        foreach ($response->toArray() as $source => $effectiveness) {
            $sourceType = $this->typeRepository->findOneBy(['name' => $source]);

            foreach ($effectiveness as $target => $multiplier) {
                $targetType = $this->typeRepository->findOneBy(['name' => $target]);

                $typeEffectiveness = new TypeEffectiveness();
                $typeEffectiveness->setSourceType($sourceType);
                $typeEffectiveness->setTargetType($targetType);
                $typeEffectiveness->setMultiplier($multiplier);

                $this->entityManager->persist($typeEffectiveness);
            }
        }

        $this->entityManager->flush();
    }
}
