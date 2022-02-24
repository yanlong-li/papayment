<?php
include_once __DIR__ . '/../vendor/autoload.php';


$tradeNo = '20220224164301'; // 订单号
$fee = 0.01; // 订单额 元
$title = '我是订单标题';
$detail = '我是订单详情';

$payChannel = \papayment\PAPayment::CHANNEL_WXPAY;
$method = \papayment\Method::MINIAPP;
// 微信、支付宝、快手必传 code
$attach = [
    'code' => '',
];

/**
 * @var $channel \papayment\contract\Channel
 */
$channel = \papayment\PAPayment::init($channel, require 'config/wxpay.php');
$response = $channel->unifiedOrder($tradeNo, $title, $detail, $fee, $method, $attach);
