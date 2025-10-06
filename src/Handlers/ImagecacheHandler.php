<?php

namespace Fomvasss\Blocks\Handlers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ImagecacheHandler
{
    public function handle(Model $block, string $url): string
    {
        $pos = strpos($url, 'photos');

        $regEx = config('blocks.images.extensions');

        if (preg_match($regEx, $url) && $pos !== false) {
            $path = substr($url, $pos);
            $fullPath = storage_path("app/public/{$path}");
            $sharesIndex = strpos($fullPath, 'shares/');
            $startIndex = $sharesIndex + strlen('shares/');
            $relativePath = substr($fullPath, $startIndex) . '.webp';
            $cacheFile = md5($relativePath) . '.webp';

            $cachedisk = config('blocks.images.cache.disk');
            if (Storage::disk($cachedisk)->exists($cacheFile)) {
                return Storage::disk($cachedisk)->url($cacheFile);
            }

            $sourcedisk = config('blocks.images.source.disk');
            if (Storage::disk($sourcedisk)->exists($path)) {
                return route(config('blocks.images.cache.routename'), $relativePath);
            }
        }

        return $url;
    }
}