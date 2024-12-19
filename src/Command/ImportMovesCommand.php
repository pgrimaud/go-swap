<?php

namespace App\Command;

use App\Entity\Move;
use App\Entity\Type;
use App\Repository\MoveRepository;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function PHPUnit\Framework\throwException;

#[AsCommand(
    name: 'app:import-moves',
    description: 'Import moves from pogoapi.net',
)]
class ImportMovesCommand extends Command
{
    private array $types;
    private SymfonyStyle $io;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly EntityManagerInterface $entityManager,
        private readonly TypeRepository $typeRepository,
        private readonly MoveRepository $moveRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->types = $this->typeRepository->findAll();

        $this->importFastMoves();
        $this->importChargedMoves();
        $this->importCurrentPokemonMoves();

        return Command::SUCCESS;
    }

    private function importFastMoves(): void
    {
        $this->io->success('Importing fast moves...');

        $request = $this->client->request('GET', 'https://pogoapi.net/api/v1/pvp_fast_moves.json');
        $response = $request->toArray();

        foreach ($response as $fastMove) {

            $move = $this->moveRepository->findOneBy([
                'apiId' => $fastMove['move_id'],
                'attackType' => Move::FAST_MOVE
            ]);

            if (!$move instanceof Move) {
                $move = new Move();
            }

            $move->setName($fastMove['name']);
            $move->setAttackType(Move::FAST_MOVE);
            $move->setApiId($fastMove['move_id']);
            $move->setPower($fastMove['power']);
            $move->setEnergyDelta($fastMove['energy_delta']);
            $move->setTurnDuration($fastMove['turn_duration']);
            $move->setType($this->getType($fastMove['type']));

            $this->entityManager->persist($move);
        }

        $this->entityManager->flush();
    }

    private function importChargedMoves(): void
    {
        $this->io->success('Importing charged moves...');

        $request = $this->client->request('GET', 'https://pogoapi.net/api/v1/pvp_charged_moves.json');
        $response = $request->toArray();

        foreach ($response as $fastMove) {

            $move = $this->moveRepository->findOneBy([
                'apiId' => $fastMove['move_id'],
                'attackType' => Move::CHARGED_MOVE
            ]);

            if (!$move instanceof Move) {
                $move = new Move();
            }

            $move->setName($fastMove['name']);
            $move->setAttackType(Move::CHARGED_MOVE);
            $move->setApiId($fastMove['move_id']);
            $move->setPower($fastMove['power']);
            $move->setEnergyDelta($fastMove['energy_delta']);
            $move->setTurnDuration($fastMove['turn_duration']);
            $move->setType($this->getType($fastMove['type']));

            $this->entityManager->persist($move);
        }

        $this->entityManager->flush();
    }

    /**
     * @param string $typeName
     * @return Type
     */
    private function getType(string $typeName): Type
    {
        foreach ($this->types as $type) {
            if ($type->getName() === $typeName) {
                return $type;
            }
        }

        $this->io->error('Type not found: ' . $typeName);

        throw new \Exception('Type not found: ' . $typeName);
    }

    private function importCurrentPokemonMoves(): void
    {
        $this->io->success('Importing current pokemon moves...');

        $request = $this->client->request('GET', 'https://pogoapi.net/api/v1/current_pokemon_moves.json');
        $response = $request->toArray();

        foreach($response as $pokemon) {
            if($pokemon['pokemon_name'] === 'Oricorio') {
                dump($pokemon);
            }
        }

    }
}
