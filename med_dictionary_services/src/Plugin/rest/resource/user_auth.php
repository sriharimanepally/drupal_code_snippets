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
 *   id = "user_auth",
 *   label = @Translation("user auth."),
 *   uri_paths = {
 *     "canonical" = "/user_auth/{phone}/{pin}"
 *   }
 * )
 */
class user_auth extends ResourceBase {
    /**
     * @param null $menu_name
     * @return ResourceResponse
     */
    public function get($phone = null , $pin = null  ) 
    {
         $pin1=substr($pin,0,4);
        $device_uuid=substr($pin,6);
      $data = db_select('users_field_data', 'n')
		->fields('n', array('uid'))
		->condition('name',$phone, '=')
		->execute()
		->fetchAssoc();

    $uid=$data['uid']; 
    
    
    

    
    
    
    
\Drupal\Core\Database\Database::setActiveConnection('funtional');
  $query = "SELECT ats_device_details.mobile_pin
  FROM ats_device_registration ats_device_registration
       INNER JOIN
       ats_device_details ats_device_details
          ON (ats_device_registration.device_unique_uuid =
                 ats_device_details.device_unique_uuid)
 WHERE (ats_device_registration.user_uid = :user_uid AND ats_device_details.device_unique_uuid=:device_uuid)";
$query_already_checked_data = db_query($query, array(":user_uid" => $uid,":device_uuid" => $device_uuid))->fetchAssoc(); 





\Drupal\Core\Database\Database::setActiveConnection();
   if(md5($pin1)==$query_already_checked_data['mobile_pin'])
    {
       $response = array(
        'response' => '1',
        'uid'=>md5($uid),    
        'response_Code' => 'I-20015',
        'response_message' => "login sucess fully");  
    }
    else
    {
        $response = array(
        'response' => '2',
        'response_Code' => 'I-20015',
        'response_message' => "login failed");
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
