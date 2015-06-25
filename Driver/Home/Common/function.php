<?php
/**
 * Created by PhpStorm.
 * User: Bin
 * Date: 15/2/10
 * Time: 上午12:00
 */

//发送email
function sendMail($to, $subject, $content) {

    Vendor('PHPMailer.PHPMailerAutoload');

    $mail = new PHPMailer(); //实例化
    $mail->IsSMTP(); // 启用SMTP
    $mail->Host=C('MAIL_HOST'); //smtp服务器的名称（这里以126邮箱为例）
    $mail->SMTPAuth = C('MAIL_SMTPAUTH'); //启用smtp认证
    $mail->Username = C('MAIL_USERNAME'); //你的邮箱名
    $mail->Password = C('MAIL_PASSWORD') ; //邮箱密码
    $mail->From = C('MAIL_FROM'); //发件人地址（也就是你的邮箱地址）
    $mail->FromName = C('MAIL_FROMNAME'); //发件人姓名
    $mail->AddAddress($to,"name");
    $mail->WordWrap = 50; //设置每行字符长度
    $mail->IsHTML(C('MAIL_ISHTML')); // 是否HTML格式邮件
    $mail->CharSet=C('MAIL_CHARSET'); //设置邮件编码
    $mail->Subject =$subject; //邮件主题
    $mail->Body = $content; //邮件内容
    $mail->AltBody = "This is the body in plain text for non-HTML mail clients"; //邮件正文不支持HTML的备用显示
    return($mail->Send());
}

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


//用csv记录查询的经纬度
function locCSV($msgs){

    $ctime = time();
    $optid = $msgs['uid'].$ctime;//封装操作编号
    $data[0] = $optid;
    $data[1] = $msgs['uid'];
    $data[2] = date("Y-m-d H:i:s", $ctime);//当前时间
    $data[3] = $msgs['lat'];
    $data[4] = $msgs['lng'];
    $data[5] = $msgs['ip'];

    $filename =  C('CSV_LOG_PATH').'/location.csv';

    $log_dir = dirname($filename);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $fp = fopen($filename, 'a');
    fputcsv($fp, $data);
    fclose($fp);


}
    
    //用csv记录查询的经纬度
    function locCSV2($msgs){
        
        $ctime = time();
        $optid = $msgs['uid'].$ctime;//封装操作编号
        $data[0] = $optid;
        $data[1] = $msgs['uid'];
        $data[2] = date("Y-m-d H:i:s", $ctime);//当前时间
        $data[3] = $msgs['curlat'];
        $data[4] = $msgs['curlng'];
        $data[5] = $msgs['lat'];
        $data[6] = $msgs['lng'];
        $data[7] = $msgs['ip'];
        $data[8] = $msgs['pushid'];
        
        $filename =  C('CSV_LOG_PATH').'/location2_'.date("Ymd", $ctime).'.csv';
        
        $log_dir = dirname($filename);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $fp = fopen($filename, 'a');
        fputcsv($fp, $data);
        fclose($fp);
        
        
    }
    
    //用csv记录查询的经纬度
    function locFreeList($msgs){
        
        $ctime = time();
        $optid = $msgs['uid'].$ctime;//封装操作编号
        $data[0] = $optid;
        $data[1] = $msgs['uid'];
        $data[2] = date("Y-m-d H:i:s", $ctime);//当前时间
        $data[3] = $msgs['lat'];
        $data[4] = $msgs['lng'];
        $data[5] = $msgs['province'];
        $data[6] = $msgs['city'];
        $data[7] = $msgs['district'];
        $data[8] = $msgs['note'];
        $data[9] = $msgs['ip'];
        $data[10] = $msgs['pushid'];
        
        $filename =  C('CSV_LOG_PATH').'/freelist_'.date("Ymd", $ctime).'.csv';
        
        $log_dir = dirname($filename);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $fp = fopen($filename, 'a');
        fputcsv($fp, $data);
        fclose($fp);
        
        
    }


?>