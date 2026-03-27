<?php

namespace Tablar\CrudGenerator\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Tablar\CrudGenerator\CrudServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            CrudServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('crud.stub_path', 'default');
        $app['config']->set('crud.layout', 'tablar::page');
        $app['config']->set('crud.model.namespace', 'App\Models');
        $app['config']->set('crud.controller.namespace', 'App\Http\Controllers');
        $app['config']->set('crud.model.unwantedColumns', [
            'id', 'password', 'email_verified_at', 'remember_token',
            'created_at', 'updated_at', 'deleted_at',
        ]);
    }
}
