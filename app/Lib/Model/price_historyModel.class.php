<?php

class price_historyModel extends Model
{
    function generateChart($goods_id,$orig_id){
        $where['goods_id'] = $goods_id;
        $where['orig_id'] = $orig_id;
    	$price_list = $this->where($where)->order("time asc")->select();
    	$x = array();
    	$y = array();
    	if(count($price_list)>0){
    		foreach ($price_list as $price) {
    			array_push($x, date("Y-m-d",$price['time']));
    			array_push($y, floatval($price['price']));
    		}
    		$result['x'] = $x;
    		$result['y'] = $y;
    		return $result;
    	}
    	else{
    		return null;
    	}
    }

    function history_price_init($goods_id,$orig_id,$url,$bottom_price){
    	$price_item = $this->where(array("orig_id"=>$orig_id,"goods_id"=>$goods_id))->find();
        if(!$price_item){
            $price_list = $this->get_price_history_list($url);
            foreach ($price_list as $price) {
                $price_item['orig_id'] = $orig_id;
                $price_item['price'] = $price['price'];
                $price_item['time'] = strtotime($price['time']);
                $price_item['goods_id'] = $goods_id;
                $this->add($price_item);
            }
        }
        $time = strtotime(date('Ymd'));
        $price_today = M("price_history")->where(array("orig_id" => $orig_id,"goods_id" => $goods_id, "time" => $time))->find();
        if(empty($price_today)){
            $status = M("price_history")->add(array("orig_id" => $orig_id,"goods_id" => $goods_id, "time" => $time,"price" => $bottom_price));
        }
    }

    function get_price_history_list($product_url){
        $url ="http://www.178hui.com/?mod=ajax&act=historyPrice&url=" . urlencode($product_url);
        $method = "GET";
        $postfields =null;
        $headers = array();
        $debug = false;

        $cookie ="userlogininfo=9d49ymMSBwxdbhZg3HWuylexWMKQV%2FoA%2BhSG7gtoVbsQGNTdK2J1Mfa563sGdX76VPIpuhD348%2FdOXdqxr0bo8FPBocoBMK6F0zF9yHkch1OEKuCEx0WKyIsw%2B7BskRzfwvD7g8SD6%2Bp1t4TPzV6VPRdmW5Qsw4aivW1jZm8YZnbl6tLsOFtBGJDcOX1yBA";

        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_COOKIE, $cookie);

        switch ($method) {
        case 'POST':
        curl_setopt($ci, CURLOPT_POST, true);
        if (!empty($postfields)) {
        curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
        $this->postdata = $postfields;
        }
        break;
        }
        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, true);

        $response = curl_exec($ci);
        $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);

        if (!$debug) {

        echo '=====info=====' . "\r\n";
        print_r(curl_getinfo($ci));

        echo '=====$response=====' . "\r\n";
        print_r($response);
        }
        curl_close($ci);
        $json_result = json_decode($response, TRUE);
        if($json_result['code'] == 100){
         return $json_result['priceHistoryData']['priceList'];
    }
    else{
        return null;
    }
       
    }
}