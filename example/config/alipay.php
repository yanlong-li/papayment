<?php

return [
    //应用ID,您的APPID。// https://openhome.alipay.com/platform/keyManage.htm#
    'app_id' => '',
    //商户私钥 // 支付宝签名工具(secret_key_tools_RSA256_win)生成的 rsa_private_key.pem
    'merchant_private_key' => '',
    //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。

    //模式二选一 公钥模式
    // 1.1 支付宝公钥
    'alipay_public_key' => '',
    //模式二选一 证书模式
    // 2.1 支支付宝证书路径
    'alipay_cert_path' => '',
    // 2.2 支付宝根证书路径
    'alipay_root_cert_path' => '',
    // 2.3 商户证书路径
    'merchant_cert_path' => '',

    //异步支付通知地址
    'notify_url' => '',
    //异步退款通知地址
    'refund_url' => '',
    //取消订单跳转地址 h5
    'cancel_url' => '',
    //支付成功跳转地址 h5
    'return_url' => '',
];
