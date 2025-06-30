<?php

namespace App\Command;

use App\Entity\Type;
use App\Entity\TypeEffectiveness;
use App\Repository\TypeRepository;
use App\Service\GameMasterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:update:types',
    description: 'Update all types',
)]
class UpdateTypesCommand extends Command
{
    public function __construct(
        private readonly GameMasterService $gameMasterService,
        private readonly TypeRepository $typeRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly HttpClientInterface $httpClient,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->updateTypes($io);
        $this->updateTypesEffectiveness($io);

        return Command::SUCCESS;
    }

    private function updateTypes(SymfonyStyle $io): void
    {
        $io->writeln('Updating types data...');

        $moves = $this->gameMasterService->getMoves();

        foreach ($moves as $move) {
            $type = $move['type'];

            $typeEntity = $this->typeRepository->findOneBy(['slug' => $type]);

            if (!$typeEntity instanceof Type) {
                $typeEntity = new Type();
                $typeEntity->setName(ucwords($type));
                $typeEntity->setSlug($type);

                $this->entityManager->persist($typeEntity);
                $this->entityManager->flush();
            }
        }

        $io->success('Pokémon types updated successfully!');
    }

    private function resetTable(): void
    {
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeStatement($platform->getTruncateTableSQL('type_effectiveness', true));
    }

    private function updateTypesEffectiveness(SymfonyStyle $io): void
    {
        $io->writeln('Updating types effectiveness data...');

        $this->resetTable();

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

        $io->success('Types effectiveness updated successfully!');
    }
}
