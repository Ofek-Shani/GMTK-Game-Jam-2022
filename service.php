<?php

$file = file_get_contents('./.env', true);

$file_split = explode("\n", $file);

$variable_map = [];

foreach ($file_split as $key_value) {
    $kv_split = explode("=", $key_value);

    $key = $kv_split[0];
    $value = $kv_split[1];

    $variable_map[$key] = $value;

}


 

// for testing locally

$db = $variable_map["DB"];
$user = $variable_map["USER"];
$pswd = $variable_map["PASSWORD"];
$host = $variable_map["HOST"];
$port = (int) $variable_map["PORT"];

error_log("About to connect!". json_encode($variable_map) ."\n", 3, "./tmp/my-errors.log");

//echo json_encode($variable_map), "\n";


error_log("$db". $db ."\n", 3, "./tmp/my-errors.log");
error_log("$user". $user ."\n", 3, "./tmp/my-errors.log");
error_log("$db". $db ."\n", 3, "./tmp/my-errors.log");
error_log("$pswd". $pswd ."\n", 3, "./tmp/my-errors.log");
error_log("$host". $host ."\n", 3, "./tmp/my-errors.log");
error_log("$port". $port ."\n", 3, "./tmp/my-errors.log");



$conn = mysqli_connect($host, $user, $pswd, $db, $port);


if ($conn->connect_errno) {
    error_log("$conn->connect_error", 3, "./tmp/my-errors.log");
    echo "Failed to connect to MySQL: " . $conn->connect_error;
    exit();
}


mysqli_set_charset($conn, "utf8");



function get_ip_address()
{
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                    return $ip;
                }
            }
        }
    }
}


$now = DateTime::createFromFormat('U.u', microtime(true));
$now_str = $now->format("m-d-Y H:i:s.u");




$entityBody = file_get_contents('php://input');

error_log($now_str."\t".$entityBody."\n", 3, "./tmp/my-errors.log");


$ip = get_ip_address();

//above fine


$input_split = explode("\t",$entityBody);



$user = mysqli_real_escape_string($conn, $input_split[0]);


$action = mysqli_real_escape_string($conn, $input_split[1]);


error_log($now_str."\t".$user."\n", 3, "./tmp/my-errors.log");
error_log($now_str."\t".$action."\n", 3, "./tmp/my-errors.log");


//run the query to search for the username and password the match

$query = "INSERT IGNORE INTO log (id, user, action, ip, timestamp) VALUES (NULL, '$user', '$action', '$ip', now())";

//$result = mysql_query($query) or die("Unable to submit session because : " . mysql_error());


$result = mysqli_query($conn, $query);



if (!$result) {
    die("Unable to a record because : " . mysqli_error($conn));
}


$insert_id = mysqli_insert_id($conn);

$json_result = new stdClass();
$json_result->message = "ok";


echo json_encode($json_result), "\n";

?>