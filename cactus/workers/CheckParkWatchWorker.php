<?php

use Foundation\EqualQuery;
use Foundation\RangeQuery;
use Foundation\Worker;
use Node\ParkInfo;
use Node\Relationship;
use Utility\ChinaNet;
use Utility\Getui;

class CheckParkWatchWorker extends Worker {
    public function work() {
        $opts = ['from' => 0, 'size' => 100];
        while (1) {
            $rels = (new Relationship())->find(new EqualQuery('status', 'watches'), $opts);

            array_walk($rels, function(Relationship $rel) {
                if (true) {
                    $driver = $rel->getSource();
                    $park = (new ParkInfo)->load($rel->targetId);

                    /**
                     * 嘟嘟提醒
                     * 您关注的“光启城地面停车场”现在有3个空闲车位
                     */
                    Getui::pushNotification($driver->pushid, "嘟嘟提醒", "您关注的“{$park->name}”现在有{$park->parkstate}个空闲车位");
                }
            });

            // check amount of result
            if (count($rels) < $opts['size']) break;

            // continue
            $opts['from'] += $opts['size'];
        }
    }
}

(new CheckParkWatchWorker())->doWork();
