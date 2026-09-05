<?php

namespace Tablar\CrudGenerator\Tests\Unit;

use Tablar\CrudGenerator\Tests\TestCase;

class LayoutStubTest extends TestCase
{
    public function testLayoutStubTargetsViteAndBootstrapFive(): void
    {
        $stub = file_get_contents(__DIR__.'/../../src/stubs/layouts/app.stub');

        $this->assertStringContainsString('@vite(', $stub);
        $this->assertStringNotContainsString("asset('js/app.js')", $stub);
        $this->assertStringNotContainsString("asset('css/app.css')", $stub);

        foreach (['data-toggle=', 'data-target=', 'mr-auto', 'ml-auto'] as $bootstrapFour) {
            $this->assertStringNotContainsString($bootstrapFour, $stub);
        }
    }

    public function testLayoutStubYieldsTheSectionTheViewsDefine(): void
    {
        $stub = file_get_contents(__DIR__.'/../../src/stubs/layouts/app.stub');
        $index = file_get_contents(__DIR__.'/../../src/stubs/views/index.stub');

        $this->assertStringContainsString("@section('title')", $index);
        $this->assertStringContainsString("@yield('title')", $stub);
    }
}
