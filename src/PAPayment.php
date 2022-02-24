<?php

namespace papayment;

use papayment\channel\AliPayChannel;
use papayment\channel\BaiduPayChennel;
use papayment\channel\ByteDancePayChennel;
use papayment\channel\KuaishouPayChennel;
use papayment\channel\WxPayChennel;
use papayment\contract\Channel;

class PAPayment
{
    /**
     * 系统支付，系统授权支付方式
     */
//    const CHANNEL_SYSTEM = 'system';
    /**
     * 余额支付
     */
//    const CHANNEL_BALANCE = "balance";
    /**
     * 支付宝支付
     */
    const CHANNEL_ALIPAY = 'alipay';
    /**
     * 微信支付
     */
    const CHANNEL_WXPAY = 'wxpay';
    /**
     * 苹果支付
     */
    const CHANNEL_APPLEPAY = 'applepay';
    /** 苹果应用商店 */
    const CHANNEL_APPLE_APPSTORE = 'appleappstore';
    /**
     * googlepay
     */
    const CHANNEL_GOOGLEPAY = 'googlepay';
    /**
     * 谷歌应用商店
     */
    const CHANNEL_GOOGLEPLAY = 'googleplay';
    /**
     * paypal
     */
    const CHANNEL_PAYPAL = 'paypal';
    /**
     * 银联卡
     */
    const CHANNEL_UNION_PAY = 'unionpay';
    /**
     * qq支付
     */
    const CHANNEL_QQPAY = 'qqpay';

    /** @var string 百度钱包 */
    const CHANNEL_BAIDU = 'baidu';

    /** @var string 字节跳动 */
    const CHANNEL_BYTEDANCE = 'bytedance';

    /** @var string 快手 */
    const CHANNEL_KUAISHOU = 'kuaishou';

    /**
     * @param $channel
     * @param $config
     * @return Channel
     */
    public static function init($channel, $config)
    {
        switch ($channel) {
            case self::CHANNEL_WXPAY;
                return new WxPayChennel($config);

            case self::CHANNEL_ALIPAY;
                return new AliPayChannel($config);

            case self::CHANNEL_BAIDU;
                return new BaiduPayChennel($config);

            case self::CHANNEL_BYTEDANCE;
                return new ByteDancePayChennel($config);

            case self::CHANNEL_KUAISHOU;
                return new KuaishouPayChennel($config);
            default:
                throw new \Exception("不支持的通道");

        }
    }
}
