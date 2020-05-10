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




$time_statistic = false; // 若为true 则返回存储时间与数量对象的数组

if(isset($_GET['getTimeArray'])){
    $time_statistic = $_GET['getTimeArray'];
    $goods_id = $_GET['goodsId'];
} else{
    //  父级id等级
    $parentCateLevel = $_GET['cateLevel'];
    //  父级id
    $parentCateId = $_GET['parentCateId'];
}

// $time_statistic = true;
// $goods_id = 16;

function getQuantity($goodsId,$conn){// 获取相应商品的出售数量
    $quantitySqlStr = "SELECT order_quantity FROM `orders` where pay_status=1 AND goods_id=".$goodsId;
    $quantityPreSqls = $conn->prepare($quantitySqlStr);
    $quantityPreSqls->execute();
    $result = $quantityPreSqls->setFetchMode(PDO::FETCH_ASSOC);
    $res = $quantityPreSqls->fetchAll();
    $quantity = 0;
    foreach($res as $key => $value){
        $value = (object) $value;
        $quantity += $value->order_quantity;
    }
    return $quantity;
}

function getTime($goodsId,$conn){// 获取相应商品的出售时间
    $timeSqlStr = "SELECT create_time,order_quantity FROM `orders` where pay_status=1 AND goods_id=".$goodsId;
    $timePreSqls = $conn->prepare($timeSqlStr);
    $timePreSqls->execute();
    $result = $timePreSqls->setFetchMode(PDO::FETCH_ASSOC);
    $res = $timePreSqls->fetchAll();
    $timeAndQuantityArr = array();
    foreach($res as $key => $value){
        $value = (object) $value;
        $timeAndQuantityObj = array(
            $value->create_time,
            $value->order_quantity
        );
        array_push($timeAndQuantityArr,$timeAndQuantityObj);
    }
    return $timeAndQuantityArr;
}

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
    // 分类名数组
    $nameArray = array();
    // 数量数组
    $dataArray = array();
    // 时间对象数组
    $timeArray = array();
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
    if(!$time_statistic){
        if(!checkRight($conn,511)){
            $response->data = null;
            $response->meta->status = 403;
            $response->meta->message = '权限不足';
            echo json_encode($response);
            exit;
        } 
        if($parentCateLevel == 3){ // 如果分类等级为三
            $goodsSqlStr = "SELECT goods_id,goods_name FROM `goods` where goods_cate=".$parentCateId;
            $goodsPreSqls = $conn->prepare($goodsSqlStr);
            $goodsPreSqls->execute();
            $result = $goodsPreSqls->setFetchMode(PDO::FETCH_ASSOC);
            $res = $goodsPreSqls->fetchAll();
            foreach($res as $key => $value) {
                $value = (object) $value;
                    array_push($nameArray,$value->goods_name);
                    $count = getQuantity($value->goods_id, $conn);
                    array_push($dataArray,$count);
            }
        }else {
            $preSqlStr = "SELECT cate_id,cate_name FROM `category` where cate_pid=".$parentCateId;
            $totalPreSqls = $conn->prepare($preSqlStr);
            $totalPreSqls->execute();
            $result = $totalPreSqls->setFetchMode(PDO::FETCH_ASSOC);
            $res = $totalPreSqls->fetchAll();
            // 从goods表中选择特定分类的商品选择语句
            $selectGoodsStr = "SELECT goods_id FROM `goods` where goods_cate=";
            foreach($res as $key => $value){
                $value = (object) $value;
                    $count = 0;
                    array_push($nameArray,$value->cate_name);
                    $selectGoods = $conn->prepare($selectGoodsStr.$value->cate_id);
                    $selectGoods->execute();
                    $selectGoodsResult = $selectGoods->setFetchMode(PDO::FETCH_ASSOC);
                    $selectGoodsRes = $selectGoods->fetchAll();
                    foreach($selectGoodsRes as $key1 => $value1) {
                        $value1 = (object) $value1;
                        $count += getQuantity($value1->goods_id, $conn);
                    }
                    array_push($dataArray,$count);
            }
        }
    } else {
        if(!checkRight($conn,521)){
            $response->data = null;
            $response->meta->status = 403;
            $response->meta->message = '权限不足';
            echo json_encode($response);
            exit;
        } 
        $timeArray += getTime($goods_id, $conn);
    }
    
    
    

    // 返回数据对象
    $response = (object) array(
        'data' => (object) array(
        ),
        'meta' => (object) array(
            'status' => 200,
            'message' => '获取订单统计数据成功！'
        )
    );
    if($time_statistic){
        $response->data = $timeArray;
    }else {
        $response->name = $nameArray;
        $response->data = $dataArray;
    }
    echo json_encode($response);
}
catch(PDOException $e){
    echo $e->getMessage();
}
$conn = null;


?>