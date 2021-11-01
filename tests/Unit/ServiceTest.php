<?php

namespace Moonpie\Test\Tp6Tools\IpBlock\Units;

use Moonpie\IpBlock\IpLocatorInterface;
use Moonpie\IpBlock\IpWhitelistInterface;
use Moonpie\IpBlock\locator\PcOnlineLocator;
use Moonpie\IpBlock\Middleware\IpBlock;
use Moonpie\IpBlock\Service;
use Moonpie\IpBlock\Whitelist\BasicIpWhitelist;
use PHPUnit\Framework\TestCase;
use think\App;
use think\Request;

class ServiceTest extends TestCase
{
    public function testRegister()
    {
        /** @var App $app */
        $app = new App();

        $app->config->set([
            'cache' => [
                'default' => 'file',

                // 缓存连接方式配置
                'stores' => [
                    'file' => [
                        // 驱动方式
                        'type' => 'File',
                        // 缓存保存目录
                        'path' => '',
                        // 缓存前缀
                        'prefix' => '',
                        // 缓存有效期 0表示永久缓存
                        'expire' => 0,
                        // 缓存标签前缀
                        'tag_prefix' => 'tag:',
                        // 序列化机制 例如 ['serialize', 'unserialize']
                        'serialize' => [],
                    ],
                ],
            ],
            'ipblock' => [

            ],
        ]);
        $service = new Service($app);
        $service->register();
        //定位器加载
        $this->assertTrue($app->has(IpLocatorInterface::class));
        $this->assertInstanceOf(PcOnlineLocator::class, $app->get(IpLocatorInterface::class));
        //白名单加载
        $this->assertTrue($app->has(IpWhitelistInterface::class));
        $this->assertInstanceOf(BasicIpWhitelist::class, $app->get(IpWhitelistInterface::class));
        return $app;
    }

    /**
     * @param App $app
     * @depends testRegister
     */
    public function testMiddleware(App $app)
    {
        /** @var IpBlock $middleware */
        $middleware  = $app->make(IpBlock::class);
        $request = $this->getMockBuilder(Request::class)
            ->getMock();
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $response = $middleware->handle($request, function($request, $next){
            return $next($request);
        });
    }
}
