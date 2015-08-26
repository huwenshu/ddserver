<?php

namespace Utility;

use Foundation\Param;
use Foundation\Queue;

require_once __ROOT__ . '/vendors/phpmailer/PHPMailerAutoload.php';

class Mail {
	public static function send($to, $subject, $message, $fromName = null, $from = null) {
		$config = config('mail')->qqmail;
		$username = sprintf($config->username, rand(1, 20));
		try {
			$mail = new \PHPMailer(true);
			$mail->IsSMTP();
			$mail->IsHTML(true);
			$mail->CharSet = 'UTF-8';
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = "ssl";
			$mail->Host = $config->host;
			$mail->Port = $config->port;
			$mail->Username = $username;
			$mail->Password = $config->password;
			$mail->From = $from ?: $username;
			$mail->FromName = $fromName ?: $config->fromname;
			foreach (explode(',', $to) as $address) {
				$mail->AddAddress(trim($address));
			}
			$mail->Subject = $subject;
			$mail->MsgHTML($message);
			$mail->Send();
			self::getLogger()->info("Delivered to {$to} [{$subject}]");
		} catch (\Exception $e) {
			self::getLogger()->error($e->getMessage());
		}
	}

	public static function sendWithQueue($to, $subject, $message, $fromName = null, $from = null) {
		$data = new Param();
		$data->to = $to;
		$data->subject = $subject;
		$data->message = $message;
		$data->from = $from;
		$data->fromName = $fromName;
		Queue::factory('SendMail')->enqueue($data);
	}

	public static function getLogger() {
		return Logger::getLogger(Logger::LOGGER_EMAIL);
	}
}
?>