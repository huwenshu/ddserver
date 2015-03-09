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

//记录日志
function takeLog($msg, $level, $destination){

    //使用Thinkphp日志方法记录日志
    Think\Log::write($msg, $level, 'File', $destination);


}

//记录csv
function takeCSV($msgs, $destination){

    $log_dir = dirname($destination);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $fp = fopen($destination, 'a');
    fputcsv($fp, $msgs);
    fclose($fp);


}


?>