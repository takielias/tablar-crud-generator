<?php

namespace Tablar\CrudGenerator\Tests\Unit;

use Tablar\CrudGenerator\Tests\TestCase;

class CrudServiceProviderTest extends TestCase
{
    public function testCrudCommandIsRegistered(): void
    {
        $commands = \Artisan::all();

        $this->assertArrayHasKey('make:crud', $commands);
    }

    protected function tearDown(): void
    {
        if (file_exists(config_path('crud.php'))) {
            unlink(config_path('crud.php'));
        }

        parent::tearDown();
    }

    public function testConfigCanBePublished(): void
    {
        $this->artisan('vendor:publish', ['--tag' => 'crud', '--force' => true])
            ->assertExitCode(0);
    }

    public function testConfigValuesAreLoaded(): void
    {
        $this->assertEquals('App\Models', config('crud.model.namespace'));
        $this->assertEquals('App\Http\Controllers', config('crud.controller.namespace'));
        $this->assertEquals('tablar::page', config('crud.layout'));
        $this->assertIsArray(config('crud.model.unwantedColumns'));
        $this->assertContains('id', config('crud.model.unwantedColumns'));
        $this->assertContains('password', config('crud.model.unwantedColumns'));
    }
}
