<?php


namespace papayment\channel;

use Alipay\EasySDK\Kernel\Config;
use Alipay\EasySDK\Kernel\Factory;
use Alipay\EasySDK\Kernel\Util\ResponseChecker;
use Exception;
use papayment\contract\Channel;
use papayment\Method;

class AliPayChannel extends Channel
{
    protected $config;

    /**
     * @param array $config
     * @return void
     * @throws Exception
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;

        $options = new Config();
        $options->protocol = $config['protocol'] ?? 'https';
        $options->gatewayHost = $config['gateway_host'] ?? 'openapi.alipay.com';
        $options->signType = $config['sign_type'] ?? 'RSA2';

        $options->appId = $config['app_id'];

        // 为避免私钥随源码泄露，推荐从文件中读取私钥字符串而不是写入源码中
        $options->merchantPrivateKey = $config['merchant_private_key'];

        $options->alipayCertPath = $this->config['alipay_cert_path'] ?? null;
        $options->alipayRootCertPath = $this->config['alipay_root_cert_path'] ?? null;
        $options->merchantCertPath = $this->config['merchant_cert_path'] ?? null;

        //注：如果采用非证书模式，则无需赋值上面的三个证书路径，改为赋值如下的支付宝公钥字符串即可
        $options->alipayPublicKey = $config['alipay_public_key'] ?? null;

        //可设置异步通知接收服务地址（可选）
        $options->notifyUrl = $this->config['notify_url'] ?? '';

        //可设置AES密钥，调用AES加解密相关接口时需要（可选）
//        $options->encryptKey = "<-- 请填写您的AES密钥，例如：aa4BtZ4tspm2wnXLb1ThQA== -->";

        //1. 设置参数（全局只需设置一次）
        Factory::setOptions($options);
    }

    /**
     * @inheritDoc
     */
    public function unifiedOrder(string $outTradeNo, string $title, string $detail, float $totalFee, string $method = Method::MINIAPP, array $attach = [])
    {
        if (!$this->config) {
            throw new Exception("支付未初始化成功");
        }

        switch ($method) {
//            case Method::WEB:
//                $response = Factory::payment()->page()->batchOptional([
//                    'body' => $detail ?: $title,
//                ])->pay($title, $outTradeNo, $totalFee,$this->config['return_url']);
//                break;
//            case Method::H5;
//                $response = Factory::payment()->wap()->batchOptional([
//                    'body' => $detail ?: $title,
//                ])->pay($title, $outTradeNo, $totalFee,
//                    $this->config['quit_url'],
//                    $this->config['return_url']);
//                break;
//            case Method::APP:
//                $response = Factory::payment()->app()->batchOptional([
//                    'body' => $detail?:$title,
//                ])->pay($title, $outTradeNo, $totalFee);
//                break;
            case Method::MINIAPP:
                $response = Factory::base()->oauth()->getToken($attach['code']);
                $response = Factory::payment()->common()->batchOptional([
                    'product_code' => 'FACE_TO_FACE_PAYMENT',
                ])->create($title, $outTradeNo, $totalFee, $response->userId);
                break;
//            case Method::NATIVE:
            default:
                throw new Exception("不支持的支付方式");
        }

        $responseChecker = new ResponseChecker();
        if ($responseChecker->success($response)) {
            return $response;
        } else {
            throw new Exception($response->msg . "," . $response->subMsg, $response->code);
        }
    }


    /**
     * 验证订单
     * @param mixed $data       要验证的数据
     * @param mixed $resultData 验证后返回的数据，根据支付方式也可能无需返回
     * @param mixed $error
     * @return bool
     */
    public function notifyVerify($data = null, &$resultData = null, array &$error = null): bool
    {
        return Factory::payment()->common()->verifyNotify($data);
    }
}
