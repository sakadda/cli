<?php

namespace Sakadda\Cli\Tests\Feature;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sakadda\Cli\NewCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class NewCommandIntegrationTest extends TestCase
{
    protected $scaffoldName;
    protected $scaffoldDirectory;

    /** @test */
    public function it_can_scaffold_a_new_sakadda_app()
    {
        $this->assertAppNotExists();

        $statusCode = $this->scaffoldNewApp();

        $this->assertSame(0, $statusCode);
        $this->assertBasicAppScaffolded();
    }

    /** @test */
    public function it_fails_if_application_folder_already_exists()
    {
        mkdir($this->appPath());

        $this->assertRuntimeException(function () {
            $this->scaffoldNewApp();
        });

        $this->assertFileExists($this->appPath(''));
        $this->assertFileDoesNotExist($this->appPath('vendor'));
        $this->assertFileDoesNotExist($this->appPath('.env'));
        $this->assertFileDoesNotExist($this->appPath('artisan'));
        $this->assertFileDoesNotExist($this->appPath('please'));
    }

    protected function assertRuntimeException($callback)
    {
        $error = false;

        try {
            $callback();
        } catch (RuntimeException $exception) {
            $error = true;
        }

        $this->assertTrue($error);
    }

    protected function appPath($path = null)
    {
        if ($path) {
            return $this->scaffoldDirectory.'/'.$path;
        }

        return $this->scaffoldDirectory;
    }

    protected  function scaffoldNewApp($args = [], $scaffoldName = NULL)
    {
        $app = new Application('Sakadda Installer');

        $app->add(new NewCommand);

        $tester = new CommandTester($app->find('new'));

        $args = array_merge(['name' => $scaffoldName], $args);

        $statusCode = $tester->execute($args);

        return $statusCode;
    }

    protected function assertBasicAppScaffolded()
    {
        $this->assertFileExists($this->appPath('vendor'));
        $this->assertFileExists($this->appPath('.env'));
        $this->assertFileExists($this->appPath('artisan'));
        $this->assertFileExists($this->appPath('please'));

        $envFile = file_get_contents($this->appPath('.env'));
        $this->assertStringContainsString('APP_URL=http://my-app.test', $envFile);
        $this->assertStringContainsString('DB_DATABASE=my_app', $envFile);
    }

    protected function assertAppNotExists()
    {
        $this->assertFileDoesNotExist($this->appPath(''));
    }
}
