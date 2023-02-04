<?php

namespace Sakadda\Cli;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

class Sain
{
    protected $output;

    protected $cwd;

    /**
     * Instantiate Sakadda `sain` command wrapper.
     *
     * @param  OutputInterface  $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Get or set current working directory.
     *
     * @param  mixed  $cwd
     * @return mixed
     */
    public function cwd($cwd = null)
    {
        if (func_num_args() === 0) {
            return $this->cwd ?? getcwd();
        }

        $this->cwd = $cwd;

        return $this;
    }

    /**
     * Run sain command.
     *
     * @param  mixed  $commandParts
     * @return int
     */
    public function run(...$commandParts)
    {
        if (! is_file($this->cwd().'/sain')) {
            throw new \RuntimeException('This does not appear to be a Sakadda project.');
        }

        $process = (new Process(array_merge([PHP_BINARY, 'sain'], $commandParts)))
            ->setTimeout(null);

        if ($this->cwd) {
            $process->setWorkingDirectory($this->cwd);
        }

        try {
            $process->setTty(true);
        } catch (RuntimeException $e) {
            // TTY not supported. Move along.
        }

        $process->run(function ($type, $line) {
            $this->output->write($line);
        });

        return $process->getExitCode();
    }
}
