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
$data = json_decode(file_get_contents("php://input"));
$goods_id = $data->goods_id;
$goods_name = $data->goods_name;
$goods_cate = $data->goods_cate;
$goods_seller = $data->goods_seller;
$goods_price = $data->goods_price;
$goods_number = $data->goods_number;
$goods_weight = $data->goods_weight;
$goods_state = $data->goods_state;
$goods_delete = $data->goods_delete;
$goods_pics = $data->goods_pics;
$add_pics = $data->addPics;
$quote = "'";

$deletePics = array();// 要删除的图片
foreach($goods_pics as $key => $value) {
    if(property_exists($value, 'isSave') && $value->isSave === true){
        array_push($deletePics,$value->pic_name);
    }
}
$upd_time = strtotime(date("Y-m-d  h:i:sa"));// 当前时间

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

function checkRight($conn,$rightId,$response){// 返回true则有效
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
    if(!checkRight($conn,111,$response)){
        $response->data = null;
        $response->meta->status = 403;
        $response->meta->message = '权限不足';
        echo json_encode($response);
        exit;
    }
    // 添加图片
    $dir_str = '../image/';
    foreach($goods_cate as $goodsCateKey => $cate){
        $dir_str = $dir_str.$cate.'/';
    }
    
    if (!is_dir($dir_str)){  
        //第三个参数是“true”表示能创建多级目录，iconv防止中文目录乱码
       //  $res=mkdir(iconv("UTF-8", "GBK", $dir_str),0777,true); 
       $res=mkdir($dir_str,0777,true); 
        if (!$res){
            echo "目录 $dir_str 创建失败";
        }
    }
    // 移动图片
    $addPicNameArr = array();
   foreach($add_pics as $key => $value){
       array_push($addPicNameArr,$value->pic);
       $tmp_image = '../tmp_images/'.$value->pic;// 缓存目录
       $path_in_folder = $dir_str.$value->pic;// 要拷贝到的目录
       copy($tmp_image,$path_in_folder); //拷贝到新目录
       unlink($tmp_image);
   }
    // 删除图片 
    $goodsPicsPreSqls = $conn->prepare("SELECT goods_pics FROM `goods` where goods_id=".$goods_id);
    $goodsPicsPreSqls->execute();
    $goodsPicsResult = $goodsPicsPreSqls->setFetchMode(PDO::FETCH_ASSOC);
    $goodsPicsRes = $goodsPicsPreSqls->fetchAll();

    $picStr = ((object) $goodsPicsRes[0])->goods_pics;
    $picStr = trim($picStr);
    $picArr = explode(" ",$picStr);
    foreach($deletePics as $delKey => $pic){
        $result = array_search($pic, $picArr);
        if($result !== false){
            $finalDir = $dir_str.$pic;
            unlink($finalDir);
            unset($picArr[$result]);
        }
    }

    $pics = implode(" ",$picArr);
    if(count($addPicNameArr) !== 0){
        $pics = $pics." ".implode(" ",$addPicNameArr);
    }


    $preSqls = $conn->prepare("UPDATE `goods` SET goods_name=".$quote.$goods_name.$quote.",goods_cate=".$quote.$goods_cate[2].$quote.",goods_price=".$goods_price.",goods_number=".$goods_number.",goods_weight=".$goods_weight.",goods_state=".$quote.$goods_state.$quote.",upd_time=".$upd_time.",goods_delete=".$goods_delete.",goods_pics=".$quote.$pics.$quote.",goods_seller=".$goods_seller." WHERE goods_id =".$goods_id);
    $isSuccuss = $preSqls->execute();
    $response = (object) array(
        'meta' => (object) array(
            'status' => 200,
            'message' => '修改成功！'
        ));
    if(!$isSuccuss){
        $response->meta->status = 500;
        $response->meta->message = '修改失败！';
    }
}
catch(PDOException $e){
    echo $e->getMessage();
}
echo json_encode($response);
$conn = null;


?>