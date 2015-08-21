<?php

use Foundation\EqualQuery;
use Foundation\RangeQuery;
use Foundation\Worker;
use Node\ParkInfo;
use Utility\ChinaNet;

class TestWorker extends Worker {
    public function work() {
        $mobile = '18602108024';


//        $mobile = '13122951470';

//        $tmpl = ChinaNet::TEMPLATE_VERIFY;
//        $code = '3321';
//        $name = '您的';
//        $time = '15分钟';
//        $rs = ChinaNet::sendSms($mobile, $tmpl, compact('code', 'name', 'time'));

//        $tmpl = ChinaNet::TEMPLATE_车位预付完成_停管;
//        $license = '沪A7N529';
//        $paid = '10元';
//        $time = '7月18日 16:45';
//        $start = '15分钟后';
//        $rs = ChinaNet::sendSms($mobile, $tmpl, compact('license', 'paid', 'time', 'start'));

//        $tmpl = ChinaNet::TEMPLATE_预付超时提醒_停管;
//        $license = '沪A7N529';
//        $time = '15分钟后';
//        $rs = ChinaNet::sendSms($mobile, $tmpl, compact('license', 'time'));

        $park = new ParkInfo();
        $park->load(1);
        $parks = $park->count(new RangeQuery('spacesum', 1000));
        var_dump($parks);

    }
}

(new TestWorker())->doWork();
