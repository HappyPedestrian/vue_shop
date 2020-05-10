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
$quote = "'";
$goods_name = $data->goods_name;// 商品名
$goods_price = $data->goods_price; // 商品价格
$goods_seller = $data->goods_seller;// 商品所属店铺
$goods_weight = $data->goods_weight;// 商品重量
$goods_number = $data->goods_number;// 商品数量

$goods_cate = $data->goods_cate;// 商品分类数组
$goods_last_cate = $goods_cate[count($goods_cate) - 1];
$goods_introduce = $data->goods_introduce;// 商品介绍
$pics = $data->pics; // 商品图片在服务端地址
$attrs = $data->attrs;//商品动态参数和静态属性
$picsStr = '';
foreach($pics as $key => $value){
    $value = (object)$value;
    $picsStr = $picsStr. ' '.$value->pic;
}

// $goods_name = 'aaa';// 商品名
// $goods_price = 10; // 商品价格
// $goods_weight = 20;// 商品重量
// $goods_number = 30;// 商品数量

// $goods_cate = '1,6,7';// 商品分类
// $goods_introduce = 'test word';// 商品介绍
// $pics = null; // 商品图片在服务端地址
// $attrs = '';//商品动态参数和静态属性


  //清空文件夹函数和清空文件夹后删除空文件夹函数的处理
  function deldir($path){
   //如果是目录则继续
   if(is_dir($path)){
    //扫描一个文件夹内的所有文件夹和文件并返回数组
   $p = scandir($path);
   foreach($p as $val){
    //排除目录中的.和..
    if($val !="." && $val !=".."){
     //如果是目录则递归子目录，继续操作
     if(is_dir($path.$val)){
      //子目录中操作删除文件夹和文件
      deldir($path.$val.'/');
      //目录清空后删除空文件夹
      @rmdir($path.$val.'/');
     }else{
      //如果是文件直接删除
      unlink($path.$val);
     }
    }
   }
  }
  }

// 移动图片到相应目录

// 首先创建目录
//$cate_array = array(1,2,3);

 //要创建的多级目录
 $dir_str = '../image/';
 foreach($goods_cate as $goodsCateKey => $cate){
     $dir_str = $dir_str.$cate.'/';
 }
 //判断目录存在否，存在给出提示，不存在则创建目录
 if (!is_dir($dir_str)){  
     //第三个参数是“true”表示能创建多级目录，iconv防止中文目录乱码
    //  $res=mkdir(iconv("UTF-8", "GBK", $dir_str),0777,true); 
    $res=mkdir($dir_str,0777,true); 
     if (!$res){
         echo "目录 $dir_str 创建失败";
     }
 }

 // 移动图片
foreach($pics as $key => $value){
    $tmp_image = '../tmp_images/'.$value->pic;// 缓存目录
    $path_in_folder = $dir_str.$value->pic;// 要拷贝到的目录
    copy($tmp_image,$path_in_folder); //拷贝到新目录
    unlink($tmp_image);
}

// 删除缓存的图片
 //调用函数，传入路径
//  deldir('../tmp_images/');

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

    $response = (object) array(
        'data' => null,
        'meta' => (object) array(
            'status' => 200,
            'message' => ''
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
    if(!checkRight($conn,112)){
        $response->data = null;
        $response->meta->status = 403;
        $response->meta->message = '权限不足';
        echo json_encode($response);
        exit;
    }    
    $selectGoodsByNameSqls = $conn->prepare("SELECT * FROM `goods` where goods_name=".$quote.$goods_name.$quote);
    $selectGoodsByNameSqls->execute();
    $result = $selectGoodsByNameSqls->setFetchMode(PDO::FETCH_ASSOC);
    $res = $selectGoodsByNameSqls->fetchAll();
    // 如果数据库里没有同名的商品
    if(count($res) == 0){
        $creat_time = strtotime(date("Y-m-d  h:i:sa"));// 当前时间

        $insertGoodsPreSqls = $conn->prepare("INSERT INTO `goods` (goods_name,goods_cate,goods_price,goods_number,goods_weight,goods_state,add_time,upd_time,goods_pics,goods_seller ) VALUES (".$quote.$goods_name.$quote.",".$goods_last_cate.",".$goods_price.",".$goods_number.",".$goods_weight.",".$quote.$goods_introduce.$quote.",".$creat_time.",".$creat_time.",".$quote.$picsStr.$quote.",".$goods_seller.")");
        $isSuccess = $insertGoodsPreSqls->execute();

        $selectGoodsid = $conn->prepare("select  LAST_INSERT_ID()");
        $selectGoodsid->execute();
        $goodsIdResult = $selectGoodsid->setFetchMode(PDO::FETCH_ASSOC);
        $goodsIdRes = $selectGoodsid->fetchAll();
        if(count($goodsIdRes) !== 0){
            $goodsIdRes = (object) $goodsIdRes[0];
            // foreach($goodsIdRes as $key => $id){
            //     $goods_id = $id;
            // }
            // echo json_encode(((array)$goodsIdRes)["LAST_INSERT_ID()"]);
            // // $goods_id = ((object) $goodsIdRes[0])->"LAST_INSERT_ID()";
            // echo $goods_id;
            $goods_id = ((array)$goodsIdRes)["LAST_INSERT_ID()"];
            foreach($attrs as $attrKey => $attrInfo){
                $insertGoodsAttrPreSqls = $conn->prepare("INSERT INTO `goods_attr` (goods_id,attr_id,attr_values) VALUES (".$goods_id.",".$attrInfo->attr_id.",".$quote.$attrInfo->attr_values.$quote.")");
                $isSuccess = $insertGoodsAttrPreSqls->execute();
            }
        }
        


        if($isSuccess) {
            $response->meta->status = 200;
            $response->meta->message = '添加商品成功！';
        }else {
            $response->meta->status = 500;
            $response->meta->message = '添加商品失败！';
        }
    }else{// 否则
        $response->meta->status = 400;
        $response->meta->message = '添加商品失败，商品名重复！';
    }
    
    echo json_encode($response);
}
catch(PDOException $e){
    echo $e->getMessage();
}
$conn = null;


?>