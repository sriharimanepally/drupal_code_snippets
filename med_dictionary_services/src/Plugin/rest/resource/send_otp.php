<?php
/**
 * @file
 *  Contains Drupal\services_menu\Plugin\rest\resource
 */
namespace Drupal\med_dictionary_services\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * Provides a service resource for menus.
 *
 * @RestResource(
 *   id = "send_otp",
 *   label = @Translation("send otp."),
 *   uri_paths = {
 *     "canonical" = "/send_otp/{device_uuid}/{mobile_no}/{device_name}/{device_model}/{device_platform}/{device_version}"
 *   }
 * )
 */
class send_otp extends ResourceBase {
    /**
     * @param null $menu_name
     * @return ResourceResponse
     */
    public function get($device_uuid = null , $mobile_no = null ,  $device_name = null , $device_model = null , $device_platform = null , $device_version = null )
            {


        $app_name=$_GET['app_name'];
        if(!isset($app_name))
        {
            $app_name='';
        }




$mobile_no=str_replace("+","",$mobile_no);
$phonelen=strlen($mobile_no);
$substr=$phonelen-10;
$country = substr($mobile_no,0,2);
 if($country=='9')
{
$countrycode= substr($mobile_no,0,2);
$phone=substr($mobile_no, -10);
}
else
{
$countrycode =substr($mobile_no,0,1);
$phone=substr($mobile_no, -10);
}
//$phone=substr($mobile_no, -10);


//echo $phone;exit;
//return false;


\Drupal\Core\Database\Database::setActiveConnection('funtional');
   $query_device_registered = "SELECT count(*) as cn FROM ats_device_registration WHERE ats_device_registration.device_unique_uuid =:device_uuid";
   $data_device_registered = db_query($query_device_registered, array(":device_uuid" => $device_uuid))->fetchAssoc();
   $cn = $data_device_registered['cn'];
\Drupal\Core\Database\Database::setActiveConnection();

   if($cn==0)
   {


	    if($country=='9')
		{
		$countrycode= substr($mobile_no,0,2);
		$phone=substr($mobile_no, -10);




		$otp = rand(1000, 9999);
	$enotp=md5($otp);
	$mobile_no = $mobile_no;
        if($app_name=='sms_capturing')
        {
       $strSMSmessage = '' . $otp . ' is your OTP for the SMS Capturing registration.Please enter the same to complete verification.';
        }
        else
        {
	$strSMSmessage = '' . $otp . ' is your OTP for the Medical Dictionary registration.Please enter the same to complete verification.';
        }
	//$sms_result=sms_send('91' . $phone, $strSMSmessage, '');
	$sms_result=send_sms('91' . $phone, $strSMSmessage, '');



\Drupal\Core\Database\Database::setActiveConnection('funtional');
    $uid='0';
    $query="INSERT INTO ats_device_registration
(device_registration_uuid, user_uid, registration_date, device_unique_uuid)
VALUES (REPLACE(UUID(),'-',''), :uid, now(), :device_uuid);";
    $data= db_query($query,array(":device_uuid"=>$device_uuid,":uid"=>$uid));

 $query_device_data="INSERT INTO ats_device_details
(device_dimensions, device_name, device_unique_uuid,mobile_pin, model,os_name,os_version,otp)
VALUES ('',:device_name, :device_uuid,'',:device_model, :device_platform, :device_version, :otp)
 ON DUPLICATE KEY UPDATE device_name=:device_name;";
    $data_extended= db_query($query_device_data,array(":device_name"=>$device_name,":device_uuid"=>$device_uuid,":device_model"=>$device_model,":device_platform"=>$device_platform,":device_version"=>$device_version,":otp"=>$enotp));

        $response = array(
        'response' => '1',
		'otp' =>  $otp,
        'response_Code' => 'I-20004',
        'response_message' => "OTP Sent Successfully.");





		}
		else
		{

		$countrycode =substr($mobile_no,0,1);
		$phone=substr($mobile_no, -10);

		$otp = rand(1000, 9999);
		$enotp=md5($otp);

		$strSMSmessage = '' . $otp . ' is your OTP for the Medical Dictionary registration.Please enter the same to complete verification.';//echo $strSMSmessage;exit;

		/* $response = array(
        'response' => '2',
        'response_Code' => 'I-20004',
		'response_otp' => $strSMSmessage,
        'response_message' => "OTP Sent to front end Successfully."); */

		\Drupal\Core\Database\Database::setActiveConnection('funtional');
    $uid='0';
    $query="INSERT INTO ats_device_registration
(device_registration_uuid, user_uid, registration_date, device_unique_uuid)
VALUES (REPLACE(UUID(),'-',''), :uid, now(), :device_uuid);";
    $data= db_query($query,array(":device_uuid"=>$device_uuid,":uid"=>$uid));

 $query_device_data="INSERT INTO ats_device_details
(device_dimensions, device_name, device_unique_uuid,mobile_pin, model,os_name,os_version,otp)
VALUES ('',:device_name, :device_uuid,'',:device_model, :device_platform, :device_version, :otp)
 ON DUPLICATE KEY UPDATE device_name=:device_name;";
    $data_extended= db_query($query_device_data,array(":device_name"=>$device_name,":device_uuid"=>$device_uuid,":device_model"=>$device_model,":device_platform"=>$device_platform,":device_version"=>$device_version,":otp"=>$enotp));

        $response = array(
        'response' => '2',
        'response_Code' => 'I-20004',
		'response_otp' => $strSMSmessage,
        'response_message' => "OTP Sent to front end Successfully.");

		return new ResourceResponse($response);


		}













   }
   else
   {



	if($country=='9')
	{

		\Drupal\Core\Database\Database::setActiveConnection();
        $otp = rand(1000, 9999);
	$enotp=md5($otp);
        if($app_name=='sms_capturing')
        {
	$strSMSmessage = '' . $otp . ' is your OTP for the SMS Capturing registration.Please enter the same to complete verification.';
        }
        else
        {
        $strSMSmessage = '' . $otp . ' is your OTP for the Medical Dictionary registration.Please enter the same to complete verification.';

        }
	$sms_result=send_sms('91' . $phone, $strSMSmessage, '');



\Drupal\Core\Database\Database::setActiveConnection('funtional');

         $query_update_users = "UPDATE ats_device_details SET otp=:otp where device_unique_uuid=:device_uuid";
         $data_update = db_query($query_update_users, array(":otp" => $enotp,":device_uuid" => $device_uuid));
         $response = array(
        'response' => '1',
		'otp'=>$otp,
        'response_Code' => 'I-20004',
        'response_message' => "OTP Sent Successfully.");



	}

	else{


\Drupal\Core\Database\Database::setActiveConnection();
        $otp = rand(1000, 9999);
	$enotp=md5($otp);



		$strSMSmessage = '' . $otp . ' is your OTP for the Medical Dictionary registration.Please enter the same to complete verification.';


\Drupal\Core\Database\Database::setActiveConnection('funtional');

         $query_update_users = "UPDATE ats_device_details SET otp=:otp where device_unique_uuid=:device_uuid";
         $data_update = db_query($query_update_users, array(":otp" => $enotp,":device_uuid" => $device_uuid));
         $response = array(
        'response' => '2',
        'response_Code' => 'I-20004',
		'response_otp' => $strSMSmessage,
        'response_message' => "OTP Sent Successfully.");
	}
}

        return new ResourceResponse($response);
		    $response = $device_uuid.'-'.$mobile_no.'-'.$device_name.'-'.$device_model.'-'.$device_platform.'-'.$device_version;




        return new ResourceResponse($response);
    }
    public function post($id = null) {

    }
    public function delete($id = null) {

    }
    public function patch($id = null) {

    }
}

function sms_send($number, $message, $options) {


 $conf['way2smsplus_username'] = 'ondemandhomecare';
 $conf['way2smsplus_password'] = 'ondemand1234';
 $conf['way2smsplus_from'] = 'TDODHC';


$query="?username=".$conf['way2smsplus_username']."&password=".$conf['way2smsplus_password']."&from=".$conf['way2smsplus_from']."&to=".$number."&msg=".urlencode($message)."&type=1&dnd_check=0";
$url='http://pointsms.in/API/sms.php'. $query;



 $client = \Drupal::httpClient();
$response = $client->request('GET', $url);

try {
//  $response = $client->send($request);
  // Expected result.
  $data = $response->getBody();

    $query="?username=ondemandhomecare&password=ondemand1234&job_id=".$data;
    $url='http://pointsms.in/API/get_dlr_status.php'. $query;

   $client = \Drupal::httpClient();
$response = $client->request('GET', $url);
try {
//  $response = $client->send($request);
  // Expected result.
  $data = $response->getBody();



      if (strpos($data,'Dlr Text: Delivered') !== false) {
          $result   = array('status' => TRUE, 'data' => $http_result_del_status->data);
}else
{
      $result = array('status' => FALSE, 'message' => 'failed to delever');
}
    $response =  $result;



}
catch (RequestException $e) {
        $response =  array('status' => FALSE, 'message' => t('An error occured during the HTTP request: @error', array('@error' => $e->getMessage())));
}



}
catch (RequestException $e) {
        $response =  array('status' => FALSE, 'message' => t('An error occured during the HTTP request: @error', array('@error' => $e->getMessage())));
}





               return $response;




}

function send_sms($number, $message, $options) {

	//$apikey = 'WtfH9TnvSmO58I2G_iy2ow==';
	$apikey = '47TXncPxQdyMmxNRnmatKQ==';

	$conf['apikey'] = urlencode($apikey);
	$conf['to'] = urlencode($number);


	$query="?apiKey=".$conf['apikey']."&to=".$conf['to']."&content=".urlencode($message);
	$url='https://platform.clickatell.com/messages/http/send'. $query;

	$client = \Drupal::httpClient();
    $response = $client->request('GET', $url);


    try {

        $data = $response->getBody();
        $data = json_decode($data);
        $response = $data;

        $apiMsgId = $response->messages[0]->apiMessageId;




    }
    catch(RequestException $e){
        $response =  array('status' => FALSE, 'message' => t('An error occured during the HTTP request: @error', array('@error' => $e->getMessage())));
    }


	return $response;


}
