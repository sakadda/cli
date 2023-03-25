<?php

namespace Sakadda\Cli;

use Sakadda\Cli\Traits\UsesCommandFilterTrait;
use Sakadda\Cli\Traits\UsesCommandCustomMessagesTrait;
use Sakadda\Cli\Traits\InteractsWithIO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PackageListCommand extends Command
{
    use UsesCommandFilterTrait;
    use UsesCommandCustomMessagesTrait;
    use InteractsWithIO;

    public $input;

    public $output;

    public function __construct()
    {
        parent::__construct();

        $this->addFilterOptions('package');
    }

    protected function configure()
    {
        $this
            ->setName('package:list')
            ->setDescription('List all locally installed packages.');
    }

    /**
     * Execute the console command.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $rows = $this->getPackagesRows();

        if (count($rows)) {
            $this->createTable(
                'Packages',
                ['Package', 'Path', 'Is Local?', 'Is Enabled?', 'Is Loaded?'],
                $rows
            )?->render();
        } else {
            $this->failed('No packages found.');
        }

        return Command::SUCCESS;
    }

    /**
     * @return array
     */
    public function getPackagesRows(): array
    {
        $yes = $this->createTableCell('YES');
        $no = $this->createTableCell('NO', 'red-bold');

        // Handle is_local, is_enabled, is_loaded, and filter
        $is_local = $this->validateBoolean('local');
        $is_enabled = $this->validateBoolean('enabled');
        $is_loaded = $this->validateBoolean('loaded');
        $filter = $this->option('filter');

        $boilerplateGenerator = new \Sakadda\Cli\Services\BoilerplateGenerator();

        return $boilerplateGenerator
            ->getSummarizedPackages($filter, $is_local, $is_enabled, $is_loaded)
            ->map(function (array $arr, string $package) use ($yes, $no) {
                return [
                    $package,
                    $arr['path'],
                    $arr['is_local'] ? $yes : $no,
                    $arr['is_enabled'] ? $yes : $no,
                    $arr['is_loaded'] ? $yes : $no,
                ];
            })
            ->toArray();
    }
}
