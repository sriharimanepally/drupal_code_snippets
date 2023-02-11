<?php

namespace Drupal\ace_theme_user_login_register\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ChangedCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;


class CustomUserLoginRegister {

    public function sendVerificationCode(){



        //print_r($_POST);exit;
        $email=$_POST['mail'];
        $to=$_POST['mail'];

        // $email=$email_value;
        // $to=$email_value;

        // return "sendVerificationCode";

        // \Drupal\Core\Database\Database::setActiveConnection();

        // $query="Select count(*) AS count FROM users_field_data WHERE name=:mail;";

        // // $response=[];

        // $data_count = db_query($query, array(":mail"=>$email))->fetchAssoc();
        // $count = $data_count['count'];return $count;
        //print_r($count);exit;

        // Create an object of type Select.
    $database = \Drupal::database();
    $query = $database->select('users_field_data', 'u');

    // Add extra detail to this query object: a condition, fields and a range.
    // $query->condition('name', $email);
    $query->condition('mail', $email);
    $query->fields('u', ['uid', 'name']);
    // $query->range(0, 50);

    $result = $query->execute();
    $records = $result->fetchAll();
    // $num_results = count($records);
    $count = count($records);


    // $count_query = $query->countQuery();
    // $num_rows = $query->countQuery()->execute()->fetchField();

    // $num_rows = $query->count()->execute();

    if($count > 0){
        $response = ['status'=>'0'];
    }
    else{
        $subject="Verification Code from Portal";
        $code=rand(100000,900000);
        $body=$code." is the verification code to verify your mail.";
        $enOtpCode=md5($code);
        //sendmail($subject,$body,$to);

//         $query="Select count(*) AS count FROM user_email_registration WHERE email=:email;";
//         $data_count = db_query($query, array(":email"=>$email))->fetchAssoc();
//           $count = $data_count['count'];
//            $uid='0';
// return $count;
$uid='0';

// Create an object of type Select.
$database = \Drupal::database();
$query = $database->select('user_email_registration', 'u');

// Add extra detail to this query object: a condition, fields and a range.
$query->condition('email', $email);
$query->fields('u', ['user_uid', 'email']);
// $query->range(0, 50);

$result = $query->execute();
$records = $result->fetchAll();
// $num_results = count($records);
$count = count($records);
// return $count;

// $count_query = $query->countQuery();
// $num_rows = $query->countQuery()->execute()->fetchField();

// $num_rows = $query->count()->execute();

$test=[];

$test['abcd']='abcd';

if($count > 0){

    $query="Update user_email_registration SET otp=:otp, registration_date=now()
             WHERE email=:email";
    $data= db_query($query,array(":email"=>$email,":otp"=>$enOtpCode));


    // Update a record in table.
// \Drupal::database()->update('TABLE_NAME')
// ->condition(CONDITION)
// ->fields([
//     'FIELD_1' => NEW_VALUE_1,  // FIELD_1 NEW value.
//     'FIELD_2' => NEW_VALUE_2,  // FIELD_2 NEW value.
//     'FIELD_3' => NEW_VALUE_3,  // FIELD_3 NEW value.
// ])
// ->execute();

    // \Drupal::database()->update('employee')
	// ->condition('employee_id' , 'CE 003')
	// ->updateFields([
	// 	'employee_name' => 'Swathy',
	// 	'employee_age' => 20,
	// ])
    // ->execute();

    // $database = \Drupal::database();
    // $query = $database->update('user_email_registration')
    // ->condition('email' , $email)
    // ->updateFields([
    //     'otp' => $enOtpCode,
    //     'registration_date' => now(),
    // ])
    // ->execute();

    // $data=$query;


     sendmail($subject,$body,$to);

    if($data)
    {
    //$response = array('status' =>1,'otp'=>$code);

    $response = [
        'status' =>1,
        'otp'=>$code,
		//'#theme' => 'form__user_register_form',
		//'#test' => $test,
	];

    }
    else
    {
    //   $response = array('status' =>0,'otp'=>$code);
      $response = [
        'status' =>0,
        'otp'=>$code,
		//'#theme' => 'form__user_register_form',
		//'#test' => $test,
	];
    }

}
else{

    $query="INSERT INTO user_email_registration
(email_registration_uuid, user_uid, registration_date, email,otp)
VALUES (REPLACE(UUID(),'-',''), :uid, now(), :email,:otp);";
    $data= db_query($query,array(":email"=>$email,":uid"=>$uid,":otp"=>$enOtpCode));


// Insert the record to table.
// \Drupal::database()->insert('TABLE_NAME')
// 	->fields([
// 		'FIELD_1',  // FIELD_1.
// 		'FIELD_2',  // FIELD_2.
// 		'FIELD_3',  // FIELD_3.
// 	])
// 	->values(array(
// 		VALUE_1,  // FIELD_1 value.
// 		VALUE_2,  // FIELD_2 value.
// 		VALUE_3,  // FIELD_3 value.
// 	))
// 	->execute();

// Insert the record to table.
// $database = \Drupal::database();
//     $query = $database->insert('user_email_registration')
// 	->fields([
// 		'email_registration_uuid',
// 		'user_uid',
//         'registration_date',
//         'email',
//         'otp',
// 	])
// 	->values(array(
// 		"REPLACE(UUID(),'-','')",
// 		$uid,
//         "now()",
//         $email,
//         $enOtpCode,


// 	))
// 	->execute();

//     $data=$query;

 sendmail($subject,$body,$to);
    if($data)
    {
            // $response = array('status' =>1,'otp'=>$code);
            $response = [
                'status' =>1,
                'otp'=>$code,
                //'#theme' => 'form__user_register_form',
                //'#test' => $test,
            ];

    }
    else
    {
        // $response = array('status' =>0,'otp'=>$code);
        $response = [
            'status' =>0,
            'otp'=>$code,
            //'#theme' => 'form__user_register_form',
            //'#test' => $test,
        ];
    }




}



        //$response = ['status'=>'1'];
    }

    // return $response;

    return new JsonResponse($response);



    // print_r($num_results);exit;



        // if($count > 0)
        // {//print_r("if");exit;
        //     $data["a"]="a";
        //     $data["b"]="b";
        //     $response = ['status' =>2];
        //     return [
        //         '#theme' => 'form__user_register_form',
        //         '#data' => $data,
        //     ];
        // }
        // else
        // {//print_r("else");exit;
        //     $subject="Verification Code from Portal";
        //     $code=rand(100000,900000);
        //     $body=$code." is the verification code to verify your mail.";
        //     //sendmail($subject,$body,$to);
        //     $response = ['status' =>1];
        //     $data["a"]="a";
        //     $data["b"]="b";
        //     return [
        //         '#theme' => 'form__user_register_form',
        //         '#data' => $data,
        //     ];

        // }

        // $subject="Verification Code from Portal";
        // $code=rand(100000,900000);
        // $body=$code." is the verification code to verify your mail.";
        // sendmail($subject,$body,$to);
        // $response = ['status' =>1];
        // return new JsonResponse($response);


    }

    public function verifyCode(){

        $email=$_POST['mail'];
        $verification_code=$_POST['verification_code'];
        // $email=$email_value;
        // $to=$email_value;

        $verified_otp=$verification_code;

        // return "sendVerificationCode";

        // \Drupal\Core\Database\Database::setActiveConnection();

        // $query="Select count(*) AS count FROM users_field_data WHERE name=:mail;";
        // $query="Select count(*) AS count FROM user_email_registration WHERE email=:mail;";

        // $response=[];

        // $data_count = db_query($query, array(":mail"=>$email))->fetchAssoc();
        // $count = $data_count['count'];return $count;
        // print_r($count);exit;

        //$email=$email_value;print_r($email);exit;

        $query="SELECT user_email_registration.otp as otp,TIME_TO_SEC(TIMEDIFF(now(),user_email_registration.registration_date))  as diff
  FROM user_email_registration user_email_registration
 WHERE (user_email_registration.email = :email)";

 $data_uid = db_query($query, array(":email" => $email))->fetchAssoc();
//  print_r($data_uid);exit;

 $otp = $data_uid['otp'];
        $diff = $data_uid['diff'];

        if($diff>1800){
            $response = array('status'=>2);
        }
        else if($otp == md5($verified_otp)){

            $query="SELECT users_field_data.uid AS uid,concat (users_extended.first_name ,' ',users_extended.last_name) as full_name,mail,
       users_extended.role
  FROM users_field_data users_field_data
       INNER JOIN users_extended users_extended
          ON (users_field_data.uid = users_extended.uid)
 WHERE (users_field_data.name = :name)";
 $data = db_query($query, array(":name" => $email))->fetchAssoc();

            if(!$data)
			 {
				$data['uid'] = '0';
             }

             $otp_flag=1;
             $query_update_users = "UPDATE user_email_registration SET is_otp_verified=:otp_flag,user_uid=:uid where email=:email";
             $data_update = db_query($query_update_users, array(":otp_flag" => $otp_flag,":email" => $email,":uid" => $data['uid']));
             $response = array('status' =>1);

        }
        else{

            $otp_flag=0;
         $query_update_users = "UPDATE user_email_registration SET is_otp_verified=:otp_flag where email=:email";

         $data_update = db_query($query_update_users, array(":otp_flag" => $otp_flag,":email" => $email));

		  $response = array('status' =>0);

        }

        // return $data_uid;
        // return $response;
        return new JsonResponse($response);


//  $response = [
//     'status' =>0,
//     'email'=>$email_value,
//     'diff'=>$diff,
//     //'#theme' => 'form__user_register_form',
//     //'#test' => $test,
// ];

// return $response;

    }

    public static function example_form_validate(&$form, FormStateInterface $form_state){

        $email = $form_state->getValue('mail');

        // $query = "SELECT is_otp_verified FROM user_email_registration WHERE user_email_registration.email=:mail";
        // $data = db_query($query, array(":mail" => $email ))->fetchAssoc();

        $database = \Drupal::database();
$query = $database->select('user_email_registration', 'u');

// Add extra detail to this query object: a condition, fields and a range.
$query->condition('email', $email);
$query->fields('u', ['is_otp_verified','user_uid', 'email']);
// $query->range(0, 50);

$result = $query->execute();
// $records = $result->fetchAll();
$records = $result->fetchAssoc();
// print_r($records);exit;
// $num_results = count($records);
//$count = count($records);

$is_otp_verified=$records['is_otp_verified'];

if($is_otp_verified=='1'){
    //print_r("if");exit;
}
else{
    exit;
    // print_r("else");exit;
    // $redirect_path = "/user/register";
// $url = url::fromUserInput($redirect_path);

// set redirect
// $form_state->setRedirectUrl($url);

}



        // $query = "select * from user_email_registration;";
        // $data = db_query($query, array(":mail" => $email ))->fetchAssoc();print_r($data);exit;

        // $is_otp_verified = $data['is_otp_verified'];



        // print_r("example_form_validate");exit;
    }


}

function sendmail($subject, $body, $to){
    $mailManager = \Drupal::service('plugin.manager.mail');
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    //$params['context']['subject'] = "Subject";
    $params['context']['subject'] = $subject;
    //$params['context']['message'] = 'body';
    $params['context']['message'] = $body;
    //$to = "mtrackerteam@gmail.com";
    $mailManager->mail('system', 'mail', $to, $langcode, $params);
}


function send_sms($number, $message, $options){

    //curl "https://platform.clickatell.com/messages/http/send?apiKey=WtfH9TnvSmO58I2G_iy2ow==&to=918341310623&content=hello"


	//echo "416 sms_send_rg".$number.'--'.$message;exit;

	$apikey = 'WtfH9TnvSmO58I2G_iy2ow==';
	// $apikey = '47TXncPxQdyMmxNRnmatKQ==';

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