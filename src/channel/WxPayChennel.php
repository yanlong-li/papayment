<?php


namespace papayment\channel;

use Exception;
use GuzzleHttp\Client;
use papayment\contract\Channel;
use papayment\Method;
use WxPayApi;
use WxPayException;
use WxPayNotifyResults;
use WxPayUnifiedOrder;

class WxPayChennel extends Channel
{
    /**
     * @var WxPayConfig
     */
    protected $config;

    /**
     * @param array $config
     * @return mixed|void
     */
    public function __construct(array $config = [])
    {
        $this->config = new WxPayConfig($config);
    }

    /**
     * 统一下单
     * @param        $outTradeNo
     * @param        $title
     * @param        $detail
     * @param        $totalFee
     * @param string $method
     * @param array  $attach
     * @return array
     * @throws WxPayException|Exception
     */
    public function unifiedOrder(string $outTradeNo, string $title, string $detail, float $totalFee, string $method = Method::MINIAPP, array $attach = [])
    {
        if (!$this->config) {
            throw new Exception("支付未初始化成功");
        }

        // 这里微信的价格是按 人民币 分 fen 为单位，所以倍率100
        $totalFee *= 100;

        $input = new WxPayUnifiedOrder();
        $input->SetBody($title);
        $input->SetOut_trade_no($outTradeNo);
        $input->SetTotal_fee($totalFee);

        switch ($method) {
            case Method::H5;
                $input->SetTrade_type('MWEB');
                break;
            case Method::APP:
                $input->SetTrade_type('APP');
                break;
            case Method::NATIVE:
                $input->SetTrade_type('NATIVE');
                $input->SetProduct_id($attach['product_id']);
                break;
            case Method::JSAPI:
            case Method::MINIAPP:
                if (isset($attach['openid'])) {
                    $openId = $attach['openid'];
                } else {
                    $openId = self::code2session($attach['code']);
                }
                $input->SetTrade_type('JSAPI');
                $input->SetOpenid($openId);
                break;
            default:
                throw new WxPayException("不支持的支付方式");
        }

        /**
         * @var $result array
         */
        $result = WxPayApi::unifiedOrder($this->config, $input, 3);
        if (!isset($result['result_code']) || $result['result_code'] != 'SUCCESS') {
            //LogError
            throw new WxPayException('系统错误：return_msg=>' . ($result['return_msg'] ?? '') . '/err_msg=>' . ($result['err_code_des'] ?? ''));
        }

        if ($method === Method::JSAPI || $method === Method::MINIAPP) {
            $result = $this->genJsApiParams($result);
        }
        if ($method === Method::APP) {
            $result = $this->genAppApiParams($result);
        }

        return $result;

    }

    public function code2session($code)
    {
        $url = 'https://api.weixin.qq.com/sns/jscode2session';
        $client = new Client();
        $queryParams = [
            'appid' => $this->config->GetAppId(),
            'secret' => $this->config->GetAppSecret(),
            'js_code' => $code,
            'grant_type' => 'authorization_code',
        ];
        $url = $url . '?' . http_build_query($queryParams);
        $response = $client->get($url)->getBody()->getContents();
        $responseArray = json_decode($response, true);
        if (!isset($responseArray['openid'])) {
            throw new Exception($response, $responseArray['errcode'] ?? 1);
        }
        return $responseArray['openid'];
    }


    public function genJsApiParams($result)
    {
        $jsPai = new WxPayJsApi();
        return $jsPai->GetJsApiParameters($result, $this->config);
    }

    public function genAppApiParams($result)
    {
        $appPay = new WxPayAppApi();
        return $appPay->GetAppApiParameters($result, $this->config);
    }

    /**
     * 微信通知验证
     * @param ?string $data xmlData
     * @param mixed   $resultData
     * @param ?array  $error
     * @return bool
     */
    public function notifyVerify($data = null, &$resultData = null, array &$error = null)
    {
        try {
            $data = $data ?? ($GLOBALS['HTTP_RAW_POST_DATA'] ?? file_get_contents("php://input"));
            $wxPayNotify = new WxPayNotifyResults();
            if (is_array($data)) {
                $wxPayNotify->FromArray($data);
            } else {
                $wxPayNotify->FromXml($data);
            }
            // 无错误表示验签成功
            $wxPayNotify->CheckSign($this->config);
            // 获取验证结果
            $resultData = $wxPayNotify->GetValues();
            return true;
        } catch (Exception $exception) {
            $error = $exception;
            return false;
        }
    }
}
