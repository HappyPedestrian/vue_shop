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
$roleId = $data->roleId;
$rightId = $data->rightId;

$quote = "'";


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
        'message' => '删除权限成功'

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
            if(array_search($rightIdStr,$rightsArr)){
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
    if(!checkRight($conn,314)){
        $response->data = null;
        $response->meta->status = 403;
        $response->meta->message = '权限不足';
        echo json_encode($response);
        exit;
    }
    $preSqls = $conn->prepare("SELECT firstLevel,secondLevel,thirdLevel FROM `roles` where id=".$quote.$roleId.$quote);
    $isRoleSucces = $preSqls->execute();
    $result = $preSqls->setFetchMode(PDO::FETCH_ASSOC);
    $res = $preSqls->fetchAll();

    $resArr = array();

    $firstLevel = array();
    $secondLevel = array();
    $thirdLevel = array();
    $roleArr = array();


    $res[0] = (object) $res[0];
    $rightsStr = $res[0]->firstLevel.','.$res[0]->secondLevel.','.$res[0]->thirdLevel;
        $rightsId = explode(',',$rightsStr);

    foreach($rightsId as $key1 => $value1){
            $rightsId[$key1] = (int) $rightsId[$key1];
    }

    foreach($rightsId as $key => $value){
        if($value==$rightId || floor($value/100)==$rightId || floor($value/10)==$rightId){
            unset($rightsId[$key]);
        }
    }
    $rightsId = array_values($rightsId);

    //更新后的权限id字符串

    foreach($rightsId as $value){
        if($value < 10){
            array_push($firstLevel,$value);
        }else if($value <100){
            array_push($secondLevel,$value);
        }else{
            array_push($thirdLevel,$value);
        }
    }
    
    //更新后的权限id字符串
    $rightsStr = implode(',',$firstLevel).",".implode(',',$secondLevel).",".implode(',',$thirdLevel);

    $updatePreSqls = $conn->prepare("UPDATE `roles` SET firstLevel=".$quote.implode(',',$firstLevel).$quote.",secondLevel=".$quote.implode(',',$secondLevel).$quote.",thirdLevel=".$quote.implode(',',$thirdLevel).$quote." WHERE id =".$quote.$roleId.$quote);
    $isUpdate = $updatePreSqls->execute();

    if($isUpdate){
        $firstLevel = array();
        $secondLevel = array();
        $thirdLevel = array();
        $rightSqls = $conn->prepare("SELECT * FROM `rights` WHERE id in(".$rightsStr.")");
        $rightSqls->execute();
        $rightsResult = $rightSqls->setFetchMode(PDO::FETCH_ASSOC);
        $rightsRes = $rightSqls->fetchAll();

        uasort($rightsRes,'compare_arr');

        foreach($rightsRes as $key => $value){
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
    

$response->data = $resArr;
    
}
catch(PDOException $e){
    // echo $e->getMessage();
    $response->data = null;
    $response->meta->status = 500;
    $response->meta->message = "删除权限失败!";
}
echo json_encode($response);
$conn = null;


?>