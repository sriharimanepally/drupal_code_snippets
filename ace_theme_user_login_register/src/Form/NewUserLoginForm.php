<?php

/**
 * @file
 * Contains \Drupal\ace_theme_user_login_register\Form\NewUserLoginForm.
 */

namespace Drupal\ace_theme_user_login_register\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\user\UserInterface;
use Drupal\user\Form\UserLoginForm;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Render\MarkupInterface;
use Drupal\Component\Render\HtmlEscapedText;

/**
 * Provides a user login form.
 */
class NewUserLoginForm extends UserLoginForm {

    public function buildForm(array $form,FormStateInterface $form_state)
    {

        $form = parent::buildForm($form, $form_state);

        $config = $this->config('system.site');

        // Display login form:
    $form['name'] = [
      '#type' => 'textfield',
      // '#title' => $this->t('Username'),
      '#size' => 60,
      '#maxlength' => UserInterface::USERNAME_MAX_LENGTH,
      // '#description' => $this->t('Enter your @s username.', ['@s' => $config->get('name')]),
      '#required' => TRUE,
      '#attributes' => [
        'autocorrect' => 'none',
        'autocapitalize' => 'none',
        'spellcheck' => 'false',
        'autofocus' => 'autofocus',
        'placeholder' => 'Username',
        'class' => ['form-control'],
      ],
    ];

    $form['pass'] = [
      '#type' => 'password',
      // '#title' => $this->t('Password'),
      '#size' => 60,
      // '#description' => $this->t('Enter the password that accompanies your username.'),
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => 'Password',
        'class' => ['form-control'],
      ],
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = ['#type' => 'submit', '#attributes' => ['class' => ['btn btn-primary button btn-default btn']] , '#value' => $this->t('Log in')];

    $form['#validate'][] = '::validateName';
    $form['#validate'][] = '::validateAuthentication';
    $form['#validate'][] = '::validateFinal';

    $this->renderer->addCacheableDependency($form, $config);

    return $form;




    }

	/**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $account = $this->userStorage->load($form_state->get('uid'));

    // A destination was set, probably on an exception controller,
    if (!$this->getRequest()->request->has('destination')) {
      $form_state->setRedirect(
        'entity.user.canonical',
        ['user' => $account->id()]
      );
    }
    else {
      $this->getRequest()->query->set('destination', $this->getRequest()->request->get('destination'));
    }

    user_login_finalize($account);
  }

  /**
   * Sets an error if supplied username has been blocked.
   */
  public function validateName(array &$form, FormStateInterface $form_state) {
    if (!$form_state->isValueEmpty('name') && user_is_blocked($form_state->getValue('name'))) {
      // Blocked in user administration.
      $form_state->setErrorByName('name', $this->t('The username %name has not been activated or is blocked.', ['%name' => $form_state->getValue('name')]));
    }
  }

  /**
   * Checks supplied username/password against local users table.
   *
   * If successful, $form_state->get('uid') is set to the matching user ID.
   */
  public function validateAuthentication(array &$form, FormStateInterface $form_state) {
    $password = trim($form_state->getValue('pass'));
    $flood_config = $this->config('user.flood');
    if (!$form_state->isValueEmpty('name') && strlen($password) > 0) {
      // Do not allow any login from the current user's IP if the limit has been
      // reached. Default is 50 failed attempts allowed in one hour. This is
      // independent of the per-user limit to catch attempts from one IP to log
      // in to many different user accounts.  We have a reasonably high limit
      // since there may be only one apparent IP for all users at an institution.
      if (!$this->flood->isAllowed('user.failed_login_ip', $flood_config->get('ip_limit'), $flood_config->get('ip_window'))) {
        $form_state->set('flood_control_triggered', 'ip');
        return;
      }
      $accounts = $this->userStorage->loadByProperties(['name' => $form_state->getValue('name'), 'status' => 1]);
      $account = reset($accounts);
      if ($account) {
        if ($flood_config->get('uid_only')) {
          // Register flood events based on the uid only, so they apply for any
          // IP address. This is the most secure option.
          $identifier = $account->id();
        }
        else {
          // The default identifier is a combination of uid and IP address. This
          // is less secure but more resistant to denial-of-service attacks that
          // could lock out all users with public user names.
          $identifier = $account->id() . '-' . $this->getRequest()->getClientIP();
        }
        $form_state->set('flood_control_user_identifier', $identifier);

        // Don't allow login if the limit for this user has been reached.
        // Default is to allow 5 failed attempts every 6 hours.
        if (!$this->flood->isAllowed('user.failed_login_user', $flood_config->get('user_limit'), $flood_config->get('user_window'), $identifier)) {
          $form_state->set('flood_control_triggered', 'user');
          return;
        }
      }
      // We are not limited by flood control, so try to authenticate.
      // Store $uid in form state as a flag for self::validateFinal().
      $uid = $this->userAuth->authenticate($form_state->getValue('name'), $password);
      $form_state->set('uid', $uid);
    }
  }

  /**
   * Checks if user was not authenticated, or if too many logins were attempted.
   *
   * This validation function should always be the last one.
   */
  public function validateFinal(array &$form, FormStateInterface $form_state) {
    $flood_config = $this->config('user.flood');
    if (!$form_state->get('uid')) {
      // Always register an IP-based failed login event.
      $this->flood->register('user.failed_login_ip', $flood_config->get('ip_window'));
      // Register a per-user failed login event.
      if ($flood_control_user_identifier = $form_state->get('flood_control_user_identifier')) {
        $this->flood->register('user.failed_login_user', $flood_config->get('user_window'), $flood_control_user_identifier);
      }

      if ($flood_control_triggered = $form_state->get('flood_control_triggered')) {
        if ($flood_control_triggered == 'user') {
          $form_state->setErrorByName('name', $this->formatPlural($flood_config->get('user_limit'), 'There has been more than one failed login attempt for this account. It is temporarily blocked. Try again later or <a href=":url">request a new password</a>.', 'There have been more than @count failed login attempts for this account. It is temporarily blocked. Try again later or <a href=":url">request a new password</a>.', [':url' => $this->url('user.pass')]));
        }
        else {
          // We did not find a uid, so the limit is IP-based.
          $form_state->setErrorByName('name', $this->t('Too many failed login attempts from your IP address. This IP address is temporarily blocked. Try again later or <a href=":url">request a new password</a>.', [':url' => $this->url('user.pass')]));
        }
      }
      else {
        // Use $form_state->getUserInput() in the error message to guarantee
        // that we send exactly what the user typed in. The value from
        // $form_state->getValue() may have been modified by validation
        // handlers that ran earlier than this one.
        $user_input = $form_state->getUserInput();
        $query = isset($user_input['name']) ? ['name' => $user_input['name']] : [];
        $form_state->setErrorByName('name', $this->t('Unrecognized username or password. <a href=":password">Forgot your password?</a>', [':password' => $this->url('user.pass', [], ['query' => $query])]));
        $accounts = $this->userStorage->loadByProperties(['name' => $form_state->getValue('name')]);
        if (!empty($accounts)) {
          $this->logger('user')->notice('Login attempt failed for %user.', ['%user' => $form_state->getValue('name')]);
        }
        else {
          // If the username entered is not a valid user,
          // only store the IP address.
          $this->logger('user')->notice('Login attempt failed from %ip.', ['%ip' => $this->getRequest()->getClientIp()]);
        }
      }
    }
    elseif ($flood_control_user_identifier = $form_state->get('flood_control_user_identifier')) {
      // Clear past failures for this user so as not to block a user who might
      // log in and out more than once in an hour.
      $this->flood->clear('user.failed_login_user', $flood_control_user_identifier);
    }
  }

}
