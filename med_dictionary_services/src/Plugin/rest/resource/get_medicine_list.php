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
 *   id = "get_medicine_list",
 *   label = @Translation("get_medicine list."),
 *   uri_paths = {
 *     "canonical" = "/get_medicine_list/{id}/{key}/{limit}"
 *   }
 * )
 */
class get_medicine_list extends ResourceBase {
    /**
     * @param null $menu_name
     * @return ResourceResponse
     */
    public function get($id= null , $key = null, $limit = null  ) 
    {
        
\Drupal\Core\Database\Database::setActiveConnection('funtional');


	$id = "%".$id."%";
	 $query = "select medicine_name AS suggestion, category,brand, manufacturer, package_price,
package_qty, package_type, unit_price, unit_qty, unit_type from rxcenter_medicine_list where medicine_name LIKE :id ";
  $medicine_data = db_query($query, array(":id" => $id));
 //$medicine_data = db_query($query);
   $medicine_data_records_data=array();
   
   $i=0;
    while($record = $medicine_data->fetchAssoc()) { 
        
        
        
        $medicine_data_records_data['suggestions'][$i]['manufacturer']=$record['manufacturer'];
        $medicine_data_records_data['suggestions'][$i]['package_price']=$record['package_price'];
        $medicine_data_records_data['suggestions'][$i]['package_qty']=$record['package_qty'];
        $medicine_data_records_data['suggestions'][$i]['package_type']=$record['package_type'];
        $medicine_data_records_data['suggestions'][$i]['unit_price']=$record['unit_price'];
        $medicine_data_records_data['suggestions'][$i]['unit_qty']=$record['unit_qty'];
        $medicine_data_records_data['suggestions'][$i]['unit_type']=$record['unit_type'];
        $medicine_data_records_data['suggestions'][$i]['suggestion']=$record['suggestion'];
        $medicine_data_records_data['suggestions'][$i]['category']=$record['category'];
        $medicine_data_records_data['suggestions'][$i]['constituents']= json_decode(get_constituents_v1($record['suggestion']),true);
        $i++;
    }
	
\Drupal\Core\Database\Database::setActiveConnection();
   
	if($medicine_data)
   {
//       $response=array(
//	'response' => 0,
//	'response_Code'=>'I-2004',
//	'response_message'=>"Fetching Medicines List",          
//	'suggestions' => $medicine_data_records_data);
	     $response = array(
            'status' => 'ok',
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


function get_constituents_v1($medicine_name)
{
            
\Drupal\Core\Database\Database::setActiveConnection('funtional');

//$query = "select constituents_name from rxcenter_medicine_constituents where  medicine_name=:id and constituents_name IS NOT NULL and constituents_name <> '[]' limit 1";
//  $medicine_data = db_query($query, array(":id" => $medicine_name));
	 $query = "select constituents_name from rxcenter_medicine_constituents where medicine_name=:id  limit 1";
  $medicine_data = db_query($query, array(":id" => $medicine_name));
// 
   $medicine_data_records_data=array();
    while($record = $medicine_data->fetchAssoc()) { 
        $medicine_data_records_data=json_decode($record['constituents_name'],true); 
    }
 $account = \Drupal::currentUser();
	$uid = $account->id();

		 
\Drupal\Core\Database\Database::setActiveConnection();
   
	if($medicine_data_records_data)
   {

$response = array(
            'status' => 200,
			'response' => $medicine_data_records_data);
   }
   else
   {
       
$medicine_data_records_data=array();
//$medicine_data_records_data=json_decode('[]',true); 
   }    
        return json_encode($medicine_data_records_data);

    
}