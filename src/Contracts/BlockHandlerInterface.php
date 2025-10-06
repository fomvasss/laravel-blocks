<?php

namespace Fomvasss\Blocks\Contracts;

use Illuminate\Database\Eloquent\Model;

interface BlockHandlerInterface
{
    public static function getType(): string;

    public function handle(Model $block, array $attrs = []): array;
}
