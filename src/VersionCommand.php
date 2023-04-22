<?php

namespace Sakadda\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sakadda\Cli\Concerns\RenderAscii;

class VersionCommand extends Command
{
    use RenderAscii;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('version')
            ->setDescription('Get the version of Sakadda installed in the current directory');
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
        $this->renderPowergridAscii();

        $sain = new Sain($output);

        $sain->run('--version');

        return 0;
    }
}
