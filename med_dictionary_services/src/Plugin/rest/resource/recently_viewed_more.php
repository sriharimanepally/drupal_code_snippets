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
 *   id = "recently_viewed_more",
 *   label = @Translation("recently viewed more."),
 *   uri_paths = {
 *     "canonical" = "/recently_viewed_more/{id}"
 *   }
 * )
 */
class recently_viewed_more extends ResourceBase {
    /**
     * @param null $menu_name
	 
     * @return ResourceResponse
     */
    public function get($id= null ) 
    {
        
\Drupal\Core\Database\Database::setActiveConnection('funtional');
 $account = \Drupal::currentUser();
	$uid1 = $account->id();
	 $query = "SELECT rxcenter_medicine_list.medicine_name,
       rxcenter_medicine_list.brand,
       rxcenter_medicine_list.category,
       rxcenter_medicine_list.d_class,
       rxcenter_medicine_list.generic_id,
       rxcenter_medicine_list.id,
       rxcenter_medicine_list.manufacturer,
       rxcenter_medicine_list.package_price,
       rxcenter_medicine_list.package_qty,
       rxcenter_medicine_list.package_type,
       rxcenter_medicine_list.unit_price,
       rxcenter_medicine_list.unit_qty,
       rxcenter_medicine_list.unit_type,
	   rxcenter_recently_viewed.viewed_timestamp,
       rxcenter_medicine_list.medicine_details_status
  FROM rxcenter_recently_viewed rxcenter_recently_viewed
       INNER JOIN
       rxcenter_medicine_list rxcenter_medicine_list
          ON (rxcenter_recently_viewed.medicine_name =
                 rxcenter_medicine_list.medicine_name)
    WHERE (rxcenter_recently_viewed.uid = :uid)
ORDER BY rxcenter_recently_viewed.viewed_timestamp DESC 
limit 5,100";
   $medicine_data = db_query($query, array(":uid" => $uid1));
	   $medicine_data_records_data=array();
           $i=0;
           
    while($record = $medicine_data->fetchAssoc()) {
          $data['constituents']= json_decode(get_constituents_more($record['medicine_name']),true);
        
  $record=array_merge($data,$record);
        $medicine_data_records_data['suggestions'][]=$record;  
        $i++;
              }
\Drupal\Core\Database\Database::setActiveConnection();
   
	if($medicine_data)
   {
       $response=array(
'count' => $i,
'response' => 0,
'response_Code'=>'I-2004',
'response_message'=>"Fetching Recently Viewed Medicines",          
'suggestions' => $medicine_data_records_data)
    ;
   }
   else
   {
       
       $response = array(
            'response' => '1',
            'response_Code' => 'F-10012',
            'response_message' => "No Recent Views");
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

function get_constituents_more($medicine_name)
{
            
\Drupal\Core\Database\Database::setActiveConnection('funtional');


	 $query = "select constituents_name from rxcenter_medicine_constituents where medicine_name=:id  limit 1";
  $medicine_data = db_query($query, array(":id" => $medicine_name));
 
   $medicine_data_records_data=array();
    while($record = $medicine_data->fetchAssoc()) { 
        $medicine_data_records_data=json_decode($record['constituents_name'],true); 
    }
 $account = \Drupal::currentUser();
	$uid = $account->id();

		 
\Drupal\Core\Database\Database::setActiveConnection();
   
	if($medicine_data)
   {

$response = array(
            'status' => 200,
			'response' => $medicine_data_records_data);
   }
   else
   {
       
$medicine_data_records_data=array();
   }    
        return json_encode($medicine_data_records_data);

    
}
