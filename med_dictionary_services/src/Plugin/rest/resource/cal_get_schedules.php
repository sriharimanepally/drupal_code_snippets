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
 *   id = "cal_get_schedules",
 *   label = @Translation("calender get schedules"),
 *   uri_paths = {
 *     "canonical" = "/cal_get_schedules/{start}/{end}"
 *   }
 * )
 */
class cal_get_schedules extends ResourceBase {
    /**
     * @param null $menu_name
     * @return ResourceResponse
     */
    public function get($start= null , $end = null  ) 
    {
       
       $response = array(
            'response' => '1',
            'response_Code' => 'F-10012',
            'response_message' => "Please  Try Again");
       
        return new ResourceResponse($response);
 
    }
    public function post($id = null) {
       
    }
    public function delete($id = null) {
       
    }
    public function patch($id = null) {
       
    }
}
