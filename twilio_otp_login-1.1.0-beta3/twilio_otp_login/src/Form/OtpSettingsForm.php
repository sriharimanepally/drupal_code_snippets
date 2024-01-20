<?php

namespace Drupal\twilio_otp_login\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class OtpSettingsForm extends ConfigFormBase {

  /** 
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'twilio_otp_login.settings';

  /** 
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'twilio_otp_login_admin_settings';
  }

  /** 
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /** 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['account_sid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Twillio Account SID'),
      '#default_value' => $config->get('account_sid'),
      '#required' => true
    ];  

    $form['auth_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Auth token'),
      '#default_value' => $config->get('auth_token'),
      '#required' => true
    ]; 
    
    $form['from_no'] = [
      '#type' => 'textfield',
      '#title' => $this->t('From Number'),
      '#default_value' => $config->get('from_no'),
      '#required' => true
    ]; 

    return parent::buildForm($form, $form_state);
  }

  /** 
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->config(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('account_sid', $form_state->getValue('account_sid'))
      // You can set multiple configurations at once by making
      // multiple calls to set().
      ->set('auth_token', $form_state->getValue('auth_token'))
      ->set('from_no', $form_state->getValue('from_no'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}