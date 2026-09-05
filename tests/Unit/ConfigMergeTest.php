<?php

namespace Tablar\CrudGenerator\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Tablar\CrudGenerator\CrudServiceProvider;

class ConfigMergeTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [CrudServiceProvider::class];
    }

    public function testConfigIsAvailableWithoutPublishing(): void
    {
        $this->assertSame('tablar::page', config('crud.layout'));
        $this->assertSame('App\Models', config('crud.model.namespace'));
        $this->assertIsArray(config('crud.model.unwantedColumns'));
    }
}
