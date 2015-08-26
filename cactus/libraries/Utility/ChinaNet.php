<?php

namespace Utility;

use Foundation\Cache;
use Foundation\Http\Http;
use Foundation\Param;

class ChinaNet {

    const SENDSMS_URL = 'http://api.189.cn/v2/emp/templateSms/sendSms';

    const TEMPLATE_VERIFY = '91548564'; // {code}({name}，请勿泄露)，有效期{time}
    const TEMPLATE_车位预付完成_停管 = '91548678'; // “{license}”已完成支付！\n付款金额：{paid}\n预订时间：{time}\n系统将在{start}开始计费，请提前预留车位。
    const TEMPLATE_预付超时提醒_停管 = '91548680'; // “{license}”将在{time}开始超时计费，如车辆已离场，请尽快“确认离场”。

    public static function sendSms($mobile, $template, array $data) {
        $params = new Param();
        $params->app_id = config('chinanet')->client->app_id;
        $params->access_token = strval(new ChinaNetToken());
        $params->acceptor_tel = $mobile;
        $params->template_id = $template;
        $params->template_param = json_encode($data);
        $params->timestamp = date('Y-m-d H:i:s');
//        $params->sign = self::getSign((array) $params);

        $resp = Http::post(self::SENDSMS_URL, http_build_query($params));

        $uri = date('c') . " - {$mobile} - {$template} - " . urldecode(http_build_query($data));
        if ($json = json_decode($resp)) {
            if (0 == $json->res_code) {
                $msg = "{$json->res_message} - {$json->idertifier} - {$uri}";
                Logger::getLogger(Logger::LOGGER_SMS)->info($msg);
            } else {
                $msg = "{$json->res_message} - {$uri}";
                Logger::getLogger(Logger::LOGGER_SMS)->warn($msg);
            }
            return $json;
        } else {
            $msg = "Decode error - {$uri}";
            Logger::getLogger(Logger::LOGGER_SMS)->error($msg);
        }
    }

    /**
     * @param $params
     * @return array
     */
    protected static function getSign(array $params) {
        ksort($params);
        return base64_encode(hash_hmac('sha1', http_build_query($params), config('chinanet')->client->app_secret));
    }
}


class ChinaNetToken {
    const REQUEST_TOKEN_URL = 'https://oauth.api.189.cn/emp/oauth2/v3/access_token';

    public $token;
    public $expires;

    public function __construct() {
        $cache = new Cache('chinanet_client_token');

        if ($cache->exists()) {
            $this->token = $cache->get();
            $this->expires = $cache->ttl() + time();
        } else {
            $payload = iterator_to_array(config('chinanet')->client);
            if ($result = json_decode(Http::post(self::REQUEST_TOKEN_URL, http_build_query($payload)))) {
                if ($result->res_message == 'Success' && !empty($result->access_token)) {
                    $cache->set($result->access_token);
                    $cache->expire($result->expires_in);
                    $this->token = $result->access_token;
                    $this->expires = $result->expires_in;
                }
            }
        }
    }

    public function isNotValid() {
        return time() > $this->expires;
    }

    public function __toString() {
        return strval($this->token);
    }
}