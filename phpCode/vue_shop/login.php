<?php

if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
    header('Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH');
    file_put_contents('option.txt',json_encode($_REQUEST));
    exit;
}

header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:*');
// 响应头设置
header('Access-Control-Allow-Headers:content-type,token,id');
header("Access-Control-Request-Headers: Origin, X-Requested-With, content-Type, Accept, Authorization");
header('Content-type: application/x-www-form-urlencoded');

$servername = "localhost";
$username = "root";
$password = "123456";
$dbname = "vue_shop";
//  表单提交后...
$data = json_decode(file_get_contents("php://input"));
// // //  清除一些空白符号
$u_password = $data->password;
$u_username = $data->username;
$quote = "'";
// $u_password = "123456";
// $u_username = "admin";
$sqlText = "password";

$response = (object) array(
    'data' => null,
    'meta' => (object) array(
        'status' => 200,
        'message' => '登录成功！'
    ));

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname",$username,$password);
    //设置PDO错误模式，抛出异常
    $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $conn->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
    $userIdpreSqls = $conn->prepare("SELECT * FROM account WHERE user_name=".$quote.$u_username.$quote." AND ".$sqlText."=".$quote.$u_password.$quote);
    $userIdpreSqls->execute();
    $userIdResult = $userIdpreSqls->setFetchMode(PDO::FETCH_ASSOC);
    $userIdRes = $userIdpreSqls->fetchAll();
    if(count($userIdRes) !== 0){
        $loginNamePreSqls = $conn->prepare("SELECT `user_name` FROM `login_status` WHERE user_name=".$quote.$u_username.$quote);
        $loginNamePreSqls->execute();
        $loginNameResult = $loginNamePreSqls->setFetchMode(PDO::FETCH_ASSOC);
        $loginNameRes = $loginNamePreSqls->fetchAll();
        $token = md5($u_username.date('Y-m-d') ,false);
        if(count($loginNameRes) === 0){// 如果login_status 里没有该数据
            $preStr = "INSERT INTO login_status (user_name,token) VALUES(".$quote.$u_username.$quote.",".$quote.$token.$quote.")";
        } else {
            $preStr = "UPDATE login_status SET token=".$quote.$token.$quote." WHERE user_name=".$quote.$u_username.$quote;
        }
        $preSqls = $conn->prepare($preStr);
        $success = $preSqls->execute();
        if ($success) {
            $response->data = (object) array(
                'username' => $u_username,
                'token' => $token);
            }
             else {
            $response->meta->message = '登录失败';
            $response->meta->status = 404;
        }

    } else {
        $response->meta->status = 404;
        $response->meta->message = '没有此用户！';
    }
    
    echo json_encode($response);
}
catch(PDOException $e){
    echo $e->getMessage();
}
$conn = null;


?>