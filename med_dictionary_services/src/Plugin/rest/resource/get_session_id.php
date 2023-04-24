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
 *   id = "get_session_id",
 *   label = @Translation("Get session id."),
 *   uri_paths = {
 *     "canonical" = "/get_session_id/{device_uuid}/{device_pin}"
 *   }
 * )
 */
class get_session_id extends ResourceBase {
    /**
     * @param null $menu_name
     * @return ResourceResponse
     */
    public function get($device_uuid= null,$device_pin=null) 
    {
        $device_uuid = $device_uuid;
		$device_pin = $device_pin;

		\Drupal\Core\Database\Database::setActiveConnection();
\Drupal\Core\Database\Database::setActiveConnection('funtional');
	 
	 $query_session_id = "SELECT session_id FROM ats_device_registration where device_unique_uuid=:device_uuid";
	  $medicine_data = db_query($query_session_id, array(":device_uuid" => $device_uuid));
 
   $medicine_data_records_data=array();
    while($record = $medicine_data->fetchAssoc()) { 
        $medicine_data_records_data=$record['session_id']; 
    }
 $account = \Drupal::currentUser();
	$uid = $account->id();

		 
\Drupal\Core\Database\Database::setActiveConnection();

   
		     $response = array(
        'response' => '1',
        'response_Code' => 'I-20004',
        'session_id'=>$medicine_data_records_data,
		'response_message' => "Retrieving Session id successfully");
         
        return new ResourceResponse($response);
 
    }
    public function post($id = null) {
       
    }
    public function delete($id = null) {
       
    }
    public function patch($id = null) {
       
    }
}
