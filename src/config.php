<?php
return [
    'default' => 'pc_online',
    'locators' => [
        'pc_online' => [
            'type' => 'pc_online',
            'cache' => [
                'store' => 'default',
                //'lifetime' => 86400,
                //cache_tmpl => 'any valid and clear cache key with placeholder "{ip}",
            ],
        ],
        'qq_lib' => [
            'type' => 'qq_lib',
            'key' => '//自己申请的腾讯地图应用key',
            'cache' => [
                'store' => 'default',
                //'lifetime' => 86400,
                //cache_tmpl => 'any valid and clear cache key with placeholder "{ip}",
            ],
        ],
        //自定义的
        //'my_lib' => [
        //    'type' => 'custom',
        //    'id' => 'your service id',
        //],
    ],
    'whitelist' => [
        'default' => 'basic',
        'deciders' => [
            'basic' => [
                'type' => 'basic',
                'keywords' => [
                ],
                'override' => false,
            ],
            //自定义白名单设置
            //'custom' => [
                //'type' => 'custom',
                //'id' => 'your service id',
            //],
        ],
    ],
];