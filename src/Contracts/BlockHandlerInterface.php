<?php

namespace Fomvasss\Blocks\Contracts;

use Illuminate\Database\Eloquent\Model;

interface BlockHandlerInterface
{
    /**
     * Повертає масив типів блоків, які обробляє цей handler.
     *
     * @return string[]
     */
    public static function getTypes(): array;

    public function handle(Model $block, array $attrs = []): array;
}
