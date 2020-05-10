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
// //  清除一些空白符号
$u_username = $data->username;
$u_password = $data->password;
$u_mobile = $data->mobile;
$u_email = $data->email;
$u_role_id = $data->roleId[0];
// $u_username = "路人丙";
// $u_password = "123456";
// $u_mobile = "13745842456";
// $u_email = "shgl@qq.com";
$u_creat_time = strtotime(date("Y-m-d  h:i:sa"));
$quote = "'";

$response = (object) array(
    'data' => null,
    'meta' => (object) array(
        'status' => 200,
        'message' => '获取用户列表成功!'
    )
);

function checkToken($conn){ // 返回true则有效
    $token = "'".(apache_request_headers())['Authorization']."'";
    $userNamePreSqls = $conn->prepare("SELECT user_name FROM `login_status` where token=".$token);
    $userNamePreSqls->execute();
    $userNameResult = $userNamePreSqls->setFetchMode(PDO::FETCH_ASSOC);
    $userNameRes = $userNamePreSqls->fetchAll();

    if(count($userNameRes) !== 0){
        return true;
    }else {
        return false;
    }
}


function checkRight($conn,$rightId){// 返回true则有效
    $token = "'".(apache_request_headers())['Authorization']."'";
    $userNamePreSqls = $conn->prepare("SELECT user_name FROM `login_status` where token=".$token);
    $userNamePreSqls->execute();
    $userNameResult = $userNamePreSqls->setFetchMode(PDO::FETCH_ASSOC);
    $userNameRes = $userNamePreSqls->fetchAll();

    $userName = "'".((object) $userNameRes[0])->user_name."'";
    $roleIdPreSqls = $conn->prepare("SELECT role_id FROM `users` where user_name=".$userName);
    $roleIdPreSqls->execute();
    $roleIdResult = $roleIdPreSqls->setFetchMode(PDO::FETCH_ASSOC);
    $roleIdRes = $roleIdPreSqls->fetchAll();

    if(count($roleIdRes) !== 0){
        $roleId = ((object) $roleIdRes[0])->role_id;
        $roleRightPreSqls = $conn->prepare("SELECT thirdLevel FROM `roles` where id=".$roleId);
        $roleRightPreSqls->execute();
        $roleRightResult = $roleRightPreSqls->setFetchMode(PDO::FETCH_ASSOC);
        $roleRightRes = $roleRightPreSqls->fetchAll();
        if(count($roleRightRes) !== 0){

            $rightsStr = ((object) $roleRightRes[0])->thirdLevel;
            $rightsArr = explode(',', $rightsStr);
            $rightIdStr = strval($rightId);
            if(array_search($rightIdStr,$rightsArr) !== false){
                return true;
            }else {
                return false;
            }
        }
        return false;
        
    }else {
        return false;
    }
}


try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname",$username,$password);
    //设置PDO错误模式，抛出异常
    $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $conn->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);

    if(!checkToken($conn)){ // 检查token是否有效
        $response->data = null;
        $response->meta->status = 403;
        $response->meta->message = '无效的token';
        echo json_encode($response);
        exit;
    }
    if(!checkRight($conn,411)){
        $response->data = null;
        $response->meta->status = 403;
        $response->meta->message = '权限不足';
        echo json_encode($response);
        exit;
    }
    
    $toUsersSqls = $conn->prepare("insert into users(user_name,mobile,email,creat_time,role_id)
                            values(:user_name,:mobile,:email,:creat_time,:role_id)");
    $toUsersSqls->bindParam(':user_name',$u_username);
    $toUsersSqls->bindParam(':mobile',$u_mobile);
    $toUsersSqls->bindParam(':email',$u_email);
    $toUsersSqls->bindParam(':creat_time',$u_creat_time);
    $toUsersSqls->bindParam(':role_id',$u_role_id);
    $isSuccess = $toUsersSqls->execute();

    $responseSqls = $conn->prepare("SELECT * FROM `users` WHERE user_name=".$quote.$u_username.$quote." AND mobile=".$quote.$u_mobile.$quote." AND email=".$quote.$u_email.$quote);
    $responseSqls->execute();
    $result = $responseSqls->setFetchMode(PDO::FETCH_ASSOC);
    $res = $responseSqls->fetchAll();
    $id = $res[0]['user_id'];
    $toAccountSqls = $conn->prepare("insert into account(id,user_name,password)
        values(:id,:user_name,:password)");
    $toAccountSqls->bindParam(':id',$id);
    $toAccountSqls->bindParam(':user_name',$u_username);
    $toAccountSqls->bindParam(':password',$u_password);
    $toAccountSqls->execute();

    $response = (object) array(
        'data' => $res,
        'meta' => (object) array(
            'status' => 201,
            'message' => '修改成功！'
        ));
    if(!$isSuccess ){
        $response->meta->status = 500;
        $response->meta->message = '添加失败！';
    }
    echo json_encode($response);
}
catch(PDOException $e){
    echo $e->getMessage();
}

$conn = null;


?>