<?php
//System Variables, all start with $ap_
$ap_baseurl = "http://localhost/apiserver";
//$ap_internal_rest_api_url  = "http://localhost/apiserver/v200/rest/";//can be different in the future
$ap_newurl = $ap_baseurl . "/login.php";//used for logins


//Amazon Settings
$ap_awsregion = "us-east-1";
$ap_sendemail = "dev_alert@whoisonmywifi.com";
$ap_awsbucket = "dev.whoisonmywifi.net";
$ap_awsdata   = "data/";
$ap_awsasyncfolder = "/async/";
$ap_awslogbucket = "logs.dev.whoisonmywifi.net";
$ap_awslogapi = "/api";
$ap_awsfilebucket = "files.dev.whoisonmywifi.net";
$ap_awslogapi    = "/api/logs/";
$ap_awsfilefolder = "files/";
$ap_awserrorfolder = "err/";
$ap_aws_configuration_set_name = "whofi-beta-patron-emails";

//Twillio Settings
$ap_twillio_number = "+14052469897";//number the texts come from


//Firebase credentials
$firebase_key = "AIzaSyBe69lekbmY5xzMBUSX1i-UTmbDwN7Vbm8";

//database settings
$db_mstr_host     = "127.0.0.1";
$db_mstr_user     = "root";
$db_mstr_pass     = "wiomw";
$db_mstr_db       = "mywifimstr";

$db_array["00000"]["db_host"] = "127.0.0.1";
$db_array["00000"]["db_user"] = "root";
$db_array["00000"]["db_pass"] = "wiomw";
$db_array["00000"]["db_db"] = "mywifisvr";
$db_array["00000"]["s3_cache_path"] = "00000.cache.dev.whoisonmywifi.net";
$db_array["00000"]["s3_path"] = "00000.dev.whoisonmywifi.net";


$db_array["00001"]["db_host"] = "127.0.0.1";
$db_array["00001"]["db_user"] = "root";
$db_array["00001"]["db_pass"] = "wiomw";
$db_array["00001"]["db_db"] = "mywifisvr00001";
$db_array["00001"]["s3_cache_path"] = "00001.cache.dev.whoisonmywifi.net";
$db_array["00001"]["s3_path"] = "00001.dev.whoisonmywifi.net";

//just make sure the $ap_aws_region is set above
$ap_opensearch_server_array["us-east-1"]["os_host"] = "http://localhost:9200";
$ap_opensearch_server_array["us-east-1"]["os_user"] = "admin";
$ap_opensearch_server_array["us-east-1"]["os_pass"] = "Localhubdev123!";
$ap_opensearch_server_array["us-east-1"]["os_index"] = "localhub";
$ap_opensearch_server_array["eu-west-2"]["os_host"] = "http://localhost:9200";
$ap_opensearch_server_array["eu-west-2"]["os_user"] = "admin";
$ap_opensearch_server_array["eu-west-2"]["os_pass"] = "Localhubdev123!";
$ap_opensearch_server_array["eu-west-2"]["os_index"] = "localhub";



?>
