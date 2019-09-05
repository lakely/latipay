<?php

namespace Latipay\LaravelPlugin\Gateways\Latipay;


use Latipay\LaravelPlugin\Contracts\GatewayInterface;
use Latipay\LaravelPlugin\Exceptions\BusinessException;
use Latipay\LaravelPlugin\Kernel\Supports\Arr;
use Symfony\Component\HttpFoundation\Response;

class WebGateway implements GatewayInterface
{

    public function pay($endpoint, array $payload)
    {
        $preHash = $payload['user_id'].$payload['wallet_id'].$payload['amount'].$payload['payment_method'].$payload['return_url'].$payload['callback_url'];
        $payload['signature'] = hash_hmac('sha256', $preHash, $payload['api_key']);
        $payload['ip'] = Support::clientIP();

        if(isset($payload['latipya_test']) && $payload['latipya_test']) {
            $payload['ip'] = '127.0.0.1';
        }

        $postData = Arr::only($payload, [
            'wallet_id',
            'amount',
            'currency',
            'user_id',
            'merchant_reference',
            'return_url' ,
            'callback_url',
            'ip',
            'version',
            'product_name',
            'payment_method', // wechat, latipay, onlineBank
            'present_qr', // wechat
            'signature'
        ]);

        $return = [];
        try {
            $options = [];
            $options['headers'] = [
                "Content-Type" => "application/json"
            ];

            //dd($postData, $endpoint, $options);
            $payment = Support::requestApi($postData, $endpoint, $options);
            if ($payment['host_url'] != '') {
                $response_signature = hash_hmac('sha256', $payment['nonce'].$payment['host_url'], $payload['api_key']);
                if ($response_signature == $payment['signature']) {
                    $redirect_url         = $payment['host_url'].'/'.$payment['nonce'];
                    $return['status']      = 'success';
                    $return['redirect_url'] = $redirect_url;
                }
            } else {
                throw new BusinessException($payment['message']);
            }
        } catch (\Exception $e) {
            throw new BusinessException($e->getMessage());
        }

        return $return;
    }

    /**
     * Find.
     *
     * @author mamba <me@mamba.cn>
     *
     * @param $order
     *
     * @return array
     */
    public function find($order)
    {
        return [
            'method'      => 'latipay.trade.query',
            'biz_content' => json_encode(is_array($order) ? $order : ['out_trade_no' => $order]),
        ];
    }

    /**
     * Build Html response.
     *
     * @author mamba <me@mamba.cn>
     *
     * @param string $endpoint
     * @param array  $payload
     * @param string $method
     *
     * @return Response
     */
    protected function buildPayHtml($endpoint, $payload, $method = 'POST')
    {

    }

    /**
     * Get method config.
     *
     * @author mamba <me@mamba.cn>
     *
     * @return string
     */
    protected function getMethod()
    {
        return 'latipay.trade.page.pay';
    }

    /**
     * Get productCode config.
     *
     * @author mamba <me@mamba.cn>
     *
     * @return string
     */
    protected function getProductCode()
    {
        return 'FAST_INSTANT_TRADE_PAY';
    }
}
