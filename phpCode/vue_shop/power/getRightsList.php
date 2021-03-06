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
function compare_arr($x,$y){
    $x = (object) $x;
    $y = (object) $y;
    if($x->id<$y->id){
	    return -1;
	}else if($x->id>$y->id){
	    return 1;
	}else{
	    return 0;
	}
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
    if(!checkRight($conn,321)){
        $response->data = null;
        $response->meta->status = 403;
        $response->meta->message = '权限不足';
        echo json_encode($response);
        exit;
    }
    $preSqls = $conn->prepare("SELECT * FROM `rights`");
    $preSqls->execute();
    $result = $preSqls->setFetchMode(PDO::FETCH_ASSOC);
    $res = $preSqls->fetchAll();
    $response = (object) array(
        'data' => array(),
        'meta' => (object) array(
            'status' => 200,
            'message' => '获取权限列表成功！'
        ));

    uasort($res,'compare_arr');
    $resArr = array();
    if($type == 'list'){//返回列表数据
        foreach($res as $value){
            array_push($resArr,$value);
        }
    }else if($type == 'tree'){//返回树形数据
        $firstLevel = array();
        $secondLevel = array();
        $thirdLevel = array();

        foreach($res as $key => $value){
            $value = (object) $value;
            if($value->id < 10){
                array_push($firstLevel,$value);
            }else if($value->id < 100){
                array_push($secondLevel,$value);
            }else{
                array_push($thirdLevel,$value);
            }
        }

        foreach($firstLevel as $right1){
            $right1 = (object) $right1;
                $rightInfo1 = (object) array(
                    'id' => $right1->id,
                    'authName' => $right1->authName,
                    'path' => $right1->path,
                    'children' => array()
                );
                foreach($secondLevel as $right2){
                    $right2 = (object) $right2;
                    if(floor(($right2->id)/10) == $right1->id){
                        $rightInfo2 = (object) array(
                            'id' => $right2->id,
                            'authName' => $right2->authName,
                            'path' => $right2->path,
                            'children' => array()
                        );

                        foreach($thirdLevel as $right3){
                            if(floor(($right3->id)/10) == $right2->id){
                                $rightInfo3 = (object) array(
                                    'id' => $right3->id,
                                    'authName' => $right3->authName,
                                    'path' => $right3->path
                                );
                                array_push($rightInfo2->children,$rightInfo3);
                            }
                        }
                        array_push($rightInfo1->children,$rightInfo2);
                    }
                }
            array_push($resArr,$rightInfo1);
        }
    }
    

    if (!empty($res)) {
        $response->data = $resArr;
        }
         else {
        $response->meta->message = '权限列表为空';
        $response->meta->status = 404;
    }
    echo json_encode($response);
}
catch(PDOException $e){
    echo $e->getMessage();
}
$conn = null;


?>