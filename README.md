<h1 align="center">Latiay</h1>

## 运行环境
- PHP 7.0+
- composer 

## 安装
```shell
composer require mamba/latipay:dev-master
```

####添加 service provider（optional. if laravel < 5.5)
```
// laravel < 5.5
Mamba\Latipay\PayServiceProvider::class,
```

####配置文件
```
// laravel < 5.5
php artisan vendor:publish --provider="Mamba\Latipay\PayServiceProvider"
```

`.env`文件中配置
```
LATIPAY_API_KEY=
LATIPAY_USER_ID=
LATIPAY_WALLET_ID_NZD=
LATIPAY_WALLET_ID_AUD=
LATIPAY_WALLET_ID_CNY=
```

## 使用说明

```php
<?php

namespace App\Http\Controllers;

use Mamba\Latipay\Pay;

class LatipayController
{
    protected $config = [
        'api_key' => 'api_key',
				'user_id' => 'user_id',
				'wallet_id' => 'wallet_id',//支付货币id
				'version' => '2.0',//default
    ];

    public function index()
    {
        $order = [
            'merchant_reference' => time(),
						'amount' => '0.2',
						'product_name' => 'test order - 测试',
						'return_url' => 'return_url',
						'callback_url' => 'callback_url',
						'payment_method' => 'wechat', // wechat, alipay, onlineBank
						'present_qr' => '1', // wechat
        ];

        $result = Pay::latipay($this->config)->web($order);

        return redirect($result['redirect_url']);
    }
    
    //获取支持的支付方式
    public function getLatipayMethod()
    {
        return Pay::latipay($this->config)->getPaymentMethods();
    }
    
    //查询订单
		public function queryOrder($orderId)
		{
				return Pay::latipay($this->config)->find($orderId);
		}


		//支付完成后（成功或失败）浏览器重定向
    public function return()
    {
        $data = Pay::latipay($this->config)->verify(); // 是的，验签就这么简单！

				//$data为collection
				//  "merchant_reference" => "1567568358"
				//  "order_id" => "2019090400003370"
				//  "currency" => "NZD"
				//  "status" => "paid"
				//  "payment_method" => "wechat"
				//  "signature" => "103600c090f5f0738a2df5c891faf192b46111f0dca3ac5712d6138234054f4b"
				//  "createDate" => "2019-09-04 03:39:19"
				//  "amount" => "0.02"
				
				
				//重定向逻辑
       
    }


		//支付结果异步通知
    public function notify()
    {
        $latipay = Pay::latipay($this->config);
    
        try{
            $data = $latipay->verify(); // 是的，验签就这么简单！
            //data内容同上

           //回调业务逻辑

        } catch (\Exception $e) {
            // $e->getMessage();
        }

        return $latipay->success();
    }
}
```

## 错误
如果在调用相关支付网关 API 时有错误产生，会抛出 `GatewayException`,`InvalidSignException` 错误，可以通过 `$e->getMessage()` 查看，同时，也可通过 `$e->raw` 查看调用 API 后返回的原始数据，该值为数组格式。


## LICENSE
MIT
