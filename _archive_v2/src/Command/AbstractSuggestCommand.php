<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractSuggestCommand extends Command
{
    protected function runParentCommand(SymfonyStyle $io, string $command): void
    {
        $question = new ConfirmationQuestion(
            sprintf(
                'Would you like to run %s command? (y/n)',
                $command
            )
        );
        $response = $io->askQuestion($question);

        if (true === $response) {
            /** @var Application $application */
            $application = $this->getApplication();

            $command = $application->find($command);

            $input = new ArrayInput([
                'command' => $command,
            ]);

            $command->run($input, new NullOutput());
        }
    }
}
