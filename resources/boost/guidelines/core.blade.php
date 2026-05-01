## Tablar CRUD Generator

@verbatim
takielias/tablar-crud-generator scaffolds a complete CRUD set (Model, Controller, Views, Routes) for an EXISTING database table via one artisan command. Built for projects already running takielias/tablar (Tabler layout) + ajax-submit pattern from takielias/lab. Generator reads the live DB schema; it does NOT generate the migration.
@endverbatim

### Install

@verbatim
<code-snippet name="install" lang="bash">
composer require takielias/tablar-crud-generator --dev
</code-snippet>
@endverbatim

For guided install + smoke scaffold, type `/laravel-boost:install-tablar-crud-generator` in Claude Code.

### Usage

@verbatim
<code-snippet name="generate" lang="bash">
# 1. Create + run a migration first (table MUST exist)
php artisan make:migration create_products_table
php artisan migrate

# 2. Then scaffold CRUD
php artisan make:crud products
</code-snippet>
@endverbatim

### Conventions

- Command signature: `make:crud {name} {--route=} {--crud-name=} {--lang=}`. Argument is the **DB table name** (plural, snake_case).
- Generator runs `Schema::hasTable($table)` and aborts if the table doesn't exist. Migrate FIRST.
- Class name is inferred from table: `products` → `Product`.
- Outputs: `app/Models/{Model}.php`, `app/Http/Controllers/{Model}Controller.php`, `resources/views/{view}/{index,create,edit,show,form,form-field,view-field}.blade.php`. Route appended to `routes/web.php` as `Route::resource('/{route-name}', Controller::class);`.
- Generated forms use `class="ajax-submit"` on submit buttons → pairs with takielias/lab fetch-based AJAX submission.
- Override route segment via `--route=admin/products`. Override class name via `--crud-name=AdminProduct`. Switch pluralizer language via `--lang=fr`.
- Publish `config/crud.php` only if you need to customize stub behaviour: `php artisan vendor:publish --tag=crud`.

### Common pitfalls

- Running `make:crud` before migrating → "table not exist" abort. Migrate first.
- Re-running on the same table appends a DUPLICATE `Route::resource(...)` line to `routes/web.php`. Generator has no idempotency check. Remove the previous route block before regenerating.
- Generated files OVERWRITE existing customizations on rerun — no `--force` prompt. Commit your work before re-running.
- Generator infers field types from DB columns; complex types (JSON, polymorphic relations) may render as plain text inputs and need manual touch-up.

### See also

- `tablar-crud-generator-development` — full flag list, what gets generated per file, stub customization, regeneration safety, integration with tablar-kit FormBuilder.
- Slash command `/laravel-boost:install-tablar-crud-generator`.
- Source: `src/Commands/CrudGenerator.php`, `src/Commands/GeneratorCommand.php`, `src/stubs/`.
