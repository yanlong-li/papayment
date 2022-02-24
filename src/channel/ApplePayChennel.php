<?php


namespace papayment\channel;


use GuzzleHttp\Client;
use papayment\contract\Channel;
use papayment\Method;

class ApplePayChennel extends Channel
{
    const ONLINE_URL = 'https://buy.itunes.apple.com/verifyReceipt';
    const SANDBOX_URL = 'https://sandbox.itunes.apple.com/verifyReceipt';

    protected $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function unifiedOrder(string $outTradeNo, string $title, string $detail, float $totalFee, string $method = Method::APP, array $attach = [])
    {
        return true;
    }

    public function notifyVerify($data = null, &$resultData = null, array &$error = null)
    {
        $notifyType = $data['notification_type'];
        if (in_array($notifyType, [
            'CANCEL',// 取消订阅
            'DID_CHANGE_RENEWAL_PREF', // 订阅变更计划
            'DID_FAIL_TO_RENEW', // 扣款失败
            'INITIAL_BUY', // 首次订阅
            'REFUND',  // 发生退款等
        ])) {
            $error = "非支付通知";
            // 订单状态不OK
            return false;
        }

        $receiptList = $data['unified_receipt']['latest_receipt_info'];
        if (empty($receiptList)) {
            // 没有订单数据
            $error = "无订单数据";
            return false;
        }
        if ($data['password'] !== $this->config['password']) {
            $error = "共享密钥错误";
            return false;
        }
        return true;
    }

    public function verify($receipt, &$error = null)
    {

        $curl = new Client();
        $responseStr = $curl->post((($this->config['in_app']['env'] ?? true) ? self::ONLINE_URL : self::SANDBOX_URL), [
            'json' => [
                'receipt-data' => $receipt,
                'password' => $this->config['in_app']['password'] ?? '',
                'exclude-old-transactions' => true,
            ]
        ])->getBody()->getContents();

        $response = json_decode($responseStr, true);
        if (!isset($response['status'])) {
            $error = "请求接口失败";
            return false;
        }
        // 支持混合验证
        if ($response['status'] === 21007) {
            $this->config['in_app']['env'] = false;
            $response = self::verify($receipt);
        } elseif ($response['status'] === 21008) {
            $this->config['in_app']['env'] = true;
            $response = self::verify($receipt);
        } elseif ($response['status'] === 21002) {
            //数据损坏
            $error = "数据损坏";
            return false;
        } elseif ($response['status'] === 21004) {
            // 共享密钥错误
            $error = "共享密钥错误";
            return false;
        } elseif ($response['status'] !== 0) {
            $error = "未处理的错误 {$response['status']}";
            return false;
        }
        return $response;
    }

    public function getProductType()
    {
        return $this->config['product_type'] ?? '';
    }
}
