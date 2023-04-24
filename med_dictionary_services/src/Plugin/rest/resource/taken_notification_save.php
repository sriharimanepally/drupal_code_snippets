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
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Provides a service resource for menus.
 *
 * @RestResource(
 *   id = "taken_notification_save",
 *   label = @Translation("Save taken notification flag."),
 *   uri_paths = {
 *     "canonical" = "/taken_notification_save/{device_uuid}/{notification_id}"
 *   }
 * )
 */
class taken_notification_save extends ResourceBase {
    /**
     * @param null $menu_name
     * @return ResourceResponse
     */
    public function get($uuid= null,$notification_id=null) 
    {
        
\Drupal\Core\Database\Database::setActiveConnection('funtional');
   $query = "SELECT user_uid,device_unique_uuid FROM ats_device_registration WHERE ats_device_registration.device_unique_uuid =:device_uuid";
   $data = db_query($query, array(":device_uuid" => $uuid))->fetchAssoc();
   $uid = $data['user_uid'];
		
		
		
          $id = '';
            $type = 'data_calander';
            $fields = array('uid'=>$uid,'id' => urlencode($notification_id), 'status' => 1, 'time' => date() );


            foreach ($fields as $key => $value) {
                $fields_string .= $key . '=' . $value . '&';
            }


            rtrim($fields_string, '&');
        
        
global $config;
            $_h = curl_init();
//curl_setopt($_h, CURLOPT_HEADER, 1); 
            curl_setopt($_h, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($_h, CURLOPT_HTTPGET, 1);
            curl_setopt($_h, CURLOPT_URL, $config['couchdb_url'].'/update_notification_status.php');
            curl_setopt($_h, CURLOPT_POST, count($fields));
            curl_setopt($_h, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($_h, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
            curl_setopt($_h, CURLOPT_DNS_CACHE_TIMEOUT, 2);
            $response = curl_exec($_h);
            return new JsonResponse(json_decode($response));
       $response = array(
            'response' => '1',
            'response_Code' => 'F-10012',
            'response_message' => "No Alternatives");
//		$response=	json_decode($response);
       
        return new ResourceResponse($response);
 
//            return new ResourceResponse(json_decode($response));

        
        
        
       
  
    }
    public function post($id = null) {
       
    }
    public function delete($id = null) {
       
    }
    public function patch($id = null) {
       
    }
}
