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
 *   id = "get_medicine_alternatives",
 *   label = @Translation("get medicine details."),
 *   uri_paths = {
 *     "canonical" = "/get_medicine_alternatives/{id}/{key}/{limit}"
 *   }
 * )
 */
class get_medicine_alternatives extends ResourceBase {
    /**
     * @param null $menu_name
     * @return ResourceResponse
     */
    public function get($id= null , $key = null ,$limit = null ) 
    {
        
\Drupal\Core\Database\Database::setActiveConnection('funtional');
$query = "SELECT brand, category, unit_price ,generic_id,id,manufacturer,package_price,package_qty,package_type,unit_qty,
	 unit_type
FROM rxcenter_medicine_alternatives INNER JOIN rxcenter_medicine_list ON (rxcenter_medicine_alternatives.alternative_medicine_name = rxcenter_medicine_list.medicine_name) 
where rxcenter_medicine_alternatives.medicine_name = :id order by package_price";
   $medicine_data = db_query($query, array(":id" => $id));
   
   $medicine_data_records_data=array();
    while($record = $medicine_data->fetchAssoc()) {
        
        $medicine_data_records_data['medicine_alternatives'][]=$record;  
        
              }

	
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
            'response_message' => "No Alternatives");
			
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
