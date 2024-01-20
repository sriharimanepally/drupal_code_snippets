<?php

namespace Drupal\dn_login\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Url;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use \Drupal\Core\Session\SessionManagerInterface;
use \Drupal\Core\Session\AccountInterface;


/**
 * Class for bulding OTP Form.
 */
class OtpForm extends FormBase {

  /**
   * Drupal\Core\Messenger\Messenger definition.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * Drupal\dn_login\Services\Otp definition.
   *
   * @var \Drupal\dn_login\Services\Otp
   */
  protected $otp;


  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $enityTypeManager;
  

  /**
   * Constructs a new OtpForm.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   
   */
  
  /**
   * {@inheritdoc}
   */
 

  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->otp = $container->get('dn_login.otp');
    $instance->enityTypeManager = $container->get('entity_type.manager');
    
    return $instance;
    

  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'otp_form_id';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $callService = \Drupal::service('dn_login.localStorage');
    
    $expirationTime = $this->otp->getExpirationTime($callService->store->get("uid"));
    $form['#cache'] = ['max-age' => 0];
    $form['#prefix'] = "<div class='otp-form'>";
    $form['#suffix'] = "</div>";
    $form['otp'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OTP'),
      '#description' => $this->t('Enter the OTP you received in mobile. Didn\'t receive the OTP? You can resend OTP in: <span id="time">'.$expirationTime.'</span>'),
      '#weight' => '0',
      '#required' => TRUE,
      '#suffix' => '<span class="otp-message"></span>'
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Login'),
      '#ajax' => [
        'callback' => '::ajaxOtpCallback',
        'event' => 'click',
      ],
    ];
    $form['resend'] = [
      '#type' => 'markup',
      '#markup' => "<span id='resend-span'>" . $this->t('Resend') . "</span>",
    ];

    $form['#attached']['library'][] = 'dn_login/dn_login.front';
    $form['#attached']['drupalSettings']['initial_time'] = date('i:s', (int) $expirationTime - time());


    if ((int) $expirationTime > time()) {
      $form['otp']['#description'] = $this->t('Enter the OTP you received in Mobile. Didn\'t receive the OTP? You can resend OTP in: <span id="time">@time</span>', ['@time' => date('i:s', (int) $expirationTime - time())]);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    
    $callService = \Drupal::service('dn_login.localStorage');
    $uid =  $callService->store->get('uid');
  
    $value = $form_state->getValue('otp');
    if ($this->otp->check($uid, $value) == FALSE) {
      
      $form_state->setErrorByName('otp', 'Invalid or expired OTP.');
    }
   
  }

  /**
   * Ajax callback of the form.
   */
  public function ajaxOtpCallback(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
   $callService = \Drupal::service('dn_login.localStorage');
    $uid =  $callService->store->get('uid');
    
    if ($form_state->getErrors()) {
      //echo "inside errors";exit;
     unset($form['#prefix']);
      unset($form['#suffix']);
      $form['status_messages'] = [
        '#type'   => 'status_messages',
        '#weight' => -10,
      ];
      $form_state->setRebuild();
      $response->addCommand(new ReplaceCommand('.otp-form', $form));
      return $response;
    }
    
   unset($form['#prefix']);
    unset($form['#suffix']);
    $form['status_messages'] = [
      '#type'   => 'status_messages',
      '#weight' => -10,
    ];
    $response->addCommand(new ReplaceCommand('.otp-form', $form));
    $account = $this->enityTypeManager->getStorage('user')->load($uid);
    $this->otp->expire($uid);
    $callService->deleteStore();
    user_login_finalize($account);
    $redirect_command = new RedirectCommand(Url::fromRoute('user.page')->toString());
    $response->addCommand($redirect_command);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

}
