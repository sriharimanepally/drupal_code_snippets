<?php

namespace Drupal\customuserauthenticationmodule;

use Drupal\Core\DependencyInjection\DeprecatedServicePropertyTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Password\PasswordInterface;
use Drupal\user\UserAuth;

/**
 * Validates user authentication credentials.
 */
class CustomUserAuth extends UserAuth {
  use DeprecatedServicePropertyTrait;

  /**
   * {@inheritdoc}
   */
  protected $deprecatedProperties = ['entityManager' => 'entity.manager'];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The password hashing service.
   *
   * @var \Drupal\Core\Password\PasswordInterface
   */
  protected $passwordChecker;

  /**
   * Constructs a UserAuth object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Password\PasswordInterface $password_checker
   *   The password service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, PasswordInterface $password_checker) {
    $this->entityTypeManager = $entity_type_manager;
    $this->passwordChecker = $password_checker;
  }

  /**
   * {@inheritdoc}
   */
  public function authenticate($username, $password) {
    $uid = FALSE;


    if (!empty($username) && strlen($password) > 0) {

      if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
        $account_search = $this->entityTypeManager->getStorage('user')->loadByProperties(['mail' => $username]);
      }
      else{
        $account_search = $this->entityTypeManager->getStorage('user')->loadByProperties(['name' => $username]);
      }


      if ($account = reset($account_search)) {
        if ($this->passwordChecker->check($password, $account->getPassword())) {
          // Successful authentication.
          $uid = $account->id();

          // Update user to new password scheme if needed.
          if ($this->passwordChecker->needsRehash($account->getPassword())) {
            $account->setPassword($password);
            $account->save();
          }
        }
      }
    }

    return $uid;
  }

}
