<?php

class quanAction extends frontendAction {

    public function index(){
            $q = $this->_get('q', 'trim');
            $s = $this->_get('s', 'trim');
            $coupon_history =urldecode(cookie('coupon_history'));
            $coupon_sort = urldecode(cookie('coupon_sort'));
        if (isset($coupon_history)) {
            $q || $q=$coupon_history;
        }
       cookie('coupon_history',urlencode($q));
        if (isset($coupon_sort)) {
            $s  || $s=$coupon_sort;
        }
        if($s==""){
          $s="v";
        }
        cookie('coupon_sort',urlencode($s));
            include_once LIB_PATH . 'Pinlib/taobao/TopSdk.php';
            $c = new TopClient;
            $c->appkey = "23232602";
            $c->secretKey = "xxxxxxxxxxxxxxxxxxxxxxxxxxxx";
            $req = new TbkDgItemCouponGetRequest;
            $req->setAdzoneId("14718353");
           // $req->setPlatform(1);
            $req->setPageSize("100");
            $req->setQ($q);
            $req->setPageNo("1");
            $resp = $c->execute($req);
           $lists=$resp->results->tbk_coupon;
         //  echo count($lists);
       //    var_dump($lists);
      //     header("Content-type: text/html; charset=utf8");
       //      var_dump($lists);
       $quan_keywords = M('setting')->where("name = 'quan_keywords'")->field('data')->find();
       $quan_keys = explode(',',$quan_keywords['data']);
           $item_list = array();

           foreach ($lists as $list) {
          $item['title'] = $list->title;
          $item['user_type'] = intval($list->user_type);
          $item['img'] = $list->pict_url;
          $item['url'] = $list->item_url;
          $item['coupon_url'] = $list->coupon_click_url;
          $item['volume'] = intval($list->volume);
          $item['coupon_info'] = $list->coupon_info;
          $pattern = '/减((\d){1,})元/';
          $pattern_num = preg_match($pattern,$item['coupon_info'],$pattern_result);
          $item['coupon'] = floatval($pattern_result[1]);
          $item['zk_final_price'] = floatval($list->zk_final_price);
          $item['price'] = $item['zk_final_price'] - $item['coupon'];
          array_push($item_list,$item);
           }
           if($s == "p"){
            usort($item_list, 'sortByPrice');
           }
           elseif($s == "c"){
            usort($item_list, 'sortByCoupon');
           }
           elseif($s == "z"){
            usort($item_list, 'sortByZK');
           }
           else{
            usort($item_list, 'sortByVolume');
           }
           $this->assign("quan_keys",$quan_keys);
           $this->assign("s",$s);
           $this->assign("q",$q);
           $this->assign("item_list",$item_list);
           if(empty($q)){
        $page_seo['title'] = "天猫优惠券|淘宝优惠券|天猫优惠券免费领取|白菜哦";
        $page_seo['keywords'] = "天猫优惠券，淘宝优惠券，天猫购物券免费领取";
        $page_seo['description'] = "天猫淘宝优惠券免费领取，实时更新最新折扣秒杀，9.9包邮特价商品，可用宝贝标题或者品牌搜券，超过500万张优惠券可供搜索查询，购物前别忘记来搜一搜优惠券哦！";
           }
           else{
        $page_seo['title'] = $q . "优惠券|天猫" . $q ."特价|天猫" . $q . "优惠券免费领取|白菜哦";
        $page_seo['keywords'] = $q . "优惠券，天猫" . $q . "特价，天猫" . $q . "优惠券免费领取";
        $page_seo['description'] = "实时更新最新" . $q . "优惠券，天猫" . $q . "优惠券免费领取，包括所有可领购物券的" . $q . "商品，可根据" . $q . "品牌或者标题查询，购物前别忘记领券哦！";
        
           }
           
    $this->assign('page_seo', $page_seo);
    $this->display();
    } 

        function sortByVolume($a, $b) {

        if ($a['volume'] == $b['volume']) {

        return 0;

        } else {

        return ( $a['volume'] < $b['volume']) ? 1 : -1;

        }
    }

    private  function sortByCoupon($a, $b) {

        if ($a['coupon'] == $b['coupon']) {

        return 0;

        } else {

        return ( $a['coupon']< $b['coupon']) ? 1 : -1;

        }
    }

    private  function sortByPrice($a, $b) {

        if ( $a['price'] == $b['price']) {

        return 0;

        } else {

        return ( $a['price'] < $b['price']) ? 1 : -1;

        }
    }

    private  function sortByZK($a, $b) {

        if ($a['price']/$a['zk_final_price'] == $b['price']/$b['zk_final_price'] ) {

        return 0;

        } else {

        return ( $a['price']/$a['zk_final_price']  < $b['price']/$b['zk_final_price'] ) ? 1 : -1;

        }
    }

}
