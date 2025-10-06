<?php

namespace Fomvasss\Blocks\Http;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImagecacheController extends \Illuminate\Routing\Controller
{
    /**
     * @param string $imgPath
     * @return Application|ResponseFactory|\Illuminate\Foundation\Application|Response|BinaryFileResponse
     */
    public function imagecache(string $imgPath)
    {
        $sourceName = Str::beforeLast($imgPath, '.webp') ?: $imgPath;
        $sourceName = str_replace(['..', '../', '..\\'], '', $sourceName);

        $cacheFile = md5($imgPath) . '.webp';

        $cachedisk = config('blocks.images.cache.disk');
        
        if (Storage::disk($cachedisk)->exists($cacheFile)) {
            return response()->file(
                Storage::disk($cachedisk)->path($cacheFile), [
                    'Content-Type' => 'image/webp',
                    'Content-Disposition' => 'inline; filename="' . basename($sourceName) . '.webp"',
                ]
            );
        }

        $path = $this->getImagePath($sourceName);

        $img = (new ImageManager(new Driver()))
            ->read($path)
            ->encode(new WebpEncoder(quality: 95));

        Storage::disk($cachedisk)->put($cacheFile, $img);
        
        return response($img, 200, [
            'Content-Type' => 'image/webp',
            'Content-Disposition' => 'inline; filename="' . basename($sourceName) . '.webp"',
        ]);
    }

    /**
     * @param string $filename
     * @return string|void
     */
    protected function getImagePath(string $filename)
    {
        $filename = str_replace(['..', '../', '..\\'], '', $filename);

        $source = config('blocks.images.source');
        
        $folders = $source['folders'] ?? [];

        foreach ($folders as $folder) {
            $path = trim($folder . '/' . $filename, '/');
            if (Storage::disk($source['disk'])->exists($path)) {
                return Storage::disk($source['disk'])->path($path);
            }
        }

        abort(404);
    }
}
