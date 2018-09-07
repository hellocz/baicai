<?php

/**
 * 逛宝贝页面
 */
class nineAction extends frontendAction {
    public function _initialize() {
        parent::_initialize();

    }

    /**
     * 逛宝贝首页
     */
    public function index() {
        $sort = $this->_get('sort', 'trim'); //排序
        $cat = $this->_get('cat_id', 'intval',0);
        $where = array();
        if($cat)$where['cat_id'] = $cat;
        //排序：最热(hot)，最新(new)
        switch ($sort) {
            case 'sum':
                $order = 'sales_volume DESC,id DESC';
                break;
            case 'new':
                $order = 'id DESC';
                break;
            case 'price_desc':
                $order = 'price DESC';
                break;
            case 'price_asc':
                $order = 'price ASC';
                break;
			default:
				$order = 'id DESC';
				break;
        }
        $this->waterfall($where, $order);

$cat_name=array('鞋服箱包','钟表首饰','运动户外','个护美妆','母婴玩具','食品酒饮','数码家电','家居用品','汽车用品','日用百货','图书音像','游戏话费','其他');
        $this->assign('cat_name', $cat_name);
        $this->assign('sort', $sort);
        $count=array();
        foreach($cat_name as $k=>$v){
            $nine = m('nine');
            $x=$k+1;
            $count[$x] = $nine->where(array('cat_id'=>$x))->count();
            if(! $count[$x]) $count[$x]=0;
        }
        $count['count']=$counts = $nine->count();
        $item = $nine->where(array('is_stick'=>1))->order('id desc')->limit(8)->select();
        $this->assign('item', $item);
        $this->assign('count', $count);
        $this->assign('cat', $cat);
		//$this->assign('strpos',$strpos);
        $this->display();
    }
    public function test(){
        $item_site = M('item_site')->where(array('code' =>'taobao'))->find();
        $api_config = unserialize($item_site['config']);
        vendor('Taobaotop.TopClient');
        vendor('Taobaotop.RequestCheckUtil');
        vendor('Taobaotop.request.PromotionCouponsGetRequest');
        vendor('Taobaotop.Logger');
        print_r($api_config);
        $c = new TopClient;
        $c->appkey = "23232602";
        $c->secretKey = "xxxxxxxxxxxxxxxxxxxxxxxxxxx";
        $req = new PromotionCouponsGetRequest;
        $req->setCouponId("586135d459dd42a784e5f03bf28332b4");
        $resp = $c->execute($req,'daasdasd');
        echo $resp;
    }
    public function waterfall($where, $order = 'i.id DESC', $field = '') {
        $nine = m('nine');
        $spage_size = C('pin_wall_spage_size'); //每次加载个数
        $spage_max = C('pin_wall_spage_max'); //每页加载次数
        $page_size = $spage_size * $spage_max; //每页显示个数
        $counts = $nine->where($where)->count();
        $pager = new Page($counts, $page_size);
        $item_list = $nine->where($where)->order($order)->limit($pager->firstRow . ',' . $pager->listRows)->select();
        $this->assign('item_list', $item_list);
        //当前页码
        $p = $this->_get('p', 'intval', 1);
        $this->assign('p', $p);
        $counts > $page_size && $this->assign('page_bar', $pager->fshow());
    }


}
