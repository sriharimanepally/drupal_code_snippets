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
 *   id = "send_forgot_pin_otp",
 *   label = @Translation("send forgot pin otp."),
 *   uri_paths = {
 *     "canonical" = "/send_forgot_pin_otp/{device_uuid}/{mobile_no}/{device_registered_in_portal}"
 *   }
 * )
 */
class send_forgot_pin_otp extends ResourceBase {
    /**
     * @param null $menu_name
     * @return ResourceResponse
     */
    public function get($device_uuid = null , $mobile_no = null ,  $device_registered_in_portal = null ) 
            {
//				echo'loc 501';
 if($device_registered_in_portal==1)
 {
 
     
     
 }
 else{
    $mobile_no = str_replace("+", "", $mobile_no);
    $phonelen = strlen($mobile_no);
    $substr = $phonelen - 10;
    $country = substr($mobile_no, 0, 1);
    if ($country == '9') {
        $countrycode = substr($mobile_no, 0, 2);
    } else {
        $countrycode = substr($mobile_no, 0, 1);
    }
    $phone = substr($mobile_no, -10);
   
\Drupal\Core\Database\Database::setActiveConnection('funtional');     

   $query = "SELECT user_uid,device_unique_uuid FROM ats_device_registration WHERE ats_device_registration.device_unique_uuid =:device_uuid";
   $data = db_query($query, array(":device_uuid" => $device_uuid))->fetchAssoc();
   $uid = $data['user_uid'];
   
	if(is_int($uid))
	{
	$uid=$uid;
	}
	else
	{
	$uid=(int)$uid;
	}
      
   
\Drupal\Core\Database\Database::setActiveConnection();     
              $query_uid = "SELECT users_field_data.name AS name,
       users_extended.first_name,
       users_field_data.mail FROM users_extended users_extended INNER JOIN users_field_data users_field_data ON (users_extended.uid = users_field_data.uid)
                                                          WHERE (users_extended.uid = :uid)";
         $data_uid = db_query($query_uid, array(":uid" => $uid))->fetchAssoc();
	 $name = $data_uid['name'];
	 
         if($name==$mobile_no)
         {
            
                $otp = rand(1000, 9999);
		$enotp=md5($otp);
		//$enotp=$otp;
             $strSMSmessage = 'Your OTP for the Medical Dictionary is ' . $otp . '. Please enter the same to complete verification.';
  //       echo'loc 101';exit;
	       db_set_active();
		   
		   
		   if($country == '9'){
		   
           //$sms_result=sms_send_forgot('91' . $phone, $strSMSmessage, '');	   
		   $sms_result=send_sms_forgot('91' . $phone, $strSMSmessage, '');	   
           if($sms_result)
           {
     
\Drupal\Core\Database\Database::setActiveConnection('funtional');     
 
         $query_update_users = "UPDATE ats_device_details SET otp=:otp where device_unique_uuid=:device_uuid";
//         
         $data_update = db_query($query_update_users, array(":otp" => $enotp,":device_uuid" => $device_uuid));
        
   //       $query_update_users = "UPDATE users,users_extended  SET users_extended.otp=:otp where users.uid = users_extended.uid and users.uid=:uid";
         
     //      $data_update = db_query($query_update_users, array(":otp" => $enotp,":uid" => $uid));
            
           if($data_update)
           {
\Drupal\Core\Database\Database::setActiveConnection();     

               $response = array(
        'response' => '0',
		'otp' => $otp,
        'response_Code' => 'I-20004',
        'response_message' => "OTP sent successfully");
           }
           else
           {
\Drupal\Core\Database\Database::setActiveConnection();     

               $response = array(
        'response' => '1',
        'response_Code' => 'I-20004',
        'response_message' => "There is Problem,Please Try Again");
           }    
          
               
           }
           else
           {
\Drupal\Core\Database\Database::setActiveConnection();     

               $response = array(
        'response' => '1',
        'response_Code' => 'I-20004',
        'response_message' => "There is Problem,Please Try Again");
               
           }


		 }


			
          else{
			  
			 \Drupal\Core\Database\Database::setActiveConnection('funtional');     
 
         $query_update_users = "UPDATE ats_device_details SET otp=:otp where device_unique_uuid=:device_uuid";
//         
         $data_update = db_query($query_update_users, array(":otp" => $enotp,":device_uuid" => $device_uuid));
        
   //       $query_update_users = "UPDATE users,users_extended  SET users_extended.otp=:otp where users.uid = users_extended.uid and users.uid=:uid";
         
     //      $data_update = db_query($query_update_users, array(":otp" => $enotp,":uid" => $uid));
            
           if($data_update)
           {
\Drupal\Core\Database\Database::setActiveConnection();     

               $response = array(
        'response' => '2',
        'response_Code' => 'I-20004',
		'response_otp_message' => $strSMSmessage,
        'response_message' => "OTP sent to front end successfully");
           }
           else
           {
\Drupal\Core\Database\Database::setActiveConnection();     

               $response = array(
        'response' => '1',
        'response_Code' => 'I-20004',
        'response_message' => "There is Problem,Please Try Again");
           } 
			  
			  
			  
			  
			  
			  
			  
		  }
		  
		  
             
         }






		 
         else
         {  
\Drupal\Core\Database\Database::setActiveConnection();     

           $response = array(
        'response' => '1',
        'response_Code' => 'I-20004',
        'response_message' => "Mobile no is not registered,Please Try Again");
             
         }  
         
         return new ResourceResponse($response);
    
}   
    }
    public function post($id = null) {
       
    }
    public function delete($id = null) {
       
    }
    public function patch($id = null) {
       
    }
}

function sms_send_forgot($number, $message, $options) {
 
        
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

function send_sms_forgot($number, $message, $options) {
	
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