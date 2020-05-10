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
$query = "'%".$_GET['query']."%'";
$pageNum = $_GET['pageNum'];
$pageSize = $_GET['pageSize'];


// $query = "'%".''."%'";
// $pageNum = 2;
// $pageSize = 3;

$response = (object) array(
    'data' => null,
    'meta' => (object) array(
        'status' => 200,
        'message' => '获取分类列表成功！!'
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
    if(!checkRight($conn,214)){
        $response->data = null;
        $response->meta->status = 403;
        $response->meta->message = '权限不足';
        echo json_encode($response);
        exit;
    }
    $userPreSqlStr = "(SELECT user_id FROM `users` where user_name LIKE ".$query.")";
    $goodsPreSqlStr = "(SELECT goods_id FROM `goods` where goods_name LIKE ".$query.")";
    // $userPreSql = $conn->prepare($userPreSqlStr);
    // $userPreSql->execute();
    // $userResult = $userPreSql->setFetchMode(PDO::FETCH_ASSOC);
    // $userRes = $userPreSql->fetchAll();

    // $userIdStr = "";
    // foreach($userRes as $userKey => $user) {
    //     $user = (object) $user;
    //     if($userKey == 0){
    //         $preSqlStr = $preSqlStr."(".$user->user_id;
    //     } else if($userKey == (count($userRes) - 1)){
    //         $preSqlStr = $preSqlStr.")";
    //     } else{
    //         $preSqlStr = $preSqlStr.",".$user->user_id;
    //     }
    // }

    
    
    $preSqlStr = "SELECT * FROM `orders` where order_number LIKE ".$query."or consignee_addr LIKE ".$query." or user_id IN ".$userPreSqlStr." or goods_id in ".$goodsPreSqlStr;
    $totalPreSqls = $conn->prepare($preSqlStr);
    $totalPreSqls->execute();
    $result = $totalPreSqls->setFetchMode(PDO::FETCH_ASSOC);
    $res = $totalPreSqls->fetchAll();
    $total = count($res);

    // 返回数据对象
    $response = (object) array(
        'data' => (object) array(
            'orders' => array(),
            'total' => $total
        ),
        'meta' => (object) array(
            'status' => 200,
            'message' => '获取订单列表成功！'
        )
    );

    $start = ($pageNum -1) * $pageSize;
    if($start + $pageSize > $total){
        $pageNum = intval($total / $pageSize) + 1;
        $start = ($pageNum - 1) * $pageSize;
        $pageSize = $total - $start;
    }
    $response->pageNum = intval($pageNum);
    

    $selectTheOrdersSql = $conn->prepare($preSqlStr." LIMIT $start,$pageSize");
    $selectTheOrdersSql->execute();
    $result = $selectTheOrdersSql->setFetchMode(PDO::FETCH_ASSOC);
    $res = $selectTheOrdersSql->fetchAll();
    // 对一些数据进行处理
    foreach($res as $key=>$value){
        $value = (object) $value;

        if($value->is_send == 0){
            $value->is_send = false;
        }else{
            $value->is_send = true;
        }

        if($value->pay_status == 0){
            $value->pay_status = false;
        }else{
            $value->pay_status = true;
        }
        // 获取并设置商品名称
        $goodspreSqlStr = "SELECT goods_id,goods_name FROM `goods` where goods_id=".$value->goods_id;
        $goodsPreSqls = $conn->prepare($goodspreSqlStr);
        $goodsPreSqls->execute();
        $result = $goodsPreSqls->setFetchMode(PDO::FETCH_ASSOC);
        $res = $goodsPreSqls->fetchAll();
        $goods_name = ((object) $res[0])->goods_name;
        $value->goods_name = $goods_name;

        // 获取并设置用户名
        $userpreSqlStr = "SELECT user_id,user_name FROM `users` where user_id=".$value->user_id;
        $userpreSql = $conn->prepare($userpreSqlStr);
        $userpreSql->execute();
        $userResult = $userpreSql->setFetchMode(PDO::FETCH_ASSOC);
        $userRes = $userpreSql->fetchAll();
        $user_name = ((object) $userRes[0])->user_name;
        $value->user_name = $user_name;

        
        array_push($response->data->orders, $value);
    }

    if (!$result) {
        $response->meta->message = '没有更多数据了！';
        $response->meta->status = 404;
    }
    echo json_encode($response);
}
catch(PDOException $e){
    echo $e->getMessage();
}
$conn = null;


?>