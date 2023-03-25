<?php

use Sakadda\Cli\Services\BoilerplateGenerator;

if (! function_exists('boilerplateGenerator')) {
    /**
     * @return BoilerplateGenerator
     */
    function boilerplateGenerator()
    {
        // $this->app->singleton('boilerplate-generator', fn () => new BoilerplateGenerator());
    }
}
