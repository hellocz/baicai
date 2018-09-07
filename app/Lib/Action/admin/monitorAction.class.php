<?php

class monitorAction extends backendAction{

    public function _initialize() {
        parent::_initialize();
    $this->_mod = D('item_monitor');
		$this->mod = D('item_monitor');
    }

    public function index() {
       $jd_where['orig_id'] = array('in','358,393');
       $jd['total'] = $this->mod->where($jd_where)->count();
       $jd_where['status'] = array('neq',2);
       $jd["process"] = $this->mod->where($jd_where)->count();
       $jd_where['status'] = 2;
       $jd["scaned"] = $this->mod->where($jd_where)->count();

       $amazon_where['orig_id'] = array('in','506,2');
       $amazon["total"] = $this->mod->where($amazon_where)->count();
       $amazon_where['status'] = array('neq',2);
       $amazon["process"] = $this->mod->where($amazon_where)->count();
       $amazon_where['status'] = 2;
       $amazon["scaned"] = $this->mod->where($amazon_where)->count();

       $tb_where['orig_id'] = array('in','29,3,5');
       $tb["total"] = $this->mod->where($tb_where)->count();
       $tb_where['status'] = array('neq',2);
       $tb["process"] = $this->mod->where($tb_where)->count();
       $tb_where['status'] = 2;
       $tb["scaned"] = $this->mod->where($tb_where)->count();

       $this->assign("jd",$jd);
       $this->assign("amazon",$amazon);
       $this->assign("tb",$tb);
       $this->display();
    }

    public function do_action(){
      $id = $this->_post('id','intval');
      $name = $this->_post('name','trim');
      $result = $this->$name($id);
      if($result == 0){
        $this->ajaxReturn(0, "fail");
      }
      else{
      $this->ajaxReturn(1, "success");
      }
    }

    public function stop_scan($id){
      switch ($id) {
        case 1:
          # jingdong
        F("jd",0);
          break;
        case 2:
          # amazon
        F("am",0);
          break;
        case 3:
          # taobao
        F("tb",0);
          break;
        default:
        $id = 0;
          break;
      }
        return $id;
    }

    public function continue_scan($id){
      switch ($id) {
        case 1:
          # jingdong
        F("jd",1);
          break;
        case 2:
          # amazon
        F("am",1);
          break;
        case 3:
          # taobao
        F("tb",1);
          break;
        default:
        $id = 0;
          break;
      }
        return $id;
    }

    public function remove_draft($id){
      switch ($id) {
        case 1:
          # jingdong
        $where['orig_id'] = array('in','358,393');
          break;
        case 2:
          # amazon
        $where['orig_id'] = array('in','506,2');
          break;
        case 3:
          # taobao
        $where['orig_id'] = array('in','29,3,5');
          break;
        default:
        $id = 0;
          break;
      }
      if($id != 0){
        $where['status'] = 8;
        $where['uname'] = "小白白";
        $result = D("item")->where($where)->delete(); 
        return $result;
      }
      return $id;
    }

    public function begin($id){
      switch ($id) {
        case 1:
          # jingdong
        $where['orig_id'] = array('in','358,393');
          break;
        case 2:
          # amazon
        $where['orig_id'] = array('in','506,2');
          break;
        case 3:
          # taobao
        $where['orig_id'] = array('in','29,3,5');
          break;
        default:
        $id = 0;
          break;
      }
      if($id != 0){
        $where['status'] = 2;
        $result = $this->mod->where($where)->setField("status",0);
        if($id == 2){
          $where['status'] = array("gt",3);
          $this->mod->where($where)->setDec("status");
        }
        return $result;
      }
      return $id;
    }
}