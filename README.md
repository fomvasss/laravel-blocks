
# Laravel Blocks package

[![License](https://img.shields.io/packagist/l/fomvasss/laravel-blocks.svg?style=for-the-badge)](https://packagist.org/packages/fomvasss/laravel-blocks)
[![Build Status](https://img.shields.io/github/stars/fomvasss/laravel-blocks.svg?style=for-the-badge)](https://github.com/fomvasss/laravel-blocks)
[![Latest Stable Version](https://img.shields.io/packagist/v/fomvasss/laravel-blocks.svg?style=for-the-badge)](https://packagist.org/packages/fomvasss/laravel-blocks)
[![Total Downloads](https://img.shields.io/packagist/dt/fomvasss/laravel-blocks.svg?style=for-the-badge)](https://packagist.org/packages/fomvasss/laravel-blocks)
[![Quality Score](https://img.shields.io/scrutinizer/g/fomvasss/laravel-blocks.svg?style=for-the-badge)](https://scrutinizer-ci.com/g/fomvasss/laravel-blocks)

Universal blocks system for Laravel (static & dynamic content).

## Installation

Install the package via composer:

```bash
composer require fomvasss/laravel-blocks
```

Publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Fomvasss\Blocks\ServiceProvider"
php artisan migrate
```

Add to filesystems.php disc config (for cache images):

```php
    'discs' => [
    //...
        'blocks' => [
            'driver' => 'local',
            'root' => storage_path('app/public/blocks'),
            'url' => env('APP_URL').'/storage/blocks',
            'visibility' => 'public',
        ],
    ],
```

## Usage

The Eloquent model for relations must has the Trait `HasBlocks`:

```php
namespace App\Models;

use Fomvasss\Blocks\Models\HasBlocks;

class PageModel extends Model {

	use HasBlocks;
	//...
}
```

Use facede

```php
 \Block::setAttrs(['sort' => 'desc'])->init('some-1', 'slug')->getBlock();
 
 \Block::init('contacts')->getData('phone');
 
 \Block::init('slider')->getDataSort('slides');
```

For prepare dinamic block content, place your hendlers in dir `app/Blocks/...`

Example `app/Blocks/ContactsBlockHandler.php`:

```php
<?php

namespace App\Blocks;

use Fomvasss\Blocks\Contracts\BlockHandlerInterface;
use Fomvasss\Blocks\Models\Block;

class ContactsBlockHandler implements BlockHandlerInterface
{

    public static function getType(): string
    {
        return 'contacts';
    }

    public function handle(Block $block, array $attrs = []): array
    {
        return [
            'email' => config('app.email'),
            'address' => $block->getContent('address', ''),
            'phone' => preg_replace('/[^0-9]/si', '', $block->getContent('phone', '')),
        ] + $attrs;
    }
}
```


## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [fomvasss](https://github.com/fomvasss)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
