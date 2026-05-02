# Laravel Blocks

[![License](https://img.shields.io/packagist/l/fomvasss/laravel-blocks.svg?style=for-the-badge)](https://packagist.org/packages/fomvasss/laravel-blocks)
[![Latest Stable Version](https://img.shields.io/packagist/v/fomvasss/laravel-blocks.svg?style=for-the-badge)](https://packagist.org/packages/fomvasss/laravel-blocks)
[![Total Downloads](https://img.shields.io/packagist/dt/fomvasss/laravel-blocks.svg?style=for-the-badge)](https://packagist.org/packages/fomvasss/laravel-blocks)

Універсальна система контент-блоків для Laravel — статичні (JSON) та динамічні (через обробники).

> English: [README.md](README.md)

---

## Вимоги

- PHP ^8.1 · Laravel ^10 / ^11 / ^12 / ^13

## Встановлення

```bash
composer require fomvasss/laravel-blocks
php artisan vendor:publish --provider="Fomvasss\Blocks\ServiceProvider"
php artisan migrate
```

---

## Facade

```php
// Отримати підготовлену модель блоку
\Block::init('contacts')->getBlock();

// Читати значення зі злитих даних
\Block::init('contacts')->getData('phone');

// Крапкова нотація для вкладених значень
\Block::init('hero')->getData('slides.0.title');

// Масив, відсортований за weight елементів
\Block::init('slider')->getDataSort('slides');

// Передати runtime-атрибути до динамічного обробника
\Block::setAttrs(['limit' => 5])->init('news')->getData('items');

// JSON-ресурси
\Block::getBlockResource('hero');
\Block::getBlocksResource(['hero', 'contacts']);          // індексований масив
\Block::getBlocksResource(['hero', 'contacts'], 'slug');  // з ключами за slug
```

---

## Динамічні обробники

Створіть клас у `app/Blocks/` — він виявляється автоматично при завантаженні.

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
        return ['contacts']; // відповідає block->type у БД
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

## Трейт HasBlocks

Прикріпіть блоки до будь-якої Eloquent-моделі через поліморфний pivot:

```php
use Fomvasss\Blocks\Models\HasBlocks;

class Page extends Model
{
    use HasBlocks;
}
```

```php
// Зв'язок
$page->blocks;

// Масив BlockResource-об'єктів
$page->getResourceBlocks();
```

---

## Обробники полів (опціонально)

Застосовуються до кожного рядкового значення у `content` блоку перед поверненням (наприклад, трансформація URL зображень).

Увімкнути у `config/blocks.php`:

```php
'fieldhandlers' => [
    \Fomvasss\Blocks\Handlers\ImagepresetHandler::class, // потребує fomvasss/laravel-imagepresets
],
```

---

## Кешування

Встановіть `cache` (хвилини) у JSON `options` блоку:

```json
{ "cache": 60 }
```

Кеш очищається автоматично при збереженні моделі.

---

## Журнал змін

Дивіться [CHANGELOG.md](CHANGELOG.md).

## Ліцензія

MIT — [LICENSE.md](LICENSE.md).
