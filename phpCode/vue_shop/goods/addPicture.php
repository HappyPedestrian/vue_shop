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
$conn = new PDO("mysql:host=$servername;dbname=$dbname",$username,$password);
//设置PDO错误模式，抛出异常
$conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$conn->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);

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

if(!checkToken($conn)){
    $response->data = null;
    $response->meta->status = 403;
    $response->meta->message = '无效的token';
    echo json_encode($response);
    exit;
}

$response = (object) array(
    'data' => (object) array(
        'tmp_path' => '',
        'url' => ''
    ),
    'meta' => (object) array(
        'status' => 200,
        'message' => '上传图片成功！'
    )
    );

$name= isset($_FILES['file']['name'])?$_FILES['file']['name']:""; //图片名字
$size=isset($_FILES['file']['size'])?$_FILES['file']['size']:''; //图片大小
$type=isset($_FILES['file']['type'])?$_FILES['file']['type']:''; //图片类型

$imageformat=array("image/jpeg","image/pjpeg","image/gif","image/png","image/x-png");
if($size!=0 && $size<=1024*1000 && in_array($type,$imageformat)){  //搜索数组中是否存在指定的值
    if($type=="image/jpeg" || $type=="image/jpeg"){
        $ext=".jpg";
    }else if($type=="image/png" || $type=="image/x-png"){
        $ext=".png";
    }else{
        $ext=".gif";
    }
    $up_name=date("Ymdhis").$ext;   //Ymdhis年月日时分秒
    $path_in_folder = '../tmp_images/'.$up_name;
    move_uploaded_file($_FILES['file']['tmp_name'],$path_in_folder);

    //$_FILES[字段名][tmp_name]保存的是文件上传到服务器临时文件夹之后的文件名
    //move_uploaded_file(规定要移动的文件，规定文件的新位置)函数将上传的文件移动到新的位置
    $response->data->tmp_path = $up_name;
    $response->data->url = 'http://localhost/vue/vue_shop/tmp_images/'.$up_name;
}else{
    $response->meta->status = 400;
    $response->meta->message = '上传图片失败！';
}

echo json_encode($response);

// $conn = null;


?>