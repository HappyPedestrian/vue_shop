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

$servername = "localhost";
$username = "root";
$password = "123456";
$dbname = "vue_shop";

$token = "'".$_GET['Authorization']."'";
// $token = "'3096bdbe2a4bd79924e7b12a12961140'";

$response = (object) array(
    'data' => array(),
    'meta' => (object) array(
        'message' => '获取菜单成功！',
        'status' => 200
    )
);

function addSecondMenu($conn,$firstMenu,$secondRightsArr){
    foreach($secondRightsArr as $secondRightKey => $secondRight){
        $secondRight = (int) $secondRight;
        $RightNamePreSqls = $conn->prepare("SELECT authName,path,pid FROM `rights` where id=".$secondRight);
        $RightNamePreSqls->execute();
        $rightNameResult = $RightNamePreSqls->setFetchMode(PDO::FETCH_ASSOC);
        $rightNameRes = $RightNamePreSqls->fetchAll();
        if(((object) $rightNameRes[0])->pid === $firstMenu->id){
            $secondMenu = (object) array(
                'id' => $secondRight,
                'authname' => ((object) $rightNameRes[0])->authName,
                'path' => ((object) $rightNameRes[0])->path
            );
            array_push($firstMenu->children,$secondMenu);
        }
        
    }
}


try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname",$username,$password);
    //设置PDO错误模式，抛出异常
    $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $conn->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
    $userNamePreSqls = $conn->prepare("SELECT user_name FROM `login_status` where token=".$token);
    $userNamePreSqls->execute();
    $userNameResult = $userNamePreSqls->setFetchMode(PDO::FETCH_ASSOC);
    $userNameRes = $userNamePreSqls->fetchAll();
    if(count($userNameRes) === 0){
        $response->meta->status = 403;
        $response->meta->message = "获取菜单失败";
        echo json_encode($response);
        exit;
    }

    $userName = "'".((object) $userNameRes[0])->user_name."'";
    $roleIdPreSqls = $conn->prepare("SELECT role_id FROM `users` where user_name=".$userName);
    $roleIdPreSqls->execute();
    $roleIdResult = $roleIdPreSqls->setFetchMode(PDO::FETCH_ASSOC);
    $roleIdRes = $roleIdPreSqls->fetchAll();

    if(count($roleIdRes) !== 0){
        $roleId = ((object) $roleIdRes[0])->role_id;
        $roleRightPreSqls = $conn->prepare("SELECT firstLevel,secondLevel FROM `roles` where id=".$roleId);
        $roleRightPreSqls->execute();
        $roleRightResult = $roleRightPreSqls->setFetchMode(PDO::FETCH_ASSOC);
        $roleRightRes = $roleRightPreSqls->fetchAll();
        if(count($roleRightRes) !== 0){

            $firstRightsStr = ((object) $roleRightRes[0])->firstLevel;
            $secondRightsStr = ((object) $roleRightRes[0])->secondLevel;
            $firstRightsArr = explode(',', $firstRightsStr);
            $secondRightsArr = explode(',', $secondRightsStr);
            
            foreach($firstRightsArr as $firstRightKey => $firstRight){
                $firstRight = (int) $firstRight;
                $RightNamePreSqls = $conn->prepare("SELECT authName,path FROM `rights` where id=".$firstRight);
                $RightNamePreSqls->execute();
                $rightNameResult = $RightNamePreSqls->setFetchMode(PDO::FETCH_ASSOC);
                $rightNameRes = $RightNamePreSqls->fetchAll();
                $menu = (object) array(
                    'id' => $firstRight,
                    'authname' => ((object) $rightNameRes[0])->authName,
                    'path' => ((object) $rightNameRes[0])->path,
                    'order' => 0,
                    'children' => array(
                    )
                );
                switch($firstRight){
                    case 1:
                        $menu->order = '3';
                        break;
                    case 2:
                        $menu->order = '4';
                        break;
                    case 3:
                        $menu->order = '2';
                        break;
                    case 4:
                        $menu->order = '1';
                        break;
                    case 5:
                        $menu->order = '5';
                        break;
                    default:
                        break;
                }
                addSecondMenu($conn,$menu,$secondRightsArr);
                array_push($response->data,$menu);
            }
        }
        
    }
    echo json_encode($response);
}
catch(PDOException $e){
    echo $e->getMessage();
}
$conn = null;

//  表单提交后...
// //  清除一些空白符号
// $menulist = (object) array(

//     'data' => array(
//         (object) array(
//             'id' => '100',
//             'authname' => '用户管理',
//             'order' => 1,
//             'path' => 'user',
//             'children' => array(
//                 (object) array(
//                     'id' => '101',
//                     'authname' => '用户列表',
//                     'path' => 'users',
//                     'children' => array()
//                 )
//             )
//         ),
//         (object) array(
//             'id' => '110',
//             'authname' => '权限管理',
//             'order' => 2,
//             'path' => 'rights',
//             'children' => array(
//                 (object) array(
//                     'id' => '111',
//                     'authname' => '角色列表',
//                     'path' => 'roles',
//                     'children' => array()
//                 ),
//                 (object) array(
//                     'id' => '112',
//                     'authname' => '权限列表',
//                     'path' => 'rights',
//                     'children' => array()
//                 )
//             )
//         ),
//         (object) array(
//             'id' => '120',
//             'authname' => '商品管理',
//             'order' => 3,
//             'path' => 'goods',
//             'children' => array(
//                 (object) array(
//                     'id' => '121',
//                     'authname' => '商品列表',
//                     'path' => 'goodsList',
//                     'children' => array()
//                 ),
//                 (object) array(
//                     'id' => '122',
//                     'authname' => '分类参数',
//                     'path' => 'categoryParam',
//                     'children' => array()
//                 ),
//                 (object) array(
//                     'id' => '123',
//                     'authname' => '商品分类',
//                     'path' => 'goodsCategory',
//                     'children' => array()
//                 )
//             )
//         ),
//         (object) array(
//             'id' => '130',
//             'authname' => '订单管理',
//             'order' => 4,
//             'path' => 'order',
//             'children' => array(
//                 (object) array(
//                     'id' => '131',
//                     'authname' => '订单列表',
//                     'path' => 'orderList',
//                     'children' => array()
//                 )
//             )
//         ),
//         (object) array(
//             'id' => '140',
//             'authname' => '数据统计',
//             'order' => 5,
//             'path' => 'dataStatistic',
//             'children' => array(
//                 (object) array(
//                     'id' => '141',
//                     'authname' => '商品销量统计',
//                     'path' => 'orderNumberStatistics',
//                     'children' => array()
//                 ),
//                 (object) array(
//                     'id' => '142',
//                     'authname' => '商品时间统计',
//                     'path' => 'orderTimeStatistics',
//                     'children' => array()
//                 ),
//                 (object) array(
//                     'id' => '143',
//                     'authname' => '商品推荐分析',
//                     'path' => 'goodsAnalyze',
//                     'children' => array()
//                 )
//             )
//         )
//     ),
//     'meta' => (object) array(
//         'message' => '获取菜单成功！',
//         'status' => 200
//     )
        
//     );
    // echo json_encode($menulist);


?>