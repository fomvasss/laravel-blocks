<?php

namespace Fomvasss\Blocks\Models;

use Fomvasss\Blocks\BlockService;
use Fomvasss\Blocks\Http\BlockResource;
use Fomvasss\Models\Block;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasBlocks
{
    public array $defaultTags = [];

    public function blocks(): MorphToMany
    {
        $modelClass = config('blocks.model.class');
        
        return $this->morphToMany($modelClass, 'model', 'blockable')
            ->withPivot('id')
            ->orderByPivot('weight');
    }

    public function getResourceBlocks(): array
    {
        $res = [];

        foreach ($this->blocks as $block) {
            $blockService = $this->app->make(BlockService::class);
            $block = $blockService->init($block->id, 'id')->getBlock();

            $res[] = BlockResource::make($block);
        }
        return $res;
    }
}
