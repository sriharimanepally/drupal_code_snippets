<?php
/**
 * @file
 * Contains \Drupal\demo\Form\MultistepFormBase.
 */

namespace Drupal\dn_login\Services;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LocalStorage {

  /**
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * @var \Drupal\Core\Session\SessionManagerInterface
   */
  private $sessionManager;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $currentUser;

  /**
   * @var \Drupal\user\PrivateTempStore
   */
  public $store;

  /**
   * Constructs a \Drupal\dn_login\LocalStorage.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   * @param \Drupal\Core\Session\SessionManagerInterface $session_manager
   * @param \Drupal\Core\Session\AccountInterface $current_user
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, SessionManagerInterface $session_manager, AccountInterface $current_user) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->sessionManager = $session_manager;
    $this->currentUser = $current_user;

    $this->store = $this->tempStoreFactory->get('otp_data');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('session_manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}.
   */
  public function sessionCheck() {
    // Start a manual session for anonymous users.
    
      if ($this->currentUser->isAnonymous() && !isset($_SESSION['otp_holds_session'])) {
      $_SESSION['otp_holds_session'] = true;
      $this->sessionManager->start();
      
    }

    
  }

  

  /**
   * Helper method that removes all the keys from the store collection used for
   */
  public function deleteStore() {
    $keys = ['uid'];
    foreach ($keys as $key) {
      $this->store->delete($key);
    }
  }
}