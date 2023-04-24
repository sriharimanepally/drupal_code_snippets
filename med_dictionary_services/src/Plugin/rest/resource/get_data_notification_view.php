<?php
/*error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);*/
include_once('config/dbconfig.php');
header('Access-Control-Allow-Origin: *');

mysql_select_db("9095_json_db_production");  








$id=$_POST['id'];
$type=$_POST['type'];
$strQuery = <<<QUERY
SELECT json 
FROM patient_data where id='$id' and type='$type';
QUERY;









$res=mysql_query($strQuery);


$data = array();
while($row=mysql_fetch_object($res))
{
	
	 $data [] = $row;
}

echo $data[0]->json;




?>
