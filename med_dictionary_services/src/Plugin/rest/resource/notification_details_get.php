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
 *   id = "notification_details_get",
 *   label = @Translation("notification details get."),
 *   uri_paths = {
 *     "canonical" = "/notification_details_get/{device_uuid}"
 *   }
 * )
 */
class notification_details_get extends ResourceBase {
    /**
     * @param null $menu_name
     * @return ResourceResponse
     */
    public function get($uuid= null) 
    {
        
        
//                    $account = \Drupal::currentUser();
//            $uid = $account->id();
//      $start =$_GET['start'];
//     $end = $_GET['end'];



            $id = $uuid;
            $type = 'data_calander';
            $fields = array('id' => urlencode($id));

            foreach ($fields as $key => $value) {
                $fields_string .= $key . '=' . $value . '&';
            }


            rtrim($fields_string, '&');
global $config;
            $_h = curl_init();
//curl_setopt($_h, CURLOPT_HEADER, 1); 
            curl_setopt($_h, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($_h, CURLOPT_HTTPGET, 1);
            curl_setopt($_h, CURLOPT_URL, $config['couchdb_url'].'/get_data_notification_view.php');
            curl_setopt($_h, CURLOPT_POST, count($fields));
            curl_setopt($_h, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($_h, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
            curl_setopt($_h, CURLOPT_DNS_CACHE_TIMEOUT, 2);
            $response = curl_exec($_h);
//            echo $response;exit;
           return new JsonResponse(json_decode($response));

//       $response = array(
//            'response' => '1',
//            'response_Code' => 'F-10012',
//            'response_message' => "No Alternatives");
////		$response=	json_decode($response);
//       
//        return new ResourceResponse($response);
// 
//            return new ResourceResponse(json_decode($response));

        
        
        
       
  
    }
    public function post($id = null) {
       
    }
    public function delete($id = null) {
       
    }
    public function patch($id = null) {
       
    }
}
