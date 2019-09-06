<h1 align="center">Latipay</h1>

## 运行环境
- PHP 7.0+

## 安装
```shell
composer require latipay/laravel-plugin
```

####添加 service provider（optional. if laravel < 5.5)
```
// laravel < 5.5
Latipay\LaravelPlugin\PayServiceProvider::class,

// lumen
$app->register(Latipay\LaravelPlugin\PayServiceProvider::class);
```

####配置文件
```
php artisan vendor:publish --provider="Latipay\LaravelPlugin\PayServiceProvider"
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

use Latipay\LaravelPlugin\Pay;

class LatipayController
{
    protected $config = [
        'api_key' => 'api_key',
        'user_id' => 'user_id',
        'wallet_id' => 'wallet_id',//支付货币id
        'version' => '2.0',//default
    ];

    //下单并返回支付url
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
				

        /** result数据，redirect_url为支付url
        * Array(
               [status] => success
               [redirect_url] => https://api.latiproduct.net/v2/gatewaydata_inapp/abcde
           )
        */
        return redirect($result['redirect_url']);
    }
    
    //获取支持的支付方式
    //返回数组 Array(
    //    [0] => Alipay
    //    [1] => Wechat
    //    [2] => Onlinebank
    //)
    public function getLatipayMethod()
    {
        return Pay::latipay($this->config)->getPaymentMethods();
    }
    
    //查询订单
    //返回数组内容参考
    //Array(
    //      [code] => 0
    //      [message] => SUCCESS
    //      [messageCN] => 操作成功
    //      [merchant_reference] => 1567568358
    //      [status] => paid
    //      [currency] => NZD
    //      [amount] => 0.02
    //      [amount_cny] => 0.1
    //      [rate] => 0
    //      [signature] => 103600c090f5f0738a2df5c891faf192b46111f0dca3ac5712d6138234054f4b
    //     [payment_method] => wechat
    //      [transaction_id] => 2019090400003370
    //      [order_id] => 2019090400003370
    //      [pay_time] => 2019-09-04 03:39:57
    //  )
    public function queryOrder($orderId)
    {
        return Pay::latipay($this->config)->find($orderId);
    }

    //支付完成后（成功或失败）浏览器重定向
    public function returnBack()
    {
        $data = Pay::latipay($this->config)->verify(); // 是的，验签就这么简单！
        
        //$data为collection
        //"merchant_reference" => "1567568358"
        //"order_id" => "2019090400003370"
        //"currency" => "NZD"
        //"status" => "paid"
        //"payment_method" => "wechat"
        //"signature" => "103600c090f5f0738a2df054f4b"
        //"createDate" => "2019-09-04 03:39:19"
        //"amount" => "0.02"
        
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
