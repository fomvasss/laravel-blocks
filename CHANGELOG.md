# Changelog

## [2.6.3] — 2026-06-03

### Changed

- `Block::getCacheName()` now returns `"blocks.{$key}"` instead of `md5("blocks-{$key}")` — removes unnecessary hashing overhead.
  **Note:** existing cache entries (md5 keys) become orphans — run `php artisan cache:clear` after deploy.

## [2.6.2] — 2026-06-03

### Fixed

- Cache is now invalidated on block deletion (`static::deleted`). Previously a deleted block remained in cache until TTL expired.

## [2.6.1] — 2026-06-03

### Fixed

- `BlockService::init()` now always returns `static` instead of `null` when block is not found — safe chaining without `?->`.
- `getBlock()` return type corrected to `?Model`.
- Dynamic handler re-runs on cache hit so current `$this->attrs` are always applied (previously cached `data` was returned regardless of attrs).
- `Block::getBlockableModels()` uses `distinct()` at SQL level instead of PHP-side deduplication via `pluck(key, key)`.

### Changed

- `declare(strict_types=1)` added to `MakeBlockCommand`, `Block` facade, `BlockResource`.
- `Block::booted()` return type annotated as `void`.
- Removed redundant `use Fomvasss\Blocks\BlockService` in `ServiceProvider` (same namespace).
- Added inline comment explaining why `$attrs` is intentionally not reset between `init()` calls.

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