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
 *   id = "reset_pin",
 *   label = @Translation("reset pin."),
 *   uri_paths = {
 *     "canonical" = "/reset_pin/{device_uuid}/{pin}"
 *   }
 * )
 */
class reset_pin extends ResourceBase {
    /**
     * @param null $menu_name
     * @return ResourceResponse
     */
    public function get($device_uuid = null , $pin = null ) 
    {
        
\Drupal\Core\Database\Database::setActiveConnection('funtional');
   $query = "SELECT user_uid,device_unique_uuid FROM ats_device_registration WHERE ats_device_registration.device_unique_uuid =:device_uuid";
   $data = db_query($query, array(":device_uuid" => $device_uuid))->fetchAssoc();
   $uid = $data['user_uid'];
   
\Drupal\Core\Database\Database::setActiveConnection();
	
	 $query_uid = "SELECT  users_field_data.name As name,
       users_extended.first_name,
       users_field_data.mail FROM users_extended users_extended INNER JOIN users_field_data users_field_data ON (users_extended.uid = users_field_data.uid)
                                                          WHERE (users_extended.uid = :uid)";
         $data_uid = db_query($query_uid, array(":uid" => $uid))->fetchAssoc();
	 $name = $data_uid['name'];
	 
	
    $pin=md5($pin);
\Drupal\Core\Database\Database::setActiveConnection('funtional');
	
	 $query_update_users1 = "UPDATE ats_device_details SET mobile_pin=:pin where device_unique_uuid=:device_uuid";
         
          $data_update = db_query($query_update_users1, array(":pin" => $pin,":device_uuid" => $device_uuid));
     db_set_active(); 

   if($data_update)
   {
     
      $response = array(
            'response' => '0',
			'name'=>$name,
		    'user_id'=>$uid,
            'response_Code' => 'E-10012',
            'response_message' => "Pin Changed Successfully");  
   }
   else
   {
       
       $response = array(
            'response' => '1',
            'response_Code' => 'F-10012',
            'response_message' => "Please  Try Again");
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
