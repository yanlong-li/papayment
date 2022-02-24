<?php


namespace papayment\channel;

use WxPayConfigInterface;

require_once __DIR__ . "/../../extends/wxpay/lib/WxPay.Api.php";

class WxPayConfig extends WxPayConfigInterface
{

    protected $config = [];

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function GetAppId()
    {
        return $this->config['app_id'];
    }

    public function GetMerchantId()
    {
        return $this->config['merchant_id'];
    }

    public function GetNotifyUrl()
    {
        return $this->config['notify_url'] ?? "";
    }

    public function GetSignType()
    {
        return strtoupper($this->config['sign_type'] ?? 'md5');
    }

    public function GetProxy(&$proxyHost, &$proxyPort)
    {
        $proxyHost = $this->config['proxy_host'] ?? $proxyHost;
        $proxyPort = $this->config['proxy_port'] ?? $proxyPort;
    }

    public function GetReportLevenl()
    {
        return $this->config['report_level'] ?? 1;
    }

    public function GetKey()
    {
        return $this->config['key'] ?? null;
    }

    public function GetAppSecret()
    {
        return $this->config['app_secret'] ?? null;
    }

    public function GetSSLCertPath(&$sslCertPath, &$sslKeyPath)
    {
        $sslCertPath = $this->config['ssl_cert_path'] ?? $sslCertPath;
        $sslKeyPath = $this->config['ssl_key_path'] ?? $sslKeyPath;
    }
}
