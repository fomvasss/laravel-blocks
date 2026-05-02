<?php

return [
    /* -----------------------------------------------------------------
     |  The default Model
     | -----------------------------------------------------------------
     */
    'model' => [
        'class' => \Fomvasss\Blocks\Models\Block::class,

        'with_loaded' => [
            //'translations',
            //'models'
        ],
    ],

    /*
     | -----------------------------------------------------------------
     |  Field Handlers
     | -----------------------------------------------------------------
     |
     | Handlers are applied to every string field value of a block's content.
     | Each handler must implement a handle(Model $block, string $url): string method.
     |
     | To enable image URL transformation via fomvasss/laravel-imagepresets:
     |   1. composer require fomvasss/laravel-imagepresets
     |   2. Uncomment the line below.
     |   3. To change the preset — extend ImagepresetHandler and override $preset.
     |
     */
    'fieldhandlers' => [
        \Fomvasss\Blocks\Handlers\ImagepresetHandler::class,
    ],

];
