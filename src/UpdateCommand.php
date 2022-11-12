<?php

namespace Sakadda\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateCommand extends Command
{
    use Concerns\RunsCommands;

    protected $input;
    protected $output;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('update')
            ->setDescription('Update the current directory\'s Sakadda install to the latest version');
    }

    /**
     * Execute the command.
     *
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $please = new Please($output);

        if ($please->isV2()) {
            $please->run('update');

            return 0;
        }

        $output->writeln(PHP_EOL.'<comment>NOTE: If you have previously updated using the CP, you may need to update the version in your composer.json before running this update!</comment>'.PHP_EOL);

        $command = $this->updateCommand();

        $this->runCommand($command);

        return 0;
    }

    /**
     * Determine the update command.
     *
     * @return string
     */
    protected function updateCommand()
    {
        $helper = $this->getHelper('question');

        $options = [
            'Update Sakadda and its dependencies [composer update sakadda/sakadda --with-dependencies]',
            'Update all project dependencies [composer update]',
        ];

        $question = new ChoiceQuestion('How would you like to update Sakadda?', $options, 0);

        $selection = $helper->ask($this->input, new SymfonyStyle($this->input, $this->output), $question);

        return strpos($selection, 'sakadda/sakadda --with-dependencies')
            ? 'composer update sakadda/sakadda --with-dependencies'
            : 'composer update';
    }
}
