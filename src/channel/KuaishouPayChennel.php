<?php

namespace papayment\channel;

use Exception;
use GuzzleHttp\Client;
use papayment\contract\Channel;
use papayment\Method;

/**
 * 快手支付
 */
class KuaishouPayChennel extends Channel
{

    /**
     * @var string
     */
    protected static $accessToken = null;
    /**
     * @var Client
     */
    protected static $client = null;
    protected $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
        self::$client = new Client();
        self::initAccessToken();
    }

    protected function initAccessToken()
    {
        // 从缓存获取
        if (self::$accessToken) {
            return;
        }
        // 获取新授权
        $response = self::$client->post('https://open.kuaishou.com/oauth2/access_token', [
            'form_params' => [
                'app_id' => $this->config['app_id'],
                'app_secret' => $this->config['app_secret'],
                'grant_type' => $this->config['grant_type'] ?? 'client_credentials',
            ]
        ])->getBody()->getContents();

        $responseArray = json_decode($response, true);
        if (!isset($responseArray['result']) || $responseArray['result'] !== 1) {
            throw new Exception("快手获取 AccessToken 发生错误", $responseArray['result'] ?? 0);
        }
        self::log('access_token', $response);
        self::$accessToken = $responseArray['access_token'];
    }

    public static function log($cate, $content)
    {
        $date = date('Ymd');
        file_put_contents("../runtime/logs/kuaishou-$date-$cate.log",
            date('Y-m-d H:i:s') . "\t" . $content . PHP_EOL . PHP_EOL, FILE_APPEND);
    }

    public function unifiedOrder(string $outTradeNo, string $title, string $detail, float $totalFee,
                                 string $method = Method::MINIAPP, array $attach = [])
    {

        $session = self::code2session($attach['code']);

        $queryParams = [
            'app_id' => $this->config['app_id'],
            'access_token' => self::$accessToken,
        ];

        $data = [
            'out_order_no' => $outTradeNo,
            'open_id' => $session['open_id'],
            'total_amount' => $totalFee * 100,
            'subject' => $title,
            'detail' => $detail ?: $title,
            'type' => $this->config['type'] ?? 3306,
            'expire_time' => 15 * 60,
//            'sign'=>'',
//            'attach'=> json_encode($attach),
            'notify_url' => $this->config['notify_url'] ?? '',
//            'goods_id'=>'',
//            'goods_detail_url'=>'',
        ];

        $data['sign'] = self::sign(array_merge($data, ['app_id' => $this->config['app_id']]));
        $response = self::$client->post('https://open.kuaishou.com/openapi/mp/developer/epay/create_order?' . http_build_query($queryParams), [
            'json' => $data
        ])->getBody()->getContents();
        $responseArray = json_decode($response, true);
        if (!isset($responseArray['result']) || $responseArray['result'] !== 1) {
            throw new Exception("快手 unifiedOrder 发生错误" . json_encode(['request' => $data, 'response' => $response]), $responseArray['result'] ?? 0);
        }
        return $responseArray['order_info'];
    }

    /**
     * @param $code
     * @return array
     *         {
     *         open_id:string,
     *         result:int,
     *         session_key:string
     *         }
     * @throws Exception
     */
    public function code2session($code)
    {
        $data = [
            'app_id' => $this->config['app_id'],
            'app_secret' => $this->config['app_secret'],
            'js_code' => $code,
        ];
        $response = self::$client->post('https://open.kuaishou.com/oauth2/mp/code2session', [
            'form_params' => $data
        ])->getBody()->getContents();
        $responseArray = json_decode($response, true);
        if (!isset($responseArray['result']) || $responseArray['result'] !== 1) {
            throw new Exception("快手 code2session 发生错误" . json_encode(['request' => $data, 'response' => $response]), $responseArray['result'] ?? 0);
        }
        return $responseArray;
    }

    protected function sign($data)
    {

        ksort($data);
        $str = [];
        foreach ($data as $key => $item) {
            if ($key === 'sign' || is_null($item) || $item === "") {
                continue;
            }
            if (is_array($item)) {
                $item = json_encode($item);
            }
            $str[] = "$key=$item";
        }
        $data = implode('&', $str) . $this->config['app_secret'];
        return md5($data);
    }

    public function notifyVerify($data = null, &$resultData = null, array &$error = null)
    {
        return KuaishouPayChennel::sign($data['data']) === $data['sign'];
    }
}
