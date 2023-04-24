<?php
/**
 * @file
 *  Contains Drupal\services_menu\Plugin\rest\resource
 */
namespace Drupal\med_dictionary_services\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * Provides a service resource for menus.
 *
 * @RestResource(
 *   id = "create_employee",
 *   label = @Translation("create employee."),
 *   uri_paths = {
 *     "canonical" = "/create_employee/{employee_id}"
 *   }
 * )
 */
class create_employee extends ResourceBase {
    /**
     * @param null $menu_name
     * @return ResourceResponse
     */
    public function get($employee_id = null) {
		
		//print_r($employee_id);exit;
		
		$employee_data=json_decode($employee_id);
		
		//$mob_uid=$employee_data->uid;
		
		
		

		if($employee_data->uid){
			$uid=$employee_data->uid;//print_r($uid);exit;
		}
		else{
        $account = \Drupal::currentUser();
        $uid = $account->id();
		}
		//print_r($uid);exit;
		//exit;

        $employee_data=json_decode($employee_id);
		
		$employee_uuid=$employee_data->employee_uuid;
		
		$query="SELECT organization_details.organization_uuid  FROM organization_details  organization_details WHERE (organization_details.user_id = :user_id);";

		$data = db_query($query, array(":user_id" => $uid))->fetchAssoc();

		$organization_uuid=$data['organization_uuid'];
		
		//print_r($organization_uuid);exit;
		
		$query1="INSERT INTO employee_details
        (employee_uuid, organization_uuid, organization_userid)
        VALUES (:employee_uuid, :organization_uuid, :organization_userid) ON DUPLICATE KEY UPDATE employee_uuid=:employee_uuid;";

$data1= db_query($query1,array(":employee_uuid"=>$employee_uuid,":organization_uuid"=>$organization_uuid,":organization_userid"=>$uid));
		
		//print_r($data1);exit;
		
		
		if($employee_data->wizard_step){
			
			$roles=$employee_data->roles;

			$roles=(array) $roles;
			
		
			//$account = \Drupal::currentUser();
			//$uid = $account->id();
			
			//print_r($uid);exit;
		
			//$query1="SELECT employee_details.is_employee AS is_employee FROM employee_details  employee_details WHERE (employee_details.employee_userid = :user_id);";
			
			$query1="SELECT employee_details.is_employee AS is_employee FROM employee_details  employee_details WHERE (employee_details.employee_uuid = :employee_uuid);";
			
            //print_r($query1);exit;


			$data1 = db_query($query1, array(":employee_uuid" => $employee_uuid))->fetchAssoc();
		
			$is_employee=$data1['is_employee'];
			
			if($is_employee==0)
			{
				//print_r("if");exit;
				
				$first_name=$employee_data->first_name;
			    $last_name=$employee_data->last_name;
				$mobile_no1=$employee_data->mobile_no;
				$mobile_no1=str_replace(" ","",$mobile_no1);
				$mobile_no1=str_replace("-","",$mobile_no1);
				$mobile_no1='+'.$mobile_no1;
			  
				$username=$employee_data->username;
				$password=getrandomPassword();
				
				//$strmessage='Login Credentials of Employee are: Username: '.$username.' and '.'Password: '.$password .' website link is http://34.67.149.247';
				$strmessage='Login Credentials of Employee are: Username: '.$mobile_no1.' and '.'Password: '.$password .' website link is http://34.67.149.247';
				
				//$roles="employee";
				//$set_roles=array_unshift($roles,'employee');
				
				$set_roles=[];
				$set_roles[0]="employee";

				foreach($roles as $role){
					$set_roles[]=$role;
				}
				
				
				
				//print_r($set_roles);exit;
				
				$new_user = array(
                'field_first_name' => $mobile_no1,
                'fieldt_last_name' => $mobile_no1,
                'name' => $mobile_no1,
                'pass' => $password, // note: do not md5 the password
                'mail' => $username,
                'status' => 1,
                'init' => '',
                'roles' => $set_roles,
              );
			  
			  // The first parameter is sent blank so a new user is created.

              $account = entity_create('user', $new_user);
              $data=$account->save();
			  
			  
			  $first_name=$employee_data->first_name;
			  $last_name=$employee_data->last_name;
			  
			  /* $mobile_no=$employee_data->mobile_no;
			  $mobile_no=str_replace(" ","",$mobile_no);
			  $mobile_no=str_replace("-","",$mobile_no);
			  $mobile_no='+'.$mobile_no;
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
			  } */
			  
			  //print_r($countrycode);exit;
			  
			  $query="SELECT users_field_data.uid AS uid
			  FROM users_field_data users_field_data
			  WHERE (users_field_data.name = :name)";
         
			  $data = db_query($query, array(":name" => $mobile_no1))->fetchAssoc();
			  
			  $role="employee";
			  
			  $nid = db_insert('users_extended') // Table name no longer needs {}

				->fields(array(
				  'uid' => $data['uid'],
				  'first_name' => $first_name,
				  'last_name' => $last_name,
				  'role' => $role,
				  'otp' => '',
				  'country_code'=>$countrycode
				))
				->execute();
				
			 $query4 = "UPDATE employee_details SET employee_userid = :employee_userid,is_employee = 1 WHERE employee_uuid = :employee_uuid;";

			  $data4=db_query($query4, array(":employee_userid" => $data['uid'],":employee_uuid" => $employee_uuid));	
			  
			  $mobile_no=$employee_data->mobile_no;
			  $mobile_no=str_replace(" ","",$mobile_no);
			  $mobile_no=str_replace("-","",$mobile_no);
			  $mobile_no='+'.$mobile_no;
			  $phonelen=strlen($mobile_no);
			  $substr=$phonelen-10;
			  $country = substr($mobile_no,0,2);
			  $phone=substr($mobile_no, -10);
			  if($country=='9')
			  {
			  $countrycode= substr($mobile_no,0,2);
			  send_sms_employee('91'.$phone, $strmessage, '');
			  }
			  else
			  {
			  $countrycode =substr($mobile_no,0,2);
			  send_sms_employee('1'.$phone, $strmessage, '');
			  }
			  
			  
			  
			  $subject='Credentials to Login';
			  $body=$strmessage;
			  $to=$username;
			  
			  sendmail($subject, $body, $to);
				
			  print_r($data4);exit;		
				
				
			}
			elseif($is_employee==1){

				$query="SELECT employee_details.employee_userid AS employee_userid,employee_details.employee_uuid FROM employee_details  employee_details WHERE (employee_details.employee_uuid = :employee_uuid);";
				$data = db_query($query, array(":employee_uuid" => $employee_uuid))->fetchAssoc();

				$employee_userid=$data['employee_userid'];

				

				$user = \Drupal\user\Entity\User::load($employee_userid);
				$user_roles = $user->getRoles();

				$user_roles=array_diff($user_roles,['authenticated','administrator','business','employee']);
				

				foreach($user_roles as $role){
					$user->removeRole($role);
				}

				foreach($roles as $role){
					$user->addRole($role);
				}
				

				$user->save();





				
				//print_r($roles);exit;

				print_r("elseif");exit;
			}
			else{}
			
			//print_r(gettype($is_employee));exit;
		
		
		
		
			
		}
		
		print_r($data1);exit;
		
		//exit;
		
		
		
		/* $emp_mobile=$employee_data->emp_mobile;
		
		$emp_mobile=str_replace(" ","",$emp_mobile);
		
		$emp_mobile='+'.$emp_mobile;
		
		$emp_mail=$employee_data->emp_mail; */


		/* $query="Select count(*) AS count FROM users_field_data WHERE name=:emp_mobile;";
		
		$data_count = db_query($query, array(":emp_mobile"=>$emp_mobile))->fetchAssoc();
		$count = $data_count['count'];
		
		//print_r($count);exit;
		
		$temp=[];
		
		if($count>0)
		{
			$response = array('status' =>'1');
			$temp["mobile_number_reponse"]="1";
		}
		else{
			$response = array('status' =>'2');
			$temp["mobile_number_reponse"]="2";
		}

		//print_r($count);exit;

        $query1="Select count(*) AS count1 FROM users_field_data WHERE mail=:emp_mail;";
		
		$data_count1 = db_query($query1, array(":emp_mail"=>$emp_mail))->fetchAssoc();
		$count1 = $data_count1['count1'];
		
		if($count1>0)
		{
			$response = array('status' =>'3');
			$temp["mail_reponse"]="3";
		}
		else{
			$response = array('status' =>'4');
			$temp["mail_reponse"]="4";
		}
		
		//print_r($response);exit;
		
		
		return new ResourceResponse($temp); */
		
		
		
		
		



    }
    public function post($employee_id = null) {
		

    }
    public function delete($employee_id = null) {

    }
    public function patch($employee_id = null) {

    }
}

function getrandomPassword() {
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
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

function sms_send_emp($number, $message, $options) {


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

function send_sms_employee($number, $message, $options) {
	
	//$apikey = 'WtfH9TnvSmO58I2G_iy2ow==';
	$apikey = '47TXncPxQdyMmxNRnmatKQ==';

	$conf['apikey'] = urlencode($apikey);
	$conf['to'] = urlencode($number);


	$query="?apiKey=".$conf['apikey']."&to=".$conf['to']."&content=".urlencode($message);
	$url='https://platform.clickatell.com/messages/http/send'. $query;

	$client = \Drupal::httpClient();
    $response = $client->request('GET', $url);

    
    try {
    
        $data = $response->getBody();
        $data = json_decode($data);
        $response = $data;

        $apiMsgId = $response->messages[0]->apiMessageId;

        /* $query1="?messageId=".$apiMsgId;
        $url1='https://platform.clickatell.com/public-client/message/status'.$query1;
		
		

        $headers = ['Accept' => 'application/json','Authorization' => 'gpTbeS2qT0Gf7o0w9IneTA=='];
		


        $client = \Drupal::httpClient();
        $response = $client->request('GET', $url1, ['headers' => $headers]);
		

	
	

        try {

			$t1=json_decode($response->getBody());$t2=$t1->status;//print_r($t2);exit;
			
			if($t2=='RECEIVED_BY_RECIPIENT'){
				$result   = array('status' => TRUE, 'message' => 'delivered');
				//print_r("if");exit;
			}
			else{
				$result = array('status' => FALSE, 'message' => 'failed to delever');	
					//print_r("else");//exit;
			}	
			$response =  $result;
            


		}
		catch (RequestException $e) {
			$response =  array('status' => FALSE, 'message' => t('An error occured during the HTTP request: @error', array('@error' => $e->getMessage())));
		} */


    }
    catch(RequestException $e){
        $response =  array('status' => FALSE, 'message' => t('An error occured during the HTTP request: @error', array('@error' => $e->getMessage())));
    }

	
	return $response;
	//return "send_sms";

}












// <?php
// /**
//  * @file
//  *  Contains Drupal\services_menu\Plugin\rest\resource
//  */
// namespace Drupal\med_dictionary_services\Plugin\rest\resource;

// use Drupal\rest\Plugin\ResourceBase;
// use Drupal\rest\ResourceResponse;
// use Symfony\Component\HttpKernel\Exception\HttpException;
// use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
// use Symfony\Component\HttpFoundation\RedirectResponse;
// use GuzzleHttp\Exception\RequestException;
// use GuzzleHttp\Psr7\Request;
// use GuzzleHttp\Psr7\Response;

// /**
//  * Provides a service resource for menus.
//  *
//  * @RestResource(
//  *   id = "create_employee",
//  *   label = @Translation("create employee."),
//  *   uri_paths = {
//  *     "canonical" = "/create_employee/{employee_id}"
//  *   }
//  * )
//  */
// class create_employee extends ResourceBase {
//     /**
//      * @param null $menu_name
//      * @return ResourceResponse
//      */
//     public function get($employee_id = null) {

//         $account = \Drupal::currentUser();
//         $uid = $account->id();//print_r(gettype($uid));exit;

//         $employee_data=json_decode($employee_id);

//         $employee_uuid=$employee_data->employee_uuid;

//         //print_r($employee_data);exit;

//         if($employee_data->wizard_step){
//             //echo "if";
//             //print_r($employee_data);exit;

//             $username=$employee_data->username;
//             $password=randomPassword();print_r($password);


//             // username is "srihari@gmail.com"
//             // password for 1409 is "idtRazq6"

//             $roles="employee";

//             print_r($username);
//             print_r($password);

//             $new_user = array(
//                 'field_first_name' => $username,
//               'fieldt_last_name' => $username,
//                 'name' => $username,
//                 'pass' => $password, // note: do not md5 the password
//                 'mail' => $username,
//                 'status' => 1,
//                 'init' => '',
//                 'roles' => $roles,
//               );

//               // The first parameter is sent blank so a new user is created.

//               $account = entity_create('user', $new_user);
//               $data=$account->save();

//               //print_r($data);

//               $query2="SELECT organization_details.organization_uuid  FROM organization_details  organization_details WHERE (organization_details.user_id = :user_id);";

//  $data2 = db_query($query2, array(":user_id" => $uid))->fetchAssoc();

//  $organization_uuid=$data2['organization_uuid'];

//  $query3="SELECT users_field_data.uid AS uid
//  FROM users_field_data users_field_data
// WHERE (users_field_data.name = :name)";

// $data3 = db_query($query3, array(":name" => $username))->fetchAssoc();

// $enotp=0;
// $nid = db_insert('users_extended') // Table name no longer needs {}

//     ->fields(array(
//       'uid' => $data3['uid'],
//       'first_name' => $username,
//       'last_name' => $username,
//       'role' => $roles,
//       'otp' => $enotp
//       //'country_code'=>$countrycode
//     ))
//     ->execute();



//  $query4 = "UPDATE employee_details SET employee_userid = :employee_userid,is_employee = 1 WHERE organization_userid = :organization_userid;";

//          $data4=db_query($query4, array(":employee_userid" => $data3['uid'],":organization_userid" => $organization_uuid));

//          print_r($data4);

//             print_r($username);
//             print_r($password);exit;





//         }

//         else{
//             //print_r("else");


//             $query="SELECT organization_details.organization_uuid  FROM organization_details  organization_details WHERE (organization_details.user_id = :user_id);";
//             //print_r($query);exit;


//         $data = db_query($query, array(":user_id" => $uid))->fetchAssoc();

//         //print_r($data);exit;

//         $organization_uuid=$data['organization_uuid'];

//         $query1="INSERT INTO employee_details
//         (employee_uuid, organization_uuid, organization_userid)
//         VALUES (:employee_uuid, :organization_uuid, :organization_userid) ON DUPLICATE KEY UPDATE employee_uuid=:employee_uuid;";

// $data1= db_query($query1,array(":employee_uuid"=>$employee_uuid,":organization_uuid"=>$organization_uuid,":organization_userid"=>$uid));

// //         $query1="INSERT INTO employee_details
// // (employee_uuid)
// // VALUES (REPLACE(UUID(),'-',''), :user_id);";
// //           $data1= db_query($query1,array(":user_id"=>$user_id));




//         print_r($data1);exit;
//         }
// exit;

//         print_r($employee_uuid);exit;


//         $query="SELECT organization_uuid FROM organization_details.organization_uuid WHERE (organization_details.user_id = :user_id);";

//         $data = db_query($query, array(":user_id" => $uid))->fetchAssoc();

//         $organization_uuid=$data['organization_uuid'];

//         $query1="INSERT INTO employee_details
//         (employee_uuid, organization_uuid, organization_userid)
//         VALUES (:employee_uuid, :organization_uuid, :organization_userid); ON DUPLICATE KEY UPDATE employee_uuid=::employee_uuid";

// $data1= db_query($query1,array(":employee_uuid"=>$employee_id,":organization_uuid"=>$organization_uuid,":organization_userid"=>$uid));

// //         $query1="INSERT INTO employee_details
// // (employee_uuid)
// // VALUES (REPLACE(UUID(),'-',''), :user_id);";
// //           $data1= db_query($query1,array(":user_id"=>$user_id));




//         print_r($data);exit;



//     }
//     public function post($employee_id = null) {

//     }
//     public function delete($employee_id = null) {

//     }
//     public function patch($employee_id = null) {

//     }
// }

// function randomPassword() {
//     $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
//     $pass = array(); //remember to declare $pass as an array
//     $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
//     for ($i = 0; $i < 8; $i++) {
//         $n = rand(0, $alphaLength);
//         $pass[] = $alphabet[$n];
//     }
//     return implode($pass); //turn the array into a string
// }