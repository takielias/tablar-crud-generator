---
name: tablar-crud-generator-development
description: Scaffold a CRUD resource with takielias/tablar-crud-generator — make:crud {table} command (table-name argument, --route/--crud-name/--lang flags), what gets generated (Model, Controller, Views, Route), DB-schema-driven field inference, stub customization, regeneration strategy.
---

# Tablar CRUD Generator

## When to use this skill

- Scaffolding a new admin resource quickly.
- Generating a CRUD against an EXISTING table (the generator reads the live DB schema).
- Customizing the generated output by publishing the config + stubs.
- Re-running safely after a schema change.

## Command reference (verified `src/Commands/CrudGenerator.php`)

```
make:crud {name : Table name}
          {--route= : Custom route name}
          {--crud-name= : Custom crud name}
          {--lang= : language}
```

| Arg / flag | Required | Notes |
|---|---|---|
| `name` | yes | DB **table name** (plural, snake_case). NOT a model name. |
| `--route` | no | Route segment. Defaults to `strtolower($className)`. Use `--route=admin/products` for nested URLs. |
| `--crud-name` | no | Override the inferred class name. Default: `Str::studly(Str::singular($table))`. |
| `--lang` | no | Locale for `Pluralizer::useLanguage()`. Default: app locale. |

No `--force` flag exists. The generator overwrites without prompting.

## End-to-end flow

```bash
# 1. Define the migration
php artisan make:migration create_products_table

# 2. Edit the migration — add real columns
# Schema::create('products', function (Blueprint $table) {
#     $table->id();
#     $table->string('product_name');
#     $table->string('product_code')->unique();
#     $table->decimal('product_price', 10, 2);
#     $table->timestamps();
# });

# 3. Run migration so table exists
php artisan migrate

# 4. Scaffold CRUD
php artisan make:crud products
```

Output:

```
Running Crud Generator ...
Creating Controller ...
Creating Model ...
Creating Views ...
Creating Route ...
```

## What gets generated

| File | Source stub | Notes |
|---|---|---|
| `app/Models/Product.php` | `src/stubs/Model.stub` | `$fillable` from columns, optional SoftDeletes, `$rules` for validation, `$perPage = 20`. |
| `app/Http/Controllers/ProductController.php` | `src/stubs/Controller.stub` | Resource controller (index/create/store/show/edit/update/destroy) using `Product::paginate(10)`. |
| `resources/views/product/index.blade.php` | `src/stubs/views/index.stub` | Tabler-styled list page. |
| `resources/views/product/create.blade.php` | `src/stubs/views/create.stub` | Includes `form.blade.php` for fields. |
| `resources/views/product/edit.blade.php` | `src/stubs/views/edit.stub` | Same shape as create. |
| `resources/views/product/show.blade.php` | `src/stubs/views/show.stub` | Read-only detail page. |
| `resources/views/product/form.blade.php` | `src/stubs/views/form.stub` | Field rendering wrapper, ends with `<button class="btn btn-primary ms-auto ajax-submit">Submit</button>`. |
| `resources/views/product/form-field.blade.php` | `src/stubs/views/form-field.stub` | Per-column form field markup. |
| `resources/views/product/view-field.blade.php` | `src/stubs/views/view-field.stub` | Per-column read-only markup. |
| Route entry in `routes/web.php` | n/a | Appended: `Route::resource('/products', App\Http\Controllers\ProductController::class);` |

(Verify exact view paths against your output — naming uses singular lowercase.)

## DB-schema-driven field inference

The generator reads the live DB schema via Laravel's driver-agnostic `Schema::getColumns()` + `Schema::getIndexes()` + `Schema::getForeignKeys()` API (Laravel 10.32+). Works across **sqlite, mysql / mariadb, postgres, sqlsrv** with the same code path. Inferred mapping (verify against `src/Commands/GeneratorCommand.php` if customizing):

- `string`, `text` → text input / textarea.
- `integer`, `bigInteger`, `decimal` → number input.
- `boolean` → checkbox.
- `date`, `datetime`, `timestamp` → date input.
- `enum` → select (options from the column's allowed values).
- `json` → text input (limited support).

Generator does NOT generate the migration. The table must exist before running.

### Foreign-key relations

Inbound and outbound foreign keys are detected via `Schema::getForeignKeys()` + a sweep over `Schema::getTables()` for inbound references. Generated Models declare `hasOne` / `hasMany` / matching docblocks for each FK relation found. Driver-agnostic — same behavior on every supported driver.

## Recipes

### 1. Standard scaffold

```bash
php artisan make:migration create_products_table
# edit migration
php artisan migrate
php artisan make:crud products
```

Visit `/products` after running.

### 2. Custom route + class name

```bash
php artisan make:crud products --route=admin/products --crud-name=AdminProduct
```

Generates `AdminProductController`, view path `resources/views/adminProduct/`, route `/admin/products`.

### 3. Localized labels (pluralizer)

```bash
php artisan make:crud articles --lang=fr
```

Pluralizer uses French rules — affects how view paths and class names are derived.

### 4. Customize stubs

```bash
php artisan vendor:publish --tag=crud
```

Writes `config/crud.php`. Stubs themselves live in `vendor/takielias/tablar-crud-generator/src/stubs/` — fork those into your app + point the config at your local copy if it supports custom paths (verify in published config).

For lighter customization, edit `src/stubs/Model.stub`, `Controller.stub`, `views/*.stub` directly via composer install path.

### 5. Regenerate safely

```bash
# Before regenerating, COMMIT the current state — generator overwrites without prompt
git add -A && git commit -m "wip: pre-regenerate"

# Remove the existing Route::resource line for this resource from routes/web.php
# (or generator will append a duplicate)
sed -i '/Route::resource.*ProductController::class/d' routes/web.php

# Now regenerate
php artisan make:crud products
```

## Driver compatibility

Generator works on **sqlite, mysql / mariadb, postgres, sqlsrv** via Laravel's driver-agnostic Schema API (Laravel 10.32+). Test suite passes on all 3 of sqlite / mysql / pgsql in the package's CI matrix.

Older versions (pre-`fix/cross-db-schema-introspection`) used MySQL-only `SHOW COLUMNS` / `SHOW KEYS` / `INFORMATION_SCHEMA.KEY_COLUMN_USAGE` and crashed on sqlite + pgsql. If you see SQL errors during `make:crud`, ensure the package is at the cross-DB-fixed release.

## Common pitfalls

- **`make:crud` aborts with "`{table}` table not exist"** — migrate first. Generator does not create migrations.
- **Duplicate route on rerun** — `routes/web.php` accumulates `Route::resource(...)` entries. Manually remove the old one before regenerating.
- **Existing files overwritten silently** — no `--force` prompt. Always commit before regenerating.
- **Generated form throws "Method [flatPicker] not found" or similar** — generator targets generic Blade markup, not tablar-kit FormBuilder. If you want FormBuilder fields, hand-port the generated `form-field.blade.php` to use `<x-flat-picker>`, `<x-tom-select>`, etc.
- **`ajax-submit` class on submit button does nothing** — pairs with takielias/lab's auto-binder. Install lab via `/laravel-boost:install-laravel-ajax-builder` if not already present, OR remove the class for plain form submission.
- **`make:crud` returns exit code 1 even after "Created Successfully."** — pre-`fix/cross-db-schema-introspection` versions had `handle()` returning `bool`, which Laravel cast to inverted exit codes. Upgrade past the fix.
- **Field type detection misclassifies columns** — `enum` may render as text input depending on driver/version. Verify generated form-field.blade.php and adjust manually.

## Configuration

Config publishes via `--tag=crud`:

```bash
php artisan vendor:publish --tag=crud
```

`config/crud.php` controls stub paths + naming overrides. Defaults work for most projects.

## Integration

- **takielias/tablar** — generated views extend the published Tablar master layout. Run `tablar:install` first.
- **takielias/tablar-kit** — generated forms do NOT use FormBuilder by default. Manual conversion if you want FormBuilder. tablar-kit components like `<x-card>`, `<x-modal>` work in any view.
- **takielias/lab** — generated submit buttons carry `class="ajax-submit"` and require lab's JS bindings + CSRF meta tag for AJAX submission.

## Related

- Slash command `/laravel-boost:install-tablar-crud-generator` — guided install + smoke scaffold.
- Skill `tablar-installation-development` — base layout install.
- Skill `tablar-kit-forms-development` — FormBuilder for hand-crafted forms.
- Skill `laravel-ajax-builder-development` — Lab JS helpers and `@alert`/`@submit` directives.
- Source: `src/Commands/CrudGenerator.php`, `src/Commands/GeneratorCommand.php`, `src/stubs/`.
