# Laravel Blocks

[![License](https://img.shields.io/packagist/l/fomvasss/laravel-blocks.svg?style=for-the-badge)](https://packagist.org/packages/fomvasss/laravel-blocks)
[![Latest Stable Version](https://img.shields.io/packagist/v/fomvasss/laravel-blocks.svg?style=for-the-badge)](https://packagist.org/packages/fomvasss/laravel-blocks)
[![Total Downloads](https://img.shields.io/packagist/dt/fomvasss/laravel-blocks.svg?style=for-the-badge)](https://packagist.org/packages/fomvasss/laravel-blocks)

Universal content-blocks system for Laravel — static (JSON) and dynamic (handler-driven).

> Ukrainian: [README.uk.md](README.uk.md)

---

## Requirements

- PHP ^8.1 · Laravel ^10 / ^11 / ^12 / ^13

## Installation

```bash
composer require fomvasss/laravel-blocks
php artisan vendor:publish --provider="Fomvasss\Blocks\ServiceProvider"
php artisan migrate
```

---

## Facade

```php
// Get prepared block model
\Block::init('contacts')->getBlock();

// Read a value from merged data
\Block::init('contacts')->getData('phone');

// Read nested value (dot-notation)
\Block::init('hero')->getData('slides.0.title');

// Read sorted array (by item weight)
\Block::init('slider')->getDataSort('slides');

// Pass runtime attrs to the dynamic handler
\Block::setAttrs(['limit' => 5])->init('news')->getData('items');

// JSON resources
\Block::getBlockResource('hero');
\Block::getBlocksResource(['hero', 'contacts']);          // indexed
\Block::getBlocksResource(['hero', 'contacts'], 'slug');  // keyed by slug
```

---

## Dynamic Block Handlers

Create a handler class in `app/Blocks/` — it is auto-discovered on boot.

```bash
php artisan make:block ContactsBlockHandler
```

```php
<?php

namespace App\Blocks;

use Fomvasss\Blocks\Contracts\BlockHandlerInterface;
use Illuminate\Database\Eloquent\Model;

class ContactsBlockHandler implements BlockHandlerInterface
{
    public static function getTypes(): array
    {
        return ['contacts']; // matches block->type in DB
    }

    public function handle(Model $block, array $attrs = []): array
    {
        return [
            'email'   => config('app.contact_email'),
            'address' => $block->getContent('address', ''),
            'phone'   => preg_replace('/[^0-9+]/si', '', $block->getContent('phone', '')),
        ] + $attrs;
    }
}
```

---

## HasBlocks Trait

Attach blocks to any Eloquent model via a polymorphic pivot:

```php
use Fomvasss\Blocks\Models\HasBlocks;

class Page extends Model
{
    use HasBlocks;
}
```

```php
// Relation
$page->blocks;

// Array of BlockResource objects
$page->getResourceBlocks();
```

---

## Field Handlers (optional)

Applied to every string value in block `content` before it is returned (e.g. image URL transformation).

Enable in `config/blocks.php`:

```php
'fieldhandlers' => [
    \Fomvasss\Blocks\Handlers\ImagepresetHandler::class, // requires fomvasss/laravel-imagepresets
],
```

---

## Caching

Set `cache` (minutes) in a block's `options` JSON to cache the prepared block:

```json
{ "cache": 60 }
```

Cache is cleared automatically on model save.

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

MIT — see [LICENSE.md](LICENSE.md).
