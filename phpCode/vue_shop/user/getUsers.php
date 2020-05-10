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
$u_query = "'%".$data->query."%'";
$u_pagenum = $data->pagenum;
$u_pagesize = $data->pagesize;
$quote = "'";
// $u_query = "'%%'";
// $u_pagenum = 1;
// $u_pagesize = 2;

$response = (object) array(
    'data' => null,
    'meta' => (object) array(
        'status' => 200,
        'message' => '获取用户列表成功!'
    )
);

function checkToken($conn){
    $token = "'".(apache_request_headers())['Authorization']."'";
    $userNamePreSqls = $conn->prepare("SELECT * FROM `login_status` where token=".$token);
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
    if(!checkToken($conn)){
        $response->data = null;
        $response->meta->status = 403;
        $response->meta->message = '无效的token';
        echo json_encode($response);
        exit;
    }
    if(!checkRight($conn,414)){
        $response->data = null;
        $response->meta->status = 403;
        $response->meta->message = '权限不足';
        echo json_encode($response);
        exit;
    }
    // 选择所有角色信息
    $rolesPreSqls = $conn->prepare("SELECT id,roleName FROM `roles`");
    $rolesPreSqls->execute();
    $roleResult = $rolesPreSqls->setFetchMode(PDO::FETCH_ASSOC);
    $roleRes = $rolesPreSqls->fetchAll();
    
    $countpre = $conn->prepare("SELECT * FROM users where user_name LIKE ".$u_query);
    $countpre->execute();
    $userResult = $countpre->setFetchMode(PDO::FETCH_ASSOC);
    $userRes = $countpre->fetchAll();
    $total = count($userRes);
    $start = ($u_pagenum -1) * $u_pagesize;
    $start = ($u_pagenum -1) * $u_pagesize;
    if($start + $u_pagesize > $total){
        $u_pagenum = intval($total / $u_pagesize) + 1;
        $start = ($u_pagenum - 1) * $u_pagesize;
        $u_pagesize = $total - $start;
    }
    $preSqls = $conn->prepare("SELECT * FROM `users` WHERE user_name LIKE ".$u_query." LIMIT $start,$u_pagesize");
    $preSqls->execute();
    $result = $preSqls->setFetchMode(PDO::FETCH_ASSOC);
    $res = $preSqls->fetchAll();
    $response = (object) array(
        'total' => $total,
        'data' => null,
        'pageNum' => $u_pagenum,
        'meta' => (object) array(
            'status' => 200,
            'message' => '获取数据成功！'
        ));
    $resArr = array();
    foreach($res as $userkey => $user) {
        $user = (object) $user;
        foreach($roleRes as $roleKey => $role){
            $role = (object) $role;
            if($user->role_id == $role->id ){
                $user->role_name = $role->roleName;
            break;
            }
        }
        array_push($resArr,$user);
    }
    if (!empty($res)) {
        $response->data = $resArr;
        }
         else {
        $response->meta->message = '没有更多数据了';
        $response->meta->status = 404;
    }
    echo json_encode($response);
}
catch(PDOException $e){
    echo $e->getMessage();
}
$conn = null;


?>