<?php
return [
    'default' => 'pc_online', //默认使用的IP定位器,注意这里指的是locators下的键名,而不是type值
    'locators' => [
        'pc_online' => [
            'type' => 'pc_online', //固定标识
            'cache' => [
                'store' => 'file', //默认使用的是cache.stores.file键下的配置,注意这里的file指的是stores下的键名,而不是type中的file
                //'lifetime' => 86400, //缓存有效期
                //cache_tmpl => 'any valid and clear cache key with placeholder "{ip}",
            ],
        ],
        'qq_lib' => [
            'type' => 'qq_lib', //固定标识
            'key' => '//自己申请的腾讯地图应用key',
            'cache' => [
                'store' => 'file', //同上注释的缓存配置注意事项
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
        'default' => 'basic', //这里指的是deciders下的键名,而不是type值
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