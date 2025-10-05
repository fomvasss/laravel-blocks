<?php

namespace Fomvasss\Blocks;

use Fomvasss\Blocks\Http\BlockResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class BlockService
{
    protected Model $block;

    protected array $attrs = [];

    protected array $handlers = [];

    /**
     * @param string $type
     * @param string $class
     * @return void
     */
    public function register(string $type, string $class): void
    {
        $this->handlers[$type] = $class;
    }

    /**
     * Initialise block.
     *
     * @param string $key
     * @param string $keyField
     * @return $this|null
     */
    public function init(string $key, string $keyField = 'slug', array $attrs = [])
    {
        $modelClass = config('blocks.model.class');
        
        if ($block = Cache::get($modelClass::getCacheName($key))) {
            $this->block = $block;

            return $this;
        }
        
        if ($block = $modelClass::with(config('blocks.model.with_loaded') ?: [])->where($keyField, $key)->first()) {
            
            if ($attrs) {
                $this->setAttrs($attrs);
            }
            
            $block->content = $this->prepareStaticContent($block, $block->content ?? []);

            $block->data = \array_merge($block->content ?: [], $this->prepareDynamicContent($block));

            if ($time = $block->getOptions('cache')) { // minutes
                Cache::remember($modelClass::getCacheName($key), $time * 60, fn () => $block);
            }

            $this->block = $block;

            return $this;
        }

        return null;
    }

    /**
     * Get model initialize block.
     */
    public function getBlock(): Model
    {
        return $this->block;
    }

    /**
     * Get preparing content.
     *
     * @param string $key
     * @param $default
     * @return array|\ArrayAccess|mixed|null
     */
    public function getData(string $key, $default = null)
    {
        if ($this->block) {
            return Arr::get($this->block->data, $key) ?? $default;
        }

        return $default;
    }


    /**
     * Get original (raw) sorting array content.
     * Alias getContentSort()
     *
     * @param string $key
     * @param $default
     * @return array|\ArrayAccess|mixed|null
     */
    public function getDataSort(string $key, $default = null)
    {
        return  $this->getContentSort($key, $default);
    }

    /**
     * Get original (raw) content.
     *
     * @param string $key
     * @param $default
     * @return array|\ArrayAccess|mixed|null
     */
    public function getContent(string $key, $default = null)
    {
        if ($this->block) {
            return $this->block->getContent($key, $default);
        }

        return  $default;
    }

    /**
     * Get original (raw) sorting array content.
     *
     * @param string $key
     * @param $default
     * @return array|\ArrayAccess|mixed|null
     */
    public function getContentSort(string $key, $default = null)
    {
        if ($this->block) {
            return $this->block->getContentSort($key, $default);
        }

        return  $default;
    }

    /**
     * TODO: Deprecated
     * 
     * @param array $attrs
     * @return null
     */
    public function setOptions(array $attrs)
    {
        return $this->setAttrs($attrs);
    }

    /**
     * Tmp dynamic settings.
     *
     * @param array $attrs
     * @return void
     */
    public function setAttrs(array $attrs)
    {
        $this->attrs = array_merge($this->attrs, $attrs);
        
        return $this;
    }
    
    /**
     * @param Model $block
     * @return array
     * @throws \Exception
     */
    protected function prepareDynamicContent(Model $block): array
    {
        $data = [];

        if (isset($this->handlers[$block->type])) {
            return app($this->handlers[$block->type])->handle($block, $this->attrs);
        }

        return $data;
    }
    
    /**
     * For resources in sblocks field (PageControlled, GlobController, etc).
     *
     * @param $blocksSlug
     * @param bool $mapSlug
     * @return array
     */
    public function getBlocksResource($blocksSlug, bool $mapSlug = false)
    {
        $res = [];

        foreach (Arr::wrap($blocksSlug) as $blockSlug) {
            if ($block = $this->init($blockSlug)?->getBlock()) {
                if ($mapSlug) {
                    $res[$block->slug] = BlockResource::make($block);
                } else {
                    $res[] = BlockResource::make($block);
                }
            }
        }

        return $res;
    }

    /**
     * @param string $blockSlug
     * @return BlockResource|null
     */
    public function getBlockResource(string $blockSlug)
    {
        if ($block = $this->init($blockSlug)) {
            return BlockResource::make($block->getBlock());
        }

        return null;
    }

    /**
     * Підготовка статичних даних блоку.
     *
     * @param array $content
     * @return array
     */
    protected function prepareStaticContent(Model $block, array $content = [])
    {
        $res = $content;

        foreach ($content as $key => $val) {
            if (is_array($val)) {
                $res[$key] = $this->prepareStaticContent($block, $val);
            } elseif (is_string($val)) {
                $resVal = $val;
                foreach (config('blocks.fieldhandlers') as $handler) {
                    if (class_exists($handler)) {
                        $resVal = app()->make($handler)->handle($block, $resVal);
                    }
                }
                $res[$key] = $resVal;
            }
        }

        return $res;
    }
}
