<?php

class messageAction extends userbaseAction {

    /**
     * 私信
     */
    public function index() {
        $uid = $this->visitor->info['id'];
        //以人为单位 查找与我对话的人
        $message_mod = M('message');
        $pagesize = 8;
        $map = "from_id > 0 AND ((from_id = '".$uid."' AND status<>2) OR (to_id = '".$uid."' AND status<>3))";
        $result = $message_mod->field('id')->where($map)->group('ftid')->select();
        $count = count($result);
        if ($count) {
            $pager = $this->_pager($count, $pagesize);
            $res_list = $message_mod->field('MAX(id) as id,COUNT(id) as num')->where($map)->group('ftid')->order('id DESC')->limit($pager->firstRow.','.$pager->listRows)->select();
            $talks = array();
            foreach ($res_list as $val) {
                $talks[$val['id']] = $val['num'];
            }
            $ids = array_keys($talks);
            if ($ids) {
                $talk_list = $message_mod->where(array('id'=>array('in', $ids)))->order('id DESC')->select();
                foreach ($talk_list as $key=>$val) {
                    //对方信息
                    if ($val['from_id'] == $uid) {
                        $talk_list[$key]['ta_id'] = $val['to_id'];
                        $talk_list[$key]['ta_name'] = $val['to_name'];
                    } else {
                        $talk_list[$key]['ta_id'] = $val['from_id'];
                        $talk_list[$key]['ta_name'] = $val['from_name'];
                    }
                    $talk_list[$key]['num'] = $talks[$val['id']];
                }
                $this->assign('talk_list', $talk_list);
                $this->assign('page_bar', $pager->fshow());
            }
            D('user_msgtip')->clear_tip($uid, 3);
        }
        $index_count=M('message')->where("to_id='".$uid."' and ck_status=0 and from_id >0")->count();
        $this->assign('index_count',$index_count);
        $this->assign('count', $count);
        $this->_curr_menu('message');
        $this->_config_seo();
        $this->display();
    }

    /**
     * 消息详细
     */
    public function talk() {
        $ftid = $this->_get('ftid');
        $uid = $this->visitor->info['id'];
        $message_mod = M('message');
        $map = "ftid='".$ftid."' AND ((from_id = '".$uid."' AND status<>2) OR (to_id = '".$uid."' AND status<>3))";
        //更新状态
        $message_mod->where($map)->setField('status', 0);
        //显示列表
        $pagesize = 8;
        $count = $message_mod->where($map)->order('id DESC')->count('id');
        $pager = $this->_pager($count, $pagesize);
        $message_list = $message_mod->where($map)->order('id DESC')->limit($pager->firstRow.','.$pager->listRows)->select();
        if ($message_list[0]['from_id'] == $uid) {
            $ta_id = $message_list[0]['to_id'];
            $ta_name = $message_list[0]['to_name'];
        } else {
            $ta_id = $message_list[0]['from_id'];
            $ta_name = $message_list[0]['from_name'];
        }
        M()->query("update try_message set ck_status=1 where ftid='".$ftid."' AND to_id='".$uid."'");//更新消息查看状态
        $_SESSION['user_info']['message']=M('message')->where("to_id='".$uid."' and ck_status=0")->count();

        $this->assign('message_list', $message_list);
        $this->assign('ta_id', $ta_id);
        $this->assign('ta_name', $ta_name);
        $this->assign('ftid', $ftid);
        $this->assign('page_bar', $pager->fshow());
        $this->_curr_menu('message');
        $this->_config_seo();
        $this->display();
    }

    /**
     * 选择发送目标
     */
    public function target() {
        //获取最近联系人
        $uid = $this->visitor->info['id'];
        $message_mod = M('message');
        $res_list = $message_mod->field('from_id,from_name,to_id,to_name')->where("from_id > 0 AND ((from_id = '".$uid."') OR (to_id = '".$uid."'))")->group('ftid')->order('add_time DESC')->limit(10)->select();
        $last_user = array();
        foreach ($res_list as $key=>$val) {
            if ($val['from_id'] == $uid) {
                $last_user[$key]['uid'] = $val['to_id'];
                $last_user[$key]['uname'] = $val['to_name'];
            } else {
                $last_user[$key]['uid'] = $val['from_id'];
                $last_user[$key]['uname'] = $val['from_name'];
            }
        }
        $this->assign('last_user', $last_user);
        $this->_curr_menu('message');
        $this->_config_seo();
        $this->display();
    }

    /**
     * 搜索用户
     */
    public function search_target() {
        $search_uname = $this->_post('search_uname', 'trim');
        $user_list = M('user')->field('id,username')->where(array('username'=>array('like', '%'.$search_uname.'%')))->limit('0,10')->select();
        $this->assign('user_list', $user_list);
        $resp = $this->fetch('search_target');
        $this->ajaxReturn(1, '', $resp);
    }

    /**
     * 写信
     */
    public function write() {
        $ta_id = $this->_get('to_id', 'intval');
        !$ta_id && $this->_404();
        $ta_name = M('user')->where(array('id'=>$ta_id))->getField('username');
        $this->assign('ta_id', $ta_id);
        $this->assign('ta_name', $ta_name);
        $this->_curr_menu('message');
        $this->_config_seo();
        $this->display();
    }

    /**
     * 发布
     */
    public function publish() {
        $uid= $this->visitor->info['id'];
  //      ( grade($uid) < 2) &&  $this->ajaxReturn(0, '您的等级还不够，需要升到 2 级才能发送私息！');
        $score = M('user')->where(array('id'=>$uid))->getField('score');
        ($score <2)&&  $this->ajaxReturn(0, '您的积分不够了');
        foreach ($_POST as $key=>$val) {
            $_POST[$key] = Input::deleteHtmlTags($val);
        }
        $to_id = $this->_post('to_id', 'intval');
        $content = $this->_post('content', 'trim');
        if (!$content) {
            $this->ajaxReturn(0, L('message_content_empty'));
        }
        $to_name = M('user')->where(array('id'=>$to_id))->getField('username');
        $ftid = $this->visitor->info['id'] + $to_id;
        $data = array(
            'ftid' => $ftid,
            'from_id' => $this->visitor->info['id'],
            'from_name' => $this->visitor->info['username'],
            'to_id' => $to_id,
            'to_name' => $to_name,
            'info' => $content,
        );
        $message_mod = D('message');
        $info = $message_mod->create($data);
        $info['id'] = $message_mod->add();
        if ($info['id']) {
            //提示接收者
            D('user_msgtip')->add_tip($to_id, 3);
            M("user")->where("id=$uid")->setDec("score",2);
            //积分日志
            set_score_log(array('id'=>$uid,'username'=>$this->visitor->info['username']),'shixin',-2,'','','');
            $this->assign('info', $info);
            $resp = $this->fetch('list_unit');
            $this->ajaxReturn(1, L('send_message_success'), $resp);
        } else {
            $this->ajaxReturn(0, L('illegal_parameters'));
        }
    }

        /**
     * 发布
     */
    public function report_bug() {
        $uid= $this->visitor->info['id'];
        foreach ($_POST as $key=>$val) {
            $_POST[$key] = Input::deleteHtmlTags($val);
        }
        $to_id = $this->_post('to_id', 'intval');
        $content = $this->_post('content', 'trim');
        if (!$content) {
            $this->ajaxReturn(0, L('message_content_empty'));
        }
        $to_name = M('user')->where(array('id'=>$to_id))->getField('username');
        $ftid = $this->visitor->info['id'] + $to_id;
        $data = array(
            'ftid' => $ftid,
            'from_id' => $this->visitor->info['id'],
            'from_name' => $this->visitor->info['username'],
            'to_id' => $to_id,
            'to_name' => $to_name,
            'info' => $content,
        );
        $message_mod = D('message');
        $info = $message_mod->create($data);
        $info['id'] = $message_mod->add();
        if ($info['id']) {
            //提示接收者
            D('user_msgtip')->add_tip($to_id, 3);
            M("user")->where("id=$uid")->setInc("score",1);
            //积分日志
            set_score_log(array('id'=>$uid,'username'=>$this->visitor->info['username']),'report_bug',1,'','','');
            $this->assign('info', $info);
            $resp = $this->fetch('list_unit');
            $this->ajaxReturn(1, L('send_message_success'), $resp);
        } else {
            $this->ajaxReturn(0, L('illegal_parameters'));
        }
    }

    /**
     * 删除短信
     */
    public function del() {
        $data=array();
        $data['mid'] = $this->_get('mid', 'intval');
        $message_mod = D('ssb');
        $data['uid'] = $this->visitor->info['id'];
        $data['type'] = 1;
        $r = $message_mod->add($data);
        if ($r) {
            $this->ajaxReturn(1, L('delete_message_success'));
        } else {
            $this->ajaxReturn(0, L('delete_message_failed'));
        }
    }

    /**
     * 删除整个对话
     */
    public function delall() {
        $ftid = $this->_get('ftid', 'intval');
        !$ftid && $this->redirect('message/index');
        $message_mod = D('message');
        $res_list = $message_mod->field('id')->where(array('ftid'=>$ftid))->select();
        $mid_arr = array();
        foreach ($res_list as $val) {
            $mid_arr[] = $val['id'];
        }
        $message_mod->user_delete($mid_arr, $this->visitor->info['id']);
        $this->redirect('message/index');
    }

    /**
     * 系统通知
     */
    public function system() {
        $uid = $this->visitor->info['id'];
        $message_mod = M('message');
        $pagesize = 8;
        $map = array();
        $map['from_id'] = '0';
        $map['to_id'] = $uid;
        $xx = $message_mod->where($map)->select();
        $sx = M('ssb');
        $where=array();
        $arr = array();
        foreach($xx as $key=>$v){
            $where['uid'] = $uid;
            $where['mid'] = $v['id'];
            $where['type'] = 1;
          $count = $sx->where($where)->count('id');
            if($count == 0){
                $arr[] = $v['id'];
            }
        }

        $map['id']  = array('in', implode(",",$arr));
        $pager = $this->_pager(count($arr), $pagesize);
        $system_list = $message_mod->where($map)->order('id DESC')->limit($pager->firstRow.','.$pager->listRows)->select();
        D('user_msgtip')->clear_tip($uid, 4);
        M()->query("update try_message set ck_status=1 where from_id=0 AND to_id='".$uid."'");//更新消息查看状态
        $_SESSION['user_info']['message']=M('message')->where("to_id='".$uid."' and ck_status=0")->count();
        $index_count=M('message')->where("to_id='".$uid."' and ck_status=0 and from_id >0")->count();
        $this->assign('index_count',$index_count);
        $this->assign('system_list', $system_list);
        $this->assign('page_bar', $pager->fshow());
		$this->_curr_menu('message');
        $this->_config_seo();
        $this->display();
    }
}