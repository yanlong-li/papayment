<?php


namespace papayment\channel;


use WxPayApi;
use WxPayException;
use WxPayJsApiPay;

class WxPayJsApi
{


    /**
     *
     * 获取jsapi支付的参数
     * @param mixed|array $UnifiedOrderResult 统一支付接口返回的数据
     * @return array
     * @throws WxPayException
     *
     */
    public function GetJsApiParameters($UnifiedOrderResult, WxPayConfig $config)
    {
        if (!array_key_exists("appid", $UnifiedOrderResult)
            || !array_key_exists("prepay_id", $UnifiedOrderResult)
            || $UnifiedOrderResult['prepay_id'] == "") {
            throw new WxPayException("参数错误");
        }

        $jsapi = new WxPayJsApiPay();
        $jsapi->SetAppid($UnifiedOrderResult["appid"]);
        $timeStamp = time();
        $jsapi->SetTimeStamp("$timeStamp");
        $jsapi->SetNonceStr(WxPayApi::getNonceStr());
        $jsapi->SetPackage("prepay_id=" . $UnifiedOrderResult['prepay_id']);

        $jsapi->SetPaySign($jsapi->MakeSign($config));
        return $jsapi->GetValues();
    }

}
