PHP 聚合支付客户端
===
> 主要针对小程序场景，也包含部分业务需要的其它支付方式没有移除。

## 引入方式

    composer require yanlong-li/papayment

## 当前支持

* 微信小程序
* 支付宝小程序
* 快手小程序
* 字节小程序（头条、抖音）
* 百度小程序

## 使用案例

```php
<?php
include_once __DIR__ . '/vendor/autoload.php';


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

```

## 说明

1. 字节跳动（头条&抖音）、快手等支付是担保支付走收银台，实际还是要绑定或单独开通微信、支付宝等支付方式，这里不做区分，就以平台命名支付。 如字节跳动支付、快手支付，或者：字节跳动收银台、快手收银台，都是一个意思。
2. 并不是开了微信、支付宝支付就不需要单独申请了，以字节小程序为例，需要在字节平台开通单独的结算账户。快手好像是绑定现有账户即可，具体是同事处理的我没管开通这块。
3. 小程序基本都要获取用户身份来下单，要先登录获取用户授权，叫法不同：user_id、openid，还有的是给 code 在服务端通过 code2session
   获取。如果不了解可以查阅各平台的文档。（百度、字节跳动不需要，微信、快手、支付宝需要）
4. 当前微信sdk使用的是v2接口，后续将迁移至v3

## 更新日志

    2022年2月23日
        初始化仓库
