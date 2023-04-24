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
 *   id = "get_medicine_details",
 *   label = @Translation("get medicine details."),
 *   uri_paths = {
 *     "canonical" = "/get_medicine_details/{id}/{key}"
 *   }
 * )
 */
class get_medicine_details extends ResourceBase {
    /**
     * @param null $menu_name
     * @return ResourceResponse
     */
    public function get($id= null , $key = null  ) 
    {
        
\Drupal\Core\Database\Database::setActiveConnection('funtional');


	 $query = "select * from rxcenter_medicine_list where medicine_name = :id";
  $medicine_data = db_query($query, array(":id" => $id));
 
   $medicine_data_records_data=array();
    while($record = $medicine_data->fetchAssoc()) { 
        $medicine_data_records_data['medicine']=$record; 
    }
 $account = \Drupal::currentUser();
	$uid = $account->id();

		 $query_roles_data="INSERT INTO rxcenter_recently_viewed(uid,medicine_name,viewed_timestamp)VALUES(:uid,:medicine_name,NOW()) ON DUPLICATE KEY
    UPDATE viewed_timestamp=now()";
		db_query($query_roles_data,array(":uid"=>$uid,":medicine_name"=>$id));
\Drupal\Core\Database\Database::setActiveConnection();
   
	if($medicine_data)
   {

$response = array(
            'status' => 200,
			'response' => $medicine_data_records_data);
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
