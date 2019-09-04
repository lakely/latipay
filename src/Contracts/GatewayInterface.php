<?php

namespace Mamba\Latipay\Contracts;

interface GatewayInterface
{

    public function pay($endpoint, array $payload);
}
