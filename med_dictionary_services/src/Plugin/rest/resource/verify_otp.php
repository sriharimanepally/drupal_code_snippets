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
 *   id = "verify_otp",
 *   label = @Translation("verify otp."),
 *   uri_paths = {
 *     "canonical" = "/verify_otp/{device_uuid}/{verified_otp}/{mobile_no}"
 *   }
 * )
 */
class verify_otp extends ResourceBase {
    /**
     * @param null $menu_name
     * @return ResourceResponse
     */
    public function get($device_uuid = null , $verified_otp = null, $mobile_no = null  ) 
    {
        
\Drupal\Core\Database\Database::setActiveConnection('funtional');

$query="SELECT ats_device_details.otp as otp,TIME_TO_SEC(TIMEDIFF(now(),ats_device_details.server_datetime))  as diff 
  FROM ats_device_details ats_device_details
 WHERE (ats_device_details.device_unique_uuid = :device_unique_uuid)";

 $data_uid = db_query($query, array(":device_unique_uuid" => $device_uuid))->fetchAssoc();

 $otp = $data_uid['otp'];
        $diff = $data_uid['diff'];
	
		 if($diff>1800)
		 {
		 
		  $response = array(
        'response' => '0',
        'response_Code' => 'I-20004',
        'response_message' => "OTP is not matched , if you need new OTP please click on send OTP button again.");
		 
		 }
         else if($otp==md5($verified_otp))
         {
             
\Drupal\Core\Database\Database::setActiveConnection();
            $phone=$mobile_no;
         $query="SELECT users_field_data.uid AS uid,concat (users_extended.first_name) as full_name,mail,
       users_extended.role
  FROM users_field_data users_field_data
       INNER JOIN users_extended users_extended
          ON (users_field_data.uid = users_extended.uid)
 WHERE (users_field_data.name = :name)";
         
 $data = db_query($query, array(":name" => $mobile_no))->fetchAssoc();
      
//        return new ResourceResponse($data);
             if(!$data)
			 {
				$data['uid'] = '0';
			 }
             


         \Drupal\Core\Database\Database::setActiveConnection();
         
       $query_count="SELECT users_field_data.uid 
  FROM users_field_data users_field_data
       INNER JOIN users_extended users_extended
          ON (users_field_data.uid = users_extended.uid)
 WHERE (users_field_data.name = :name)";
         
 $data_count = db_query($query_count, array(":name" => $mobile_no))->fetchAssoc();  
         

         
      if($data_count)   
      {
                
          
          
          \Drupal\Core\Database\Database::setActiveConnection('funtional');
           $query_uid_select="SELECT count(*) AS count FROM ats_device_registration 
 WHERE (ats_device_registration.user_uid = :uid and is_registartion_successful=1) group by device_registration_uuid";
         
 $data_uid_select = db_query($query_uid_select, array(":uid" => $data_count['uid']))->fetchAssoc();


\Drupal\Core\Database\Database::setActiveConnection();

$cuid = $data['uid'];

$query1 = "SELECT employee_details.organization_userid AS organization_userid FROM employee_details  employee_details WHERE (employee_details.employee_userid = :employee_userid);";



$data1 = db_query($query1, array(":employee_userid" => $cuid))->fetchAssoc();

if(!$data1)
{
$data1['organization_userid'] = '0';
}


  
 
 if($data_uid_select['count'] == 0)
 {
     

         $response = array(
        'response' => '2',
        'response_Code' => 'I-20005',
        'response_message' => "OTP Verified Successfully",
         'full_name' =>$data['full_name'],     
         'mail' =>$data['mail'],     
         'role' =>$data['role'],     
         'uid' =>$data['uid'],
			'organization_userid' => $data1['organization_userid'],
                );   
         

 }
 else if($data_uid_select['count'] == 1)
 {
        $response = array(
        'response' => '3',
        'response_Code' => 'I-20005',
        'response_message' => "OTP Verified Successfully",
         'full_name' =>$data['full_name'],     
         'mail' =>$data['mail'],     
         'role' =>$data['role'],     
         'uid' =>$data['uid'],
		'organization_userid' => $data1['organization_userid'],
                );   
         

 }  
 else
 {
      \Drupal\Core\Database\Database::setActiveConnection('funtional');
           $query_mobile_pin="select count(*) AS count FROM ats_device_details where device_unique_uuid = 
(select device_unique_uuid FROM ats_device_registration WHERE ats_device_registration.user_uid=:uid)
group by mobile_pin;";
         
 $data_mobile_pin = db_query($query_mobile_pin, array(":uid" => $data_count['uid']))->fetchAssoc();
     if($data_mobile_pin['count'] == 1)
     {
         $response = array(
        'response' => '3',
        'response_Code' => 'I-20005',
        'response_message' => "OTP Verified Successfully",
         'full_name' =>$data['full_name'],     
         'mail' =>$data['mail'],     
         'role' =>$data['role'],     
         'uid' =>$data['uid'],
			'organization_userid' => $data1['organization_userid'],
                );   
         

     }
     else
     {
         $response = array(
        'response' => '4',
        'response_Code' => 'I-20005',
        'response_message' => "OTP Verified Successfully",
         'full_name' =>$data['full_name'],     
         'mail' =>$data['mail'],     
         'role' =>$data['role'],     
         'uid' =>$data['uid'],
			'organization_userid' => $data1['organization_userid'],
                );   
         
     }
 }
      }
      else{
           $response = array(
        'response' => '1',
        'response_Code' => 'I-20004',
        'response_message' => "OTP Verified Successfully");   
          
         
          
      }
           
        \Drupal\Core\Database\Database::setActiveConnection('funtional');

         $otp_flag=1; 
         $query_update_users = "UPDATE ats_device_registration SET is_otp_verified=:otp_flag,user_uid=:uid where device_unique_uuid=:device_uuid";
         
         $data_update = db_query($query_update_users, array(":otp_flag" => $otp_flag,":device_uuid" => $device_uuid,":uid" => $data['uid']));

         }   
         
         else
         {
\Drupal\Core\Database\Database::setActiveConnection('funtional');
         $otp_flag=0; 
         $query_update_users = "UPDATE ats_device_registration SET is_otp_verified=:otp_flag where device_unique_uuid=:device_uuid";
         
         $data_update = db_query($query_update_users, array(":otp_flag" => $otp_flag,":device_uuid" => $device_uuid));  
         
              $response = array(
        'response' => '0',
        'response_Code' => 'I-20004',
        'response_message' => "OTP is not matched , if you need new OTP please click on send OTP button again.");
             
         }
         
        return new ResourceResponse($response);
 
    }
    public function post($id = null) {
       
    }
    public function delete($id = null) {
       
    }
    public function patch($id = null) {
       
    }
}
