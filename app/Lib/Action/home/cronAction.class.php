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
        exit;
        $this->assign("contents", $contents);
        $this->display();
    }
 
    //定时执行的方法
    public function crons()
    {
        //在文件中写入内容
     //   $this->jd_monitor();
      //  file_put_contents("test.txt", date("Y-m-d H:i:s") . "执行定时任务！" . "\r\n<br>", FILE_APPEND);
    }

    public function jd_monitor(){
        if (false === $jd = F('jd')) {
            F('jd',1);
        }
        else{
            if(F('jd') == 0){
                return;
            }
        }
        $time = time() - 3600 *24 *30;
        $i=0;
        while($i<=1){
        $item_list =M("item_monitor")->where(array("orig_id"=>358,"status" =>$i,"add_time"=>array("lt",$time)))->order("add_time desc")->limit(0,30)->select();
        if(count($item_list)>0){
            $i++;
            break;
        }
        $i++;
        }
        foreach ($item_list as $item) {
            $price = jd_price($item['goods_id']);
            if($price <= 0){
                $item['status'] = 2;
                M("item_monitor")->save($item);
                continue;
            }
            if($price < $item['bottom_price']){
                $this->robot_publish($item,$price,$item['bottom_price'],$price);
            }
            elseif($price < 1.05 * $item['bottom_price'] && time() >= ($item['add_time'] + 3600*24*30)){
                $this->robot_publish($item,$price,1.05 * $item['bottom_price'],$item['bottom_price']);
            }
            elseif($price < 1.1 * $item['bottom_price'] && time() >= ($item['add_time'] + 3600*24*60)){
                $this->robot_publish($item,$price,1.1 * $item['bottom_price'],$item['bottom_price']);
            }
            else{
                M("item_monitor")->where("id=" . $item['id'])->setField("status",$i);
                if($i==2){
                    $this->history_price_create($item['orig_id'],$price,$item['goods_id']);
                }
            }
        }
    }

    public function tb_monitor(){
        if (false === $tb = F('tb')) {
            F('tb',1);
        }
        else{
            if(F('tb') == 0){
                return;
            }
        }
        $i=0;
        $time = time() - 3600 *24 *14;
        while($i<=1){
        $item_list =M("item_monitor")->where(array("orig_id"=>3,"status" =>$i,"add_time"=>array("lt",$time)))->order("add_time desc")->limit(0,20)->select();
        if(count($item_list)>0){
            $i++;
            break;
        }
        $i++;
        }
        foreach ($item_list as $item) {
            //var_dump($item['id'] . $item['title']);
            $price_info = tb_price($item['goods_id']);
            if(!$price_info || $price_info['retStatus'] != 0){
                $item['status'] = 2;
                M("item_monitor")->save($item);
            }
            else{
                $item['bottom_price'] = floatval($item['bottom_price']);
               // var_dump($item['bottom_price']);
                $price_info['price'] = bcsub($price_info['discountPrice'],$price_info['amount'],2);

                if($price_info['discountPrice']){
                   // continue;
                }
                if($price_info['price'] < $item['bottom_price']){
                //    var_dump(1);
                    $this->robot_publish_tb($item,$price_info,$item['bottom_price'],$price_info['price']);
                }
                elseif($price_info['price'] < 1.05 * $item['bottom_price'] && time() >= ($item['add_time'] + 3600*24*30)){
                 //   var_dump(2);
                    $this->robot_publish_tb($item,$price_info,1.05 * $item['bottom_price'],$item['bottom_price']);
                }
                elseif($price_info['price'] < 1.1 * $item['bottom_price'] && time() >= ($item['add_time'] + 3600*24*60)){
                 //   var_dump(3);
                    $this->robot_publish_tb($item,$price_info,1.1 * $item['bottom_price'],$item['bottom_price']);
                }
                else{
                    M("item_monitor")->where("id=" . $item['id'])->setField("status",$i);
                    if($i==2){
                        $this->history_price_create($item['orig_id'],$price_info['price'],$item['goods_id']);
                    }
                }
            }
        }
    }

    public function amazon_monitor(){
        if (false === $am = F('am')) {
            F('am',1);
        }
        else{
            if(F('am') == 0){
                return;
            }
        }
        $i=0;
        while($i<=1){
            $where['orig_id'] = array('in','506,2');
            $where['status'] = $i;
        $item_list =M("item_monitor")->where($where)->order("add_time desc")->limit(0,5)->select();
        if(count($item_list)>0){
            $i++;
            break;
        }
        $i++;
        }
        if($item_list){
            $goods_array = array();
            foreach ($item_list as $item) {
                array_push($goods_array, $item['goods_id']);
            }
            $goods_ids = join(",",$goods_array);
            
            $price_list = amazon_price($goods_ids);
            
            if($price_list){
                for($j=0; $j<count($item_list);$j++){
                    $item_list[$j]['price']=$price_list[$j];
                }
                foreach ($item_list as $item) {
                    $price = $item['price'];
                        if($price <= 0){
                        $item['status'] = 2;
                        M("item_monitor")->save($item);
                        continue;
                        }
                if($price < $item['bottom_price']){
                    $this->robot_publish($item,$price,$item['bottom_price'],$price);
                }
                elseif($price < 1.05 * $item['bottom_price'] && time() >= ($item['add_time'] + 3600*24*30)){
                    $this->robot_publish($item,$price,1.05 * $item['bottom_price'],$item['bottom_price']);
                }
                elseif($price < 1.1 * $item['bottom_price'] && time() >= ($item['add_time'] + 3600*24*60)){
                    $this->robot_publish($item,$price,1.1 * $item['bottom_price'],$item['bottom_price']);
                }
                else{
                    M("item_monitor")->where("id=" . $item['id'])->setField("status",$i);
                    if($i==2){
                        $this->history_price_create($item['orig_id'],$price,$item['goods_id']);
                    }
                }
                }
            }
            
        }
    }

    public function amazon_reprice(){
        $where['orig_id'] = array('in','506,2');
        $where['status'] = 3;
        $item_list =M("item_monitor")->where($where)->order("add_time desc")->limit(0,5)->select();
        if(count($item_list)==0){
            return;
        }
        if($item_list){
            $goods_array = array();
            foreach ($item_list as $item) {
                array_push($goods_array, $item['goods_id']);
            }
            $goods_ids = join(",",$goods_array);
            
            $price_list = amazon_price($goods_ids);
            
            if($price_list){
                for($j=0; $j<count($item_list);$j++){
                    $item_list[$j]['price']=$price_list[$j];
                }
                foreach ($item_list as $item) {
                    $price = $item['price'];
                        if($price <= 0){
                        $item['status'] = 5;
                        M("item_monitor")->save($item);
                        continue;
                        }
                if($price <= $item['bottom_price']){
                    $item['status'] = 2;
                    $item['bottom_price'] = $price;
                    $item['last_price'] = $price;
                    M("item_monitor")->save($item);
                }
                else{
                    $item['status'] = 4;
                    M("item_monitor")->save($item);
                }
                }
            }
            
        }
    }


    public function robot_publish($price_item,$price,$standard_price,$bottom_price){
        $item['cate_id'] = $price_item['cate_id'];
        $item['orig_id'] = $price_item['orig_id'];
        $item['uid'] = 0;
        $item['uname'] = "小白白";
        $item['title'] = $price_item['title'];
        $item['otitle'] = "";
        $item['intro'] = $price_item['intro'];
        $item['img'] = $price_item['img'];
        $item['price'] = $price . "元";
        $item['pure_price'] = $price;
        $item['url'] = $price_item['url'];
        $item['tag_cache'] = $price_item['tag_cache'];
        $item['status'] = 8;
         switch ($item['orig_id']){
        case 2;
        case 506;
        case 358:
         $item['content'] = "价格追踪: <br>目前这款" . $item['title'] . " 售价" . $item['price'] . " 低于可入手价格" . $standard_price . "<br> 上次爆料时间:" . mdate($price_item['add_time']) . "<br>细节参考编辑内容如下:<br>" . $price_item['content'] ;
        }
       
        $item['go_link'] = $price_item['go_link'];
        $item['ispost'] = $price_item['ispost'];

        $result =d('item')->add($item);
        $price_item['status'] = 2;
        $price_item['add_time'] = time();
        $price_item['last_price'] = $price;
        $price_item['bottom_price'] =$bottom_price;
        M("item_monitor")->save($price_item);
        $this->history_price_create($price_item['orig_id'],$price,$price_item['goods_id']);
    }

    public function robot_publish_tb($price_item,$price_info,$standard_price,$bottom_price){
        $item['cate_id'] = $price_item['cate_id'];
        $item['orig_id'] = $price_item['orig_id'];
        $item['uid'] = 0;
        $item['uname'] = "小白白";
        $item['title'] = $price_item['title'];
        $item['otitle'] = "";
        $item['intro'] = $price_item['intro'];
        $item['img'] = $price_item['img'];
        $item['price'] = $price_info['price'] . "元";
        $item['pure_price'] = $price_info['price'];
        $item['url'] = $price_info['uland_url'];
        $item['tag_cache'] = $price_item['tag_cache'];
        $item['status'] = 8;
         switch ($item['orig_id']){
        case 3:
         $item['content'] = "<br>目前这款" . $item['title'] . "售价" . $price_info['discountPrice'] . "元 可使用<a href=\"" . $item['url'] . "\" target=\"_blank\">" . $price_info['amount'] . "元优惠券</a> 实付" . $price_info['price'] . "<br> 上次爆料时间:" . mdate($price_item['add_time']) . "<br>" . $price_item['content'];
        }
        $arr[0] = array("name"=>"直达链接","link"=>$price_info['uland_url']);
        $item['go_link']=serialize($arr);
        $item['ispost'] = $price_item['ispost'];
        $result =d('item')->add($item);
        $price_item['status'] = 2;
        $price_item['add_time'] = time();
        $price_item['last_price'] = $price_info['price'];
        $price_item['bottom_price'] =$bottom_price;
        M("item_monitor")->save($price_item);
        $this->history_price_create($price_item['orig_id'],$price_info['price'],$price_item['goods_id']);
    }

    private function history_price_create($orig_id,$price,$goods_id){
        $time = strtotime(date('Ymd'));
         $historyPrice = M("price_history")->where(array("orig_id"=>$orig_id,"goods_id"=>$goods_id))->find();

        if($historyPrice){

        }
        else{
            $price_list = $this->get_price_history_list("https://item.jd.com/" . $goods_id . ".html");
            foreach ($price_list as $price) {
                $historyPrice['orig_id'] = $orig_id;
                $historyPrice['price'] = $price['price'];
                $historyPrice['time'] = strtotime($price['time']);
                $historyPrice['goods_id'] = $goods_id;
                M("price_history")->add($historyPrice);
            }
        }

        $price_item = M("price_history")->where(array("orig_id" => $orig_id,"goods_id" => $goods_id, "time" => $time))->find();
        if(empty($price_item)){
            $status = M("price_history")->add(array("orig_id" => $orig_id,"goods_id" => $goods_id, "time" => $time,"price" => $price));
        }
    }

    

    public function monitor_reset(){
        $item['status'] = 0;
        M("item_monitor")->where("status = 6")->save($item);
    }

    public function wash_jd_price(){
      //  $item_list = M("item")->where(array("orig_id"=>358,"status" =>1,"pure_price" => array('exp','is null')))->limit(0,500)->select();
        $item_list = M("item")->where(array("orig_id"=>358,"status" =>1,"pure_price" => 0))->limit(0,500)->select();
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
    public function tb_test(){
        // $test_url = "https://uland.taobao.com/coupon/edetail?e=uktfscyU%2FgFt3vqbdXnGlmsz%2BK%2B8wyZ5O26ewZqGRroa6BmuqwRHfrcZelJt%2BzjyFsfUUXeNUtcTL17IOhsekNzNwQTGaE3k14t9QUPD0GYBgNcqeXkNotw1YMxWamzlY5H4W1Kw6mdCExOygFLPDxhXKd56aRQD2lHRRnS20LU%3D&af=1&pid=mm_27883119_3410238_58178267";
        // echo get_tb_id($test_url);
         // $price_info = tb_price("570760842286");
         //   // var_dump($item['bottom_price']);
         //    $price_info['price'] = bcsub($price_info['discountPrice'],$price_info['amount'],2);
         //    var_dump($price_info);
         //    $item['bottom_price'] = floatval("169.0");
         //       // var_dump($item['bottom_price']);
         //        $price_info['price'] = bcsub($price_info['discountPrice'],$price_info['amount'],2);
         //        //var_dump($price_info['price']);
         //        if($price_info['price'] < $item['bottom_price']){
         //        //    var_dump(1);
         //           var_dump("aaaaaaaaaa");
         //        }

        //$tb_price = get_tb_id("https://uland.taobao.com/coupon/edetail?activityId=624022cb300f44e8adefcb9f9a3a8f97&itemId=41743095880&pid=mm_27883119_3410238_118454389&dx=1");
        //var_dump($tb_price);

       $url = "https://detail.tmall.com/item.htm?id=558555982159";
       $price_list = D("price_history")->get_price_history_list($url);
       var_dump($price_list);
    }

    public function amazon_test(){
        // $item_list =M("item_monitor")->where(array("orig_id"=>2,"status" =>0))->order("add_time desc")->limit(0,30)->select();
        // $goods_array = array();
        // foreach ($item_list as $item) {
        //     array_push($goods_array, $item['goods_id']);
        // }
        // $goods_ids = join(",",$goods_array);
        
        $price_list = amazon_price("B0792QFPYP,B078JDXPBQ,B07479P3KY");
        var_dump($price_list);
        // if($price_list){
        //     for($i=0; $i<count($item_list);$i++){
        //         $item_list[$i]['price']=$price_list[$i];
        //     }
        //     foreach ($item_list as $item) {
        //         # code...
        //     }
        // }
    }


    

}
