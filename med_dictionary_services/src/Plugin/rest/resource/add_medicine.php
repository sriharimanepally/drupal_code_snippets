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
 *   id = "add_medicine",
 *   label = @Translation("add medicine list."),
 *   uri_paths = {
 *     "canonical" = "/add_medicine/{med_name}/{category}/{manufacturer}/{package_qty}/{package_price}"
 *   }
 * )
 */
class add_medicine extends ResourceBase {
    /**
     * @param null $menu_name
     * @return ResourceResponse
     */
    public function get($med_name= null , $category = null, $manufacturer = null , $package_qty = null, $package_price = null ) 
    {
        $account = \Drupal::currentUser();
            $uid = $account->id();
            
\Drupal\Core\Database\Database::setActiveConnection('funtional');
$query_med_count = "SELECT count(*) as cn FROM rxcenter_medicine_list WHERE medicine_name =:med_name";
   $data_med_count = db_query($query_med_count, array(":med_name" => $med_name))->fetchAssoc();
   $cn = $data_med_count['cn'];

   if($cn == 0)
   {
 $query="INSERT INTO rxcenter_medicine_list
(medicine_name, category, manufacturer, package_price,package_qty,uid,is_verified) 
VALUES (:med_name,:category,:manufacturer,:package_price,:package_qty,:uid,'0');";
    $data= db_query($query,array(":med_name"=>$med_name,":category"=>$category,":manufacturer"=>$manufacturer,":package_price"=>$package_price,":package_qty"=>$package_qty,":uid"=>$uid));
	
\Drupal\Core\Database\Database::setActiveConnection();
   
	if($data)
   {
        $response = array(
        'response' => '1',
        'response_Code' => 'I-20004',
        'response_message' => "OTP Sent Successfully.");

   }
   else
   {
       
       $response = array(
            'response' => '0',
            'response_Code' => 'F-10012',
            'response_message' => "Please  Try Again");
   }    

   }
   else
   {
       $response = array(
            'response' => '3',
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
