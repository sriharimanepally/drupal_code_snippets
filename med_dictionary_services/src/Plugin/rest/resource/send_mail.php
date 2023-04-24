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
 *   id = "send_mail",
 *   label = @Translation("Send mail."),
 *   uri_paths = {
 *     "canonical" = "/send_mail/{mail_id}"
 *   }
 * )
 */
class send_mail extends ResourceBase {
    /**
     * @param null $menu_name
     * @return ResourceResponse
     */
    public function get($employee_id = null) {

        $account = \Drupal::currentUser();
        $uid = $account->id();//print_r($uid);exit;
		
		//print_r($employee_id);exit;

        $mailManager = \Drupal::service('plugin.manager.mail');
		$langcode = \Drupal::currentUser()->getPreferredLangcode();
		$params['context']['subject'] = "Subject";
		$params['context']['message'] = 'body';
		$to = "mtrackerteam@gmail.com";
		$mailManager->mail('system', 'mail', $to, $langcode, $params);
		
		//print_r($uid);exit;



    }
    public function post($employee_id = null) {

    }
    public function delete($employee_id = null) {

    }
    public function patch($employee_id = null) {

    }
}

