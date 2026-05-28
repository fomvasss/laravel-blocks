<?php

declare(strict_types=1);

namespace Fomvasss\Blocks\Models;

use Fomvasss\Blocks\BlockService;
use Fomvasss\Blocks\Http\BlockResource;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasBlocks
{
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

        // TODO: https://i.ibb.co/KjBSTqFd/2026-05-08-09-35.jpg
        foreach ($this->blocks as $block) {
            $blockService = app()->make(BlockService::class);

            if ($initialized = $blockService->init($block->id, 'id')) {
                $res[] = BlockResource::make($initialized->getBlock());
            }
        }

        return $res;
    }
}
