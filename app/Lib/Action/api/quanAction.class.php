<?php
class quanAction extends userbaseAction
{
    public function _initialize() {
        parent::_initialize();
    }

    public function init() {
        if(IS_POST){
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data)) {
                return '';
            }
            $this->$data['reqBody']['api']($data['reqBody']);
        }
    }

    /**
     * 我的抽奖
     */
    public function keywords() {
       $quan_keywords = M('setting')->where("name = 'quan_keywords'")->field('data')->find();
       $quan_keys = explode(',',$quan_keywords['data']);
        $code = 10001;
        if(count($quan_keys) < 1){
            $code = 10002;
        }
        echo get_result($code,$quan_keys);return ;
    }

    /**
     * 抽奖详情页
     */
    public function search_quan($data) {
            $q = $data['q'];
            $s = $data['s'];
        
            if(empty($s)){
              $s="v";
            }
            include_once LIB_PATH . 'Pinlib/taobao/TopSdk.php';
            $c = new TopClient;
            $c->appkey = "23232602";
            $c->secretKey = "xxxxxxxxxxxxxxxxxxxxxxxxx";
            $req = new TbkDgItemCouponGetRequest;
            $req->setAdzoneId("14718353");
           // $req->setPlatform(1);
            $req->setPageSize("100");
            $req->setQ($q);
            $req->setPageNo("1");
            $resp = $c->execute($req);
           $lists=$resp->results->tbk_coupon;
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
            $code = 10001;
           if(count($item_list) < 1){
            $code = 10002;
        }
        echo get_result($code,$item_list);return ;
           
    }


}
