<?php

namespace Fomvasss\Blocks\Contracts;

use Fomvasss\Blocks\Models\Block;

interface BlockHandlerInterface
{
    public static function getType(): string;

    public function handle(Block $block, array $attrs = []): array;
}
