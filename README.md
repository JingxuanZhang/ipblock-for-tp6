# ipblock-for-tp6
ipblock service for thinkphp6.0

## 示例
```php
composer require moonpie/think-ipblock
```
之后,在项目配置目录config下会多出ipblock.php

根据配置文件注解,选择好自己要使用的IP定位器和白名单规则

声明需要中间件保护的路由信息
```php
// route/app.php

Route::get('should protect by ip-block')
->middleware(\Moonpie\Tp6Tools\IpBlock\Middleware\IpBlock::class);
```

这样您的网站就只能在满足地理位置的条件下才能访问了

