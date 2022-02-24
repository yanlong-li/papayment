<?php

namespace papayment;

/**
 * 支付方式
 */
final class Method
{

    /**
     * 手机浏览器支付
     */
    const H5 = 'h5';
    /**
     * 电脑浏览器支付
     */
    const WEB = 'web';
    /**
     * app支付
     */
    const APP = 'app';
    /**
     * 扫码支付
     */
    const NATIVE = 'native';
    /**
     * 微信公众号支付独有
     */
    const JSAPI = 'jsapi';
    /** @var string 小程序支付 */
    const MINIAPP = 'miniapp';
    /** @var string 刷脸支付 */
    const FACE = 'face';

    private function __construct()
    {
    }
}
