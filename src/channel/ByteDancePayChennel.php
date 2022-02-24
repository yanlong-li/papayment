<?php

namespace papayment\channel;

use Exception;
use GuzzleHttp\Client;
use papayment\contract\Channel;
use papayment\Method;

class ByteDancePayChennel extends Channel
{
    protected $config;

    /**
     * @inheritDoc
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function unifiedOrder(string $outTradeNo, string $title, string $detail, float $totalFee,
                                 string $method = Method::MINIAPP, array $attach = [])
    {

        // 这里微信的价格是按 人民币 分 fen 为单位，所以倍率100
        $totalFee *= 100;

        $data = [
            // 小程序id
            'app_id' => $this->config['app_id'],
            // 订单号
            'out_order_no' => $outTradeNo,
            // 价格 分
            'total_amount' => $totalFee,
            // 商品标题，最长128
            'subject' => $title,
            //商品描述
            'body' => $detail ?: $title,// 字节跳动商品详情不能为空
            // 有效时间 秒，最长15分钟
            'valid_time' => 15 * 60,
            // '签名'
//            'sign' => '',
            // 自定义返回
//                'cp_extra' => '',
            // 通知回调地址，可选
            'notify_url' => $this->config['notify_url'] ?? '',
            // 是否屏蔽担保支付推送
//                'disable_msg' => '0',
            // 担保支付跳转页面
//                'msg_page' => '',
        ];

        $data['sign'] = self::sign($data);

        $client = new Client();
        $response = $client->post('https://developer.toutiao.com/api/apps/ecpay/v1/create_order', [
            'json' => $data
        ])->getBody()->getContents();
        $responseArray = json_decode($response, true);

        if (!isset($responseArray['err_no']) || $responseArray['err_no'] !== 0) {
            throw new Exception($responseArray['err_no'] ?? 255, $responseArray['err_tips'] ?? '请求发生未知错误');
        } else {
            return $responseArray['data'];
        }
    }

    public function sign($map)
    {
        $rList = array();
        foreach ($map as $k => $v) {
            if ($k == "other_settle_params" || $k == "app_id" || $k == "sign" || $k == "thirdparty_id")
                continue;
            $value = trim(strval($v));
            $len = strlen($value);
            if ($len > 1 && substr($value, 0, 1) == "\"" && substr($value, $len, $len - 1) == "\"")
                $value = substr($value, 1, $len - 1);
            $value = trim($value);
            if ($value == "" || $value == "null")
                continue;
            $rList[] = $value;
        }
        $rList[] = $this->config['salt'];
        sort($rList, SORT_STRING);
        return md5(implode('&', $rList));
    }

    /**
     * @inheritDoc
     */
    public function notifyVerify($data = null, &$resultData = null, array &$error = null)
    {
        $sortedString[] = $this->config['token'] ?? '';
        $sortedString[] = $data['timestamp'];
        $sortedString[] = $data['nonce'];
        $sortedString[] = $data['msg'];
        sort($sortedString, SORT_STRING);

        $resultData = json_decode($data['msg'], true);
        return $data['msg_signature'] === sha1(implode('', $sortedString));
    }

    /**
     * @inheritDoc
     */
    public function query($outTradeNo, $attach = [])
    {

        $params = [
            // 小程序id
            'app_id' => $this->config['app_id'],
            // 订单号
            'out_order_no' => $outTradeNo,
        ];
        $params['sign'] = self::sign($params);

        $client = new Client();
        $response = $client->post('https://developer.toutiao.com/api/apps/ecpay/v1/query_order', [
            'json' => $params
        ])->getBody()->getContents();
        $responseArray = json_decode($response, true);

        if (!isset($responseArray['err_no']) || $responseArray['err_no'] !== 0) {
            throw new Exception($responseArray['err_no'] ?? 255, $responseArray['err_tips'] ?? '请求发生未知错误');
        } else {
            return $responseArray['payment_info'];
        }
    }
}
