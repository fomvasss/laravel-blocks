<?php

return [
    /* -----------------------------------------------------------------
     |  The default Model meta-tag
     | -----------------------------------------------------------------
     */
    'model' => [
        'class' => \Fomvasss\Blocks\Models\Block::class,
        
        'with_loaded' => [
            //'translations',
            //'models'
        ],
    ],

    'fieldhandlers' => [
        \Fomvasss\Blocks\Handlers\ImagecacheHandler::class,
    ],
    
    'images' => [
        'source' => [
            'disc' => 'public',
            'folders' => [
                'upload',
                'images',
                'photos/shares',
            ],
        ],
        
        'cache' => [
            'disc' => 'blocks', // Add config disc!
            'format' => 'webp',
            'routename' => 'blocks.imagecache',
        ],

//         Add next configuration disc to filesystems.php
//        'blocks' => [
//            'driver' => 'local',
//            'root' => storage_path('app/public/blocks'),
//            'url' => env('APP_URL').'/storage/blocks',
//            'visibility' => 'public',
//        ],
        
        'extensions' => '/\.(jpeg|jpg|png|gif)$/',
    ]
];
