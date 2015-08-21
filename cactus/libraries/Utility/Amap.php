<?php

namespace Utility;

use Foundation\Http\Http;
use Foundation\Http\ProxyHttp;

class Amap {
    const API_KEY = '47666a4c26d754194fa19731c5118bfd'; // 3990ad5f0460867b9b248ea2dd38dca1 // 47666a4c26d754194fa19731c5118bfd
    const BAIDU_KEY = 'YpcGeHRHuxNrkmGFvVaBUXbS';
    const KEYWORD_TO_PLACE = 'http://restapi.amap.com/v3/place/text?key=%s&s=rsv3&city=310000&keywords=%s';
    const LNGLAT_TO_REGEO = 'http://restapi.amap.com/v3/geocode/regeo?key=%s&location=%f,%f&s=rsv3&output=json';
    const BAIDU_TO_AMAP = 'http://restapi.amap.com/v3/assistant/coordinate/convert?key=%s&locations=%f,%f&coordsys=baidu';
    const BAIDU_METRIC_TO_LNGLAT = 'http://api.map.baidu.com/geoconv/v1/?ak=%s&coords=%f%%2C%f&from=6&to=5';

    private static function getRegion($lng, $lat) {
        $url = sprintf(self::LNGLAT_TO_REGEO, self::API_KEY, floatval($lng), floatval($lat));
        return Http::get($url);
    }

    public static function getCity($lng, $lat) {
        $data = json_decode(self::getRegion($lng, $lat));
        return $data->regeocode->addressComponent->city;
    }

    public static function getDistrict($lng, $lat) {
        $data = json_decode(self::getRegion($lng, $lat));
        return $data->regeocode->addressComponent->district;
    }

    public static function get($lng, $lat) {
        $data = json_decode(self::getRegion($lng, $lat));
        return $data->regeocode->addressComponent->city;
    }

    public static function getCityCode($lng, $lat) {
        $data = json_decode(self::getRegion($lng, $lat));
        return $data->regeocode->addressComponent->citycode;
    }

    public static function getNameFromKeyword($keyword) {
        $url = sprintf(self::KEYWORD_TO_PLACE, self::API_KEY, $keyword);
        $resp = ProxyHttp::get($url);
        $data = json_decode($resp);

        if (!empty($data->pois[0])) {
            return $data->pois[0]->name;
        }
    }

    public static function getLnglatFromKeyword($keyword) {
        $url = sprintf(self::KEYWORD_TO_PLACE, self::API_KEY, $keyword);
        $resp = Http::get($url);
        $data = json_decode($resp);

        if (!empty($data->pois[0])) {
            $lnglat = explode(',', $data->pois[0]->location);
            return [floatval($lnglat[0]), floatval($lnglat[1])];
        }
    }

    public static function getLnglatFromBaiduMetric($x, $y) {
        $url = sprintf(self::BAIDU_METRIC_TO_LNGLAT, self::BAIDU_KEY, $x, $y);
        $resp = Http::get($url);
        $data = json_decode($resp);

        if (!empty($data->result[0])) {
            $url = sprintf(self::BAIDU_TO_AMAP, self::API_KEY, $data->result[0]->x, $data->result[0]->y);
            $resp = Http::get($url);
            $data = json_decode($resp);

            if (!empty($data->locations) && $data->info == 'ok') {
                $lnglat = explode(',', $data->locations);
                return [floatval($lnglat[0]), floatval($lnglat[1])];
            }
        }
    }

}
