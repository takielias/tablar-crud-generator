![Laravel Tablar Crud Generator](https://banners.beyondco.de/Laravel%20Tablar%20Crud%20Generator.png?theme=light&packageManager=composer+require&packageName=takielias%2Ftablar-crud-generator&pattern=topography&style=style_1&description=It%27s+a+simple+CRUD+generator+based+on+Laravel+Tablar+&md=1&showWatermark=0&fontSize=125px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg)


[![Latest Version](https://img.shields.io/packagist/v/takielias/tablar-crud-generator?color=blue&label=release&style=for-the-badge)](https://packagist.org/packages/takielias/tablar-crud-generator)
[![Stars](https://img.shields.io/github/stars/takielias/tablar-crud-generator?color=rgb%2806%20189%20248%29&label=stars&style=for-the-badge)](https://github.com/takielias/tablar-crud-generator)
[![Total Downloads](https://img.shields.io/packagist/dt/takielias/tablar-crud-generator.svg?color=rgb%28249%20115%2022%29&style=for-the-badge)](https://packagist.org/packages/takielias/tablar-crud-generator)
[![Forks](https://img.shields.io/github/forks/takielias/tablar-crud-generator?color=rgb%28134%20115%2022%29&style=for-the-badge)](https://github.com/takielias/tablar-crud-generator/network/members)
[![Issues](https://img.shields.io/github/issues/takielias/tablar-crud-generator?color=rgb%28134%20239%20128%29&style=for-the-badge)](https://github.com/takielias/tablar-crud-generator/issues)
[![Linkedin](https://img.shields.io/badge/-LinkedIn-black.svg?logo=linkedin&color=rgba(235%2068%2050)&style=for-the-badge)](https://linkedin.com/in/takielias)

<a href="https://www.buymeacoffee.com/takielias" target="_blank"> <img align="left" src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" height="50" width="210" alt="takielias" /></a>

<br/>
<br/>

### Pairs with [Laravel Tablar Admin Dashboard](https://github.com/takielias/tablar)

Scaffolds a complete CRUD set (Model, Controller, Views, Route) for an EXISTING database table via one artisan command. Built for projects already running [takielias/tablar](https://github.com/takielias/tablar) (Tabler-based admin layout) and pairs naturally with [takielias/tablar-kit](https://github.com/takielias/tablar-kit) and [takielias/lab](https://github.com/takielias/laravel-ajax-builder).

**Inspired by** [`ibex/crud-generator`](https://github.com/awais-vteams/laravel-crud-generator).

## Features

- **Cross-database** — works on **SQLite, MySQL/MariaDB, PostgreSQL, SQL Server** via Laravel's driver-agnostic Schema API. Test suite passes on all three drivers in CI.
- Generates a **Model** with Eloquent relations, `$fillable`, validation `$rules`, and optional `SoftDeletes`.
- Generates a **resource Controller** with all 7 CRUD actions.
- Generates **Bootstrap 5 / Tabler-styled views** (`index`, `create`, `edit`, `show`, `form`, `form-field`, `view-field`).
- Appends a `Route::resource(...)` entry to `routes/web.php`.
- Submit buttons use [takielias/lab](https://github.com/takielias/laravel-ajax-builder)'s `ajax-submit-button` class for native fetch + spinner UX out of the box.
- Tabler icon classes (`<i class="ti ti-...">`) match the published tablar layout idiom.
- AI-friendly: ships [Laravel Boost](https://laravel.com/docs/13.x/boost) guidelines + skills under `resources/boost/` for in-editor agent help.

## Requirements

| Component | Minimum |
|-----------|---------|
| PHP       | 8.2     |
| Laravel   | 10.32+ / 11.x / 12.x / 13.x (uses `Schema::getColumns/getIndexes/getForeignKeys`) |
| Tablar    | [takielias/tablar](https://github.com/takielias/tablar) (recommended for the published `tablar::page` layout) |

## Installation

```bash
composer require takielias/tablar-crud-generator --dev
```

Service provider auto-discovered via `extra.laravel.providers`.

Optional: publish the config (only needed if you want to customize stub paths or naming):

```bash
php artisan vendor:publish --tag=crud
```

## Usage

The generator reads the **live database schema** to infer field types — so the table must already exist before running:

```bash
# 1. Create + edit the migration
php artisan make:migration create_products_table

# 2. Run migration (table must exist before make:crud)
php artisan migrate

# 3. Scaffold CRUD
php artisan make:crud products
```

The argument is the **DB table name** (plural, snake_case). Class names + view paths are inferred via `Str::studly(Str::singular($table))`.

### Options

```
make:crud {name}
          {--route= : Custom route name}
          {--crud-name= : Custom crud name}
          {--lang= : pluralizer language}
```

Examples:

```bash
# Custom route segment
php artisan make:crud products --route=admin/products

# Custom class / view path name
php artisan make:crud emergencies --crud-name=Emergencies

# French / Norwegian / Portuguese / Spanish / Turkish pluralization
php artisan make:crud incidencies --lang=spanish
```

## What gets generated

| File | Notes |
|------|-------|
| `app/Models/Product.php` | `$fillable` from columns, `$rules` from NOT NULL constraints, optional `SoftDeletes` if a `deleted_at` column exists |
| `app/Http/Controllers/ProductController.php` | Resource controller (index/create/store/show/edit/update/destroy) using `Product::paginate(10)` |
| `resources/views/product/index.blade.php` | Tabler list view with action dropdown |
| `resources/views/product/create.blade.php` | Includes `form.blade.php` |
| `resources/views/product/edit.blade.php` | Includes `form.blade.php` with method spoofing |
| `resources/views/product/show.blade.php` | Read-only detail page |
| `resources/views/product/form.blade.php` | Field rendering wrapper, ends with `<button class="btn btn-primary ms-auto ajax-submit-button has-spinner">Submit</button>` |
| `resources/views/product/form-field.blade.php` | Per-column form field markup |
| `resources/views/product/view-field.blade.php` | Per-column read-only markup |
| Route entry | Appended to `routes/web.php`: `Route::resource('/products', App\Http\Controllers\ProductController::class);` |

The generator does **NOT** create the migration. Define + migrate the table first.

## Layout convention

Generated views follow tablar's published layout idiom:

```blade
@extends('tablar::page')

@section('title', '{{modelTitle}}')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">List</div>
                <h2 class="page-title">{{ __('{{modelTitle}}') }}</h2>
            </div>
            <div class="col-12 col-md-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="..." class="btn btn-primary"><i class="ti ti-plus me-1"></i> Create</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">...</div>
    </div>
</div>
@endsection
```

Override `crud.layout` in `config/crud.php` to extend a different master layout.

## Regeneration safety

The generator **overwrites without prompting** and **appends `Route::resource(...)` on every run** (no idempotency check). Workflow when re-scaffolding:

```bash
git add -A && git commit -m "wip: pre-regenerate"
sed -i '/Route::resource.*ProductController::class/d' routes/web.php
php artisan make:crud products
```

## Database support

| Driver | Status |
|--------|--------|
| SQLite | ✅ tested |
| MySQL / MariaDB | ✅ tested |
| PostgreSQL | ✅ tested |
| SQL Server | ✅ supported (same Schema API surface) |

Schema introspection uses `Schema::getColumns()`, `Schema::getIndexes()`, and `Schema::getForeignKeys()` — driver-agnostic since Laravel 10.32.

## AI guidelines (Laravel Boost)

This package ships Boost-compatible guidelines + skills under `resources/boost/`. Consumer apps with `laravel/boost` installed get them automatically:

```bash
composer require laravel/boost --dev
php artisan boost:install   # opt into "skills" feature; select takielias/tablar-crud-generator
```

Once published, your AI agent (Claude Code, Cursor, Copilot, etc.) can use the `tablar-crud-generator-development` skill to answer questions about flag semantics, generated file shapes, and stub customization without reading source.

## Example

<img width="855" alt="tablar-crud-generator-light" src="https://user-images.githubusercontent.com/38932580/197386382-562d6e3a-055a-42b8-8524-df76f70aa051.png">

![tablar-crud-generator-dark](https://user-images.githubusercontent.com/38932580/197386398-b9541389-5d63-4bcd-87f2-c4aa5d49072d.png)

## Contact

Taki Elias — [@takiele](https://twitter.com/takiele) — [ebuz.xyz](https://ebuz.xyz) — taki.elias@gmail.com

<a href="https://www.buymeacoffee.com/takielias" target="_blank">
<img align="left" src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" height="50" width="210" alt="takielias" /></a>
