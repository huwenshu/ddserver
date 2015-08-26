<?php

namespace Utility;

use IGeTui\ActionChain;
use IGeTui\ActionChainType;
use IGeTui\AppStartUp;
use IGeTui\IGeTui;
use IGeTui\IGtTarget;
use IGeTui\PushInfo;
use IGeTui\SingleMessage;
use IGeTui\Target;
use IGeTui\Template\BaseTemplate;
use IGeTui\Template\LinkTemplate;
use IGeTui\Template\NotificationTemplate;
use IGeTui\Template\NotyPopLoadTemplate;
use IGeTui\Template\TransmissionTemplate;

class Getui {
    private static function getConfig() {
        return config('getui')->production;
    }

    private static function getAppId() {
        return self::getConfig()->app_id;
    }

    private static function getAppKey() {
        return self::getConfig()->app_key;
    }

    private static function getMasterSecret() {
        return self::getConfig()->master_secret;
    }

    private static function getUrl() {
        return self::getConfig()->url;
    }

    public static function pushNotification($cid, $subject, $content) {
        $tpl = new NotificationTemplate();
        $tpl->set_appId(self::getAppId());
        $tpl->set_appkey(self::getAppKey());
        $tpl->set_transmissionType(1);//透传消息类型
        $tpl->set_transmissionContent("notification");
        $tpl->set_title($subject);//通知栏标题
        $tpl->set_text($content);//通知栏内容
        $tpl->set_isRing(true);//是否响铃
        $tpl->set_isVibrate(true);//是否震动
        $tpl->set_isClearable(true);//通知栏是否可清除

        // iOS推送需要设置的pushInfo字段
        //$template ->set_pushInfo($actionLocKey,$badge,$message,$sound,$payload,$locKey,$locArgs,$launchImage);
        //$template ->set_pushInfo("test",1,"message","","","","","");

        return self::pushMessageToSingle($cid, $tpl);
    }

    public static function pushPayload($cid, $payload, $message) {
        $tpl = new Transmission();
        $tpl->set_appId(self::getAppId());
        $tpl->set_appkey(self::getAppKey());
        $tpl->set_transmissionContent($payload);
        $tpl->set_transmissionType(2); // 收到消息是否立即启动应用：1为立即启动，2则广播等待客户端自启动
        $tpl->set_pushInfo(null, 1, null, "sound", "", $message, null, null); // iOS参数
        return self::pushMessageToSingle($cid, $tpl);
    }

    /**
     * 消息模版：
     * 1.TransmissionTemplate: 透传功能模板
     * 2.LinkTemplate: 通知打开链接功能模板
     * 3.NotificationTemplate: 通知打开应用模板
     * 4.NotyPopLoadTemplate: 通知弹框下载功能模板
     * @param $cid
     * @param $payload
     * @return array
     */
    public static function pushMessageToSingle($cid, $template) {
        $igt = new IGeTui(self::getUrl(), self::getAppKey(), self::getMasterSecret());

        //信息体
        $msg = new SingleMessage();
        $msg->set_isOffline(true);
        $msg->set_offlineExpireTime(3600 * 1000);
        $msg->set_data($template);

        //接收方
        $tgt = new IGtTarget();
        $tgt->set_appId(self::getAppId());
        $tgt->set_clientId($cid);

        return $igt->pushMessageToSingle($msg, $tgt);

    }
}

class Transmission extends TransmissionTemplate {

    var $transmissionType;
    var $transmissionContent;

    protected function getActionChain() {

        $chains = [];

        //actionChain
        $chain1 = new ActionChain();
        $chain1->set_actionId(1);
        $chain1->set_type(ActionChainType::refer);
        $chain1->set_next(10030);

        //appStartUp
        $appStartUp = new AppStartUp();
        $appStartUp->set_android("");
        $appStartUp->set_symbia("");
        $appStartUp->set_ios("");

        //启动app
        $chain2 = new ActionChain();
        $chain2->set_actionId(10030);
        $chain2->set_type(ActionChainType::startapp);
        $chain2->set_appid("");
        $chain2->set_autostart($this->transmissionType == '1' ? true : false);
        $chain2->set_appstartupid($appStartUp);
        $chain2->set_failedAction(100);
        $chain2->set_next(100);

        //结束
        $chain3 = new ActionChain();
        $chain3->set_actionId(100);
        $chain3->set_type(ActionChainType::eoa);

        array_push($chains, $chain1, $chain2, $chain3);

        return $chains;
    }

    public function set_pushInfo($actionLocKey, $badge, $message, $sound, $payload, $locKey, $locArgs, $launchImage) {
        $this->pushInfo = new PushInfo();
        $this->pushInfo->set_actionLocKey($actionLocKey);
        $this->pushInfo->set_badge($badge);
        $this->pushInfo->set_message($message);
        if ($sound!=null) {
            $this->pushInfo->set_sound($sound);
        }
        if ($payload!=null) {
            $this->pushInfo->set_payload($payload);
        }
        if ($locKey!=null) {
            $this->pushInfo->set_locKey($locKey);
        }
        if ($locArgs!=null) {
            $this->pushInfo->set_locArgs($locArgs);
        }
        if ($launchImage!=null) {
            $this->pushInfo->set_launchImage($launchImage);
        }
    }
}
