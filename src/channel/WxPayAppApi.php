<?php


namespace papayment\channel;


use WxPayApi;
use WxPayException;

class WxPayAppApi
{


    /**
     *
     * 获取jsapi支付的参数
     * @param mixed|array $UnifiedOrderResult 统一支付接口返回的数据
     * @return array
     * @throws WxPayException
     *
     */
    public function GetAppApiParameters($UnifiedOrderResult, WxPayConfig $config)
    {
        if (!array_key_exists("appid", $UnifiedOrderResult)
            || !array_key_exists("prepay_id", $UnifiedOrderResult)
            || $UnifiedOrderResult['prepay_id'] == "") {
            throw new WxPayException("参数错误:" . json_encode($UnifiedOrderResult));
        }

        $jsapi = new WxPayAppApiPay();
        $jsapi->SetAppid($UnifiedOrderResult["appid"]);
        $timeStamp = time();
        $jsapi->SetTimeStamp("$timeStamp");
        $jsapi->SetPrepayid($UnifiedOrderResult['prepay_id']);
        $jsapi->SetPartnerid($config->GetMerchantId());
        $jsapi->SetNonceStr(WxPayApi::getNonceStr());
        $jsapi->SetPackage("Sign=WXPay");

        $jsapi->SetSign($jsapi->MakeSign($config, false));
        return $jsapi->GetValues();
    }

}
