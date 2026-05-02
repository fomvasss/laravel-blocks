
# Laravel Blocks package

[![License](https://img.shields.io/packagist/l/fomvasss/laravel-blocks.svg?style=for-the-badge)](https://packagist.org/packages/fomvasss/laravel-blocks)
[![Build Status](https://img.shields.io/github/stars/fomvasss/laravel-blocks.svg?style=for-the-badge)](https://github.com/fomvasss/laravel-blocks)
[![Latest Stable Version](https://img.shields.io/packagist/v/fomvasss/laravel-blocks.svg?style=for-the-badge)](https://packagist.org/packages/fomvasss/laravel-blocks)
[![Total Downloads](https://img.shields.io/packagist/dt/fomvasss/laravel-blocks.svg?style=for-the-badge)](https://packagist.org/packages/fomvasss/laravel-blocks)
[![Quality Score](https://img.shields.io/scrutinizer/g/fomvasss/laravel-blocks.svg?style=for-the-badge)](https://scrutinizer-ci.com/g/fomvasss/laravel-blocks)

Universal blocks system for Laravel (static & dynamic content).

## Requirements

- PHP ^8.1
- Laravel ^10 / ^11 / ^12 / ^13

## Installation

```bash
composer require fomvasss/laravel-blocks
```

Publish the config and run migrations:

```bash
php artisan vendor:publish --provider="Fomvasss\Blocks\ServiceProvider"
php artisan migrate
```

## Usage

### Facade

```php
\Block::init('contacts')->getBlock();

\Block::init('contacts')->getData('phone');

\Block::init('slider')->getDataSort('slides');

\Block::setAttrs(['sort' => 'desc'])->init('some-1', 'slug')->getBlock();
```

### HasBlocks Trait

The Eloquent model that owns blocks must use the `HasBlocks` trait:

```php
namespace App\Models;

use Fomvasss\Blocks\Models\HasBlocks;

class PageModel extends Model
{
    use HasBlocks;
}
```

### Dynamic Block Handlers

Place your handlers in `app/Blocks/`. Each handler must implement `BlockHandlerInterface`.

Generate a handler with artisan:

```bash
php artisan make:block ContactsBlockHandler
```

Example `app/Blocks/ContactsBlockHandler.php`:

```php
<?php

namespace App\Blocks;

use Fomvasss\Blocks\Contracts\BlockHandlerInterface;
use Illuminate\Database\Eloquent\Model;

class ContactsBlockHandler implements BlockHandlerInterface
{
    public static function getType(): string
    {
        return 'contacts';
    }

    public function handle(Model $block, array $attrs = []): array
    {
        return [
            'email'   => config('app.email'),
            'address' => $block->getContent('address', ''),
            'phone'   => preg_replace('/[^0-9]/si', '', $block->getContent('phone', '')),
        ] + $attrs;
    }
}
```

## Field Handlers (optional)

Field handlers are applied to **every string value** in block content before it is returned.
They are useful for transforming stored values (e.g., image URLs) on-the-fly.

Register them in `config/blocks.php`:

```php
'fieldhandlers' => [
    \Fomvasss\Blocks\Handlers\ImagepresetHandler::class,
],
```

### ImagepresetHandler

An optional built-in handler that transforms image URLs using the
[fomvasss/laravel-imagepresets](https://github.com/fomvasss/laravel-imagepresets) package
(on-the-fly resize/convert via League Glide).

**Setup:**

1. Install the dependency:

```bash
composer require fomvasss/laravel-imagepresets
```

2. Publish its config and configure presets in `config/imagepresets.php`:

```bash
php artisan vendor:publish --provider="Fomvasss\Imagepresets\ImagepresetServiceProvider"
```

3. Enable `ImagepresetHandler` in `config/blocks.php`:

```php
'fieldhandlers' => [
    \Fomvasss\Blocks\Handlers\ImagepresetHandler::class,
],

```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [fomvasss](https://github.com/fomvasss)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
