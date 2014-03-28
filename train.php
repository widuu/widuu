<?php
/*
 *@Description 火车票余票查询和价格查询
 *@author	   widuu 
 *@mktime	   2014/1/13
 *@license	   http://www.widuu.com
 */
header("content-type:text/html;charset='utf-8'");
function gettrain($start,$end,$data){
    $station = include('./name.php');
    $startstaion = $station[$start];
    $endstation = $station[$end];
    if(empty($data)){
        $date = date("Y-m-d",time());
    }else{
        $num = explode("-", $data);
         if(count($num)==2){
             $date = date("Y",time())."-".$data;
        }else if(count($num)==3){
            $date = $data;
        }else{
            exit("time error");
        }
    }
    if(empty($startstaion)||empty($endstation)){
        exit("stdin error");
    }
    $url ="http://kyfw.12306.cn/otn/lcxxcx/query?purpose_codes=ADULT&queryDate=$date&from_station=$startstaion&to_station=$endstation";
    $data = doget($url);
    if(!$data['status']){
        exit('check error');
    }else{
        $data = $data['data']['datas'];
        foreach ($data as $key => $value) {
            $price = doget("http://kyfw.12306.cn/otn/leftTicket/queryTicketPrice?train_no=".$data[$key]["train_no"]."&from_station_no=".$data[$key]["from_station_no"]."&to_station_no=".$data[$key]["to_station_no"]."&seat_types=".$data[$key]["seat_types"]."&train_date=$date");
			$data[$key]["gr_num"] =  $data[$key]["gr_num"]."(".$price["data"]["A6"].")";
			$data[$key]["qt_num"] =  $data[$key]["qt_num"]."(".$price["data"]["OT"][0].")";
			$data[$key]["rw_num"] =  $data[$key]["rw_num"]."(".$price["data"]["A4"].")";
			$data[$key]["rz_num"] =  $data[$key]["rz_num"]."(".$price["data"]["A2"].")";
			$data[$key]["tz_num"] =  $data[$key]["tz_num"]."(".$price["data"]["P"].")";
			$data[$key]["wz_num"] =  $data[$key]["wz_num"]."(".$price["data"]["WZ"].")";
			$data[$key]["yw_num"] =  $data[$key]["yw_num"]."(".$price["data"]["A3"].")";
			$data[$key]["yz_num"] =  $data[$key]["yz_num"]."(".$price["data"]["A1"].")";
			$data[$key]["ze_num"] =  $data[$key]["ze_num"]."(".$price["data"]["O"].")";
			$data[$key]["zy_num"] =  $data[$key]["zy_num"]."(".$price["data"]["M"].")";
			$data[$key]["swz_num"]=  $data[$key]["swz_num"]."(".$price["data"]["A9"].")";
        }
    }
    return $data;
}

function doget($url){
    if(function_exists('file_get_contents')) {
        $optionget = array('http' => array('method' => "GET", 'header' => "User-Agent:Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.5.21022; .NET CLR 3.0.04506; CIBA)\r\nAccept:*/*\r\nReferer:https://kyfw.12306.cn/otn/lcxxcx/init"));
        $file_contents = file_get_contents($url, false , stream_context_create($optionget));
    } else {
            $ch = curl_init();
            $timeout = 5;
            $header = array(
                'Accept:*/*',
                'Accept-Charset:GBK,utf-8;q=0.7,*;q=0.3',
                'Accept-Encoding:gzip,deflate,sdch',
                'Accept-Language:zh-CN,zh;q=0.8,ja;q=0.6,en;q=0.4',
                'Connection:keep-alive',
                'Host:kyfw.12306.cn',
                'Referer:https://kyfw.12306.cn/otn/lcxxcx/init',
            );
            curl_setopt ($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $file_contents = curl_exec($ch);
            curl_close($ch);
    }
    $file_contents = json_decode($file_contents,true);
    return $file_contents;
}
/*
 *这个由于时间紧写的，所以很多地方还需要优化，希望大家改善一下，可以分部和文件缓存的形式提高执行速度--缺点执行速度慢--有待优化
 */
$data = gettrain("北京","天津","01-03");
/*
 *   ["gr_num"]=>高级软卧
 *   ["qt_num"]=>其他
 *   ["rw_num"]=> 软卧
 *   ["rz_num"]=>软座
 *   ["tz_num"]=>特等座
 *   ["wz_num"]=>无座
 *   ["yw_num"]=>硬卧
 *   ["yz_num"]=>硬座
 *   ["ze_num"]=>二等座
 *   ["zy_num"]=> 一等座
 *   ["swz_num"]=> 商务座
 */
$str="";
foreach($data as $key =>$value){
	$str.="火车列次:{$data[$key]['station_train_code']}";
	$str.="始发站:{$data[$key]['start_station_name']},终点站{$data[$key]['end_station_name']}<br>";
	$str.="出发时间:{$data[$key]['start_time']},到站时间:{$data[$key]['arrive_time']}<br>";
	$str.="";
	$str.= strpos($data[$key]["gr_num"],"()") ? "" :"高级软卧余票和价格:".$data[$key]["gr_num"]."<br>";
	$str.= strpos($data[$key]["qt_num"],"()") ? "" :"其他余票和价格:".$data[$key]["qt_num"]."<br>";
        $str.= strpos($data[$key]["rw_num"],"()") ? "" :"软卧余票和价格:".$data[$key]["rw_num"]."<br>";
	$str.= strpos($data[$key]["rz_num"],"()") ? "" :"软座余票和价格:".$data[$key]["rz_num"]."<br>";
	$str.= strpos($data[$key]["tz_num"],"()") ? "" :"特等座余票和价格:".$data[$key]["tz_num"]."<br>";
	$str.= strpos($data[$key]["wz_num"],"()") ? "" :"无座余票和价格:".$data[$key]["wz_num"]."<br>";
	$str.= strpos($data[$key]["yw_num"],"()") ? "" :"硬卧余票和价格:".$data[$key]["yw_num"]."<br>";
	$str.= strpos($data[$key]["yz_num"],"()") ? "" :"硬座余票和价格:".$data[$key]["yz_num"]."<br>";
	$str.= strpos($data[$key]["ze_num"],"()") ? "" :"二等座余票和价格:".$data[$key]["ze_num"]."<br>";
	$str.= strpos($data[$key]["zy_num"],"()") ? "" :"一等座余票和价格:".$data[$key]["zy_num"]."<br>";
	$str.= strpos($data[$key]["swz_num"],"()") ? "" :"商务座余票和价格:".$data[$key]["swz_num"]."<br>";
	$str.= "======================下一列火车上===============================<br>";
}
echo $str;
