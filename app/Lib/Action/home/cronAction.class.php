<?php

/**
 * 逛宝贝页面
 */
class cronAction extends frontendAction {

    public function _initialize() {
        parent::_initialize();
    }

    public function index()
    {
        $contents = file_get_contents("test.txt");
        //每次访问此路径将内容输出，查看内容的差别
        var_dump($contents);
        exit;
        $this->assign("contents", $contents);
        $this->display();
    }
 
    //定时执行的方法
    public function crons()
    {
        //在文件中写入内容
        $this->jd_monitor();
        file_put_contents("test.txt", date("Y-m-d H:i:s") . "执行定时任务！" . "\r\n<br>", FILE_APPEND);
    }

    public function jd_monitor(){
        $i=0;
        $item_list=null;
        while($i<=1 && $item_list == null){
        $item_list =M("item_monitor")->where(array("orig_id"=>358,"status" =>$i))->limit(0,5)->select();
        $i++;
        }
        foreach ($item_list as $item) {
            $price = $this->jd_price($item['goods_id']);
            if($price <= 0){
                $item['status'] = 2;
                M("item_monitor")->save($item);
                break;
            }
            if($price < $item['bottom_price']){
                $this->robot_publish($item,$price,$item['bottom_price'],$price,0);
            }
            elseif($price < 1.05 * $item['bottom_price'] && time() >= ($item['add_time'] + 3600*24*30)){
                $this->robot_publish($item,$price,1.05 * $item['bottom_price'],$item['bottom_price'],1);
            }
            elseif($price < 1.1 * $item['bottom_price'] && time() >= ($item['add_time'] + 3600*24*60)){
                $this->robot_publish($item,$price,1.1 * $item['bottom_price'],$item['bottom_price'],2);
            }
            else{
                M("item_monitor")->where("id=" . $item['id'])->setInc("status");
                if($i=2){
                    $this->history_price_create($item['orig_id'],$price,$item['goods_id']);
                }
            }
        }
    }

    public function robot_publish($price_item,$price,$standard_price,$bottom_price,$type){
        $item['cate_id'] = $price_item['cate_id'];
        $item['orig_id'] = $price_item['orig_id'];
        $item['uid'] = 0;
        $item['uname'] = "小白白";
        $item['title'] = $price_item['title'] . time() . "|" . ($price_item['add_time'] + 3600*24*30) . "type" . $type;
        $item['otitle'] = $price_item['otitle'];
        $item['intro'] = $price_item['intro'];
        $item['img'] = $price_item['img'];
        $item['price'] = $price;
        $item['pure_price'] = $price;
        $item['url'] = $price_item['url'];
        $item['tag_cache'] = $price_item['tag_cache'];
        $item['status'] = 2;
         switch ($item['orig_id']){
        case 358:
         $item['content'] = "价格追踪: <br>目前这款" . $item['title'] . " 售价" . $item['price'] . " 低于可入手价格" . $standard_price . "<br> 细节参考编辑内容如下:<br>" . $price_item['content'] ;
        }
       
        $item['go_link'] = $price_item['go_link'];
        $item['ispost'] = $price_item['ispost'];
        M("item")->add($item);
        $price_item['status'] = 2;
        $price_item['add_time'] = time();
        $price_item['last_price'] = $price;
        $price_item['bottom_price'] =$bottom_price;
        M("item_monitor")->save($price_item);
        $this->history_price_create($price_item['orig_id'],$price,$price_item['goods_id']);
    }

    private function history_price_create($orig_id,$price,$goods_id){
        $time = strtotime(date('Ymd'));
        $price_item = M("price_history")->where(array("orig_id" => $orig_id,"goods_id" => $goods_id, "time" => $time))->find();
        if(empty($price_item)){
            $status = M("price_history")->add(array("orig_id" => $orig_id,"goods_id" => $goods_id, "time" => $time,"price" => $price));
        }
    }

    public function jd_price($skuid){
        include_once LIB_PATH . 'Pinlib/jd/JdClient.php';
        include_once LIB_PATH . 'Pinlib/jd/request/WarePriceGetRequest.php';

        $c = new JdClient();

        $c->appKey = "01E4158966092DC5F5E95A0ED39F2C64";

        $c->appSecret = "5a0beb8b45964b43a5e0e98bd455afa4";

        $c->accessToken = "53fd32f5-27fb-404d-90f2-b70a12df4936";

        $req = new WarePriceGetRequest();

        $req->setSkuId($skuid);

        $resp = $c->execute($req, $c->accessToken);
        $resp_json = json_encode($resp);
        $result = json_decode($resp_json,true);
        if($result['code'] == 0){
        return floatval($result['price_changes'][0]['price']);
    }
    else
        return 0;
    }

    public function monitor_reset(){
        $item['status'] = 0;
        M("item_monitor")->where("status = 6")->save($item);
    }

    public function wash_jd_price(){
      //  $item_list = M("item")->where(array("orig_id"=>358,"status" =>1,"pure_price" => array('exp','is null')))->limit(0,500)->select();
        $item_list = M("item")->where(array("orig_id"=>358,"status" =>1,"pure_price" => 0))->limit(0,500)->select();
        var_dump(count($item_list));
        foreach ($item_list as $item) {
             preg_match("/(\d+\.?\d+)元/", $item['price'],$match_prices);
            $item['pure_price'] = floatval($match_prices[1]);
            if($item['pure_price'] == 0){
                  preg_match("/￥(\d+\.?\d+)/", $item['price'],$match_prices);
            $item['pure_price'] = floatval($match_prices[1]);
            }
            M("item")->save($item);
        }
    }

    public function monitor_jd(){
        $item_list = M("item")->where(array("orig_id"=>358,"status" =>1,"pure_price" => array('NEQ',0)))->order("add_time desc")->limit(0,100)->select();
       // $item_list = M("item")->where(array("orig_id"=>358,"status" =>1,"pure_price" => 0))->limit(0,1)->select();
        var_dump(count($item_list));
        foreach ($item_list as $item) {
            $this->create_monitor($item);
            $item['status'] = 8;
            M("item")->save($item);
        }
        if(count($item_list) == 100){
            $this->monitor_jd();
        }
    }
    /*
        测试数据初始化
    */
    function create_monitor($data){
        if($data['status'] != 1 || $data['orig_id'] == 0 || $data['title'] =="" || $data['pure_price'] == 0 || strpos($data['title'],"*")!== false ){
            return ;
        }
        $goods_info = $this->getgoods_info($data['url'],$data['orig_id']);
        if($goods_info == ""){
            return;
        }
        $goods_item = M("item_monitor")->where(array("orig_id"=>$data['orig_id'],"goods_id"=>$goods_info['goods_id']))->find();
        if(!$goods_item){
        $monitor_goods['cate_id'] = $data['cate_id'];
        $monitor_goods['orig_id'] = $data['orig_id'];
        $monitor_goods['goods_id'] = $goods_info['goods_id'];
        $monitor_goods['title'] = $data['title'];
        $monitor_goods['img'] = $data['img'];
        $monitor_goods['last_price'] = $data['pure_price'];
        $monitor_goods['bottom_price'] = $data['pure_price'];
        $monitor_goods['url'] = $goods_info['url'];
        $monitor_goods['tag_cache'] = $data['tag_cache'];
        $monitor_goods['content'] = $data['content'];
        $monitor_goods['go_link'] = $data['go_link'];
        $monitor_goods['ispost'] = $data['ispost'];
        $monitor_goods['add_time'] = $data['add_time'];
        M("item_monitor")->add($monitor_goods);
        }

        $price_item = M("price_history")->where(array("orig_id"=>$data['orig_id'],"goods_id"=>$goods_info['goods_id']))->find();

        if($price_item){

        }
        else{
            $price_list = $this->get_price_history_list($goods_info['url']);
            foreach ($price_list as $price) {
                $price_item['orig_id'] = $data['orig_id'];
                $price_item['price'] = $price['price'];
                $price_item['time'] = strtotime($price['time']);
                $price_item['goods_id'] = $goods_info['goods_id'];
                M("price_history")->add($price_item);
            }
        }
        
    }

    function getgoods_info($url,$orig_id){
        switch ($orig_id){
        case 358:
         preg_match("/(\d+)\.html/", $url,$match_id);
         if(empty($match_id[1])){
                return "";
            }
            return array("goods_id"=>$match_id[1],"url"=>"https://item.jd.com/" . $match_id[1] . ".html");
        }
        return "";
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

        if ($debug) {

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
