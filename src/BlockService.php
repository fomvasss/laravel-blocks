<?php

declare(strict_types=1);

namespace Fomvasss\Blocks;

use Fomvasss\Blocks\Http\BlockResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class BlockService
{
    protected ?Model $block = null;

    protected array $attrs = [];

    protected array $handlers = [];

    /**
     * @param string $type
     * @param string $class
     * @return void
     */
    public function register(string $type, string $class): void
    {
        if (isset($this->handlers[$type]) && $this->handlers[$type] !== $class) {
            throw new \LogicException(
                "Handler для типу \"{$type}\" вже зареєстровано: {$this->handlers[$type]}. Конфлікт з: {$class}"
            );
        }

        $this->handlers[$type] = $class;
    }

    /**
     * Initialise block.
     *
     * @param string|int $key
     * @param string $keyField
     * @return static
     */
    public function init(string|int $key, string $keyField = 'slug', array $attrs = []): static
    {
        // Reset block state between calls (singleton safety).
        // $attrs is intentionally NOT reset — it is a global runtime config
        // set once via setAttrs()/replaceAttrs() and shared across all init() calls.
        // Use replaceAttrs([]) to clear manually if needed.
        $this->block = null;

        $modelClass = config('blocks.model.class');

        if ($block = Cache::get($modelClass::getCacheName($key))) {
            // Static content is already prepared in cache; re-run dynamic handler
            // so current $this->attrs are applied (attrs are not part of the cache key).
            $block->data = array_merge($block->content ?: [], $this->prepareDynamicContent($block));
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
                Cache::put($modelClass::getCacheName($key), $block, $time * 60);
            }

            $this->block = $block;

            return $this;
        }

        return $this;
    }

    /**
     * Initialise block from already loaded model (no DB query).
     */
    public function initFromModel(Model $block): static
    {
        $this->block = null;

        $block->content = $this->prepareStaticContent($block, $block->content ?? []);
        $block->data = array_merge($block->content ?: [], $this->prepareDynamicContent($block));

        $this->block = $block;

        return $this;
    }

    /**
     * Get model initialize block.
     */
    public function getBlock(): ?Model
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
     * Tmp dynamic settings.
     *
     * @param array $attrs
     * @return void
     */
    public function setAttrs(array $attrs): static
    {
        $this->attrs = array_merge($this->attrs, $attrs);

        return $this;
    }

    public function replaceAttrs(array $attrs): static
    {
        $this->attrs = $attrs;

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
     * @param string|array $blocksKeys
     * @param string|bool $mapKey
     * @param string $initKey
     * @param mixed $default
     * @return array|null
     */
    public function getBlocksResource(string|array $blocksKeys, string|bool $mapKey = '', string $initKey = 'slug', $default = []): array|null
    {
        $res = [];

        // Нормалізуємо mapKey один раз поза циклом
        $resolvedMapKey = is_string($mapKey) && $mapKey !== '' ? $mapKey : ($mapKey ? 'slug' : '');

        foreach (Arr::wrap($blocksKeys) as $blockSlug) {
            if ($block = $this->init($blockSlug, $initKey)->getBlock()) {
                if ($resolvedMapKey) {
                    $res[$block->{$resolvedMapKey}] = BlockResource::make($block);
                } else {
                    $res[] = BlockResource::make($block);
                }
            }
        }

        return $res ?: $default;
    }

    /**
     * @param string $blockSlug
     * @return BlockResource|null
     */
    public function getBlockResource(string $blockSlug): ?BlockResource
    {
        if ($block = $this->init($blockSlug)->getBlock()) {
            return BlockResource::make($block);
        }

        return null;
    }

    protected function prepareStaticContent(Model $block, array $content = []): array
    {
        $handlers = array_filter(
            array_map(
                fn(string $class) => class_exists($class) ? app()->make($class) : null,
                config('blocks.fieldhandlers') ?? []
            )
        );

        return $this->applyFieldHandlers($block, $content, $handlers);
    }

    private function applyFieldHandlers(Model $block, array $content, array $handlers): array
    {
        $res = $content;

        foreach ($content as $key => $val) {
            if (is_array($val)) {
                $res[$key] = $this->applyFieldHandlers($block, $val, $handlers);
            } elseif (is_string($val)) {
                foreach ($handlers as $handler) {
                    $val = $handler->handle($block, $val);
                }
                $res[$key] = $val;
            }
        }

        return $res;
    }
}
