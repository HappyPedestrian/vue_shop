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
$attr_id = $data->attr_id;
$attr_name = $data->attr_name;
$goods_id = $data->goods_id;
$attr_values = $data->attr_values;

// $attr_id = 1;
// $attr_name = "版式";
// $goods_id = 26;
// $attr_values = "sdfjlk";

$quote = "'";

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


try {
    $response = (object) array(
        "data" => null,
        "meta" => (object) array(
        "message" => "更新参数成功",
        "status" => 200
        )
    );
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
    // 首先查看数据库中是否存有值
    $selectGoodsAttrVal = $conn->prepare("select  attr_values FROM `goods_attr` WHERE goods_id=".$goods_id." AND attr_id=".$attr_id);
    $selectGoodsAttrVal->execute();
    $goodsAttrResult = $selectGoodsAttrVal->setFetchMode(PDO::FETCH_ASSOC);
    $goodsAttrRes = $selectGoodsAttrVal->fetchAll();


    if(count($goodsAttrRes) !== 0){
        $preSqls = $conn->prepare("UPDATE `goods_attr` set attr_values=".$quote.$attr_values.$quote." WHERE attr_id=".$attr_id." AND goods_id=".$goods_id);
        $success1 = $preSqls->execute();

        $preSqls = $conn->prepare("UPDATE `attrs` set attr_name=".$quote.$attr_name.$quote." WHERE attr_id=".$attr_id);
        $success2 = $preSqls->execute();
    if(!$success1 && !$success2){
        $response->meta->status = 500;
        $response->meta->message = "更新参数失败";
    }
    }else{
        $insertGoodsAttrVal = $conn->prepare("INSERT INTO goods_attr (goods_id,attr_id,attr_values) VALUES (".$goods_id.",".$attr_id.",".$quote.$attr_values.$quote.")");
        $insertGoodsAttrVal->execute();
    }
    
    echo json_encode($response);
}
catch(PDOException $e){
    echo $e->getMessage();
}
$conn = null;


?>