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
$goods_id = $_GET['goods_id'];
// $goods_id = 1;

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
    // 商品id与名称数组
    $goodsInfoArray = array();

    // 存储每个用户的购买的相关商品id的数组
    $customerCase = array();

    // 商品id暂存数组
    $goodsIdArray = array();

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
    if(!checkRight($conn,531)){
        $response->data = null;
        $response->meta->status = 403;
        $response->meta->message = '权限不足';
        echo json_encode($response);
        exit;
    }
    $usersPreSqls = $conn->prepare("SELECT user_id FROM `orders` where goods_id=".$goods_id);
    $usersPreSqls->execute();
    $usersResult = $usersPreSqls->setFetchMode(PDO::FETCH_ASSOC);
    $usersRes = $usersPreSqls->fetchAll();
    $usersIdArr = array();

    foreach($usersRes as $userKey => $user){
        $user = (object)$user;
        array_push($usersIdArr, $user->user_id);
    }
    $usersIdArr = array_unique($usersIdArr);
    foreach($usersIdArr as $userIdKey => $userId){
        $goodsIdPreSqls = $conn->prepare("SELECT goods_id FROM `orders` where user_id=".$userId." AND goods_id <>".$goods_id);
        $goodsIdPreSqls->execute();
        $goodsResult = $goodsIdPreSqls->setFetchMode(PDO::FETCH_ASSOC);
        $goodsRes = $goodsIdPreSqls->fetchAll();
        $goodsIdArr = array();// 存储该顾客买的其他商品id
        foreach($goodsRes as $goodsIdKey => $goodsId){
            $goodsId = (object) $goodsId;
            if($goodsId->goods_id !== $goods_id){
                array_push($goodsIdArr, $goodsId->goods_id);
                array_push($goodsIdArray, $goodsId->goods_id);
            }
        }
        $goodsIdArr = array_unique($goodsIdArr);
        array_push($customerCase,$goodsIdArr);
    }
    $goodsIdArray = array_unique($goodsIdArray);
    // 获取商品名
    foreach($goodsIdArray as $idKey => $id){
        $goodPreSqls = $conn->prepare("SELECT goods_id,goods_name FROM `goods` where goods_id=".$id);
        $goodPreSqls->execute();
        $goodResult = $goodPreSqls->setFetchMode(PDO::FETCH_ASSOC);
        $goodRes = $goodPreSqls->fetchAll();
        $goodObj = (object) array(
            'goods_id' => '',
            'goods_name' => ''
        );
        $goodObj->goods_id = ((object)$goodRes[0])->goods_id;
        $goodObj->goods_name = ((object)$goodRes[0])->goods_name;
        array_push($goodsInfoArray,$goodObj);
    }


    // 存储每个商品的频繁信息对象数组
    $frequentArray = array();

    foreach($goodsInfoArray as $goodKey => $goods){
        $count = 0;
        foreach($customerCase as $caseKey => $case){
            foreach($case as $key => $value){
                if($goods->goods_id === $value){
                    $count++;
                }
            }
        }
        $frequenObj = (object) array(
            'value' => $count,
            'name' => $goods->goods_name
        );
        array_push($frequentArray,$frequenObj);
    }

    $response = (object) array(
        'data' => $frequentArray,
        'meta' => (object) array(
            'status' => 200,
            'message' => '获取数据成功！'
        )
    );
    echo json_encode($response);
}
catch(PDOException $e){
    echo $e->getMessage();
}
$conn = null;


?>