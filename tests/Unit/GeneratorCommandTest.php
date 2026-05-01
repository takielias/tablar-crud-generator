<?php

namespace Tablar\CrudGenerator\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use Tablar\CrudGenerator\Tests\TestCase;

class GeneratorCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('crud.model.namespace', 'App\Models');
        $this->app['config']->set('crud.controller.namespace', 'App\Http\Controllers');
        $this->app['config']->set('crud.layout', 'tablar::page');
    }

    public function testStubFilesExist(): void
    {
        $stubPath = __DIR__ . '/../../src/stubs/';
        $files = new Filesystem();

        $this->assertTrue($files->exists($stubPath . 'Controller.stub'));
        $this->assertTrue($files->exists($stubPath . 'Model.stub'));
        $this->assertTrue($files->exists($stubPath . 'views/index.stub'));
        $this->assertTrue($files->exists($stubPath . 'views/create.stub'));
        $this->assertTrue($files->exists($stubPath . 'views/edit.stub'));
        $this->assertTrue($files->exists($stubPath . 'views/show.stub'));
        $this->assertTrue($files->exists($stubPath . 'views/form.stub'));
        $this->assertTrue($files->exists($stubPath . 'views/form-field.stub'));
        $this->assertTrue($files->exists($stubPath . 'views/view-field.stub'));
        $this->assertTrue($files->exists($stubPath . 'layouts/app.stub'));
    }

    public function testControllerStubContainsRequiredPlaceholders(): void
    {
        $stub = file_get_contents(__DIR__ . '/../../src/stubs/Controller.stub');

        $this->assertStringContainsString('{{controllerNamespace}}', $stub);
        $this->assertStringContainsString('{{modelNamespace}}', $stub);
        $this->assertStringContainsString('{{modelName}}', $stub);
        $this->assertStringContainsString('{{modelNamePluralLowerCase}}', $stub);
        $this->assertStringContainsString('{{modelNameLowerCase}}', $stub);
        $this->assertStringContainsString('{{modelRoute}}', $stub);
        $this->assertStringContainsString('{{modelView}}', $stub);
    }

    public function testModelStubContainsRequiredPlaceholders(): void
    {
        $stub = file_get_contents(__DIR__ . '/../../src/stubs/Model.stub');

        $this->assertStringContainsString('{{modelNamespace}}', $stub);
        $this->assertStringContainsString('{{modelName}}', $stub);
        $this->assertStringContainsString('{{fillable}}', $stub);
        $this->assertStringContainsString('{{rules}}', $stub);
        $this->assertStringContainsString('{{relations}}', $stub);
        $this->assertStringContainsString('{{properties}}', $stub);
        $this->assertStringContainsString('{{softDeletesNamespace}}', $stub);
        $this->assertStringContainsString('{{softDeletes}}', $stub);
    }

    public function testFormFieldStubUsesFormFacade(): void
    {
        $stub = file_get_contents(__DIR__ . '/../../src/stubs/views/form-field.stub');

        $this->assertStringContainsString('Form::label', $stub);
        $this->assertStringContainsString('Form::text', $stub);
        $this->assertStringContainsString('{{column}}', $stub);
        $this->assertStringContainsString('{{modelNameLowerCase}}', $stub);
        $this->assertStringContainsString('is-invalid', $stub);
    }

    public function testIndexStubContainsTableStructure(): void
    {
        $stub = file_get_contents(__DIR__ . '/../../src/stubs/views/index.stub');

        $this->assertStringContainsString('{{tableHeader}}', $stub);
        $this->assertStringContainsString('{{tableBody}}', $stub);
        $this->assertStringContainsString('{{modelRoute}}', $stub);
        $this->assertStringContainsString('{{modelNamePluralLowerCase}}', $stub);
        $this->assertStringContainsString('@forelse', $stub);
        $this->assertStringContainsString('@endforelse', $stub);
        $this->assertStringContainsString('pagination', $stub);
    }

    public function testViewFieldStubContainsRequiredPlaceholders(): void
    {
        $stub = file_get_contents(__DIR__ . '/../../src/stubs/views/view-field.stub');

        $this->assertStringContainsString('{{title}}', $stub);
        $this->assertStringContainsString('{{column}}', $stub);
        $this->assertStringContainsString('{{modelNameLowerCase}}', $stub);
    }

    public function testCrudCommandFailsForNonExistentTable(): void
    {
        $this->artisan('make:crud', ['name' => 'non_existent_table_xyz'])
            ->expectsOutput('`non_existent_table_xyz` table not exist')
            ->assertExitCode(1);
    }
}
