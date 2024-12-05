<?php

namespace App\Command;

use App\Service\OpenAI;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test',
    description: 'This is just a test command',
)]
class TestCommand extends Command
{
    public function __construct(private readonly OpenAI $openAI)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $response = $this->openAI->getTextFromPicture(__DIR__ . '/../../public/images/test.png');

        dump($response);

        $io->success('Done');

        return Command::SUCCESS;
    }
}
