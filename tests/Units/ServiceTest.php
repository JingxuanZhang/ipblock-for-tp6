<?php

namespace Moonpie\Test\Tp6Tools\IpBlock\Units;

use Moonpie\IpBlock\IpLocatorInterface;
use Moonpie\IpBlock\IpWhitelistInterface;
use Moonpie\IpBlock\locator\PcOnlineLocator;
use Moonpie\IpBlock\Whitelist\BasicIpWhitelist;
use Moonpie\Tp6Tools\IpBlock\Middleware\IpBlock;
use Moonpie\Tp6Tools\IpBlock\Service;
use PHPUnit\Framework\TestCase;
use think\App;
use think\exception\HttpException;
use think\Request;

class ServiceTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        parent::setUp();
        /** @var App $app */
        $this->app = new App();

        $this->app->config->set(
            [
                'log'     => [
                    // 默认日志记录通道
                    'default'      => 'file',
                    // 日志记录级别
                    'level'        => [],
                    // 日志类型记录的通道 ['error'=>'email',...]
                    'type_channel' => ['alert' => 'file'],
                    // 关闭全局日志写入
                    'close'        => false,
                    // 全局日志处理 支持闭包
                    'processor'    => null,

                    // 日志通道列表
                    'channels'     => [
                        'file' => [
                            // 日志记录方式
                            'type'           => 'File',
                            // 日志保存目录
                            'path'           => '',
                            // 单文件日志写入
                            'single'         => false,
                            // 独立日志级别
                            'apart_level'    => [
                                'error',
                                'info',
                                'sql',
                                'debug',
                                'alert',
                            ],
                            // 最大日志文件数量
                            'max_files'      => 0,
                            // 使用JSON格式记录
                            'json'           => false,
                            // 日志处理
                            'processor'      => null,
                            // 关闭通道日志写入
                            'close'          => false,
                            // 日志输出格式化
                            'format'         => '[%s][%s] %s',
                            // 是否实时写入
                            'realtime_write' => false,
                        ],
                        // 其它日志通道配置
                    ],
                ],
                'cache'   => [
                    'default' => 'file',

                    // 缓存连接方式配置
                    'stores'  => [
                        'file' => [
                            // 驱动方式
                            'type'       => 'File',
                            // 缓存保存目录
                            'path'       => '',
                            // 缓存前缀
                            'prefix'     => '',
                            // 缓存有效期 0表示永久缓存
                            'expire'     => 0,
                            // 缓存标签前缀
                            'tag_prefix' => 'tag:',
                            // 序列化机制 例如 ['serialize', 'unserialize']
                            'serialize'  => [],
                        ],
                    ],
                ],
                'ipblock' => [

                ],
            ]
        );

        $service = new Service($this->app);
        $service->register();
    }

    public function testRegister()
    {

        //定位器加载
        $this->assertTrue($this->app->has(IpLocatorInterface::class));
        $this->assertInstanceOf(
            PcOnlineLocator::class,
            $this->app->get(IpLocatorInterface::class)
        );
        //白名单加载
        $this->assertTrue($this->app->has(IpWhitelistInterface::class));
        $this->assertInstanceOf(
            BasicIpWhitelist::class,
            $this->app->get(IpWhitelistInterface::class)
        );

        return $this->app;
    }

    public function providerIp()
    {
        return [
            'localhost' => ['127.0.0.1', 'continue'],
            'outside'   => ['39.62.54.392', '网站维护中'],
        ];
    }

    /**
     * @depends      testRegister
     * @dataProvider providerIp
     * @param $ip
     * @param $expect
     */
    public function testMiddleware($ip, $expect)
    {
        /** @var IpBlock $middleware */
        $middleware             = $this->app->make(IpBlock::class);
        $request                = $this->getMockBuilder(Request::class)
                                       ->getMock();
        $_SERVER['REMOTE_ADDR'] = $ip;
        try {
            $str = $middleware->handle(
                $request,
                function ($request) use ($expect) {
                    return $expect;
                }
            );
        }catch (HttpException $e) {
            $str = $e->getMessage();
        }
        $this->assertEquals($str, $expect);
    }
}
