<?php

declare(strict_types=1);

namespace Fomvasss\Blocks\Handlers;

use Illuminate\Database\Eloquent\Model;

/**
 * Опційний field handler для трансформації URL зображень через пакет
 * fomvasss/laravel-imagepresets (on-the-fly resize/convert via League Glide).
 *
 * Підключення у config/blocks.php:
 *
 *   'fieldhandlers' => [
 *       \Fomvasss\Blocks\Handlers\ImagepresetHandler::class,
 *   ],
 *
 * Потребує встановленого пакету:
 *   composer require fomvasss/laravel-imagepresets
 *
 * Для зміни пресету — розширте клас та перевизначте властивість $preset.
 */
class ImagepresetHandler
{
    /**
     * Назва пресету з config/imagepresets.php → presets або конфігурація.
     * Змініть значення при розширенні/перевизначенні класу.
     */
    protected string|array $preset = ['fm' => 'webp', 'q' => 80];

    /**
     * Трансформує URL зображення, підставляючи URL до preset-варіанту.
     *
     * @param  Model   $block  Модель блоку.
     * @param  string  $url    Поточне значення поля.
     * @return string          Перетворений URL або оригінальний, якщо не застосовно.
     */
    public function handle(Model $block, string $url): string
    {
        if (! function_exists('imagepreset_url')) {
            return $url;
        }

        if (! $this->isImageUrl($url)) {
            return $url;
        }

        return imagepreset_url($url, $this->preset);
    }

    /**
     * Перевіряє, чи є рядок URL до зображення за розширенням.
     */
    protected function isImageUrl(string $url): bool
    {
        return (bool) preg_match('/\.(jpe?g|png|gif|webp|avif|svg)(\?.*)?$/i', $url);
    }
}
