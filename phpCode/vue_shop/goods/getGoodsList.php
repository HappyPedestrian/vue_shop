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
    if(!checkRight($conn,114)){
        $response->data = null;
        $response->meta->status = 403;
        $response->meta->message = '权限不足';
        echo json_encode($response);
        exit;
    }
    $totalPreSqls = $conn->prepare("SELECT * FROM `goods` where goods_name LIKE".$query);
    $totalPreSqls->execute();
    $result = $totalPreSqls->setFetchMode(PDO::FETCH_ASSOC);
    $res = $totalPreSqls->fetchAll();
    $total = count($res);

    $response = (object) array(
        'data' => (object) array(
            'goods' => array(),
            'total' => $total
        ),
        'meta' => (object) array(
            'status' => 200,
            'message' => '获取商品列表成功！'
        )
    );
    $start = ($pageNum -1) * $pageSize;
    if($start + $pageSize > $total){
        $pageNum = intval($total / $pageSize) + 1;
        $start = ($pageNum - 1) * $pageSize;
        $pageSize = $total - $start;
    }
    $response->pageNum = intval($pageNum);

    $selectTheGoodsSql = $conn->prepare("SELECT * FROM `goods` where goods_name LIKE".$query." LIMIT $start,$pageSize");
    $selectTheGoodsSql->execute();
    $result = $selectTheGoodsSql->setFetchMode(PDO::FETCH_ASSOC);
    $res = $selectTheGoodsSql->fetchAll();
    // 对一些数据进行处理
    foreach($res as $key=>$value){
        $value = (object) $value;
        // 图片返回url地址
        $value->goods_pics = trim($value->goods_pics);
        $pic_infos = explode(" ",trim($value->goods_pics));// 图片名数组

        $cate_array = array();

        $selectParentCateSql = $conn->prepare("SELECT cate_pid FROM `category` where cate_id=".$value->goods_cate);
        $selectParentCateSql->execute();
        $cateResult = $selectParentCateSql->setFetchMode(PDO::FETCH_ASSOC);
        $cateRes = $selectParentCateSql->fetchAll();

        $cateRes = (object) $cateRes[0];

        while($cateRes->cate_pid !== 0){
            array_push($cate_array,$cateRes->cate_pid);

            $selectParentCateSql = $conn->prepare("SELECT cate_pid FROM `category` where cate_id=".$cateRes->cate_pid);
            $selectParentCateSql->execute();
            $cateResult = $selectParentCateSql->setFetchMode(PDO::FETCH_ASSOC);
            $cateRes = $selectParentCateSql->fetchAll();
            $cateRes = (object) $cateRes[0];
        }
        
        // 对数组长度不足3进行处理
        $len = sizeof($cate_array);
        if($len < 2){
            $pre_path = 'http://localhost/vue/vue_shop/image/smileCat.jpg';
        }else{
            $pre_path = 'http://localhost/vue/vue_shop/image/'.$cate_array[1].'/'.$cate_array[0].'/'.$value->goods_cate.'/';
        }
        foreach($pic_infos as $pic_key => $pic_value){
            if($pic_value === ''){
                $pic_infos = array();
            } else {
                $pic_url = $pre_path.$pic_value;
                // 将图片名数组转为图片信息数组
                $pic_infos[$pic_key] = (object) array(
                    'pic_url' => $pic_url,// 图片url
                    'pic_name' => $pic_value// 图片名字
                );
        }
            }
            
        $value->goods_pics = $pic_infos;
        array_push($response->data->goods, $value);
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