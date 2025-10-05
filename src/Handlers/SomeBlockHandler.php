<?php

namespace Fomvasss\Blocks\Handlers;

use Fomvasss\Blocks\Contracts\BlockHandlerInterface;
use Fomvasss\Blocks\Models\Block;

class SomeBlockHandler implements BlockHandlerInterface
{
    public static function getType(): string
    {
        return 'some';
    }

    public function handle(Block $block, array $attrs = []): array
    {
        return [
            'phones' => $block->content['phones'] ?? [],
            'emails' => $block->content['emails'] ?? [],
            'address' => $block->content['address'] ?? null,
        ] + $attrs;
    }
}
