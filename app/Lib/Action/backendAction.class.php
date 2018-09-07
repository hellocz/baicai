<?php
/**
 * 后台控制器基类
 *
 * @author andery
 */
class backendAction extends baseAction
{
    protected $_name = '';
    protected $menuid = 0;
    public function _initialize() {
        parent::_initialize();
        $this->_name = $this->getActionName();
        $this->check_priv();
        $this->menuid = $this->_request('menuid', 'trim', 0);
        if ($this->menuid) {
            $sub_menu = D('menu')->sub_menu($this->menuid, $this->big_menu);
            $selected = '';
            foreach ($sub_menu as $key=>$val) {
                $sub_menu[$key]['class'] = '';
                if (MODULE_NAME == $val['module_name'] && ACTION_NAME == $val['action_name'] && strpos(__SELF__, $val['data'])) {
                    $sub_menu[$key]['class'] = $selected = 'on';
                }
            }
            if (empty($selected)) {
                foreach ($sub_menu as $key=>$val) {
                    if (MODULE_NAME == $val['module_name'] && ACTION_NAME == $val['action_name']) {
                        $sub_menu[$key]['class'] = 'on';
                        break;
                    }
                }
            }
            $this->assign('sub_menu', $sub_menu);
        }
        $this->assign('menuid', $this->menuid);
    }

    /**
     * 列表页面
     */
    public function index() {
        $map = $this->_search();
        if($this->_name == 'item' && $this->_request('keyword', 'trim')!="" && ($this->_request('status', 'trim')=="" || $this->_request('status', 'trim')=="1") && $this->_request('time_start', 'trim')=="" && $this->_request('time_end', 'trim')==""){ 
              $p = $this->_get('p', 'intval', 1);
            $keyword = $this->_request('keyword', 'trim');
        require LIB_PATH . 'Pinlib/php/lib/XS.php';
        $xs = new XS('baicai');
        $search = $xs->search;   //  获取搜索对象
        $search->setLimit(20,20*($p-1)); 
        $search->setSort('add_time',false);
        $docs = $search->setQuery($keyword)->search();
        //$docs = $search->search();
        $count = $search->count();
        $pager = new Page($count, 20);
        

        $item_list = Array();

         foreach ($docs as $doc) {
            if($str==""){
                 $str=$doc->id;
            }
            else{
               $str.=",".$doc->id;
            }
        }
        $field = 'id,uid,uname,orig_id,title,intro,img,price,likes,comments,comments_cache,url,zan,hits,go_link,add_time,cate_id,status';
        $str && $where['id'] = array('in', $str);
        $str && $item_list = M("item")->field($field)->where($where)->order("add_time desc")->select();

        foreach ($item_list as $key=>$val) {
            /*
            if($val["sh_time"]>$val["ds_time"]){
                $item_list[$key]['add_time']=$val["sh_time"];
                
            }else{
                $item_list[$key]['add_time']=$val["ds_time"];
                
            }
            */
            $item_list[$key]['zan'] = $item_list[$key]['zan']   +intval($item_list[$key]['hits'] /10);
            isset($val['comments_cache']) && $item_list[$key]['comment_list'] = unserialize($val['comments_cache']);
            $item_list[$key]['orig_name']=getly($val['orig_id']);
        }       
        
    
        $this->assign('list', $item_list);
        
        $page = $pager->show();
        $this->assign("page", $page);
        $this->assign('list_table', true);
        if($count == 0 ){
       $where_amazon['go_link'] =array('like',"%$keyword%");
       $item_list = M("item")->field($field)->where($where_amazon)->order("add_time desc")->select();
       $this->assign('list', $item_list);
       }
        }
            else{
        $mod = D($this->_name);
        !empty($mod) && $this->_list($mod, $map);
        }
        $this->display();
    }

    /**
     * 添加
     */
    public function add() {
        $mod = D($this->_name);
        if (IS_POST) {
            if (false === $data = $mod->create()) {
                IS_AJAX && $this->ajaxReturn(0, $mod->getError());
                $this->error($mod->getError());
            }
            if (method_exists($this, '_before_insert')) {
                $data = $this->_before_insert($data);
            }
            if( $mod->add($data) ){
                if( method_exists($this, '_after_insert')){
                    $id = $mod->getLastInsID();
                    $this->_after_insert($id);
                }
                IS_AJAX && $this->ajaxReturn(1, L('operation_success'), '', 'add');
                $this->success(L('operation_success'));
            } else {
                IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
                $this->error(L('operation_failure'));
            }
        } else {
            $this->assign('open_validator', true);
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(1, '', $response);
            } else {
                $this->display();
            }
        }
    }

    /**
     * 修改
     */
    public function edit()
    {
        $mod = D($this->_name);
        $pk = $mod->getPk();
        if (IS_POST) {
            if (false === $data = $mod->create()) {
                IS_AJAX && $this->ajaxReturn(0, $mod->getError());
                $this->error($mod->getError());
            }
            if (method_exists($this, '_before_update')) {
                $data = $this->_before_update($data);
            }
            if($this->_name == 'user'){
                $x = $mod->where('id ='.$data['id'])->find();
                 $sb = $data['score']-$x['score'];
            }
             if($this->_name == 'article'){
                $score_article  = "publish_article" . $data['id'];
                $x = $mod->where('id ='.$data['id'])->find();
                $score_log = M('score_log')->where("action='$score_article'")->find();
            }
            
            if (false !== $mod->save($data)) {
                if($sb!=0){
                    set_score_log(array('id'=>$x['id'],'username'=>$x['username']),'ssbx',$sb,'','','');
                    if($sb>0)$sb="+".$sb;
                    $xc = array();
                    $xc['ftid']=$x['id'];
                    $xc['to_id']=$x['id'];
                    $xc['to_name']=$x['username'];
                    $xc['from_id']=$_SESSION['admin']['id'];
                    $xc['from_name']=$_SESSION['admin']['username'];
                    $xc['add_time']=time();
                    $xc['info'] ='小编修改了你的积分:'.$sb;
                    M('message')->add($xc);
                }
                if($score_article && false == $score_log && ($data['status'] == 1 || $data['status'] == 4)){
                    $user = M('user')->where('id='.$x['uid'])->find();
                    $score = 20;
                    $coin = 50;
                    $offer = 50;
                    $exp = 50;
                     set_score($user,$score,$coin,$offer,$exp);
                    //积分日志
                    $xc = array();
                    $xc['ftid']=$x['uid'];
                    $xc['to_id']=$x['uid'];
                    $xc['to_name']=$x['uname'];
                    $xc['from_id']=0;
                    $xc['from_name']='tryine';
                    $xc['add_time']=time();
                    if($data['cate_id'] ==10){
                        $xc['info'] ='您的晒单>>'.$data['title'].'通过审核，感谢您的爆料,系统给您奖励积分：'.$score.'，金币：'.$coin.'，贡献值：'.$offer.'，经验：'.$exp.'.';
                    }else{
                        $xc['info'] ='您的攻略>>'.$data['title'].'通过审核，感谢您的爆料,系统给您奖励积分：'.$score.'，金币：'.$coin.'，贡献值：'.$offer.'，经验：'.$exp.'.';
                    }

                    M('message')->add($xc);
                    set_score_log($user,$score_article,"$score","$coin","$offer","$exp");
                }
                if($this->_name == 'item_cate'){
                   $child_cates = $mod->where("pid=" . $data['id'])->select();
                   if(!empty($child_cates)){
                    $spid = $this->_mod->where(array('id'=>$data['id']))->getField('spid');
                    $new_spid = $spid . $data['id'] . "|";
                    foreach ($child_cates as $child_cate) {
                        $child_cate['spid'] = $new_spid;
                        $mod->save($child_cate);
                    }
                   }
                }

                if( method_exists($this, '_after_update')){
                    $id = $data['id'];
                    $this->_after_update($id);
                }
                IS_AJAX && $this->ajaxReturn(1, L('operation_success'), '', 'edit');
                $this->success(L('operation_success'));
            } else {
                IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
                $this->error(L('operation_failure'));
            }
        } else {
            $id = $this->_get($pk, 'intval');
            $info = $mod->find($id);
            $this->assign('info', $info);
            $this->assign('open_validator', true);
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(1, '', $response);
            } else {
                $this->display();
            }
        }
    }
    function fuzhi(){
        $mod = D($this->_name);
        $pk = $mod->getPk();
        if (IS_POST) {
            if (false === $data = $mod->create()) {
                IS_AJAX && $this->ajaxReturn(0, $mod->getError());
                $this->error($mod->getError());
            }
            if (method_exists($this, '_before_update')) {
                $data = $this->_before_update($data);
            }
            if (false !== $mod->save($data)) {
                if( method_exists($this, '_after_update')){
                    $id = $data['id'];
                    $this->_after_update($id);
                }
                IS_AJAX && $this->ajaxReturn(1, L('operation_success'), '', 'edit');
                $this->success(L('operation_success'));
            } else {
                IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
                $this->error(L('operation_failure'));
            }
        } else {
            $id = $this->_get($pk, 'intval');
            $info = $mod->find($id);
            $this->assign('info', $info);
            $this->assign('open_validator', true);
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(1, '', $response);
            } else {
                $this->display();
            }
        }
    }

    /**
     * ajax修改单个字段值
     */
    public function ajax_edit()
    {
        //AJAX修改数据
        $mod = D($this->_name);
        $pk = $mod->getPk();
        $id = $this->_get($pk, 'intval');
        $field = $this->_get('field', 'trim');
        $val = $this->_get('val', 'trim');
        //允许异步修改的字段列表  放模型里面去 TODO
        $mod->where(array($pk=>$id))->setField($field, $val);
        $this->ajaxReturn(1);
    }

    /**
     * 删除
     */
    public function delete()
    {
        $mod = D($this->_name);
        $pk = $mod->getPk();
        $ids = trim($this->_request($pk), ',');
        if ($ids) {
            if (false !== $mod->delete($ids)) {
                IS_AJAX && $this->ajaxReturn(1, L('operation_success'));
                $this->success(L('operation_success'));
            } else {
                IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
                $this->error(L('operation_failure'));
            }
        } else {
            IS_AJAX && $this->ajaxReturn(0, L('illegal_parameters'));
            $this->error(L('illegal_parameters'));
        }
    }

    public function delete_tk(){
        $mod = D($this->_name); $del_mod = D('tk');
        $pk = $mod->getPk();
        $ids = trim($this->_request($pk), ',');
        if ($ids) {
            if (false !== $del_mod->delete($ids)) {
                IS_AJAX && $this->ajaxReturn(1, L('operation_success'));
                $this->success(L('operation_success'));
            } else {
                IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
                $this->error(L('operation_failure'));
            }
        } else {
            IS_AJAX && $this->ajaxReturn(0, L('illegal_parameters'));
            $this->error(L('illegal_parameters'));
        }
    }

    /**
     * 获取请求参数生成条件数组
     */
    protected function _search() {
        //生成查询条件
        $mod = D($this->_name);

        $map = array();
        foreach ($mod->getDbFields() as $key => $val) {
            if (substr($key, 0, 1) == '_') {
                continue;
            }
            if ($this->_request($val)) {
                $map[$val] = $this->_request($val);
            }
        }
        return $map;
    }

    /**
     * 列表处理
     *
     * @param obj $model  实例化后的模型
     * @param array $map  条件数据
     * @param string $sort_by  排序字段
     * @param string $order_by  排序方法
     * @param string $field_list 显示字段
     * @param intval $pagesize 每页数据行数
     */
    protected function _list($model, $map = array(), $sort_by='', $order_by='', $field_list='*', $pagesize=20)
    {
        //排序
        $mod_pk = $model->getPk();
        if ($this->_request("sort", 'trim')) {
            $sort = $this->_request("sort", 'trim');
        } else if (!empty($sort_by)) {
            $sort = $sort_by;
        } else if ($this->sort) {
            $sort = $this->sort;
        } else {
            $sort = $mod_pk;
        }
        if ($this->_request("order", 'trim')) {
            $order = $this->_request("order", 'trim');
        } else if (!empty($order_by)) {
            $order = $order_by;
        } else if ($this->order) {
            $order = $this->order;
        } else {
            $order = 'DESC';
        }

        //如果需要分页
        if ($pagesize) {
            $count = $model->where($map)->count($mod_pk);
            $pager = new Page($count, $pagesize);
        }
		
        $select = $model->field($field_list)->where($map)->order($sort . ' ' . $order);
	
        $this->list_relation && $select->relation(true);
        if ($pagesize) {
            $select->limit($pager->firstRow.','.$pager->listRows);
            $page = $pager->show();
            $this->assign("page", $page);
        }
        $list = $select->select();
		
		
		//echo "<pre>";
		//var_dump($list);
		
        $this->assign('list', $list);
        $this->assign('list_table', true);
    }

    public function check_priv() {

        if (MODULE_NAME == 'attachment') {
            return true;
        }
        if ( (!isset($_SESSION['admin']) || !$_SESSION['admin']) && !in_array(ACTION_NAME, array('login','verify_code')) ) {
            $this->redirect('index/login');
        }
        if(isset($_SESSION['admin']['role_id']) && $_SESSION['admin']['role_id'] == 1) {
            return true;
        }
        if(isset($_SESSION['admin']['role_id']) && $_SESSION['admin']['role_id'] == 2) {
            return true;
        }
        if (in_array(MODULE_NAME, explode(',', 'index'))) {
            return true;
        }
        $menu_mod = M('menu');
        $menu_id = $menu_mod->where(array('module_name'=>MODULE_NAME, 'action_name'=>ACTION_NAME))->getField('id');
        
        //$priv_mod = D('admin_role_priv');
        $priv_mod = D('admin_auth');
        
        if (MODULE_NAME == 'item_cate') {
            return true;
        }
        $r = $priv_mod->where(array('menu_id'=>$menu_id, 'role_id'=>$_SESSION['admin']['role_id']))->count();
        if (!$r) {
            $this->error(L('_VALID_ACCESS_'));
        }
    }
    
    protected function update_config($new_config, $config_file = '') {
        !is_file($config_file) && $config_file = CONF_PATH . 'home/config.php';
        if (is_writable($config_file)) {
            $config = require $config_file;
            $config = array_merge($config, $new_config);
            file_put_contents($config_file, "<?php \nreturn " . stripslashes(var_export($config, true)) . ";", LOCK_EX);
            @unlink(RUNTIME_FILE);
            return true;
        } else {
            return false;
        }
    }
}
