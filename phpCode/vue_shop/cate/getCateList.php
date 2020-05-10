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
$type = $_GET['type'];
if($type == 3){
    $pageNum = 0;
    $pageSize = 0;
    $getGoods = false;
    if(isset($_GET['pageNum'])){
        $pageNum = $_GET['pageNum'];
    }
    if(isset($_GET['pageSize'])){
        $pageSize = $_GET['pageSize'];
    }
    if(isset($_GET['getGoods'])){
        $getGoods = $_GET['getGoods'];
    }
    $start = ($pageNum-1)*$pageSize;
}


// $type = 3;
// $pageNum = 2;
// $pageSize = 3;
// $start = ($pageNum-1)*$pageSize;

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

    $resArr = array();
    $firstLevel = array();
    $secondLevel = array();
    $thirdLevel = array();
    // 记录各级Id数组
    $firstLevelIds = array();
    $secondLevelIds = array();
    $thirdLevelIds = array();
    // 各级id字符串
    $firstIdsStr = "";
    $secondIdsStr = "";
    $thirdIdsStr = "";

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
    
    //当type为3时
    if($type==3){
        //查询一级元素
        $firstLevelPreSqls = $conn->prepare("SELECT * FROM `category` where cate_level=0");
        $firstLevelPreSqls->execute();
        $result = $firstLevelPreSqls->setFetchMode(PDO::FETCH_ASSOC);
        $res = $firstLevelPreSqls->fetchAll();
        $total = count($res);

        if($pageNum*$pageSize>$total){
            $pageSize = $total - $start;
        }
        if(isset($_GET['pageNum'])){
            $selectFirstLevelStr = "SELECT * from (SELECT * FROM `category` where cate_level=0) as first_level limit $start,$pageSize";
        }else{
            $selectFirstLevelStr = "SELECT * from (SELECT * FROM `category` where cate_level=0) as first_level";
        }
        
        $theFirstLevelPreSqls = $conn->prepare($selectFirstLevelStr);
        $theFirstLevelPreSqls->execute();
        $result = $theFirstLevelPreSqls->setFetchMode(PDO::FETCH_ASSOC);
        $res = $theFirstLevelPreSqls->fetchAll();

        $response = (object) array(
            'data' => (object) array(
                'pageNum' => $pageNum,
                'pageSize' => $pageSize,
                'total' => $total,
                'result' => array()
            ),
            'meta' => (object) array(
                'status' => 200,
                'message' => '获取分类列表成功！'
            )
        );


        // 对一级元素进行处理
            foreach($res as $key => $value){
                $value = (object) $value;
                array_push($firstLevel,$value);
                array_push($firstLevelIds,$value->cate_id);
            }
        //选择二级元素
        
        foreach($firstLevelIds as $key=>$value){
            if($key == 0){
                $firstIdsStr = $firstIdsStr."'".$value."'";
            }else{
                $firstIdsStr = $firstIdsStr.",'".$value."'";
            }   
        }
        $selectSecondLevelStr = "SELECT * FROM `category` where cate_pid in (".$firstIdsStr."'')";
        $secondLevelPreSqls = $conn->prepare($selectSecondLevelStr);
        $secondLevelPreSqls->execute();
        $result = $secondLevelPreSqls->setFetchMode(PDO::FETCH_ASSOC);
        $res = $secondLevelPreSqls->fetchAll();
        // 对二级元素进行处理
        foreach($res as $key => $value){
            $value = (object) $value;
            array_push($secondLevel,$value);
            array_push($secondLevelIds,$value->cate_id);
        }    

        //选择三级元素
        foreach($secondLevelIds as $key=>$value){
            if($key == 0){
                $secondIdsStr = $secondIdsStr."'".$value."'";
            }else{
                $secondIdsStr = $secondIdsStr.",'".$value."'";
            }
        }
        $selectThirdLevelStr = "SELECT * FROM `category` where cate_pid in (".$secondIdsStr."'')";
        $thirdLevelPreSqls = $conn->prepare($selectThirdLevelStr);
        $thirdLevelPreSqls->execute();
        $result = $thirdLevelPreSqls->setFetchMode(PDO::FETCH_ASSOC);
        $res = $thirdLevelPreSqls->fetchAll();
        // 对三级元素进行处理
        foreach($res as $key => $value){
            $value = (object) $value;
            array_push($thirdLevel,$value);
        } 
    }else if($type == 2){//当$type为2时
        $response = (object) array(
            'data' => array(),
            'meta' => (object) array(
                'status' => 200,
                'message' => '获取分类列表成功！'
            )
        );
        //查询一级元素
        $allLevelPreSqls = $conn->prepare("SELECT * FROM `category`");
        $allLevelPreSqls->execute();
        $result = $allLevelPreSqls->setFetchMode(PDO::FETCH_ASSOC);
        $res = $allLevelPreSqls->fetchAll();

        foreach($res as $key => $value){
            $value = (object) $value;
            if($value->cate_level == 0){
                array_push($firstLevel,$value);
            }else if($value->cate_level == 1){
                array_push($secondLevel,$value);
            }
        }

    }

        foreach($firstLevel as $cate1){
            $cate1 = (object) $cate1;
            $cateInfo1 = (object) array(
                'cate_id' => $cate1->cate_id,
                'cate_name' => $cate1->cate_name,
                'cate_pid' => $cate1->cate_pid,
                'cate_level' => $cate1->cate_level,
                'cate_delete' => $cate1->cate_delete==0?false:true,
                'children' => array()
            );
            if($type >= 2){
                foreach($secondLevel as $cate2){
                    $cate2 = (object) $cate2;
                    if($cate2->cate_pid == $cate1->cate_id){
                        if($type == 3){
                            $cateInfo2 = (object) array(
                                'cate_id' => $cate2->cate_id,
                                'cate_name' => $cate2->cate_name,
                                'cate_pid' => $cate2->cate_pid,
                                'cate_level' => $cate2->cate_level,
                                'cate_delete' => $cate2->cate_delete==0?false:true,
                                'children' => array()
                            );
                        }else if($type == 2){
                            $cateInfo2 = (object) array(
                                'cate_id' => $cate2->cate_id,
                                'cate_name' => $cate2->cate_name,
                                'cate_pid' => $cate2->cate_pid,
                                'cate_level' => $cate2->cate_level,
                                'cate_delete' => $cate2->cate_delete==0?false:true
                            );
                        }
                        
                        if($type >= 3){
                            foreach($thirdLevel as $cate3){
                                if($cate3->cate_pid == $cate2->cate_id){
                                    if($getGoods){
                                    }
                                    $cateInfo3 = (object) array(
                                        'cate_id' => $cate3->cate_id,
                                        'cate_name' => $cate3->cate_name,
                                        'cate_pid' => $cate3->cate_pid,
                                        'cate_level' => $cate3->cate_level,
                                        'cate_delete' => $cate3->cate_delete==0?false:true
                                    );
                                    if($getGoods){
                                        $cateInfo3->children = array();// 添加children属性，存储商品信息

                                        $selectGoodsStr = "SELECT goods_id,goods_name,goods_state,goods_price FROM `goods` where goods_cate=".$cateInfo3->cate_id;
                                        $selectGoodsPreSqls = $conn->prepare($selectGoodsStr);
                                        $selectGoodsPreSqls->execute();
                                        $goodsResult = $selectGoodsPreSqls->setFetchMode(PDO::FETCH_ASSOC);
                                        $goodsRes = $selectGoodsPreSqls->fetchAll();

                                        foreach($goodsRes as $goodsKey => $goods){
                                            $goods = (object) $goods;
                                            $goodsInfo = (object) array(
                                                'cate_id' => $goods->goods_id,
                                                'cate_name' => $goods->goods_name,
                                                'goods_state' => $goods->goods_state,
                                                'goods_price' =>$goods->goods_price
                                            );
                                            array_push($cateInfo3->children,$goodsInfo);
                                        }
                                    }
                                    array_push($cateInfo2->children,$cateInfo3);
                            }
                        }
                    }
                    array_push($cateInfo1->children,$cateInfo2);
                }
            }
            array_push($resArr,$cateInfo1);
        }
    }

    if (!empty($resArr)) {
        if($type == 3){
            $response->data->result = $resArr;
        }else if($type == 2){
            $response->data = $resArr;
        }
       
    }else {
        $response->meta->message = '分类列表为空';
        $response->meta->status = 404;
    }
    echo json_encode($response);
}
catch(PDOException $e){
    echo $e->getMessage();
}
$conn = null;


?>