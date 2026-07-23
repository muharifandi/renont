<?php
/*
|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
|| Google Cloud Messaging Configurations
|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
*/
/*
|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
|| Google Cloud Messaging Configurations
|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
*/
/*
|--------------------------------------------------------------------------
| User data
|--------------------------------------------------------------------------
| Get API Key: https://code.google.com/apis/console/
*/
$config['fcm_api_key_android'] = getenv('FCM_API_KEY');
$config['fcm_api_key'] = getenv('FCM_API_KEY');
/*
|--------------------------------------------------------------------------
| API Send Address
|--------------------------------------------------------------------------
|
*/
//$config['gcm_api_send_address'] = 'https://android.googleapis.com/gcm/send';
$config['fcm_api_send_address'] = 'https://fcm.googleapis.com/fcm/send';