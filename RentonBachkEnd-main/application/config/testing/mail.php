<?php defined('BASEPATH') OR exit('No direct script access allowed');
$config['mail_name'] = 'Verifikasi RentOn';
$config['mail_email'] = getenv('MAIL_EMAIL') ?: 'verifikasi@renton.co.id';
$config['mail_password'] = getenv('MAIL_PASSWORD');
$config['mail_setting'] = Array(
								'protocol' => 'smtp',
								'smtp_host' => getenv('MAIL_SMTP_HOST') ?: 'ssl://mail.renton.co.id',
								'smtp_port' => (int) (getenv('MAIL_SMTP_PORT') ?: 465),
							//	'smtp_crypto' => 'ssl',
							//	'smtp_timeout' => '30',
								'smtp_user' => $config['mail_email'],
								'smtp_pass' => $config['mail_password'],
								'charset'       => 'utf-8',
								'mailtype'      => 'html',
						);
						
						//jika pakai gmial smtp aktifkan less secure di https://myaccount.google.com/lesssecureapps?pli=1