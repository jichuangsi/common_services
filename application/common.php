<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件


/**
 * 通用第三方请求方法
 * @param string $url
 * @param array $data
 * @param string $method
 * @param number $second
 * @return boolean|mixed|json
 */
function curl($url = '', $data = array(), $method = 'GET', $second = 30) {
    if (empty($url)) {
        return false;
    }
    
    $ch = curl_init();//初始化curl
    /* $headers = [
     'form-data' => ['Content-Type: multipart/form-data'],
     'json'      => ['Content-Type: application/json'],
     ]; */
    
    
    if($method == 'GET'){
        if($data){
            $querystring = http_build_query($data);
            $url = $url.'?'.$querystring;
        }
    }
    //dump($url);
    curl_setopt($ch, CURLOPT_TIMEOUT, $second);//设置超时
    curl_setopt($ch, CURLOPT_URL, $url);//抓取指定网页
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);// https请求 不验证证书和hosts
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
    //curl_setopt($ch, CURLOPT_HTTPHEADER,$headers[$type]);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);//设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);//要求结果为字符串且输出到屏幕上
    
    if($method == 'POST'){
        $post_data = "p=" . urlencode(json_encode($data));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'POST');     // 请求方式
        curl_setopt($ch, CURLOPT_POST, TRUE);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    }
    
    $data = curl_exec($ch);//运行curl
    
    //返回结果
    if($data){
        curl_close($ch);
        return $data;
    } else {
        $error = curl_error($ch);
        //$error = curl_errno($ch);
        curl_close($ch);
        return $error;
    }
}