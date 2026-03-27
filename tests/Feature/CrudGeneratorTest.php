<?php

namespace Tablar\CrudGenerator\Tests\Feature;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Tablar\CrudGenerator\Tests\TestCase;

/**
 * Feature tests for the CRUD generator command.
 *
 * These tests require a MySQL connection because the package uses
 * MySQL-specific queries (SHOW COLUMNS, SHOW KEYS, INFORMATION_SCHEMA).
 *
 * Start DDEV or set DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD to run.
 */
class CrudGeneratorTest extends TestCase
{
    private Filesystem $files;
    private string $testTable = 'crud_test_posts';
    private static bool $mysqlAvailable = false;
    private static bool $checkedMysql = false;

    private static function checkMysqlAvailability(): bool
    {
        if (self::$checkedMysql) {
            return self::$mysqlAvailable;
        }
        self::$checkedMysql = true;

        $host = $_ENV['DB_HOST'] ?? 'db';
        $port = (int) ($_ENV['DB_PORT'] ?? 3306);
        $connection = @fsockopen($host, $port, $errno, $errstr, 2);
        if ($connection) {
            fclose($connection);
            self::$mysqlAvailable = true;
        }

        return self::$mysqlAvailable;
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        if (self::checkMysqlAvailability()) {
            $app['config']->set('database.default', 'mysql');
            $app['config']->set('database.connections.mysql', [
                'driver' => 'mysql',
                'host' => env('DB_HOST', 'db'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'db'),
                'username' => env('DB_USERNAME', 'db'),
                'password' => env('DB_PASSWORD', 'db'),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
            ]);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$mysqlAvailable) {
            $this->markTestSkipped('MySQL not available — feature tests require MySQL (SHOW COLUMNS, INFORMATION_SCHEMA).');
        }

        $this->files = new Filesystem();

        Schema::dropIfExists($this->testTable);
        Schema::create($this->testTable, function ($table) {
            $table->id();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('slug');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        if (self::$mysqlAvailable) {
            try {
                Schema::dropIfExists($this->testTable);
                Schema::dropIfExists('crud_test_soft_items');
            } catch (\Exception $e) {
                // Ignore cleanup errors
            }

            $this->cleanGeneratedFiles('CrudTestPost');
            $this->cleanGeneratedFiles('Article');
            $this->cleanGeneratedFiles('CrudTestSoftItem');
        }

        parent::tearDown();
    }

    private function cleanGeneratedFiles(string $modelName): void
    {
        $controllerPath = app_path("Http/Controllers/{$modelName}Controller.php");
        $modelPath = app_path("Models/{$modelName}.php");

        if ($this->files->exists($controllerPath)) {
            $this->files->delete($controllerPath);
        }

        if ($this->files->exists($modelPath)) {
            $this->files->delete($modelPath);
        }

        $patterns = [
            strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $modelName)),
            strtolower($modelName),
        ];

        foreach ($patterns as $dir) {
            $viewPath = resource_path("views/{$dir}");
            if ($this->files->isDirectory($viewPath)) {
                $this->files->deleteDirectory($viewPath);
            }
        }
    }

    private function getOriginalRoutes(): string
    {
        $routesPath = base_path('routes/web.php');

        return $this->files->exists($routesPath)
            ? $this->files->get($routesPath)
            : '';
    }

    private function restoreRoutesFile(string $originalContent): void
    {
        $this->files->put(base_path('routes/web.php'), $originalContent);
    }

    public function testCrudGeneratorCreatesController(): void
    {
        $originalRoutes = $this->getOriginalRoutes();

        $this->artisan('make:crud', ['name' => $this->testTable])
            ->assertSuccessful();

        $controllerPath = app_path('Http/Controllers/CrudTestPostController.php');
        $this->assertFileExists($controllerPath);

        $content = $this->files->get($controllerPath);
        $this->assertStringContainsString('class CrudTestPostController', $content);
        $this->assertStringContainsString('use App\Models\CrudTestPost;', $content);
        $this->assertStringContainsString('function index()', $content);
        $this->assertStringContainsString('function create()', $content);
        $this->assertStringContainsString('function store(', $content);
        $this->assertStringContainsString('function show(', $content);
        $this->assertStringContainsString('function edit(', $content);
        $this->assertStringContainsString('function update(', $content);
        $this->assertStringContainsString('function destroy(', $content);

        $this->restoreRoutesFile($originalRoutes);
    }

    public function testCrudGeneratorCreatesModel(): void
    {
        $originalRoutes = $this->getOriginalRoutes();

        $this->artisan('make:crud', ['name' => $this->testTable])
            ->assertSuccessful();

        $modelPath = app_path('Models/CrudTestPost.php');
        $this->assertFileExists($modelPath);

        $content = $this->files->get($modelPath);
        $this->assertStringContainsString('class CrudTestPost extends Model', $content);
        $this->assertStringContainsString("'title'", $content);
        $this->assertStringContainsString("'body'", $content);
        $this->assertStringContainsString("'slug'", $content);
        $this->assertStringContainsString("'is_published'", $content);
        $this->assertStringContainsString('$fillable', $content);
        $this->assertStringContainsString('$rules', $content);

        $this->restoreRoutesFile($originalRoutes);
    }

    public function testModelExcludesUnwantedColumns(): void
    {
        $originalRoutes = $this->getOriginalRoutes();

        $this->artisan('make:crud', ['name' => $this->testTable])
            ->assertSuccessful();

        $modelPath = app_path('Models/CrudTestPost.php');
        $content = $this->files->get($modelPath);

        $fillableMatch = [];
        preg_match('/\$fillable\s*=\s*\[([^\]]*)\]/', $content, $fillableMatch);
        $fillableContent = $fillableMatch[1] ?? '';

        $this->assertStringNotContainsString("'id'", $fillableContent);
        $this->assertStringNotContainsString("'created_at'", $fillableContent);
        $this->assertStringNotContainsString("'updated_at'", $fillableContent);

        $this->restoreRoutesFile($originalRoutes);
    }

    public function testModelGeneratesRequiredValidationRules(): void
    {
        $originalRoutes = $this->getOriginalRoutes();

        $this->artisan('make:crud', ['name' => $this->testTable])
            ->assertSuccessful();

        $modelPath = app_path('Models/CrudTestPost.php');
        $content = $this->files->get($modelPath);

        $this->assertStringContainsString("'title' => 'required'", $content);
        $this->assertStringContainsString("'slug' => 'required'", $content);

        $this->restoreRoutesFile($originalRoutes);
    }

    public function testNullableColumnsAreNotRequired(): void
    {
        $originalRoutes = $this->getOriginalRoutes();

        $this->artisan('make:crud', ['name' => $this->testTable])
            ->assertSuccessful();

        $modelPath = app_path('Models/CrudTestPost.php');
        $content = $this->files->get($modelPath);

        $this->assertStringNotContainsString("'body' => 'required'", $content);

        $this->restoreRoutesFile($originalRoutes);
    }

    public function testCrudGeneratorCreatesViews(): void
    {
        $originalRoutes = $this->getOriginalRoutes();

        $this->artisan('make:crud', ['name' => $this->testTable])
            ->assertSuccessful();

        $viewPath = resource_path('views/crud-test-post');

        $this->assertFileExists($viewPath . '/index.blade.php');
        $this->assertFileExists($viewPath . '/create.blade.php');
        $this->assertFileExists($viewPath . '/edit.blade.php');
        $this->assertFileExists($viewPath . '/show.blade.php');
        $this->assertFileExists($viewPath . '/form.blade.php');

        $this->restoreRoutesFile($originalRoutes);
    }

    public function testIndexViewContainsTableColumns(): void
    {
        $originalRoutes = $this->getOriginalRoutes();

        $this->artisan('make:crud', ['name' => $this->testTable])
            ->assertSuccessful();

        $content = $this->files->get(resource_path('views/crud-test-post/index.blade.php'));

        $this->assertStringContainsString('<th>Title</th>', $content);
        $this->assertStringContainsString('<th>Body</th>', $content);
        $this->assertStringContainsString('<th>Slug</th>', $content);
        $this->assertStringContainsString('$crudTestPost->title', $content);
        $this->assertStringContainsString('$crudTestPost->body', $content);
        $this->assertStringContainsString('$crudTestPost->slug', $content);

        $this->restoreRoutesFile($originalRoutes);
    }

    public function testFormViewContainsFormFacadeCalls(): void
    {
        $originalRoutes = $this->getOriginalRoutes();

        $this->artisan('make:crud', ['name' => $this->testTable])
            ->assertSuccessful();

        $content = $this->files->get(resource_path('views/crud-test-post/form.blade.php'));

        $this->assertStringContainsString('Form::label', $content);
        $this->assertStringContainsString('Form::text', $content);
        $this->assertStringContainsString('title', $content);
        $this->assertStringContainsString('body', $content);
        $this->assertStringContainsString('slug', $content);

        $this->restoreRoutesFile($originalRoutes);
    }

    public function testShowViewContainsViewFields(): void
    {
        $originalRoutes = $this->getOriginalRoutes();

        $this->artisan('make:crud', ['name' => $this->testTable])
            ->assertSuccessful();

        $content = $this->files->get(resource_path('views/crud-test-post/show.blade.php'));

        $this->assertStringContainsString('$crudTestPost->title', $content);
        $this->assertStringContainsString('$crudTestPost->body', $content);
        $this->assertStringContainsString('$crudTestPost->slug', $content);

        $this->restoreRoutesFile($originalRoutes);
    }

    public function testCrudGeneratorAppendsRoute(): void
    {
        $originalRoutes = $this->getOriginalRoutes();

        $this->artisan('make:crud', ['name' => $this->testTable])
            ->assertSuccessful();

        $content = $this->files->get(base_path('routes/web.php'));
        $this->assertStringContainsString('CrudTestPostController::class', $content);

        $this->restoreRoutesFile($originalRoutes);
    }

    public function testCrudGeneratorWithCustomRoute(): void
    {
        $originalRoutes = $this->getOriginalRoutes();

        $this->artisan('make:crud', [
            'name' => $this->testTable,
            '--route' => 'blog-posts',
        ])->assertSuccessful();

        $controllerContent = $this->files->get(app_path('Http/Controllers/CrudTestPostController.php'));
        $this->assertStringContainsString("route('blog-posts.index')", $controllerContent);

        $routeContent = $this->files->get(base_path('routes/web.php'));
        $this->assertStringContainsString("Route::resource('/blog-posts'", $routeContent);

        $this->restoreRoutesFile($originalRoutes);
    }

    public function testCrudGeneratorWithCustomCrudName(): void
    {
        $originalRoutes = $this->getOriginalRoutes();

        $this->artisan('make:crud', [
            'name' => $this->testTable,
            '--crud-name' => 'Article',
        ])->assertSuccessful();

        $this->assertFileExists(app_path('Http/Controllers/ArticleController.php'));
        $this->assertFileExists(app_path('Models/Article.php'));

        $this->restoreRoutesFile($originalRoutes);
    }

    public function testCrudGeneratorFailsForNonExistentTable(): void
    {
        $this->artisan('make:crud', ['name' => 'non_existent_table'])
            ->expectsOutput('`non_existent_table` table not exist');
    }

    public function testSoftDeletesIncludedWhenDeletedAtColumnExists(): void
    {
        $originalRoutes = $this->getOriginalRoutes();

        Schema::create('crud_test_soft_items', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        $this->artisan('make:crud', ['name' => 'crud_test_soft_items'])
            ->assertSuccessful();

        $modelPath = app_path('Models/CrudTestSoftItem.php');
        $content = $this->files->get($modelPath);

        $this->assertStringContainsString('use Illuminate\Database\Eloquent\SoftDeletes;', $content);
        $this->assertStringContainsString('use SoftDeletes;', $content);

        $this->restoreRoutesFile($originalRoutes);
    }

    public function testSoftDeletesNotIncludedWhenNoDeletedAtColumn(): void
    {
        $originalRoutes = $this->getOriginalRoutes();

        $this->artisan('make:crud', ['name' => $this->testTable])
            ->assertSuccessful();

        $modelPath = app_path('Models/CrudTestPost.php');
        $content = $this->files->get($modelPath);

        $this->assertStringNotContainsString('SoftDeletes', $content);

        $this->restoreRoutesFile($originalRoutes);
    }

    public function testControllerUsesCorrectNamespace(): void
    {
        $originalRoutes = $this->getOriginalRoutes();

        $this->artisan('make:crud', ['name' => $this->testTable])
            ->assertSuccessful();

        $content = $this->files->get(app_path('Http/Controllers/CrudTestPostController.php'));
        $this->assertStringContainsString('namespace App\Http\Controllers;', $content);

        $this->restoreRoutesFile($originalRoutes);
    }

    public function testModelUsesCorrectNamespace(): void
    {
        $originalRoutes = $this->getOriginalRoutes();

        $this->artisan('make:crud', ['name' => $this->testTable])
            ->assertSuccessful();

        $content = $this->files->get(app_path('Models/CrudTestPost.php'));
        $this->assertStringContainsString('namespace App\Models;', $content);

        $this->restoreRoutesFile($originalRoutes);
    }

    public function testViewsUseConfiguredLayout(): void
    {
        $originalRoutes = $this->getOriginalRoutes();

        $this->artisan('make:crud', ['name' => $this->testTable])
            ->assertSuccessful();

        $content = $this->files->get(resource_path('views/crud-test-post/index.blade.php'));
        $this->assertStringContainsString("@extends('tablar::page')", $content);

        $this->restoreRoutesFile($originalRoutes);
    }

    public function testControllerHasPagination(): void
    {
        $originalRoutes = $this->getOriginalRoutes();

        $this->artisan('make:crud', ['name' => $this->testTable])
            ->assertSuccessful();

        $content = $this->files->get(app_path('Http/Controllers/CrudTestPostController.php'));
        $this->assertStringContainsString('paginate(10)', $content);

        $this->restoreRoutesFile($originalRoutes);
    }

    public function testControllerHasAllCrudMethods(): void
    {
        $originalRoutes = $this->getOriginalRoutes();

        $this->artisan('make:crud', ['name' => $this->testTable])
            ->assertSuccessful();

        $content = $this->files->get(app_path('Http/Controllers/CrudTestPostController.php'));

        $this->assertStringContainsString('public function index()', $content);
        $this->assertStringContainsString('public function create()', $content);
        $this->assertStringContainsString('public function store(Request $request)', $content);
        $this->assertStringContainsString('public function show($id)', $content);
        $this->assertStringContainsString('public function edit($id)', $content);
        $this->assertStringContainsString('public function update(', $content);
        $this->assertStringContainsString('public function destroy($id)', $content);

        $this->restoreRoutesFile($originalRoutes);
    }
}
