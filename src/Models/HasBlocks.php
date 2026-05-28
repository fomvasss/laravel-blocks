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
        $blockService = app()->make(BlockService::class);

        return $this->blocks
            ->map(fn($block) => BlockResource::make($blockService->initFromModel($block)->getBlock()))
            ->values()
            ->all();
    }
}
