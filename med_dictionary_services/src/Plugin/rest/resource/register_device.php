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
 *   id = "register_device",
 *   label = @Translation("register device."),
 *   uri_paths = {
 *     "canonical" = "/register_device/{device_uuid}/{mobile_no}/{mail}/{firstname}/{lastname}/{roles}/{pin}/{verified_otp}"
 *   }
 * )
 */
class register_device extends ResourceBase {
    /**
     * @param null $menu_name
     * @return ResourceResponse
     */
    public function get($device_uuid = null , $mobile_no = null , $mail = null , $firstname = null , $lastname = null , $roles = null ,  $pin = null , $verified_otp = null) {

         $app_name=$_GET['app_name'];
        if(!isset($app_name))
        {
            $app_name='';
        }

        $mobile_number = $mobile_no;
	$mobile_number=str_replace(" ","",$mobile_number);
	$mobile_number='+'.$mobile_number;
 $mobile_no=str_replace("+","",$mobile_no);
 $mobile_no=str_replace(" ","",$mobile_no);
$phonelen=strlen($mobile_no);
$substr=$phonelen-10;
$country = substr($mobile_no,0,1);
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
        //  $password = randomPassword();
        $password = $pin;
        $pin = '1';

\Drupal\Core\Database\Database::setActiveConnection('funtional');
	   $user_uid = '0';
	   $query = "SELECT count(*) As count FROM ats_device_registration WHERE ats_device_registration.device_unique_uuid =:device_uuid and user_uid = :user_uid";
   $data_count = db_query($query, array(":device_uuid" => $device_uuid,":user_uid" => $user_uid))->fetchAssoc();
   $count = $data_count['count'];


	if($count > 0)
	{
\Drupal\Core\Database\Database::setActiveConnection();
	$enotp=0;

 $new_user = array(
  'field_first_name' => $firstname,
'fieldt_last_name' => $firstname,
  'name' => $mobile_number,
  'pass' => $password, // note: do not md5 the password
  'mail' => $mail,
  'status' => 1,
  'init' => '',
  'roles' => $roles,
);

// The first parameter is sent blank so a new user is created.

$account = entity_create('user', $new_user);
$data=$account->save();


	  $query="SELECT users_field_data.uid AS uid
  FROM users_field_data users_field_data
 WHERE (users_field_data.name = :name)";

 $data = db_query($query, array(":name" => $mobile_number))->fetchAssoc();

 $user_id=$data['uid'];
$organization_uuid=md5($user_id);

 $query1="INSERT INTO organization_details
(organization_uuid, user_id)
VALUES (:organization_uuid, :user_id) ON DUPLICATE KEY UPDATE organization_uuid=:organization_uuid";
          $data1= db_query($query1,array(":organization_uuid"=>$organization_uuid,":user_id"=>$user_id));




$nid = db_insert('users_extended') // Table name no longer needs {}

->fields(array(
  'uid' => $data['uid'],
  'first_name' => $firstname,
  'last_name' => $lastname,
  'role' => $roles,
  'otp' => $enotp,
  'country_code'=>$countrycode
))
->execute();

        // $query_update_users_relations = "UPDATE users_relations SET relation_uid = :relation_uid,status = 0 WHERE mobile_number = :mobile_number;";

        //  db_query($query_update_users_relations, array(":relation_uid" => $data['uid'],":mobile_number" => $mobile_number));


//   $query_roles_data="INSERT INTO users_roles
//(uid,rid)VALUES(:uid,:rid) ON DUPLICATE KEY UPDATE rid=:rid;";
//
// db_query($query_roles_data,array(":uid"=>$data['uid'],":rid"=>$roles));
//


\Drupal\Core\Database\Database::setActiveConnection('funtional');
         $otp_flag=1;
         $query_update_users = "UPDATE ats_device_registration SET is_otp_verified=:otp_flag,user_uid=:uid where device_unique_uuid=:device_uuid";

         $data_update = db_query($query_update_users, array(":otp_flag" => $otp_flag,":uid" => $data['uid'],":device_uuid" => $device_uuid));



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

	$cuid = $data['user_uid'];

	$query2 = "SELECT employee_details.organization_userid AS organization_userid FROM employee_details  employee_details WHERE (employee_details.employee_userid = :employee_userid);";



	$data2 = db_query($query2, array(":employee_userid" => $cuid))->fetchAssoc();

	if(!$data2)
	{
		$data2['organization_userid'] = '0';
	}

\Drupal\Core\Database\Database::setActiveConnection();

\Drupal\Core\Database\Database::setActiveConnection('funtional');
	  $pin=md5($pin);
	 $query_update_users1 = "UPDATE ats_device_details SET mobile_pin=:pin where device_unique_uuid=:device_uuid";

          $data_update = db_query($query_update_users1, array(":pin" => $pin,":device_uuid" => $device_uuid));


          $query_update_users2 = "UPDATE ats_device_registration SET is_registartion_successful='1' where device_unique_uuid=:device_uuid";

          $data_update = db_query($query_update_users2, array(":device_uuid" => $device_uuid));

         if($data_update)
		 {
             if($app_name=='sms_capturing')
             {
                      	 $strSMSmessage = 'Thank you for getting in touch! ';
             }
             else
             {
                      	 $strSMSmessage = 'Thank you for getting in touch! We do provide Medical Dictionary web application. Visit our web application at http://34.67.149.247. Your temporary password is ' . $password . '';

             }

			if($country=='9'){
				//sms_send_rel($phone, $strSMSmessage, '');
				send_sms_up('91'.$phone, $strSMSmessage, '');

				   $response = array(
				'response' => '1',
				'user' => $uid,
				 'phone'=>$mobile_no,
				'organization_userid' => $data2['organization_userid'],
				'response_Code' => 'I-20004',
				'response_message' => "Registration successfully");
				}
				else{
				$response = array(
				'response' => '2',
				'user' => $uid,
				 'phone'=>$mobile_no,
				 'organization_userid' => $data2['organization_userid'],
				'registration_message' => $strSMSmessage,
				'response_Code' => 'I-20004',
				'response_message' => "Registration successfully");
				}



		}
		else
         {

              $response = array(
        'response' => '0',
        'response_Code' => 'I-20004',
        'response_message' => "Registration not successful,Please Try Again");

         }

	}
	else
	{
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

	$cuid = $data['user_uid'];

	$query2 = "SELECT employee_details.organization_userid AS organization_userid FROM employee_details  employee_details WHERE (employee_details.employee_userid = :employee_userid);";



	$data2 = db_query($query2, array(":employee_userid" => $cuid))->fetchAssoc();

	if(!$data2)
	{
		$data2['organization_userid'] = '0';
	}

	\Drupal\Core\Database\Database::setActiveConnection();


$user=\Drupal\user\Entity\User::load($uid);
 $user->addRole($roles);

\Drupal\Core\Database\Database::setActiveConnection();

\Drupal\Core\Database\Database::setActiveConnection('funtional');
if($pin == 'samepin')
{
      $query = "SELECT mobile_pin FROM ats_device_details ats_device_details
JOIN ats_device_registration ats_device_registration
ON (ats_device_details.device_unique_uuid=ats_device_registration.device_unique_uuid)
WHERE ats_device_registration.user_uid =:uid";
   $data_mobile_pin = db_query($query, array(":uid" => $uid))->fetchAssoc();
   $pin = $data_mobile_pin['mobile_pin'];

   $query_update_users1 = "UPDATE ats_device_details SET mobile_pin=:pin where device_unique_uuid=:device_uuid";

          $data_update = db_query($query_update_users1, array(":pin" => $pin,":device_uuid" => $device_uuid));

             $query_update_users2 = "UPDATE ats_device_registration SET is_registartion_successful='1' where device_unique_uuid=:device_uuid";

          $data_update = db_query($query_update_users2, array(":device_uuid" => $device_uuid));

        if($data_update)
		 {
            if($app_name=='sms_capturing')
             {
                      	 $strSMSmessage = 'Thank you for getting in touch! ';
             }
             else
             {
                     	 $strSMSmessage = 'Thank you for getting in touch! We do provide Medical Dictionary web application. Visit our web application at http://34.67.149.247';
             }
      if($country=='9'){
				//sms_send_rel($phone, $strSMSmessage, '');

				send_sms_up('91'.$phone, $strSMSmessage, '');

				   $response = array(
				'response' => '1',
				'user' => $uid,
				 'phone'=>$mobile_no,
				'organization_userid' => $data2['organization_userid'],
				'response_Code' => 'I-20004',
				'response_message' => "Registration successfully");
				}
				else{
				$response = array(
				'response' => '2',
				'user' => $uid,
				 'phone'=>$mobile_no,
				'organization_userid' => $data2['organization_userid'],
				'registration_message' => $strSMSmessage,
				'response_Code' => 'I-20004',
				'response_message' => "Registration successfully");
				}
		}
		else
         {

              $response = array(
        'response' => '0',
        'response_Code' => 'I-20004',
        'response_message' => "Registration not successful,Please Try Again");

         }

}
else
{

	  $pin=md5($pin);
	 $query_update_users1 = "UPDATE ats_device_details SET mobile_pin=:pin where device_unique_uuid=:device_uuid";

          $data_update = db_query($query_update_users1, array(":pin" => $pin,":device_uuid" => $device_uuid));

             $query_update_users2 = "UPDATE ats_device_registration SET is_registartion_successful='1' where device_unique_uuid=:device_uuid";

          $data_update = db_query($query_update_users2, array(":device_uuid" => $device_uuid));

        if($data_update)
		 {
            if($app_name=='sms_capturing')
             {
                      	 $strSMSmessage = 'Thank you for getting in touch! ';
             }
             else
             {
            	 $strSMSmessage = 'Thank you for getting in touch! We do provide Medical Dictionary web application. Visit our web application at "http://34.67.149.247"';
             }
      if($country=='9'){
				//sms_send_rel($phone, $strSMSmessage, '');

				send_sms_up('91'.$phone, $strSMSmessage, '');

				   $response = array(
				'response' => '1',
				'user' => $uid,
				 'phone'=>$mobile_no,
				'organization_userid' => $data2['organization_userid'],
				'response_Code' => 'I-20004',
				'response_message' => "Registration successfully");
				}
				else{
				$response = array(
				'response' => '2',
				'user' => $uid,
				 'phone'=>$mobile_no,
				'organization_userid' => $data2['organization_userid'],
				'registration_message' => $strSMSmessage,
				'response_Code' => 'I-20004',
				'response_message' => "Registration successfully");
				}

           /* $response = array(
        'response' => '1',
        'user' => $uid,
         'phone'=>$mobile_no,
        'response_Code' => 'I-20004',
        'response_message' => "Registration successfully");  */
		}
		else
         {

              $response = array(
        'response' => '0',
        'response_Code' => 'I-20004',
        'response_message' => "Registration not successful,Please Try Again");

         }

}



	}

        return new ResourceResponse($response);
    }
    public function post($id = null) {

    }
    public function delete($id = null) {

    }
    public function patch($id = null) {

    }
}

function randomPassword() {
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}




function sms_send_rel($number, $message, $options) {


    $conf['way2smsplus_username'] = 'ondemandhomecare';
    $conf['way2smsplus_password'] = 'ondemand1234';
    $conf['way2smsplus_from'] = 'TDODHC';


    $query = "?username=" . $conf['way2smsplus_username'] . "&password=" . $conf['way2smsplus_password'] . "&from=" . $conf['way2smsplus_from'] . "&to=" . $number . "&msg=" . urlencode($message) . "&type=1&dnd_check=0";
    $url = 'http://pointsms.in/API/sms.php' . $query;



    $client = \Drupal::httpClient();
    $response = $client->request('GET', $url);

    try {
//  $response = $client->send($request);
        // Expected result.
        $data = $response->getBody();

        $query = "?username=ondemandhomecare&password=ondemand1234&job_id=" . $data;
        $url = 'http://pointsms.in/API/get_dlr_status.php' . $query;

        $client = \Drupal::httpClient();
        $response = $client->request('GET', $url);
        try {
//  $response = $client->send($request);
            // Expected result.
            $data = $response->getBody();



            if (strpos($data, 'Dlr Text: Delivered') !== false) {
                $result = array('status' => TRUE, 'data' => $http_result_del_status->data);
            } else {
                $result = array('status' => FALSE, 'message' => 'failed to delever');
            }
            $response = $result;
        } catch (RequestException $e) {
            $response = array('status' => FALSE, 'message' => t('An error occured during the HTTP request: @error', array('@error' => $e->getMessage())));
        }
    } catch (RequestException $e) {
        $response = array('status' => FALSE, 'message' => t('An error occured during the HTTP request: @error', array('@error' => $e->getMessage())));
    }





    return $response;
}

function send_sms_up($number, $message, $options) {

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