<?php

use Foundation\AndQuery;
use Foundation\DateTimeRangeQuery;
use Foundation\EqualQuery;
use Foundation\Firewall;
use Foundation\Form\Email;
use Foundation\Validator;
use Foundation\InQuery;
use Foundation\RangeQuery;
use Foundation\Worker;
use Node\ParkInfo;
use Node\ParkOrder;
use Utility\ChinaNet;
use Utility\Logger;
use Utility\Mail;
use Utility\Strings;

class CheckOrderTimeoutWorker extends Worker {
    public function work() {
        $loader = new ParkOrder();

        $orders = $loader->find(new AndQuery(
            new InQuery('state', [0, 1, 2]),
            new DateTimeRangeQuery('endtime', date_create('now'), date_create('+15 minutes'))
//            new DateTimeRangeQuery('createtime', date_create('yesterday'), date_create('+15 minutes')) // Test code
            ));

        array_walk($orders, function(ParkOrder $order) {

            if ((new Firewall("CheckOrderTimeout:" . $order->id, 3600, 1))->hit()) return; // 每个订单每小时最多提醒一次

            $park = $order->getParkInfo();
            if ($park->corp_type == ParkInfo::CORP_TYPE_包月分销) {
                if ($admin = current($park->getParkAdmin())) {
                    $time = $order->endtime->format('H:i');
                    $license = $order->carid;
                    if (Validator::mobile($admin->phone)) {
//                        if ($park->status == ParkInfo::STATUS_已合作) {
//                            ChinaNet::sendSms($admin->phone, ChinaNet::TEMPLATE_预付超时提醒_停管, compact('license', 'time'));
//                        }
                        Mail::send('hejiachen@duduche.me', '预付超时提醒_停管', "TO:{$park->name}({$admin->phone})\n\n“{$license}”将在{$time}开始超时计费，如车辆已离场，请尽快“确认离场”。");
                    } else {
                        Logger::getLogger(Logger::LOGGER_WORKER)->warn("Invalid park admin mobile format: {$admin->phone}");
                    }
                } else {
                    Logger::getLogger(Logger::LOGGER_WORKER)->warn("Park admin not exists: {$park->id}");
                }
            }
        });



    }
}

(new CheckOrderTimeoutWorker())->doWork();
