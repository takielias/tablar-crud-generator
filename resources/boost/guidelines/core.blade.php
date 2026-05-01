## Tablar CRUD Generator

@verbatim
takielias/tablar-crud-generator — scaffold Model + Controller + Views + Route from an EXISTING DB table. Generator reads live schema; does NOT generate migration. Forms use takielias/lab `class="ajax-submit"` pattern.
@endverbatim

### Install

@verbatim
<code-snippet name="install" lang="bash">
composer require takielias/tablar-crud-generator --dev
</code-snippet>
@endverbatim

Guided install: `/laravel-boost:install-tablar-crud-generator`.

### Usage

@verbatim
<code-snippet name="generate" lang="bash">
php artisan make:migration create_products_table
php artisan migrate              # table MUST exist first
php artisan make:crud products   # arg = table name (plural snake_case)
</code-snippet>
@endverbatim

### Conventions

- Signature: `make:crud {table} {--route=} {--crud-name=} {--lang=}`. No `--force`.
- Generator overwrites without prompt and APPENDS `Route::resource(...)` (duplicate on rerun). Commit + remove old route line before regenerating.

### See also

- `tablar-crud-generator-development` for full flag list, generated file list, regeneration safety, stub customization.
