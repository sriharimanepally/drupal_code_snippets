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
 *   id = "forgot_pin_verify_otp",
 *   label = @Translation("forgot pin verify otp."),
 *   uri_paths = {
 *     "canonical" = "/forgot_pin_verify_otp/{device_uuid}/{verified_otp}"
 *   }
 * )
 */
class forgot_pin_verify_otp extends ResourceBase {
    /**
     * @param null $menu_name
     * @return ResourceResponse
     */
    public function get($device_uuid = null , $verified_otp = null ) 
    {
        
     
\Drupal\Core\Database\Database::setActiveConnection('funtional');
   $query = "SELECT user_uid,device_unique_uuid FROM ats_device_registration WHERE ats_device_registration.device_unique_uuid =:device_uuid";
   $data = db_query($query, array(":device_uuid" => $device_uuid))->fetchAssoc();
   $uid = $data['user_uid'];
   
   $query = "SELECT otp FROM ats_device_details WHERE ats_device_details.device_unique_uuid =:device_uuid";
   $data_uid = db_query($query, array(":device_uuid" => $device_uuid))->fetchAssoc();
   $otp = $data_uid['otp'];
   
\Drupal\Core\Database\Database::setActiveConnection();
		
		
         if($otp==md5($verified_otp))
         {
        
           $response = array(
        'response' => '0',
        'response_Code' => 'I-20004',
        'response_message' => "OTP Verified Successfully");   
         }   
         
         else
         {
          
              $response = array(
        'response' => '1',
        'response_Code' => 'I-20004',
        'response_message' => "OTP is not matched , if you need new OTP please click on send OTP button again.");
             
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
