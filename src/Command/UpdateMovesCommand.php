<?php

namespace App\Command;

use App\Entity\Move;
use App\Entity\Type;
use App\Helper\HashHelper;
use App\Repository\MoveRepository;
use App\Repository\TypeRepository;
use App\Service\GameMasterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update:moves',
    description: 'Update all pokémon moves',
)]
class UpdateMovesCommand extends AbstractSuggestCommand
{
    /** @var array<Type> */
    private array $types = [];

    public function __construct(
        private readonly GameMasterService $gameMasterService,
        private readonly MoveRepository $moveRepository,
        private readonly TypeRepository $typeRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->writeln('Updating moves data...');

        $moves = $this->gameMasterService->getMoves();

        foreach ($moves as $move) {
            // skip special moves
            if ('TRANSFORM' === $move['moveId']) {
                continue;
            }

            $moveEntity = $this->moveRepository->findOneBy(['slug' => strtolower($move['moveId'])]);

            if (!$moveEntity instanceof Move || $moveEntity->getHash() !== HashHelper::fromMove($move)) {
                $moveEntity = $moveEntity ?? new Move();
                $moveEntity->setName($move['name']);
                $moveEntity->setSlug(strtolower($move['moveId']));
                $moveEntity->setType($this->getType($io, (string) $move['type']));
                $moveEntity->setPower($move['power']);
                $moveEntity->setEnergy($move['energy']);
                $moveEntity->setEnergyGain($move['energyGain']);
                $moveEntity->setCooldown($move['cooldown']);
                $moveEntity->setBuffAttack($move['buffs'][0] ?? null);
                $moveEntity->setBuffDefense($move['buffs'][1] ?? null);
                $moveEntity->setBuffTarget($move['buffTarget'] ?? null);
                $moveEntity->setBuffChance(isset($move['buffApplyChance']) ? (float) $move['buffApplyChance'] : null);
                $moveEntity->setCategory($move['archetype'] ?? '');
                $moveEntity->setClass($move['energyGain'] > 0 ? Move::FAST_MOVE : Move::CHARGED_MOVE);
                $moveEntity->setHash(HashHelper::fromMove($move));

                $this->entityManager->persist($moveEntity);
                $this->entityManager->flush();
            }
        }

        $io->success('Pokémon moves updated successfully!');

        return Command::SUCCESS;
    }

    private function getType(SymfonyStyle $io, string $typeAsString): Type
    {
        if (array_key_exists($typeAsString, $this->types)) {
            return $this->types[$typeAsString];
        }

        $type = $this->typeRepository->findOneBy(['slug' => $typeAsString]);

        if (!$type instanceof Type) {
            $io->error('Type not found : '.$typeAsString);
            $this->runParentCommand($io, 'app:update:types');

            return $this->getType($io, $typeAsString);
        }

        return $type;
    }
}
