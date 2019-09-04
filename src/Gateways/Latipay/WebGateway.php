<?php

namespace Mamba\Latipay\Gateways\Latipay;


use Mamba\Latipay\Contracts\GatewayInterface;
use Mamba\Latipay\Exceptions\BusinessException;
use Mamba\Latipay\Kernel\Supports\Arr;
use Symfony\Component\HttpFoundation\Response;

class WebGateway implements GatewayInterface
{

    public function pay($endpoint, array $payload)
    {
        $preHash = $payload['user_id'].$payload['wallet_id'].$payload['amount'].$payload['payment_method'].$payload['return_url'].$payload['callback_url'];
        $payload['signature'] = hash_hmac('sha256', $preHash, $payload['api_key']);
        $payload['ip'] = Support::clientIP();

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
     * @author yansongda <me@yansongda.cn>
     *
     * @param $order
     *
     * @return array
     */
    public function find($order): array
    {
        return [
            'method'      => 'latipay.trade.query',
            'biz_content' => json_encode(is_array($order) ? $order : ['out_trade_no' => $order]),
        ];
    }

    /**
     * Build Html response.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @param string $endpoint
     * @param array  $payload
     * @param string $method
     *
     * @return Response
     */
    protected function buildPayHtml($endpoint, $payload, $method = 'POST'): Response
    {

    }

    /**
     * Get method config.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    protected function getMethod(): string
    {
        return 'latipay.trade.page.pay';
    }

    /**
     * Get productCode config.
     *
     * @author yansongda <me@yansongda.cn>
     *
     * @return string
     */
    protected function getProductCode(): string
    {
        return 'FAST_INSTANT_TRADE_PAY';
    }
}
