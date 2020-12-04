<?php

namespace Arsoft\Module\Tests;

use Arsoft\Modules\arsoftModuleServiceProvider;

class testCase extends \Orchestra\Testbench\TestCase
{
  public function setUp(): void
  {
    parent::setUp();
    // additional setup
  }

  protected function getPackageProviders($app)
  {
    return [
        ModuleServiceProvider::class,
    ];
  }

  protected function getEnvironmentSetUp($app)
  {
    // perform environment setup
  }
}