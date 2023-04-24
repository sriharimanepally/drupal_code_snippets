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
 *   id = "synchronize_current_day_schedules_get",
 *   label = @Translation("synchronize current day schedules details."),
 *   uri_paths = {
 *     "canonical" = "/synchronize_current_day_schedules_get/{device_uuid}"
 *   }
 * )
 */
class synchronize_current_day_schedules_get extends ResourceBase {
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

        
\Drupal\Core\Database\Database::setActiveConnection('funtional');
	  

        $query="SELECT ats_device_registration.user_uid as uid
  FROM ats_device_registration ats_device_registration
 WHERE (ats_device_registration.device_unique_uuid = :uuid)";

$data_uid = db_query($query, array(":uuid" => $uuid))->fetchAssoc();
   
//            $type = 'data_calander';
            $fields = array('id' => urlencode($id), 'type' => urlencode($type));
            $fields['param1'] = date("Y-m-d",strtotime("-1 days"));
            $fields['param2'] = date("Y-m-d",strtotime("+1 days"));
            $fields['param3'] =$data_uid['uid'];
//           return new JsonResponse($fields);


//            $id = '123456';
//            $type = 'data_calander';
//            $fields = array('id' => urlencode($id), 'type' => urlencode($type));
//
//
//
//
//            $fields['param1'] = '2016-03-11';
//            $fields['param2'] = '2016-03-21';
//            $fields['param3'] ='0';

            foreach ($fields as $key => $value) {
                $fields_string .= $key . '=' . $value . '&';
            }


            rtrim($fields_string, '&');
global $config;
            $_h = curl_init();
//curl_setopt($_h, CURLOPT_HEADER, 1); 
            curl_setopt($_h, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($_h, CURLOPT_HTTPGET, 1);
            curl_setopt($_h, CURLOPT_URL, $config['couchdb_url'].'/get_cal_data.php');
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
