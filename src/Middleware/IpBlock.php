<?php
declare (strict_types=1);

namespace Moonpie\Tp6Tools\IpBlock\Middleware;


use Moonpie\IpBlock\IpLocatorInterface;
use Moonpie\IpBlock\IpWhitelistInterface;
use think\exception\HttpException;
use think\Request;

class IpBlock
{

    protected $whitelist;
    protected $locator;

    public function __construct(
        IpWhitelistInterface $whitelist,
        IpLocatorInterface $locator
    ) {
        $this->whitelist = $whitelist;
        $this->locator   = $locator;
    }

    private function getIp()
    {
        $ip = false;
        //客户端IP 或 NONE
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        //多重代理服务器下的客户端真实IP地址（可能伪造）,如果没有使用代理，此字段为空
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ip) {
                array_unshift($ips, $ip);
                $ip = false;
            }
            for ($i = 0; $i < count($ips); $i++) {
                if (!preg_match("/^(10│172.16│192.168)$/", $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        }

        //客户端IP 或 (最后一个)代理服务器 IP
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }

    public function handle(Request $request, \Closure $next)
    {
        $ip = $this->getIp();

        if (false === $ip) {
            $ip = $request->ip();
        }

        $ip_location = $this->locator->getLocation($ip);

        $continue = $this->whitelist->isPassed($ip_location);
        if (!$continue) {
            $remark = sprintf(
                '系统拒绝(ip: %s, 地址: %s)的网络请求,因为它不是系统认可的IP地址,其访问的地址是%s',
                $ip_location->getIp(),
                $ip_location->getFullLocation(),
                $request->url(true)
            );
            $this->whitelist->logUnSafeIp($ip_location, $remark);
            if ($request->isAjax()) {
                return \json(
                    [
                        'code' => -100,
                        'msg'  => '信息不存在',
                    ]
                );
            }

            throw new HttpException(404, '网站维护中');
        }

        return $next($request);
    }
}
