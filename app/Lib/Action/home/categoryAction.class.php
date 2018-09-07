<?php



/**
 * 逛宝贝页面
 */
class categoryAction extends frontendAction {

    public function _initialize() {
        parent::_initialize();
        $this->assign('nav_curr', 'book');
    }

	public function index(){
		$p = $this->_get('p',"intval",1);
		$cateid = $this->_get('cateid','intval');
    $cate = M("item_cate")->where("id=$cateid")->find();
    $level = substr_count($cate['spid'],"|")+1;
		if(!empty($cate)){
            include_once LIB_PATH . 'Pinlib/taobao/TopSdk.php';
            $c = new TopClient;
            $c->appkey = "23232602";
            $c->secretKey = "xxxxxxxxxxxxxxxxxxxxxxxx";
            $req = new TbkDgItemCouponGetRequest;
            $req->setAdzoneId("14718353");
           // $req->setPlatform(1);
            $req->setPageSize("100");
            $req->setPageNo("1");
            $brand_list_ids = unserialize($cate['top']);
            $brand_map['id']=array('in',$brand_list_ids);
            $brand_list = M("brand")->field('id,name,chn_name,country,abstract,tb,img')->where($brand_map)->select();
            $brand_names = array();
            foreach ($brand_list as $key=>$val) {
              $brand_list[$key]['abstract'] = trim($brand_list[$key]['abstract']);
              $req->setQ($cate['name'] . " " . trim($brand_list[$key]['chn_name']));
              $resp = $c->execute($req);
              $lists=$resp->results->tbk_coupon;
              array_push($brand_names, trim($brand_list[$key]['chn_name']));
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
          usort($item_list, 'sortByVolume');
          $brand_list[$key]['recommend']=array_slice($item_list,0,5);

            }

            $this->assign('brand_list',$brand_list);
        }

            
    $this->assign('cate_info', $cate);
    if(empty($cate['seo_title'])){
      if($level == 2){
        $child_cates =M("item_cate")->where("pid=$cateid")->field("name")->select();
        $child = "";
        foreach ($child_cates as $child_cate) {
          $child .= $child_cate['name'] . ",";
        }
        $child = rtrim($child, ',');
        $page_seo['title'] = $cate['name'] . "内部优惠券|" . $cate['name'] . "优惠活动|" . $cate['name'] . "海淘|白菜哦";
        $page_seo['keywords'] = $cate['name'] . "内部优惠券|" . $cate['name'] . "优惠活动|" . $cate['name'] . "海淘|白菜哦";
        $page_seo['description'] =  "提供最新" . $cate['name'] . "相关内部优惠券大全,教你海淘" . $cate['name'] ."无需找代购,这里是白菜哦" . $cate['name'] . "优惠信息专栏,最新热门品牌榜等,下载APP不错过任何好价.包括" . $child . "等各类优惠促销";
      }
      else{
        $page_seo['title'] = $cate['name'] . "哪个牌子好|2018最新" . $cate['name'] . "十大品牌榜|" .$cate['name'] . "知名品牌|什么" . $cate['name'] . "值得买";
        $page_seo['keywords'] = $cate['name'] . "哪个牌子好，2018最新" . $cate['name'] . "十大品牌榜，" . $cate['name'] . "知名品牌，什么" . $cate['name'] . "值得买";
        $page_seo['description'] =  "想知道" .  $cate['name'] . "哪个牌子最好，什么" .  $cate['name'] . "值得买，就来看看" .  $cate['name'] . "2018年最新品牌榜吧！前三名有" . $brand_names[0] . "、" . $brand_names[1] . "和" . $brand_names[2] . "。还提供最新" . $cate['name'] . "优惠券领取，以及" . $cate['name'] . "优惠活动和特价商品推荐，想购物来白菜哦，买得便宜又称心！";
      }
    }
    else{
        $page_seo['title'] = $cate['seo_title'];
        $page_seo['keywords'] = $cate['seo_keys'];
        $page_seo['description'] = $cate['seo_desc'];
    }
    $this->assign('page_seo', $page_seo);
    $this->assign("strpos",getpos($cateid,''));
    $this->assign('category',$cate['name']);
		$this->display();
	}

  public function bridgecate(){
    $cates = M("item_cate")->where("pid=0")->order("ordid asc")->select();
    $categorys = M("category")->where("pid=0 and mpid!=0")->order("id asc")->select();
    $this->assign('cates',$cates);
    $this->assign('categorys',$categorys);
    $this->display();
  }
  public function bridge(){
      $cateid = $this->_post('cate','trim');
      $categoryid = $this->_post('category','trim');

      $category=  M("category")->where("id=$categoryid")->find();

      if(empty($category)){
        $this->ajaxReturn("1",'添加失败');
      }
      $cate['name'] = $category['name'];
      $cate['pid'] = $cateid;
      $cate['spid'] = $cateid . "|";
      $cate['status'] = 1;

      $cate_pid = M("item_cate")->add($cate);

      $categorylist = M("category")->where("pid=$category[id]")->order("id asc")->select();

      foreach ($categorylist as $r) {
        $cate_child = array();
        $cate_child['name'] = $r['name'];
        $cate_child['pid'] = $cate_pid;
        $cate_child['spid'] =$cate['spid'] . $cate_pid . "|";
        $cate_child['top'] = $r['top'];
        $cate_child['status'] = 1;
        M("item_cate")->add($cate_child);
      }

      $this->ajaxReturn("1",'添加成功');

  }

  public function showCategory(){
    $levelones = M("category")->where("pid=0 and mpid!=0")->order("id asc")->select();
    foreach ($levelones as $levelone) {
     echo "http://www.baicaio.com/";
    }
  }

    
}
