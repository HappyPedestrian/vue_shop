<?php

// $cate_array =  explode(',',$goods_cate);
$cate_array = array(1,2,3);

 //要创建的多级目录
 $dir_str = '../image/'.$cate_array[0].'/'.$cate_array[1].'/'.$cate_array[2].'/';
 //判断目录存在否，存在给出提示，不存在则创建目录
 if (is_dir($dir_str)){  
     echo "对不起！目录 " . $dir_str . " 已经存在！";
 }else{
     //第三个参数是“true”表示能创建多级目录，iconv防止中文目录乱码
     $res=mkdir(iconv("UTF-8", "GBK", $dir_str),0777,true); 
     if ($res){
         echo "目录 $dir_str 创建成功";
     }else{
         echo "目录 $dir_str 创建失败";
     }
 }

// $dir_str = $dir_str.$cate_array[1].'/';
// if(!is_dir($dir_str){
//     mkdir($dir_str);
// })

// $dir_str = $dir_str.$cate_array[2].'/';
// if(!is_dir($dir_str){
//     mkdir($dir_str);
// })
?>