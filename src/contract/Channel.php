<?php

namespace papayment\contract;

use papayment\Method;

/**
 * 支付接口
 * Interface Pay
 * @package pay\components\pay
 */
abstract class Channel
{

    /**
     * 统一下单
     * @param string $outTradeNo 商户交易号
     * @param string $title      订单标题
     * @param string $detail     订单描述
     * @param float  $totalFee   价格 单位 RMB 元 yuan
     * @param string $method     支付方式
     * @param array  $attach     通道附加属性
     * @return mixed
     */
    abstract public function unifiedOrder(string $outTradeNo, string $title, string $detail, float $totalFee, string $method = Method::H5, array $attach = []);

    /**
     * 验证订单数据
     * @param mixed  $data
     * @param mixed  $resultData 返回验证后的数据
     * @param ?array $error      返回验证失败的提示信息
     * @return mixed
     */
    abstract public function notifyVerify($data = null, &$resultData = null, array &$error = null);
}
