<?php

class exchangeAction extends frontendAction {

    public function _initialize() {
        parent::_initialize();
        $this->assign('nav_curr', 'exchange');
    }

    /**
     * 积分兑换首页
     */
    public function index() {
        $cid = $this->_get('cid', 'intval');
        $sort = $this->_get('sort', 'trim', 'hot');
        //排序：最热(hot)，最新(new)
        switch ($sort) {
            case 'hot':
                $sort_order = 'buy_num DESC,id DESC';
                break;
            case 'new':
                $sort_order = 'id DESC';
                break;
        }
        $cname = D('score_item_cate')->get_name($cid);
        $where = array('status'=>'1');
        $cid && $where['cate_id'] = $cid;
        !$where['cate_id'] && $where['cate_id'] = array('NEQ','8');

        $score_item = M('score_item');
        $count = $score_item->where($where)->count('id');
        $pager = $this->_pager($count, 20);
        $item_list = $score_item->where($where)->order($sort_order)->limit($pager->firstRow.','.$pager->listRows)->select();
        $this->assign('item_list', $item_list);
        $this->assign('page_bar', $pager->fshow());
        $this->assign('cid', $cid);
        $this->assign('sort', $sort);
        $this->assign('cname', $cname);
        $this->assign('page_seo',set_seo("礼品兑换"));
        $this->display();
    }

    /**
     * 抽奖页面
     */
    public function lucky() {
        $cid = $this->_get('cid', 'intval');
        $sort = $this->_get('sort', 'trim', 'new');
        //排序：最热(hot)，最新(new)
        switch ($sort) {
            case 'hot':
                $sort_order = 'buy_num DESC,id DESC';
                break;
            case 'new':
                $sort_order = 'sign_date DESC';
                break;
        }
        $cname = D('score_item_cate')->get_name($cid);
        $where = array('status'=>'1');
        $where['cate_id'] = 8;
        $where['win'] = '';
        

        $score_item = M('score_item');
        $count = $score_item->where($where)->count('id');
        $pager = $this->_pager($count, 20);
        $item_list = $score_item->where($where)->order($sort_order)->limit($pager->firstRow.','.$pager->listRows)->select();

        $where['win'] = array('NEQ','');
        $item_finish = $score_item->where($where)->order($sort_order)->limit(5)->select();

        $this->assign('item_list', $item_list);
        $this->assign('item_finish', $item_finish);
        $this->assign('page_bar', $pager->fshow());
        $this->assign('cid', $cid);
        $this->assign('sort', $sort);
        $this->assign('cname', $cname);
        $this->assign('page_seo',set_seo("礼品兑换"));
        $this->display();
    }

    /**
     * 抽奖过期页面
     */
    public function lucky_expired() {
        $cid = $this->_get('cid', 'intval');
        $sort = $this->_get('sort', 'trim', 'new');
        //排序：最热(hot)，最新(new)
        switch ($sort) {
            case 'hot':
                $sort_order = 'buy_num DESC,id DESC';
                break;
            case 'new':
                $sort_order = 'sign_date DESC';
                break;
        }
        $cname = D('score_item_cate')->get_name($cid);
        $where = array('status'=>'1');
        $where['cate_id'] = 8;
        $where['win'] = array('NEQ','');
        

        $score_item = M('score_item');
        $count = $score_item->where($where)->count('id');
        $pager = $this->_pager($count, 20);
        $item_list = $score_item->where($where)->order($sort_order)->limit($pager->firstRow.','.$pager->listRows)->select();
        $item_finish = $score_item->where($where)->order($sort_order)->limit(5)->select();
        $this->assign('item_list', $item_list);
        $this->assign('page_bar', $pager->fshow());
        $this->assign('cid', $cid);
        $this->assign('sort', $sort);
        $this->assign('cname', $cname);
        $this->assign('page_seo',set_seo("礼品兑换"));
        $this->display();
    }

    /**
     * 积分商品详细页
     */
    public function detail() {
        $id = $this->_get('id', 'intval');
        !$id && $this->_404();
        $item_mod = M('score_item');
        $item = $item_mod->field('id,title,img,score,coin,stock,user_num,buy_num,desc,cate_id,win,sign_date')->find($id);
        $exchange_desc = M('article_page')->where(array('cate_id'=>'7'))->getField('info');
        $this->assign('exchange_desc', $exchange_desc);
        $this->assign('item', $item);
		//兑换记录(首页不做分页)
        $count = M("score_order")->where("item_id=$id")->count('id');
        $pagesize = 20;
        $pager = $this->_pager($count, $pagesize);
        $pager_bar = $pager->fshow();
		$list = M("score_order")->where("item_id=$id")->order('add_time desc,id desc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
        
		foreach($list as $key=>$val){
			$list[$key]['uname']=get_uname($val['uid']);
            if($item['cate_id'] == 8 ){
                if( $item['sign_date'] !=""){
                    if($item['sign_date'] >time()){
                        $list[$key]['zero_info'] = "未开奖";}
                        else{
                            if($item['win'] !="" && $list[$key]['luckdraw_num'] == $item['win']){
                                $list[$key]['zero_info'] = "中奖";
                            }
                            else{
                                $list[$key]['zero_info'] = "未中奖";
                            }

                    }
                }
            }
		}
         $expire = 0;
        if($item['sign_date'] < time()){
            $expire = 1;
        }
        $this->assign('pagebar', $pager_bar);
        $this->assign('expire',$expire);
		$this->assign('list',$list);
        $this->_config_seo();
        $this->display();
    }

    /**
     * 兑换
     */
    public function ec() {
        !$this->visitor->is_login && $this->ajaxReturn(0, L('login_please'));
        $id = $this->_get('id', 'intval');
        $num = $this->_get('num', 'intval', 1);
        if (!$id || !$num) $this->ajaxReturn(0, L('invalid_item'));
        $item_mod = M('score_item');
        $user_mod = M('user');
        $order_mod = D('score_order');
         $uid = $this->visitor->info['id'];
        $uname = $this->visitor->info['username'];
        $mobile = M("user")->field('mobile')->find($uid);
        if($mobile['mobile'] ==""){
            $this->ajaxReturn(0, '没有绑定手机号' . $mobile['mobile'] );
        }
       
        $item = $item_mod->find($id);
        !$item && $this->ajaxReturn(0, L('invalid_item'));
        $item['sign_date']<time() && $this->ajaxReturn(0,"抽奖已过期,不能再兑换,请关注其他抽奖商品");
	!$item['stock'] && $this->ajaxReturn(0, L('no_stock'));
        //金币够不
        $order_coin = $num * $item['coin'];
        $order_score = $num * $item['score'];

        $user_cf = $user_mod->where(array('id'=>$uid))->field('coin,score')->find();
        $user_cf['coin'] < $order_coin && $this->ajaxReturn(0, '没有足够的金币' );
        $user_cf['score'] < $order_score && $this->ajaxReturn(0, '没有足够的积分' );
        //限额
        $eced_num = $order_mod->where(array('uid'=>$uid, 'item_id'=>$item['id']))->sum('item_num');

        $luck_num = $order_mod->where(array('item_id'=>$item['id']))->sum('item_num');
        if ($item['user_num'] && $eced_num + $num > $item['user_num']) {
            $this->ajaxReturn(0, sprintf(L('ec_user_maxnum'), $item['user_num']));
        }

        $data = array(
            'uid' => $uid,
            'uname' => $uname,
            'item_id' => $item['id'],
            'item_name' => $item['title'],
            'item_num' => $num,
            'order_coin' => $order_coin,
            'order_score' => $order_score,
        );
        if($item['cate_id'] == 8){
            $data['order_coin'] = $item['coin'];
            $data['order_score'] = $item['score'];
            $data['item_num'] = 1;
            for ($x=1; $x<=$num; $x++) {
                 $data['luckdraw_num'] = ++$luck_num;
                if (false === $order_mod->create($data)) {
                    $this->ajaxReturn(0, L('ec_failed'));
                }
                $order_id = $order_mod->add();
            } 
        //    $this->ajaxReturn(0, '进入抽奖兑换');
        }
        else{
        if (false === $order_mod->create($data)) {
            $this->ajaxReturn(0, L('ec_failed'));
        }
        $order_id = $order_mod->add();
        }
        //扣除用户积分并记录日志
        $user_mod->where(array('id'=>$uid))->setDec('coin', $order_coin);
         $user_mod->where(array('id'=>$uid))->setDec('score', $order_score);
        $score_log_mod = D('score_log');
        $score_log_mod->create(array(
            'uid' => $uid,
            'uname' => $uname,
            'action' => 'exchange',
            'coin' => $order_coin*-1,
            'score' => $order_score*-1,
        ));
        $score_log_mod->add();

        //减少库存和增加兑换数量
        $item_mod->save(array(
            'id' => $item['id'],
            'stock' => $item['stock'] - $num,
            'buy_num' => $item['buy_num'] + $num,
        ));
        //返回
        if ($item['type'] == '1') {
            //如果是虚拟商品直接提示
            $this->ajaxReturn(1, L('ec_success'));
        } else {
            //如果是实物则弹窗询问收货地址
            $address_list = M('user_address')->field('id,consignee,address,zip,mobile')->where(array('uid'=>$uid))->select();
            $this->assign('address_list', $address_list);
            $this->assign('order_id', $order_id);
            $resp = $this->fetch('dialog:address');
            $this->ajaxReturn(2, L('please_input_address'), $resp);
        }
    }

    /**
     * 收货地址
     */
    public function address() {
        !$this->visitor->is_login && $this->ajaxReturn(0, L('login_please'));
        $order_id = $this->_post('order_id', 'intval');
        $address_id = $this->_post('address_id', 'intval');
        $consignee = $this->_post('consignee', 'trim');
        $address = $this->_post('address', 'trim');
        $zip = $this->_post('zip', 'trim');
        $mobile = $this->_post('mobile', 'trim');
        if (!$address_id && (!$order_id || !$consignee || !$address || !$mobile)) {
            $this->ajaxReturn(0, L('please_input_address_info'));
        }
        $order_mod = M('score_order');
        if (!$order_mod->where(array('uid'=>$this->visitor->info['id'], 'id'=>$order_id))->count('id')) {
            $this->ajaxReturn(0, L('order_not_foryou'));
        }
        $user_address_mod = M('user_address');
        if ($address_id) {
            $address = $user_address_mod->field('consignee,address,zip,mobile')->find($address_id);
        } else {
            $address = array(
                'uid' => $this->visitor->info['id'],
                'consignee' => $consignee,
                'address' => $address,
                'zip' => $zip,
                'mobile' => $mobile,
            );
            //添加收货地址
            $user_address_mod->add($address);
        }
        $result = $order_mod->save(array(
            'id' => $order_id,
            'consignee' => $address['consignee'],
            'address' => $address['address'],
            'zip' => $address['zip'],
            'mobile' => $address['mobile'],
        ));
        $this->ajaxReturn(1, L('ec_success'));
    }

    /**
     * 积分规则
     */
    public function rule() {
        $info = M('article_page')->find(6);
        $this->assign('info', $info);
        $this->_config_seo();
        $this->display();
    }
}
