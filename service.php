<?php

function write_log($msg)
{
    $now = DateTime::createFromFormat('U.u', microtime(true));
    $timestamp = $now->format("m-d-Y H:i:s.u");
    error_log("[".$timestamp."] ".$msg."\n", 3, "./tmp/my-errors.log");
    
}

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

$ip = get_ip_address();

write_log("ip: ".$ip);




$file = file_get_contents('./.env', true);

$file_split = explode("\n", $file);

$variable_map = [];

foreach ($file_split as $key_value) {
    $kv_split = explode("=", $key_value);

    $key = trim($kv_split[0]);
    $value = trim($kv_split[1]);

    $variable_map[$key] = $value;

} 

// for testing locally

$db = $variable_map["DB"];
$user = $variable_map["USER"];
$pswd = $variable_map["PASSWORD"];
$host = $variable_map["HOST"];
$port = (int) $variable_map["PORT"];

// json_encode($variable_map) .
//error_log("About to connect!". "\n", 3, "./tmp/my-errors.log");

//echo json_encode($variable_map), "\n";

/*
error_log("db: ". $db ."\n", 3, "./tmp/my-errors.log");
error_log("user: ". $user ."\n", 3, "./tmp/my-errors.log");
error_log("pswd: ". $pswd ."\n", 3, "./tmp/my-errors.log");
error_log("host: ". $host ."\n", 3, "./tmp/my-errors.log");
error_log("port: ". $port ."\n", 3, "./tmp/my-errors.log");
*/



/*
$link = mysqli_init();

if (!$link) {
    error_log('mysqli_init failed'."\n", 3, "./tmp/my-errors.log");
    die('mysqli_init failed');
}
error_log("After init: "."\n", 3, "./tmp/my-errors.log");
*/


//error_log("Before connect: "."\t".$entityBody."\n", 3, "./tmp/my-errors.log");


//$conn = mysqli_real_connect($link, "localhost", $user, $pswd, $db, $port);

write_log("Connect from: ".$ip);
$conn = mysqli_connect($host, $user, $pswd, $db, $port);

//error_log("After connect: "."\t".$entityBody."\n", 3, "./tmp/my-errors.log");

if(!$conn){
    // mysqli_connect_error()
    write_log("Error: ".mysqli_connect_error());
    exit();
}
else if ($conn->connect_errno) {
    write_log("$conn->connect_error");
    echo "Failed to connect to MySQL: " . $conn->connect_error;
    exit();
}




//error_log("No connect_error: "."\n", 3, "./tmp/my-errors.log");


//mysqli_set_charset($conn, "uft8_general_ci");

//error_log("After set_charset: "."\n", 3, "./tmp/my-errors.log");




$entityBody = trim(file_get_contents('php://input'));

//error_log($now_str."\t". "body: ".$entityBody."\n", 3, "./tmp/my-errors.log");

write_log("body: ".$entityBody);





//error_log("ip: ".$ip."\n", 3, "./tmp/my-errors.log");

//above fine

/*
error_log("Before explode: "."\n", 3, "./tmp/my-errors.log");
$input_split = explode("\t",$entityBody);
error_log("After explode: "."\n", 3, "./tmp/my-errors.log");
*/

//error_log("Before json_decode: "."\n", 3, "./tmp/my-errors.log");
$input_json = json_decode(urldecode($entityBody), true);

/*
error_log("After json_decode: "."\n", 3, "./tmp/my-errors.log");

error_log("input_json: ".json_encode($input_json)."\n", 3, "./tmp/my-errors.log");
*/

/*
$user = mysqli_real_escape_string($conn, $input_split[0]);
$action = mysqli_real_escape_string($conn, $input_split[1]);

$user = mysqli_real_escape_string($conn, $input_json["studyId"]);
$action = mysqli_real_escape_string($conn, $input_json["msg"]);
*/




$user = mysqli_real_escape_string($conn, $input_json["prolificId"]);
$action =  mysqli_real_escape_string($conn, $input_json["msg"]);
$studyId = mysqli_real_escape_string($conn, $input_json["studyId"]);
$sessionId = mysqli_real_escape_string($conn, $input_json["sessionId"]);

/*
error_log("user: "."$user"."\n", 3, "./tmp/my-errors.log");
error_log("action: "."$action"."\n", 3, "./tmp/my-errors.log");
error_log("studyId: "."$studyId"."\n", 3, "./tmp/my-errors.log");
error_log("sessionId: "."$sessionId"."\n", 3, "./tmp/my-errors.log");
*/




if(strlen($user) < 10 || strlen($action) < 1 || strlen($studyId) < 10 || strlen($sessionId) < 10){
    write_log("Issue: params invalid!");
    exit();
}
else {
    //run the query to search for the username and password the match

    $query = "INSERT IGNORE INTO log (id, user, action, study_id, session_id, ip, timestamp) VALUES (NULL, '$user', '$action', '$studyId', '$sessionId', '$ip', now())";

    //$result = mysql_query($query) or die("Unable to submit session because : " . mysql_error());


    $result = mysqli_query($conn, $query);



    if (!$result) {
        $msg = "Unable to a insert a record because : " . mysqli_error($conn);
        write_log($msg);



        die($msg);
    }


    $insert_id = mysqli_insert_id($conn);
    write_log("Successfullly insert a record!");

}



$json_result = new stdClass();
$json_result->message = "ok";

echo json_encode($json_result), "\n";

?>