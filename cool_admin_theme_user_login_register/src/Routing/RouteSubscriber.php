<?php

/**
 * @file
 * Contains \Drupal\cool_admin_theme_user_login_register\Routing\RouteSubscriber.
 */

namespace Drupal\cool_admin_theme_user_login_register\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
     if ($route = $collection->get('user.login')) {
$route->setDefault('_form', '\Drupal\cool_admin_theme_user_login_register\Form\NewUserLoginForm');
     }

   //   if ($route = $collection->get('user.register')) {
   //      $route->setDefault('_form', '\Drupal\cool_admin_theme_user_login_register\Form\NewUserRegisterForm');
   //  }

   }
}