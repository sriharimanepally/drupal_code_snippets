<?php

/**
 * @file
 * Contains \Drupal\custom_user_login_register\Form\NewUserLoginForm.
 */

namespace Drupal\custom_user_login_register\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\user\UserInterface;
use Drupal\user\Form\UserLoginForm;

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
        // '#required' => TRUE,
        '#attributes' => [
            'autocorrect' => 'none',
            'autocapitalize' => 'none',
            'spellcheck' => 'false',
            'autofocus' => 'autofocus',
            'placeholder' => 'Username',
            'class' => ['form-control','form-control-lg'],
            'aria-label' => 'Password',
            'aria-describedby' => 'basic-addon1',
            'required' => '',
        ],
        '#field_prefix' => '<div class="input-group mb-3">
        <div class="input-group-prepend">
            <span class="input-group-text bg-success text-white" id="basic-addon1"><i class="ti-user"></i></span>
        </div>',
        '#field_suffix' => '</div>',
        // '#prefix' => '<div class="abcd">',
        // '#suffix' => '</div>',
        ];

        $form['pass'] = [
        '#type' => 'password',
        // '#title' => $this->t('Password'),
        '#size' => 60,
        // '#description' => $this->t('Enter the password that accompanies your username.'),
        // '#required' => TRUE,
        '#attributes' => [
            'placeholder' => 'Password',
            'class' => ['form-control','form-control-lg'],
            'aria-label' => 'Password',
            'aria-describedby' => 'basic-addon1',
            'required' => '',
        ],
        '#field_prefix' => '<div class="input-group mb-3">
        <div class="input-group-prepend">
            <span class="input-group-text bg-warning text-white" id="basic-addon2"><i class="ti-pencil"></i></span>
        </div>',
        '#field_suffix' => '</div>'
        ];

        $form['#attached']['library'][] = 'custom_user_login_register/custom_user_login_register.custom_user_login';

        $form['actions'] = ['#type' => 'actions'];
        $form['actions']['submit'] = ['#type' => 'submit', '#attributes' => ['class'=>['btn','btn-success','float-right']] , '#value' => $this->t('Log in')];

        $form['#validate'][] = '::validateName';
        $form['#validate'][] = '::validateAuthentication';
        $form['#validate'][] = '::validateFinal';

        $this->renderer->addCacheableDependency($form, $config);

        return $form;




    }

}