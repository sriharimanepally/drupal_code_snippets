<?php

/**
 * @file
 * Contains \Drupal\sufee_theme_user_login_register\Form\NewUserRegisterForm.
 */

namespace Drupal\sufee_theme_user_login_register\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityConstraintViolationListInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\UserInterface;
use Drupal\user\RegisterForm;

/**
 * Provides a user register form.
 */

class NewUserRegisterForm extends RegisterForm {

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildForm($form, $form_state);

  //During the initial form build, add this form object to the form state and
  //allow for initial preparation before form building and processing.
  // if (!$form_state->has('entity_form_initialized')) {
  //   $this->init($form_state);
  // }

    $user = $this->currentUser();
    /** @var \Drupal\user\UserInterface $account */
    $account = $this->entity;

    // This form is used for two cases:
    // - Self-register (route = 'user.register').
    // - Admin-create (route = 'user.admin_create').
    // If the current user has permission to create users then it must be the
    // second case.
    $admin = $account->access('create');

    // Pass access information to the submit handler. Running an access check
    // inside the submit function interferes with form processing and breaks
    // hook_form_alter().
    $form['administer_users'] = [
      '#type' => 'value',
      '#value' => $admin,
    ];

    $form['#attached']['library'][] = 'core/drupal.form';

    // For non-admin users, populate the form fields using data from the
    // browser.
    if (!$admin) {
      $form['#attributes']['data-user-info-from-browser'] = TRUE;
    }

    // Because the user status has security implications, users are blocked by
    // default when created programmatically and need to be actively activated
    // if needed. When administrators create users from the user interface,
    // however, we assume that they should be created as activated by default.
    if ($admin) {
      $account->activate();
    }

    // Start with the default user account fields.
    $form = parent::form($form, $form_state, $account);

    // Check for new account.
    $register = $account->isAnonymous();

    if ($this->currentUser()->isAnonymous()) {

      //$form['account']['mail']['#title'] = FALSE;

      // Only show name field on registration form or user can change own username.
      $form['account']['name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('USER NAME'),
        '#maxlength' => UserInterface::USERNAME_MAX_LENGTH,
        // '#description' => $this->t("Several special characters are allowed, including space, period (.), hyphen (-), apostrophe ('), underscore (_), and the @ sign."),
        '#required' => TRUE,
        '#attributes' => [
          'class' => ['username','form-control'],
          'autocorrect' => 'off',
          'autocapitalize' => 'off',
          'spellcheck' => 'false',
          'placeholder' => 'User Name',
        ],
        '#default_value' => (!$register ? $account->getAccountName() : ''),
        '#access' => $account->name->access('edit'),
      ];

      $form['account']['mail'] = [
        '#type' => 'email',
        '#title' => $this->t('EMAIL ADDRESS'),
        // '#description' => $this->t('A valid email address. All emails from the system will be sent to this address. The email address is not made public and will only be used if you wish to receive a new password or wish to receive certain news or notifications by email.'),
        '#attributes' => [
          'placeholder' => 'Email',
          'class' => ['form-control'],
        ],
        '#required' => !(!$account->getEmail() && $user->hasPermission('administer users')),
        '#default_value' => (!$register ? $account->getEmail() : ''),
      ];

      $form['account']['pass'] = [
        '#type' => 'password_confirm',
        '#size' => 25,
        // '#description' => $this->t('To change the current user password, enter the new password in both fields.'),
      ];



      $form['user_picture'] = FALSE;

      $form['contact'] = FALSE;

      $form['timezone'] = FALSE;

    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);
    $element['submit']['#value'] = $this->t('Create new account');
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $admin = $form_state->getValue('administer_users');

    if (!\Drupal::config('user.settings')->get('verify_mail') || $admin) {
      $pass = $form_state->getValue('pass');
    }
    else {
      $pass = user_password();
    }

    // Remove unneeded values.
    $form_state->cleanValues();

    $form_state->setValue('pass', $pass);
    $form_state->setValue('init', $form_state->getValue('mail'));

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $account = $this->entity;
    $pass = $account->getPassword();
    $admin = $form_state->getValue('administer_users');
    $notify = !$form_state->isValueEmpty('notify');

    // Save has no return value so this cannot be tested.
    // Assume save has gone through correctly.
    $account->save();

    $form_state->set('user', $account);
    $form_state->setValue('uid', $account->id());

    $this->logger('user')->notice('New user: %name %email.', ['%name' => $form_state->getValue('name'), '%email' => '<' . $form_state->getValue('mail') . '>', 'type' => $account->toLink($this->t('Edit'), 'edit-form')->toString()]);

    // Add plain text password into user account to generate mail tokens.
    $account->password = $pass;

    // New administrative account without notification.
    if ($admin && !$notify) {
      $this->messenger()->addStatus($this->t('Created a new user account for <a href=":url">%name</a>. No email has been sent.', [':url' => $account->toUrl()->toString(), '%name' => $account->getAccountName()]));
    }
    // No email verification required; log in user immediately.
    elseif (!$admin && !\Drupal::config('user.settings')->get('verify_mail') && $account->isActive()) {
      _user_mail_notify('register_no_approval_required', $account);
      user_login_finalize($account);
      $this->messenger()->addStatus($this->t('Registration successful. You are now logged in.'));
      $form_state->setRedirect('<front>');
    }
    // No administrator approval required.
    elseif ($account->isActive() || $notify) {
      if (!$account->getEmail() && $notify) {
        $this->messenger()->addStatus($this->t('The new user <a href=":url">%name</a> was created without an email address, so no welcome message was sent.', [':url' => $account->toUrl()->toString(), '%name' => $account->getAccountName()]));
      }
      else {
        $op = $notify ? 'register_admin_created' : 'register_no_approval_required';
        if (_user_mail_notify($op, $account)) {
          if ($notify) {
            $this->messenger()->addStatus($this->t('A welcome message with further instructions has been emailed to the new user <a href=":url">%name</a>.', [':url' => $account->toUrl()->toString(), '%name' => $account->getAccountName()]));
          }
          else {
            $this->messenger()->addStatus($this->t('A welcome message with further instructions has been sent to your email address.'));
            $form_state->setRedirect('<front>');
          }
        }
      }
    }
    // Administrator approval required.
    else {
      _user_mail_notify('register_pending_approval', $account);
      $this->messenger()->addStatus($this->t('Thank you for applying for an account. Your account is currently pending approval by the site administrator.<br />In the meantime, a welcome message with further instructions has been sent to your email address.'));
      $form_state->setRedirect('<front>');
    }
  }

}
