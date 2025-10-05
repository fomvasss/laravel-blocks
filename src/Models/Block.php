<?php

namespace Fomvasss\Blocks\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class Block extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'ids' => 'array',
        'content' => 'array',
        'options' => 'array',
        'locales' => 'array',
    ];

    protected $attributes = [
        'weight' => 1000,
    ];

    protected static function booted()
    {
        static::addGlobalScope('sort', function (Builder $builder) {
            $builder->orderBy('weight')->oldest();
        });

        static::saved(function ($model) {
            Cache::forget(self::getCacheName($model->slug));
            Cache::forget(self::getCacheName($model->id));
        });
    }

    public static function getCacheName(string $key): string
    {
        return md5(serialize("blocks-{$key}"));
    }

    /**
     * @param string $key
     * @param $default
     * @return array|\ArrayAccess|mixed|null
     */
    public function getIds(string $key, $default = null)
    {
        return Arr::get($this->ids ?: [], $key) ?: $default;
    }

    /**
     * @param string $key
     * @param $default
     * @return array|\ArrayAccess|mixed|null
     */
    public function getOptions(string $key, $default = null)
    {
        return Arr::get($this->options ?: [], $key) ?: $default;
    }

    /**
     * Raw content.
     * 
     * @param string $key
     * @param $default
     * @return array|\ArrayAccess|mixed|null
     */
    public function getContent(string $key, $default = null)
    {
        return Arr::get($this->content ?: [], $key) ?: $default;
    }

    /**
     * @param string $key
     * @param $default
     * @return array|\ArrayAccess|mixed|null
     */
    public function getContentSort(string $key, $default = null)
    {
        $items = $this->getContent($key, $default);

        if (\is_array($items)) {
            usort($items, function ($item1, $item2) {
                return ($item1['weight'] ?? 0) <=> ($item2['weight'] ?? 0);
            });
        
            return $items;
        }

        return $items;
    }

    /**
     * @return array
     */
    public static function getBlockableModels(): array
    {
        return \DB::table('blockable')->pluck('model_type', 'model_type')
            ->unique()
            ->mapWithKeys(fn($type) => [$type => ucfirst($type)])
            ->toArray();
    }
}
