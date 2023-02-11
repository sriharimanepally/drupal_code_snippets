<?php

/**
 * @file
 * Contains \Drupal\ace_theme_user_login_register\Form\NewUserPasswordForm.
 */

namespace Drupal\ace_theme_user_login_register\Form;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\Element\Email;
use Drupal\user\UserInterface;
use Drupal\user\UserStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\Form\UserPasswordForm;

/**
 * Provides a user password reset form.
 *
 * Send the user an email to reset their password.
 *
 * @internal
 */
class NewUserPasswordForm extends UserPasswordForm {


  public function buildForm(array $form, FormStateInterface $form_state) {

	$form = parent::buildForm($form, $form_state);

    // $form['name'] = [
    //   '#type' => 'textfield',
    //   '#title' => $this->t('Username or email address'),
    //   '#size' => 60,
    //   '#maxlength' => max(UserInterface::USERNAME_MAX_LENGTH, Email::EMAIL_MAX_LENGTH),
    //   '#required' => TRUE,
    //   '#attributes' => [
    //     'autocorrect' => 'off',
    //     'autocapitalize' => 'off',
    //     'spellcheck' => 'false',
    //     'autofocus' => 'autofocus',
    //   ],
    // ];
    // // Allow logged in users to request this also.
    // $user = $this->currentUser();
    // if ($user->isAuthenticated()) {
    //   $form['name']['#type'] = 'value';
    //   $form['name']['#value'] = $user->getEmail();
    //   $form['mail'] = [
    //     '#prefix' => '<p>',
    //     '#markup' => $this->t('Password reset instructions will be mailed to %email. You must log out to use the password reset link in the email.', ['%email' => $user->getEmail()]),
    //     '#suffix' => '</p>',
    //   ];
    // }
    // else {
    //   $form['mail'] = [
    //     '#prefix' => '<p>',
    //     '#markup' => $this->t('Password reset instructions will be sent to your registered email address.'),
    //     '#suffix' => '</p>',
    //   ];
    //   $form['name']['#default_value'] = $this->getRequest()->query->get('name');
    // }





    $config = $this->config('system.site');

    $form['account'] = [
      '#type'   => 'container',
      '#weight' => -10,
//      '#prefix' => '<div id="edit-tests-wrapper" >',
//      '#suffix' => '</div>',

    ];

    $form['account']['name'] = [
      '#type' => 'tel',
       '#id' => 'edit-name',
      '#required' => TRUE,
	    // '#description' => $this->t('Enter your @s Registered Mobile Number.', array('@s' => $config->get('name'))),
      '#attributes' => [
        'autocomplete' => 'off',
        'autocapitalize' => 'off',
        'spellcheck' => 'false',
        'placeholder' => 'Mobile Number',
		'style' => 'max-width:196px',
      ],
      '#prefix' => '<div id="edit-test1-wrapper">',
        '#default_value' => '',
//      '#access' => ($register || ($user->id() == $account->id() && $user->hasPermission('change own username')) || $admin),
    ];


    $form['account']['send_otp'] = [
      '#type' => 'button',
       '#id' => 'edit-send-otp',
      '#value' => $this->t('Send OTP'),
        '#attributes' => [
           'class' => ['btn-primary'],

        ],
         '#executes_submit_callback' => FALSE,
    ];


    $form['account']['edit'] = [
      '#type' => 'button',
      '#id' => 'edit-otp',
      '#value' => $this->t('Edit'),
      '#suffix' => '</div>',
    '#attributes' => [
           'class' => ['btn-primary'],
          'style' => 'display:none',
      ],
    ];


    $form['account']['enter_otp'] = [
      '#type' => 'password',
        '#id' => 'edit-enter-otp',
      '#maxlength' => 4,
      '#attributes' => [
        'class' => ['username'],
        'autocomplete' => 'off',
        'autocapitalize' => 'off',
        'spellcheck' => 'false',
        'placeholder' => 'Enter OTP',
          'style' => 'display:none',
    ],
      '#prefix' => '<div id="edit-test2-wrapper" style="display:inline">',
    ];


    $form['account']['verify_otp'] = [
      '#type' => 'button',
      '#id' => 'edit-verify',
      '#value' => $this->t('Verify OTP'),
      '#suffix' => '</div>',
        '#attributes' => [
            'class' => ['btn-primary'],
          'style' => 'display:none',
        ],
    ];

    $form['account']['pass'] = [
      '#type' => 'password_confirm',
      '#size' => 50,
      '#id' => 'pass',
   '#prefix' => '<div id="pass_field" class="hide">',
      '#suffix' => '</div>',
      '#description' => $this->t('To change the current user password, enter the new password in both fields.'),
      '#attributes' => [
  'class' => ['hide'],
        'style' => 'display:none',
      ],
  ];

  // $form['#attached']['library'][] = 'user/drupal.user.telinputflag';
  $form['#attached']['library'][] = 'custom_user_profile/custom_user_profile.custom_script_new_account';


    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['#attributes']['class'][] ='hide center';
    $form['actions']['submit'] = ['#type' => 'submit', '#value' => $this->t('Change Password')];
    $form['#cache']['contexts'][] = 'url.query_args';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // $flood_config = $this->configFactory->get('user.flood');
    // if (!$this->flood->isAllowed('user.password_request_ip', $flood_config->get('ip_limit'), $flood_config->get('ip_window'))) {
    //   $form_state->setErrorByName('name', $this->t('Too many password recovery requests from your IP address. It is temporarily blocked. Try again later or contact the site administrator.'));
    //   return;
    // }
    // $this->flood->register('user.password_request_ip', $flood_config->get('ip_window'));
    // $name = trim($form_state->getValue('name'));
    // // Try to load by email.
    // $users = $this->userStorage->loadByProperties(['mail' => $name]);
    // if (empty($users)) {
    //   // No success, try to load by name.
    //   $users = $this->userStorage->loadByProperties(['name' => $name]);
    // }
    // $account = reset($users);
    // if ($account && $account->id()) {
    //   // Blocked accounts cannot request a new password.
    //   if (!$account->isActive()) {
    //     $form_state->setErrorByName('name', $this->t('%name is blocked or has not been activated yet.', ['%name' => $name]));
    //   }
    //   else {
    //     // Register flood events based on the uid only, so they apply for any
    //     // IP address. This allows them to be cleared on successful reset (from
    //     // any IP).
    //     $identifier = $account->id();
    //     if (!$this->flood->isAllowed('user.password_request_user', $flood_config->get('user_limit'), $flood_config->get('user_window'), $identifier)) {
    //       $form_state->setErrorByName('name', $this->t('Too many password recovery requests for this account. It is temporarily blocked. Try again later or contact the site administrator.'));
    //       return;
    //     }
    //     $this->flood->register('user.password_request_user', $flood_config->get('user_window'), $identifier);
    //     $form_state->setValueForElement(['#parents' => ['account']], $account);
    //   }
    // }
    // else {
    //   $form_state->setErrorByName('name', $this->t('%name is not recognized as a username or an email address.', ['%name' => $name]));
    // }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // $langcode = $this->languageManager->getCurrentLanguage()->getId();

    // $account = $form_state->getValue('account');
    // // Mail one time login URL and instructions using current language.
    // $mail = _user_mail_notify('password_reset', $account, $langcode);
    // if (!empty($mail)) {
    //   $this->logger('user')->notice('Password reset instructions mailed to %name at %email.', ['%name' => $account->getAccountName(), '%email' => $account->getEmail()]);
    //   $this->messenger()->addStatus($this->t('Further instructions have been sent to your email address.'));
    // }

    // $form_state->setRedirect('user.page');


    $password = $form_state->getValue('pass');

       $mobile_no=$form['account']['name']['#value'];
          $mobile_no=str_replace(" ","",$mobile_no);
		  $mobile_no=str_replace("-","",$mobile_no);
 $query="SELECT users_field_data.uid AS uid
  FROM users_field_data users_field_data
 WHERE (users_field_data.name = :name)";

 $data = db_query($query, array(":name" => $mobile_no))->fetchAssoc();
 $uid=$data['uid'];

    $user=\Drupal\user\Entity\User::load($uid);
    $pass = $user->getPassword();
    $user->setPassword($password);
     $user->save();

     drupal_set_message($this->t('Password Changed Successfully.'));
    $form_state->setRedirect('user.page');



  }

}
