<?php

/**
 * @file
 * Contains \Drupal\srt_theme_user_login_register\Form\NewUserRegisterForm.
 */

namespace Drupal\srt_theme_user_login_register\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityConstraintViolationListInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\UserInterface;
use Drupal\user\RegisterForm;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ChangedCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormBase;
use Drupal\srt_theme_user_login_register\Controller\CustomUserLoginRegister;

/**
 * Provides a user register form.
 */

class NewUserRegisterFormBackupWithAjaxChangeOrAddField extends RegisterForm {

  //public function buildForm(array $form, FormStateInterface $form_state) {
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

    if($this->currentUser()->isAnonymous()){



      //$form['#foo'] = array();
      // $form['#foo']['var1']='var1';

      //$form['#myvars']['var1'] = 'xyz';

      // Only show name field on registration form or user can change own username.
      // $form['account']['name'] = [
      //   '#type' => 'textfield',
      //   // '#title' => $this->t('Username'),
      //   '#title' => $this->t('Full Name'),
      //   '#maxlength' => UserInterface::USERNAME_MAX_LENGTH,
      //   // '#description' => $this->t("Several special characters are allowed, including space, period (.), hyphen (-), apostrophe ('), underscore (_), and the @ sign."),
      //   '#required' => TRUE,
      //   '#attributes' => [
      //     'class' => ['username'],
      //     'autocorrect' => 'off',
      //     'autocapitalize' => 'off',
      //     'spellcheck' => 'false',
      //   ],
      //   '#default_value' => (!$register ? $account->getAccountName() : ''),
      //   '#access' => $account->name->access('edit'),
      //   '#prefix' => '<div class="form-gp">',
      //   '#suffix' => '</div>',
      //   '#field_suffix' => '<i class="ti-user"></i>',
      // ];


      // The mail field is NOT required if account originally had no mail set
      // and the user performing the edit has 'administer users' permission.
      // This allows users without email address to be edited and deleted.
      // Also see \Drupal\user\Plugin\Validation\Constraint\UserMailRequired.
      $form['account']['mail'] = [
        '#type' => 'email',
        '#title' => $this->t('Email address'),
        // '#description' => $this->t('A valid email address. All emails from the system will be sent to this address. The email address is not made public and will only be used if you wish to receive a new password or wish to receive certain news or notifications by email.'),
        '#required' => !(!$account->getEmail() && $user->hasPermission('administer users')),
        '#default_value' => (!$register ? $account->getEmail() : ''),
        '#prefix' => '<div class="button-aligment" style="display:inline;"><div class="form-gp is-valid">',
        // '#suffix' => '</div><span class="email-validation"></span>',
        '#suffix' => '</div><div class="email-validation" style="margin-top: -18px;"></div>',
        '#field_suffix' => '<i class="ti-email"></i></div>',
        // '#field_suffix' => '<i class="ti-email email-icon-aligment"></i></div>',
        // '#attributes' => [
        //   'style' => 'max-width: 248px;',
        // ],
      ];


      $form['account']['send_verification_code'] = [
        // '#type' => 'button',
        '#type' => 'submit',
        '#limit_validation_errors' => [],
        '#value' => $this->t('Send Code'),
        '#id' => 'send-verification-code',
        '#attributes' => [
          // 'onclick' => 'return false;',
          // 'onclick' => '::validateForm',
          // 'class' => ['btn','btn-primary'],
          'class' => ['btn','btn-primary','btn-xs','mb-3'],
          // 'style' => ['margin-left: 270px;'],
          // 'style' => ['margin-left: 265px;','margin-top: -92px;'],
        ],
        // '#attached' => array(
        //   'library' => array(
        //     'cool_admin_user_login_register/foo',
        //   ),
        // ),
        // '#submit' => ['::addOne'],
        // '#submit' => ['::editButton'],
        '#ajax' => [
          // 'callback' => [$this, 'checkEmailValidation'],
          'callback' => '::checkEmailValidation',
          'effect' => 'fade',
          'wrapper' => 'email-validation',
          'method' => 'replace',
          'event' => 'click',
          'progress' => [
            // 'type' => 'throbber',
            'message' => NULL,
          ],
        ],
        '#prefix' => '<div class="hide_send_verification form-gp">',
        '#suffix' => '</div>'
        // '#suffix' => '<div class="enter-otp"></div>',
      ];

      $form['container']['output'] = [
        '#type' => 'textfield',
        // '#size' => '60',
        // '#disabled' => TRUE,
        '#value' => 'Hello, Drupal!!1',
        '#attributes' => [
          'id' => ['edit-output'],
        ],
      ];

      $form['account']['button'] = [
        '#type' => 'button',
        '#value' => 'Button',
        '#name' => 'test_button',
        '#ajax' => [
          'callback' => '::myAjaxCallback', // don't forget :: when calling a class method.
          //'callback' => [$this, 'myAjaxCallback'], //alternative notation
          'disable-refocus' => FALSE, // Or TRUE to prevent re-focusing on the triggering element.
          'event' => 'click',
          'wrapper' => 'edit-output', // This element is updated with this AJAX callback.
          'progress' => [
            'type' => 'throbber',
            'message' => $this->t('Verifying entry...'),
          ],
        ],
      ];



      // $form['account']['edit_otp_btn']=[
      //   '#type' => 'markup',
      //   'markup' => '<div class="edit_mail"></div>'
      // ];

      // Gather the number of names in the form already.
      // $num_names1 = $form_state->get('num_names');
      // // We have to ensure that there is at least one name field.
      // if ($num_names1 === NULL) {
      //   $edit_btn = $form_state->set('num_names', 1);
      //   $num_names1 = 1;
      // }

      // $form['account']['edit_otp_wrapper']=[
      //   '#type' => 'fieldset',
      //   '#prefix' => '<div id="names-fieldset-wrapper1">',
      //   '#suffix' => '</div>',
      // ];

    //   $form['account']['send_verification_code']=[
    //     // '#type' => 'button',
    //     '#type' => 'submit',
    //     '#value' => $this->t('Send Code'),
    //    '#id' => 'send-verification-code',
    //    '#attributes' => [
    //      'class' => ['btn','btn-primary','btn-xs','mb-3'],
    //      'style' => ['margin-left: 265px;','margin-top: -92px;'],
    //    ],
    //    '#suffix' => '</div>',
    //    '#ajax' => [
    //     // 'callback' => [$this, 'checkEmailValidation'],
    //     'callback' => '::checkEmailValidation',
    //     'effect' => 'fade',
    //     'wrapper' => 'email-validation',
    //     'method' => 'replace',
    //     'event' => 'click',
    //     'progress' => [
    //       'type' => 'throbber',
    //       'message' => NULL,
    //     ],
    //   ],

    //  ];




      // $form['account']['pass'] = [
      //   '#type' => 'password_confirm',
      //   '#size' => 25,
      //   // '#description' => $this->t('To change the current user password, enter the new password in both fields.'),
      // ];





    //   // Gather the number of names in the form already.
    //     $num_names = $form_state->get('num_names');
    //     // We have to ensure that there is at least one name field.
    //     if ($num_names === NULL) {
    //       $name_field = $form_state->set('num_names', 1);
    //       $num_names = 1;
    //     }

    //     $form['names_fieldset']['name'] = [
    //       '#type' => 'textfield',
    //       '#title' => $this->t('Name'),
    //     ];

    //   $form['names_fieldset'] = [
    //     '#type' => 'fieldset',
    //     '#title' => $this->t('People coming to picnic'),
    //     '#prefix' => '<div id="names-fieldset-wrapper">',
    //     '#suffix' => '</div>',
    //   ];

    //   // $form['names_fieldset']['foo']['#myvars'] = [];

    //   for ($i = 0; $i < $num_names; $i++) {
    //     $form['names_fieldset']['name'][$i] = [
    //       '#type' => 'textfield',
    //       '#title' => $this->t('Name'),
    //     ];

    //     // $form['names_fieldset']['foo']['#myvars'] = $i;

    //   }

    //   $form['names_fieldset']['actions'] = [
    //     '#type' => 'actions',
    //   ];

    //   $form['names_fieldset']['actions']['add_name'] = [
    //     '#type' => 'submit',
    //     '#limit_validation_errors' => [],
    //     '#value' => $this->t('Add one more'),
    //     '#submit' => ['::addOne'],
    //     '#ajax' => [
    //       'callback' => '::addmoreCallback',
    //       'wrapper' => 'names-fieldset-wrapper',
    //     ],
    //   ];

    //   // If there is more than one name, add the remove button.
    // if ($num_names > 1) {
    //   $form['names_fieldset']['actions']['remove_name'] = [
    //     '#type' => 'submit',
    //     '#limit_validation_errors' => [],
    //     '#value' => $this->t('Remove one'),
    //     '#submit' => ['::removeCallback'],
    //     '#ajax' => [
    //       'callback' => '::addmoreCallback',
    //       'wrapper' => 'names-fieldset-wrapper',
    //     ],
    //   ];
    // }
    // $form['actions']['submit'] = [
    //   '#type' => 'submit',
    //   '#value' => $this->t('Submit'),
    // ];

      // $form['user_picture'] = FALSE;

      // $form['contact'] = FALSE;

      // $form['timezone'] = FALSE;



    }

    $form['#attached']['library'][] = 'srt_theme_user_login_register/srt_theme_user_login_register.custom_user_login';




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


// Get the value from example select field and fill
// the textbox with the selected text.
// public function myAjaxCallback(array &$form, FormStateInterface $form_state) {
//   // Prepare our textfield. check if the example select field has a selected option.
//   // if ($selectedValue = $form_state->getValue('example_select')) {
//     if(1==1){
//       // Get the text of the selected option.
//       $selectedText = $form['example_select']['#options'][$selectedValue];
//       $selectedText = 'abcd';
//       // Place the text of the selected option in our textfield.
//       $form['container']['output']['#value'] = $selectedText;
//   }
//   // Return the prepared textfield.
//   return $form['container']['output'];
// }


/**
 * An Ajax callback.
 */
public function myAjaxCallback(array &$form, FormStateInterface $form_state) {
  // Try to get the selected text from the select element on our form.
  $selectedText = 'nothing selected';
  // if ($selectedValue = $form_state->getValue('example_select')) {
    if(1==1){
    // Get the text of the selected option.
    $selectedText = $form['example_select']['#options'][$selectedValue];
    $selectedText = 'drupal example testing';
  }

  // Create a new textfield element containing the selected text.
  // We're replacing the original textfield using an AJAX replace command which
  // expects HTML markup. So we need to render the textfield render array here.
  $elem = [
    '#type' => 'button',
    // '#size' => '60',
    // '#disabled' => TRUE,
    '#value' => "I am a new textfield: $selectedText!",
    '#attributes' => [
      'id' => ['edit-output'],
    ],
  ];

  //Note: As of writing, the Drupal 8 Ajax API document suggests using 'drupal_render()' to render the HTML passed to ReplaceCommand. But here we use Drupal's renderer service instead.
  $renderer = \Drupal::service('renderer');
  $renderedField = $renderer->render($elem);

  // Attach the javascript library for the dialog box command
  // in the same way you would attach your custom JS scripts.
  $dialogText['#attached']['library'][] = 'core/drupal.dialog.ajax';
  // Prepare the text for our dialogbox.
  $dialogText['#markup'] = "You selected: $selectedText";

  // If we want to execute AJAX commands our callback needs to return
  // an AjaxResponse object. let's create it and add our commands.
  $response = new AjaxResponse();
  // Issue a command that replaces the element #edit-output
  // with the rendered markup of the field created above.
  $response->addCommand(new ReplaceCommand('#edit-output', $renderedField));
  // Show the dialog box.
  $response->addCommand(new OpenModalDialogCommand('My title', $dialogText, ['width' => '300']));

  // Finally return the AjaxResponse object.
  return $response;
}

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  // public function addmoreCallback(array &$form, FormStateInterface $form_state) {
  //   return $form['names_fieldset'];
  // }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  // public function addOne(array &$form, FormStateInterface $form_state) {
  //   $name_field = $form_state->get('num_names');
  //   $add_button = $name_field + 1;
  //   $form_state->set('num_names', $add_button);
  //   // Since our buildForm() method relies on the value of 'num_names' to
  //   // generate 'name' form elements, we have to tell the form to rebuild. If we
  //   // don't do this, the form builder will not call buildForm().
  //   $form_state->setRebuild();
  // }

  /**
   * Submit handler for the "remove one" button.
   *
   * Decrements the max counter and causes a form rebuild.
   */
  // public function removeCallback(array &$form, FormStateInterface $form_state) {
  //   $name_field = $form_state->get('num_names');
  //   if ($name_field > 1) {
  //     $remove_button = $name_field - 1;
  //     $form_state->set('num_names', $remove_button);
  //   }
  //   // Since our buildForm() method relies on the value of 'num_names' to
  //   // generate 'name' form elements, we have to tell the form to rebuild. If we
  //   // don't do this, the form builder will not call buildForm().
  //   $form_state->setRebuild();
  // }

  // public function editButton(array &$form, FormStateInterface $form_state) {
  //   $name_field1 = $form_state->get('num_names');
  //   //$add_button = $name_field + 1;
  //   $form_state->set('num_names', $name_field1);
  //   // Since our buildForm() method relies on the value of 'num_names' to
  //   // generate 'name' form elements, we have to tell the form to rebuild. If we
  //   // don't do this, the form builder will not call buildForm().
  //   $form_state->setRebuild();
  // }

  public function checkEmailValidation(array $form, FormStateInterface $form_state){

    $ajax_response=new AjaxResponse();
    $email_value=$form_state->getValue('mail');
    // if (empty($email_value) || !\Drupal::service('email.validator')->isValid($email_value)) {
    //   //$form_state->setError($form['email_address'], $this->t('Please enter a valid email address.'));
    //   $text='<div class="invalid-feedback">Invalid Email Address.</div>';
    //   $ajax_response->addCommand(new HtmlCommand('.email-validation',$text));
    //   // return $ajax_response;
    // }

    if (empty($email_value)) {
      //$form_state->setError($form['email_address'], $this->t('Please enter a valid email address.'));
      $text='<div class="invalid-feedback" style="margin-top: -18px;">Please Enter Email Address.</div>';
      $ajax_response->addCommand(new HtmlCommand('.email-validation',$text));
      // return $ajax_response;
    }

    elseif(!\Drupal::service('email.validator')->isValid($email_value)) {
      $text='<div class="invalid-feedback" style="margin-top: -18px;">Invalid Email Address.</div>';
      $ajax_response->addCommand(new HtmlCommand('.email-validation',$text));
    }

    else {

      $text=CustomUserLoginRegister::sendVerificationCode($email_value);
      // $text=gettype($text);
      $status=$text["status"];
      if($status==1){
        $verification_message='<div class="valid-feedback" style="margin-top: -18px;">Verification code has been sent to email.</div>';
        $ajax_response->addCommand(new HtmlCommand('.email-validation',$verification_message));


      }
      else{
        // print_r("else");exit;
      }

    }

    return $ajax_response;

   }




  // function my_callback_function(array $form,FormStateInterface $form_state) {
  //   $arguments = $form['#foo'];
  //   // $form['#myvars']['var1'] = 'xyz';
  //   $form['#foo']['var1']='var1';
  //   return $form;
  // }

  function my_callback_function(array &$form,FormStateInterface &$form_state) {
    $form['#foo']['var1']='var1';
    $form_state['rebuild'] = TRUE;

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
      $pass = $form_state->getValue('pass');
    }

    $name=$form['account']['name']['#value'];

    // Remove unneeded values.
    $form_state->cleanValues();
    $form_state->setValue('name', $name);
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




    $name=$form['account']['name']['#value'];
    $query="SELECT users_field_data.uid AS uid
  FROM users_field_data users_field_data
 WHERE (users_field_data.name = :name)";

$data = db_query($query, array(":name" => $name))->fetchAssoc();

$user_id=$data['uid'];
$organization_uuid=md5($user_id);
$query1="INSERT INTO organization_details
(organization_uuid, user_id)
VALUES (:organization_uuid, :user_id);";
         $data1= db_query($query1,array(":organization_uuid"=>$organization_uuid,":user_id"=>$user_id));

  $nid = db_insert('users_extended') // Table name no longer needs {}

    ->fields(array(
      'uid' => $data['uid'],
      'first_name' => $name,
      'last_name' => '',
      'role' => 'business',
      'otp' => '',
      'country_code'=>'abcd'
    ))
    ->execute();


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
      if ($this->currentUser()->isAnonymous()) {
        drupal_set_message($this->t('Registration successful.'));

        $form_state->setRedirect('<front>');
      }
      else{
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
    }
    // Administrator approval required.
    else {
      _user_mail_notify('register_pending_approval', $account);
      $this->messenger()->addStatus($this->t('Thank you for applying for an account. Your account is currently pending approval by the site administrator.<br />In the meantime, a welcome message with further instructions has been sent to your email address.'));
      $form_state->setRedirect('<front>');
    }
  }

}
