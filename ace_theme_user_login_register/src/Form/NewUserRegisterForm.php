<?php

/**
 * @file
 * Contains \Drupal\ace_theme_user_login_register\Form\NewUserRegisterForm.
 */

namespace Drupal\ace_theme_user_login_register\Form;

// use Drupal\Core\Ajax\AjaxResponse;
// use Drupal\Core\Ajax\ChangedCommand;
// use Drupal\Core\Ajax\CssCommand;
// use Drupal\Core\Ajax\HtmlCommand;
// use Drupal\Core\Ajax\InvokeCommand;

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


    $config = \Drupal::config('user.settings');
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


      // Only show name field on registration form or user can change own username.
      $form['account']['name'] = [
        '#type' => 'tel',
        '#id' => 'edit-name',
        '#required' => TRUE,
        '#attributes' => [
          'autocomplete' => 'off',
          'autocapitalize' => 'off',
          'spellcheck' => 'false',
          'placeholder' => 'Mobile Number',
          'style' => 'max-width:196px',
          'class' => ['form-control'],
        ],
        // '#prefix' => '<div id="edit-test1-wrapper" style="display:inline">',
        '#default_value' => '',
        '#access' => $account->name->access('edit'),
      ];

      $form['account']['send_otp'] = [
        '#type' => 'button',
         '#id' => 'edit-send-otp',
        '#value' => $this->t('Send OTP'),
        '#limit_validation_errors' => [],
      '#attributes' => [
           'class' => ['btn-primary'],

      ],
           '#executes_submit_callback' => FALSE,
      ];

      $form['account']['edit'] = [
        '#type' => 'button',
        '#id' => 'edit-otp',
        '#value' => $this->t('Edit'),
        '#limit_validation_errors' => [],
        // '#suffix' => '</div>',
      '#attributes' => [
    'class' => ['btn-primary','hide'],
        //    'style' => 'display:none',
      ],
      '#executes_submit_callback' => FALSE,
      ];

      $form['account']['enter_otp'] = [
        '#type' => 'password',
          '#id' => 'edit-enter-otp',
        '#maxlength' => 4,
        '#attributes' => [
          'class' => ['username','hide','form-control'],
          'autocomplete' => 'off',
          'autocapitalize' => 'off',
          'spellcheck' => 'false',
          'placeholder' => 'Enter OTP',
            'style' => 'display:none',
      ],
        // '#prefix' => '<div id="edit-test2-wrapper">',
      ];

      $form['account']['verify_otp'] = [
        '#type' => 'button',
        '#id' => 'edit-verify',
        '#value' => $this->t('Verify OTP'),
        '#limit_validation_errors' => [],
        // '#suffix' => '</div>',
          '#attributes' => [
      'class' => ['btn-primary','hide'],
    //        'style' => 'display:none',
          ],
          '#executes_submit_callback' => FALSE,
      ];

      $form['account']['name1'] = [
        '#type' => 'textfield',
        '#id' => 'edit-name1',
        '#title' => $this->t(''),
        '#maxlength' => USERNAME_MAX_LENGTH,
       '#attributes' => [
      'class' => ['hide','form-control'],
          'autocorrect' => 'off',
          'autocapitalize' => 'off',
          'spellcheck' => 'false',
          'placeholder' => 'Full Name',
          'style' => 'display:none',
       ],
      ];

      $form['account']['mail'] = [
        '#type' => 'email',
        '#attributes' => [
          'placeholder' => 'Email (Optional)',
      'class' => ['hide','form-control'],
           'style' => 'display:none',
        ],
       // '#description' => $this->t('A valid email address. All emails from the system will be sent to this address. The email address is not made public and will only be used if you wish to receive a new password or wish to receive certain news or notifications by email.'),

      ];

      $form['account']['role_select'] = [
        '#type' => 'select',
       // '#title' => $this->t(''),
        '#options' => [
      // 'business' => t('Business'),
      'event_owner' => t('Event Owner'),
      'bride' => t('Bride'),
      'groom' => t('Groom'),
           //'patient' => t('I am Patient'),
           //'physician' => t('I am Physician'),
           //'patient_family_member' => t('I am Patient Family Member'),
          ],
        '#attributes' => [
      'class' => ['hide','form-control'],
        //    'style' => 'display:none',
        ],
      ];

      $form['account']['pass'] = [
        '#type' => 'password_confirm',
        '#size' => 50,
        '#id' => 'pass',
		 '#prefix' => '<div id="pass_field" class="hide">',
        '#suffix' => '</div>',
        // '#description' => $this->t('To change the current user password, enter the new password in both fields.'),
        '#attributes' => [
		'class' => ['hide','form-control'],
          'style' => 'display:none',
        ],
    ];

    $status = $config->get('register') == UserInterface::REGISTER_VISITORS ? 1 : 0;

    $form['#attached']['library'][] = 'ace_theme_user_login_register/ace_theme_user_login_register.telinputflag';
    $form['#attached']['library'][] = 'custom_user_profile/custom_user_profile.custom_script_new_account';








    }

    $form['#attached']['library'][] = 'ace_theme_user_login_register/ace_theme_user_login_register.custom_user_login';


    // $form['#validate'][] = 'Drupal\ace_theme_user_login_register\Controller\CustomUserLoginRegister::example_form_validate';






    return $form;
  }









  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);
    $element['submit']['#value'] = $this->t('Create new account');
    if ($this->currentUser()->isAnonymous()) {
      $element['submit']['#prefix'] = '<div id="pass_field" class="hide">';
      $element['submit']['#suffix'] = '</div>';
    }
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
      $pass = $form_state->getValue('pass');
    }

    $name=$form['account']['name']['#value'];
    $mobile_no=$form['account']['name']['#value'];
    $mobile_no=str_replace(" ","",$mobile_no);
    $mobile_no=str_replace("-","",$mobile_no);

    $phonelen=strlen($mobile_no);
    $substr=$phonelen-10;
    $country = substr($mobile_no,0,2);
    if($country=='9')
    {
    $countrycode= substr($mobile_no,0,2);
    }
    else
    {
    $countrycode =substr($mobile_no,0,2);
    }

    $phone=substr($mobile_no, -10);

    // Remove unneeded values.
    $form_state->cleanValues();
    $form_state->setValue('name', $mobile_no);
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

    //$account->addRole('business');
	  $role=$form['account']['role_select']['#value'];
	  $account->addRole($role);

    // Save has no return value so this cannot be tested.
    // Assume save has gone through correctly.
    $account->save();

    $name=$form['account']['name']['#value'];
    $full_name=$form['account']['name1']['#value'];
    $roles=$form['account']['role_select']['#value'];
    $mobile_no=$form['account']['name']['#value'];
    $mobile_no=str_replace(" ","",$mobile_no);
		$mobile_no=str_replace("-","",$mobile_no);
    $phonelen=strlen($mobile_no);
    $substr=$phonelen-10;
    $country = substr($mobile_no,0,2);
    if($country=='9')
    {
    $countrycode= substr($mobile_no,0,2);
    }
    else
    {
    $countrycode =substr($mobile_no,0,2);
    }
    $phone=substr($mobile_no, -10);





    $query="SELECT users_field_data.uid AS uid
  FROM users_field_data users_field_data
 WHERE (users_field_data.name = :name)";

$data = db_query($query, array(":name" => $mobile_no))->fetchAssoc();

$user_id=$data['uid'];
$organization_uuid=md5($user_id);
$query1="INSERT INTO organization_details
(organization_uuid, user_id)
VALUES (:organization_uuid, :user_id);";
         $data1= db_query($query1,array(":organization_uuid"=>$organization_uuid,":user_id"=>$user_id));

/* $json['user_profile_data']['userdetails']['fullname']=$full_name;
 $json['user_profile_data']['userdetails']['mail']=$form_state->getValue('mail');
 $json['user_profile_data']['userdetails']['roles']=$roles;
 $json['user_profile_data']['userdetails']['mobnum']=$mobile_no; */

 $json['business_profile']['businessdetails']['businessname']=$full_name;
 $json['business_profile']['businessdetails']['mail']=$form_state->getValue('mail');
 $json['business_profile']['businessdetails']['roles']=$roles;
 $json['business_profile']['businessdetails']['mobnum']=$mobile_no;

 $json['organization_uuid']=$organization_uuid;

 $fields['json']=$json;

 $account_temp = \Drupal::currentUser();
 $current_uid = $account_temp->id();

 //$fields['uid'] =$data['uid'];

 $fields['uid'] = md5($data['uid']);

 $id = $data['uid'];

$data['name']=$id;
$data['password']=$id;
$data['roles']=[];
$data['type']='user';

//  foreach ($fields as $key => $value) {
//   $fields_string .= $key . '=' . $value . '&';
// }
// rtrim($fields_string, '&');
global $config;

$json_data = json_encode($fields);

$data_encode=json_encode($data);

$ch1 = curl_init();

	$url='http://localhost:5984/_users/org.couchdb.user:'.$id;

	//curl_setopt($ch2, CURLOPT_URL, 'http://admin:Eapp4U123@localhost:5984/_users/org.couchdb.user:'.$username);
	curl_setopt($ch1, CURLOPT_URL, $url);
	$test=curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, 'PUT'); /* or PUT */
	curl_setopt($ch1, CURLOPT_POSTFIELDS, $data_encode);
	//curl_setopt($ch2, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
	//curl_setopt($ch2, CURLOPT_DNS_CACHE_TIMEOUT, 2);
	curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch1, CURLOPT_HTTPHEADER, array(
		'Content-type: application/json',
		'Accept: */*'
	));

	curl_setopt($ch1, CURLOPT_USERPWD, 'admin:Eapp4U123');

	$response = curl_exec($ch1);
  curl_close($ch1);

  $response=json_decode($response);
  $response=$response->ok;

  sleep(2);

  if($response==1){
    //print_r("if");exit;

		$document_id=md5($id);

		$database_name='userdb-'.bin2hex($id);

		$data_abcd['uid']=$id;

		$data_abcd=json_encode($data_abcd);



		//$url1='http://127.0.0.1:5984'.'/'.$database_name.'/'.$document_id;

		$url2='http://'.$id.':'.$id.'@'.'localhost:5984/'.$database_name.'/'.$document_id;//echo $url2;exit;


		$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url2);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); /* or PUT */
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);//echo $t1;exit;
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-type: application/json',
		'Accept: */*'
	));

	//curl_setopt($ch, CURLOPT_USERPWD, 'admin:Eapp4U123');

	//$username_password=$id.':'.$id;
	//curl_setopt($ch, CURLOPT_USERPWD, $username_password);

	$response1 = curl_exec($ch);

	curl_close($ch);

	//return $response1;
	// echo json_encode($response1);



  }
  else{
    //print_r("else");exit;
		$document_id=md5($id);

		$database_name='userdb-'.bin2hex($id);

		$data_abcd['uid']=$id;

		$data_abcd=json_encode($data_abcd);

		//$data_abcd['name']='abcd';

		//$data_abcd=json_encode($data_abcd);



		//$url1='http://127.0.0.1:5984'.'/'.$database_name.'/'.$document_id;

		$url2='http://'.$id.':'.$id.'@'.'localhost:5984/'.$database_name.'/'.$document_id;//echo $url2;exit;


		$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url2);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); /* or PUT */
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_abcd);//echo $t1;exit;
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-type: application/json',
		'Accept: */*'
	));

	//curl_setopt($ch, CURLOPT_USERPWD, 'admin:Eapp4U123');

	//$username_password=$id.':'.$id;
	//curl_setopt($ch, CURLOPT_USERPWD, $username_password);

	$response1 = curl_exec($ch);

	curl_close($ch);

	//return $response1;
	// echo json_encode($response1);
  }


  $nid = db_insert('users_extended') // Table name no longer needs {}

    ->fields(array(
      'uid' => $data['uid'],
      'first_name' => $full_name,
      'last_name' => '',
      'role' => $roles,
      'otp' => '',
      'country_code'=>$countrycode
    ))
    ->execute();


    $form_state->set('user', $account);
    $form_state->setValue('uid', $account->id());

    $this->logger('user')->notice('New user: %name %email.', ['%name' => $form_state->getValue('name'), '%email' => '<' . $form_state->getValue('mail') . '>', 'type' => $account->toLink($this->t('Edit'), 'edit-form')->toString()]);

    // Add plain text password into user account to generate mail tokens.
    $account->password = $pass;

    $strSMSmessage = 'Thank you for getting in touch! Our app link is http://playstore.medicaldictionaryapplication.com';
      if($country=='9'){
        //sms_send_rel($phone, $strSMSmessage, '');
        //abcd('91' . $phone, $strSMSmessage, '');
        //echo " 237 country   ".$country;exit;
        send_sms_rg('91' . $phone, $strSMSmessage, '');
      }
      else{
        //send_sms_to_us_mobile_number('1' . $phone, $strSMSmessage, '');
        //abcd('1' . $phone, $strSMSmessage, '');
        //echo " 237 country   ".$country;exit;
        send_sms_rg('1' . $phone, $strSMSmessage, '');
      }

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

function url_current(){
  if(isset($_SERVER['HTTPS'])){
      $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
  }
  else{
      $protocol = 'http';
  }
  return $protocol . "://" . $_SERVER['HTTP_HOST'] ;
}
function sms_send_rel($number, $message, $options) {


  $conf['way2smsplus_username'] = 'ondemandhomecare';
  $conf['way2smsplus_password'] = 'ondemand1234';
  $conf['way2smsplus_from'] = 'TDODHC';


  $query = "?username=" . $conf['way2smsplus_username'] . "&password=" . $conf['way2smsplus_password'] . "&from=" . $conf['way2smsplus_from'] . "&to=" . $number . "&msg=" . urlencode($message) . "&type=1&dnd_check=0";
  $url = 'http://pointsms.in/API/sms.php' . $query;



  $client = \Drupal::httpClient();
  $response = $client->request('GET', $url);

  try {
//  $response = $client->send($request);
      // Expected result.
      $data = $response->getBody();

      $query = "?username=ondemandhomecare&password=ondemand1234&job_id=" . $data;
      $url = 'http://pointsms.in/API/get_dlr_status.php' . $query;

      $client = \Drupal::httpClient();
      $response = $client->request('GET', $url);
      try {
//  $response = $client->send($request);
          // Expected result.
          $data = $response->getBody();



          if (strpos($data, 'Dlr Text: Delivered') !== false) {
              $result = array('status' => TRUE, 'data' => $http_result_del_status->data);
          } else {
              $result = array('status' => FALSE, 'message' => 'failed to delever');
          }
          $response = $result;
      } catch (RequestException $e) {
          $response = array('status' => FALSE, 'message' => t('An error occured during the HTTP request: @error', array('@error' => $e->getMessage())));
      }
  } catch (RequestException $e) {
      $response = array('status' => FALSE, 'message' => t('An error occured during the HTTP request: @error', array('@error' => $e->getMessage())));
  }





  return $response;
}


function send_sms_to_us_mobile_number($number, $message, $options) {

$conf['clickatell_username'] = urlencode('odhc85');
$conf['clickatell_password'] = urlencode('BEIYcv8');
$conf['clickatell_api_id'] = urlencode('3360789');
$conf['clickatell_from'] = urlencode('19726967056');
$conf['clickatell_mo'] = urlencode('1');

$query="?user=".$conf['clickatell_username']."&password=".$conf['clickatell_password']."&api_id=".$conf['clickatell_api_id']."&MO=".$conf['clickatell_mo']."&from=".$conf['clickatell_from']."&to=".urlencode($number)."&text=".urlencode($message);
$url='https://api.clickatell.com/http/sendmsg'. $query;

$client = \Drupal::httpClient();
$response = $client->request('GET', $url);


try {

  $data = $response->getBody();

  $apiMsgId = explode(": ",$data);
  $apiMsgId = $apiMsgId[1];

  $query="?user=".$conf['clickatell_username']."&password=".$conf['clickatell_password']."&api_id=".$conf['clickatell_api_id']."&apimsgid=".$apiMsgId;
  $url='https://api.clickatell.com/http/querymsg'. $query;

  $client = \Drupal::httpClient();
  $response = $client->request('GET', $url);

  try {

    $data = $response->getBody();

    if (strpos($data,'Status: 004') !== false) {
      $result   = array('status' => TRUE, 'data' => $http_result_del_status->data);
    }else
    {
        $result = array('status' => FALSE, 'message' => 'failed to delever');
    }
    $response =  $result;

  }
  catch (RequestException $e) {
    $response =  array('status' => FALSE, 'message' => t('An error occured during the HTTP request: @error', array('@error' => $e->getMessage())));
  }



}
catch (RequestException $e) {
  $response =  array('status' => FALSE, 'message' => t('An error occured during the HTTP request: @error', array('@error' => $e->getMessage())));
}
return $response;

}

function send_sms_rg($number, $message, $options){

//echo "416 sms_send_rg".$number.'--'.$message;exit;

//$apikey = 'WtfH9TnvSmO58I2G_iy2ow==';
$apikey = '47TXncPxQdyMmxNRnmatKQ==';

$conf['apikey'] = urlencode($apikey);
$conf['to'] = urlencode($number);
$conf['message'] = urldecode($message);


$query="?apiKey=".$conf['apikey']."&to=".$conf['to']."&content=".$conf['message'];
$url='https://platform.clickatell.com/messages/http/send'. $query;

$client = \Drupal::httpClient();
  $response = $client->request('GET', $url);

try {
//  $response = $client->send($request);
      // Expected result.
      $data = $response->getBody();
  //print_r(json_decode($data));exit;

  $data_object = json_decode($data);

  $apiMsgId = $data_object->messages[0]->apiMessageId;

  //echo $apiMsgId;exit;

  /* $query = "?messageId=" . $apiMsgId;
  $url = 'https://platform.clickatell.com/public-client/message/status' . $query;

  $client = \Drupal::httpClient();
  $response = $client->request('GET', $url, ['headers' => [
    'Accept' => 'application/json',
    'Authorization' => $apikey
  ]]);

  try{

  $data = $response->getBody();

  $data_object = json_decode($data);

  $status = $data_object->status;

  if ((strpos($data, 'RECEIVED_BY_RECIPIENT') !== false)) {
    $result = array('status' => TRUE, 'data' => $http_result_del_status->data);
  } else {
    $result = array('status' => FALSE, 'message' => 'failed to delever');
  }
  $response = $result;


  }
  catch(RequestException $e){

    $response = array('status' => FALSE, 'message' => t('An error occured during the HTTP request: @error', array('@error' => $e->getMessage())));

  } */



  } catch (RequestException $e) {
      $response = array('status' => FALSE, 'message' => t('An error occured during the HTTP request: @error', array('@error' => $e->getMessage())));
  }

return $response;

}
