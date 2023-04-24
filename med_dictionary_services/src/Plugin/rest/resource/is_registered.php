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
 *   id = "is_registered",
 *   label = @Translation("device registered."),
 *   uri_paths = {
 *     "canonical" = "/is_registered/{device_uuid}"
 *   }
 * )
 */
class is_registered extends ResourceBase {
    /**
     * @param null $menu_name
     * @return ResourceResponse
     */
    public function get($device_uuid = null) {



\Drupal\Core\Database\Database::setActiveConnection('funtional');
       $query = "SELECT is_otp_verified,is_registartion_successful,user_uid FROM ats_device_registration WHERE ats_device_registration.device_unique_uuid =:device_uuid";
        $data = db_query($query, array(":device_uuid" => $device_uuid))->fetchAssoc();


        $is_otp_verified = $data['is_otp_verified'];
	  $is_registartion_successful= $data['is_registartion_successful'];
	   $cn=count($data);



	   $user_uid=$data['user_uid'];
\Drupal\Core\Database\Database::setActiveConnection();


        if($is_otp_verified=='1' && $is_registartion_successful=='1' && $cn > 0)
        {



		 $query_user_pwd = "SELECT name FROM users_field_data WHERE uid =:user_uid";
       $data_pwd = db_query($query_user_pwd, array(":user_uid" => $user_uid))->fetchAssoc();
         $name = $data_pwd['name'];
\Drupal\Core\Database\Database::setActiveConnection();
        $response = array(
        'response' => '1',
		'name'=>$name,
		'user_id'=>$user_uid,
        'response_Code' => 'I-20004',
        'response_message' => "OTP Verifed  and Registration verified successfully");
         }
         else if($is_otp_verified=='1' && $is_registartion_successful=='0' && $cn=='1')
         {

              $response = array(
        'response' => '2',
        'response_Code' => 'I-20004',
        'response_message' => "OTP Verifed  and Registration Pending");

         }
         else if($is_otp_verified=='0' && $is_registartion_successful=='0' && $cn=='1')
         {
\Drupal\Core\Database\Database::setActiveConnection();
              $query_uid = "SELECT  users_field_data.name,
       users_extended.first_name,
       users_field_data.mail FROM users_extended users_extended INNER JOIN users_field_data users_field_data ON (users_extended.uid = users_field_data.uid)
                                                          WHERE (users_extended.uid = :uid)";
         $data_uid = db_query($query_uid, array(":uid" => $user_uid))->fetchAssoc();
         $first_name = $data_uid['first_name'];
         $mail = $data_uid['mail'];
		  $phone = $data_uid['name'];

		    $response = array(
        'response' => '3',
        'response_Code' => 'I-20004',
        'name'=>$first_name,
		'phone'=>$phone,
        'mail'=>$mail,
        'response_message' => "OTP  Not Verifed  and Registration Not Verified but device registered");
		 }
         else
         {
		    $response = array(
        'response' => '4',
        'response_Code' => 'I-20004',
        'response_message' => "Device not yet registered");
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