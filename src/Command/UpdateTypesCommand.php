<?php

namespace App\Command;

use App\Entity\Type;
use App\Repository\TypeRepository;
use App\Service\GameMasterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

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

        return Command::SUCCESS;
    }
}
