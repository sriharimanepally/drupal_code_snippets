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
 *   id = "save_session_id",
 *   label = @Translation("Save session id."),
 *   uri_paths = {
 *     "canonical" = "/save_session_id/{device_uuid}/{session_id}"
 *   }
 * )
 */
class save_session_id extends ResourceBase {
    /**
     * @param null $menu_name
     * @return ResourceResponse
     */
    public function get($device_uuid= null,$session_id=null) 
    {
        $device_uuid = $device_uuid;
		$session_id = $session_id;
		\Drupal\Core\Database\Database::setActiveConnection();
\Drupal\Core\Database\Database::setActiveConnection('funtional');
	 
	 $query_session_id = "UPDATE ats_device_registration SET session_id=:session_id where device_unique_uuid=:device_uuid";
     $data_update = db_query($query_session_id, array(":session_id" => $session_id,":device_uuid" => $device_uuid));
		  
		     $response = array(
        'response' => '1',
        'response_Code' => 'I-20004',
        'session_id'=>$session_id,
		'response_message' => "Session id saved successfully");
         
        return new ResourceResponse($response);
 
    }
    public function post($id = null) {
       
    }
    public function delete($id = null) {
       
    }
    public function patch($id = null) {
       
    }
}
