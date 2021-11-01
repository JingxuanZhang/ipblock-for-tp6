<?php


namespace Moonpie\Tp6Tools\IpBlock;

use Moonpie\IpBlock\IpLocatorInterface;
use Moonpie\IpBlock\IpWhitelistInterface;
use Moonpie\IpBlock\Locator\LibsQQLocator;
use Moonpie\IpBlock\Locator\PcOnlineLocator;
use Moonpie\IpBlock\Whitelist\BasicIpWhitelist;
use think\App;
use think\helper\Arr;

class Service extends \think\Service
{
    public function register()
    {
        //绑定IP管理器
        $this->app->bind(
            IpWhitelistInterface::class,
            function (App $app) {
                $driver_name    = $app->config
                    ->get('ipblock.whitelist.default', 'basic');
                $service_config = $app
                    ->config->get(
                        sprintf('ipblock.whitelist.deciders.%s', $driver_name),
                        [
                            'type'     => 'basic',
                            'override' => false,
                            'keywords' => [],
                        ]
                    );
                switch ($service_config['type']) {
                    case 'custom':
                        return $app->get($service_config['id']);
                    case 'basic':
                    default:
                        return new BasicIpWhitelist(
                            Arr::get($service_config, 'keywords', []),
                            Arr::get($service_config, 'override', false),
                            $app->log
                        );
                }
            }
        );
        //绑定IP解析器
        $this->app->bind(
            IpLocatorInterface::class,
            function (App $c) {
                $driver_name    = $this->app->config
                    ->get('ipblock.default', 'pc_online');
                $service_config = $this->app
                    ->config->get(
                        sprintf('ipblock.locators.%s', $driver_name),
                        [
                            'type'  => 'pc_online',
                            'store' => 'default',
                        ]
                    );
                switch ($service_config['type']) {
                    case 'custom':
                        return $this->app->get($service_config['id']);
                    case 'qq_lib':
                    case 'pc_online':
                    default:
                        $lifetime           = Arr::get(
                            $service_config,
                            'cache.lifetime',
                            86400
                        );
                        $default_cache_tmpl = 'mp-tp6-ipblock:cache-item:ip-{ip}:information';
                        $cache_tmpl         = Arr::get(
                            $service_config,
                            'cache.cache_tmpl',
                            $default_cache_tmpl
                        );
                        if (strpos($cache_tmpl, '{ip}') === false) {
                            trigger_error(
                                sprintf(
                                    'Your cache tmpl not define placeholder for var "ip", please add string "{ip}" in some where,'.
                                    'and for safe reason we will use the default tmpl key: %s',
                                    $default_cache_tmpl
                                )
                            );
                            $cache_tmpl = $default_cache_tmpl;
                        }
                        $cache = $this->app->cache->store(
                            Arr::get($service_config,
                                'cache.store',
                                $this->app->config->get('cache.default', 'file')
                            )
                        );
                        if ($service_config['type'] == 'qq_lib') {

                            return new LibsQQLocator(
                                Arr::get($service_config, 'key', ''),
                                $cache,
                                $lifetime,
                                $cache_tmpl
                            );
                        }

                        return new PcOnlineLocator(
                            $cache,
                            $lifetime,
                            $cache_tmpl
                        );
                }
            }
        );
    }
}