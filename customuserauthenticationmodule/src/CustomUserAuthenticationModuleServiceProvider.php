<?php

namespace Drupal\customuserauthenticationmodule;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class CustomUserAuthenticationModuleServiceProvider.
 *
 * @package Drupal\customuserauthenticationmodule
 */
class CustomUserAuthenticationModuleServiceProvider extends ServiceProviderBase {

   /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('user.auth');
    $definition->setClass('Drupal\customuserauthenticationmodule\CustomUserAuth');
  }

}