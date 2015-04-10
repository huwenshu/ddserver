<?php
/**
 * Created by PhpStorm.
 * User: Bin
 * Date: 15/2/10
 * Time: 上午12:00
 */

//记录log文件
function takeLog($msg, $level, $destination){

    //使用Thinkphp日志方法记录日志
    Think\Log::write($msg, $level, 'File', $destination);


}

//记录csv
function takeCSV($msgs){

    $ctime = time();
    $optid = $msgs['parkid'].$msgs['uid']. $msgs['opt'].$ctime;//封装操作编号
    $data[0] = $optid;
    $data[1] = date("Y-m-d H:i:s", $ctime);//当前时间
    $data[2] = $msgs['ip'];
    $data[3] = $msgs['parkid'];
    $data[4] = $msgs['uid'];
    $data[5] = $msgs['opt'];
    $data[6] = $msgs['oldValue'];
    $data[7] = $msgs['newValue'];
    $data[8] = $msgs['change'];
    $data[9] = $msgs['note'];

    $filename =  C('CSV_LOG_PATH').'/admin_'.date("Ymd", $ctime).'.csv';

    $log_dir = dirname($filename);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $fp = fopen($filename, 'a');
    fputcsv($fp, $data);
    fclose($fp);


}


?>