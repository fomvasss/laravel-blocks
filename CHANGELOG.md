# Changelog

## [2.0.0] — 2026-05-02

### Breaking Changes

- **Removed** built-in WebP image caching (`ImagecacheHandler`, `ImagecacheController`).
- **Removed** `intervention/image` dependency.
- **Removed** HTTP route `blocks.imagecache` (`/blocks-imagecache/{path}`).
- **Removed** `images` config section (`source`, `cache`, `extensions`).
- **Removed** `blocks` filesystem disk requirement.
- **Removed** `getType(): string` з `BlockHandlerInterface` — замінено на `getTypes(): array`.
  Всі handler-и зобов'язані реалізувати `getTypes()` замість `getType()`.
- PHP minimum version raised to **^8.1**.
- Laravel minimum version raised to **^10**.

### Added

- `ImagepresetHandler` — optional field handler that transforms image URLs using
  [fomvasss/laravel-imagepresets](https://github.com/fomvasss/laravel-imagepresets) (on-the-fly resize via League Glide).
  Disabled by default; requires `composer require fomvasss/laravel-imagepresets`.
- New `imagepresets` config section with `preset` key.
- `declare(strict_types=1)` added to all package PHP files.
- `BlockHandlerInterface::getTypes(): array` — handler тепер може оголосити **кілька типів** блоків,
  які він обробляє. Один handler реєструється для всіх вказаних типів автоматично.
- `BlockService::register()` — захист від конфліктів: виключення `\LogicException` при спробі
  перереєструвати тип з іншим handler-ом.

### Migration from 1.x

1. Remove the `blocks` disk from `config/filesystems.php`.
2. Remove references to `blocks.imagecache` route from templates/code.
3. Re-publish config: `php artisan vendor:publish --provider="Fomvasss\Blocks\ServiceProvider" --force`.
4. If you need image transformation, install `fomvasss/laravel-imagepresets`,
   uncomment `ImagepresetHandler` in `config/blocks.php` and configure `imagepresets.preset`.


У кожному handler-і замінити:
```php
// було:
public static function getType(): string
{
    return 'my_type';
}

// стало:
public static function getTypes(): array
{
    return ['my_type'];
}
```