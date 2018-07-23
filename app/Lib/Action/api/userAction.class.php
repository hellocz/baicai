<?php

/*
* 用户类
* Created by PhpStorm.
* Author: 初心 [jialin507@foxmail.com]
* Date: 2017/4/11
*/

/**
 * mobile 18569555555
 * uid 192795
 * password 123456
 * Class userAction
 */
class userAction extends userbaseAction
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
     * 用户注册
     * @param $data
     */
    public function register($data) {
            //封IP start
            $kill_ip=C('pin_kill_ip');
            $kill_ip = explode("\n",$kill_ip);
            $myip = get_client_ip();
            if(in_array($myip,$kill_ip)){
                echo get_result(20001,[], '您的IP已被禁用');return ;
            }
            //封IP end
//            $data['username'] = $data['mobile'];
            $data['email'] = '0@0.c';
            $data['gender'] = 0;

//            $smscode = M('codesms')->where(['mobile'=>$data['mobile']])->find();
//            if($smscode){
//                if($smscode['out_time'] < time()){
//                    echo get_result(20001,[], "验证码超时");return ;
//                }
//                if($data['captcha'] != $smscode['code']){
//                    echo get_result(20001,[], "验证码错误");return ;
//                }
//            }else{
//                echo get_result(20001,[], "未请求验证码");return ;
//            }
//            M('codesms')->where(['mobile'=>$data['mobile']])->delete();

            //方式
//            $type = $this->_post('type', 'trim', 'reg');
//            if ($type == 'reg') {
//                //验证
//                $agreement = $this->_post('agreement');
//                !$agreement && $this->error(L('agreement_failed'));
//
//                $captcha = $this->_post('captcha', 'trim');

//            }

 //          if ($data['password'] != $data['repassword']) {
 //              echo get_result(20001,[], "两次输入的密码不一致");return ;
 //          }

//            //用户禁止
//            $ipban_mod = D('ipban');
//            $ipban_mod->clear(); //清除过期数据
//            $is_ban = $ipban_mod->where("(type='name' AND name='".$data['username']."') OR (type='email' AND name='".$data['email']."')")->count();
//            if($is_ban){
//                echo get_result(20001,[], '您的帐号或邮箱被禁止注册');return ;
//            }
            //连接用户中心
            $passport = $this->_user_server();


            //注册
            $uid = $passport->register($data['username'], $data['password'], $data['email'], $data['gender']);
            if(!$uid){
                echo get_result(20001,[],$passport->get_error());return ;
            }

            //给用户加积分
            M("user")->where("id=$uid")->setField(array('score'=>10,'exp'=>10));
            //积分日志
            set_score_log(array('id'=>$uid,'username'=>$data['username']),'register',10,'','',10);


            echo get_result(10001,$uid);
//        $data['mobile']

//            //是否通过朋友分享注册的
//            if(trim($_SESSION['tg'])!=''){
//                $suid = M("user")->field('try_user.*')->join("try_share as s on s.uid=try_user.id")->where("s.dm='$_SESSION[tg]'")->find();
//                //查找一天是否超过5次
//                $time=time();
//                $start=strtotime(date('Y-m-d',$time));
//                $end = strtotime(date('Y-m-d',$time))+24*3600;
//                $count = M("score_log")->where("add_time>$start and $end>add_time and uid=$suid[id] and action='share_register'")->count();
//                if($count<5){
//                    //给用户加积分
//                    M("user")->where("id=$suid[id]")->setField(array("coin"=>$suid['coin']+5,"offer"=>$suid['offer']+5,'score'=>$suid['score']+5,'exp'=>$suid['exp']+5));
//                    //积分日志
//                    set_score_log(array('id'=>$suid['id'],'username'=>$suid['username']),'share_register',5,5,5,5);
//                }
//            }
            //第三方帐号绑定
//            if (cookie('user_bind_info')) {
//                $user_bind_info = object_to_array(cookie('user_bind_info'));
//                $oauth = new oauth($user_bind_info['type']);
//                $bind_info = array(
//                    'pin_uid' => $uid,
//                    'keyid' => $user_bind_info['keyid'],
//                    'bind_info' => $user_bind_info['bind_info'],
//                );
//                $oauth->bindByData($bind_info);
//                //临时头像转换
//                $this->_save_avatar($uid, $user_bind_info['temp_avatar']);
//                //清理绑定COOKIE
//                cookie('user_bind_info', NULL);
//            }
//            //注册完成钩子
//            $tag_arg = array('uid'=>$uid, 'uname'=>$data['username'], 'action'=>'register');
//            tag('register_end', $tag_arg);
//            //登陆
//            $this->visitor->login($uid);
//            //登陆完成钩子
//            $tag_arg = array('uid'=>$uid, 'uname'=>$data['username'], 'action'=>'login');
//            tag('login_end', $tag_arg);
//            //同步登陆
//            $synlogin = $passport->synlogin($uid);
//            $this->success(L('register_successe').$synlogin, U('user/index'));


    }

    /**
     * 获取手机验证码
     */
    public function smscode($data) {
        //$code = '123456';

             $count = M('user')->where(['mobile'=>$data['mobile']])->count('id');
             if($count >0) {
                   echo get_result(20001, [], "手机号码已经被注册!");
                   return;
             }
        $code = rand('100000','999999');
        $msg = "您的短信验证码为".$code;
        file_get_contents("http://sms.253.com/msg/send?un=N2204759&pw=1w0Xqu5xC&phone=".$data['mobile']."&msg=$msg&rd=0");

        $smscode = M('codesms')->where(['mobile'=>$data['mobile']])->find();
        if($smscode){
            $res = M('codesms')->where(['mobile'=>$data['mobile']])->save(['code'=>$code,'out_time'=>time()+600]);
        }else{
            $res = M('codesms')->add(['mobile'=>$data['mobile'],'code'=>$code,'out_time'=>time()+600]);
        }
        if($res){
            echo get_result(10001,['code'=>$code]);
        }else{
            echo get_result(20001, [], "未知错误");
        }
    }

    /**
     * 用户登陆
     */
    public function login($data) {
        //封IP start
        $kill_ip=C('pin_kill_ip');
        $kill_ip = explode("\n",$kill_ip);
        $myip = get_client_ip();
        if(in_array($myip,$kill_ip)){
            echo get_result(20001,[], '您的账户已被禁用');return ;
        }
        //封IP end
        if (empty($data['mobile'])) {
            echo get_result(20001,[], '用户名不能为空');return ;
        }
        if (empty($data['password'])) {
            echo get_result(20001,[], '密码不能为空');return ;
        }
        //连接用户中心
        $passport = $this->_user_server();
        $uid = $passport->auth($data['mobile'], $data['password']);
        if (!$uid) {
            echo get_result(20001,[], '账号或密码错误');return ;
        }
        $userinfo = $passport->get($uid);
        $info = [
            'userid'    =>  $userinfo['id'],
            'username'    =>  $userinfo['username'],
            'gender'    =>  $userinfo['gender'],
            'score'    =>  $userinfo['score'],
        ];

        //登陆
//        $this->visitor->login($uid, $remember);
        echo get_result(10001,$info);
    }

    public function mobilelogin($data)
    {

        $user = M('user')->where(array('mobile'=>$data['mobile'],'username'=>$data['mobile'],'email'=>$data['mobile'],'_logic'=>'OR'))->field('id,username,gender,score,password')->find();//查找用户
	$passport = $this->_user_server();
        $uid = $passport->auth($user['username'], $data['password']);
        if($uid){
            $info = [
                'userid'    =>  $user['id'],
                'username'    =>  $user['username'],
                'gender'    =>  $user['gender'],
                'score'    =>  $user['score'],
            ];
            $notify_tag_count = M('notify_tag')->where(array("userid"=>$user['id']))->count('id');
            if($notify_tag_count == 0){
                $notify_tag = M('notify_tag');
                $notify_tag->add(array(
                'userid' => $user['id'],
                'tag' => "白菜",
                'p_sign' => 1,
                'f_sign' => 1
            ));
                $notify_tag->add(array(
                'userid' => $user['id'],
                'tag' => "手快有",
                'p_sign' => 1,
                'f_sign' => 1
            ));
                $notify_tag->add(array(
                'userid' => $user['id'],
                'tag' => "神价格",
                'p_sign' => 1,
                'f_sign' => 1
            ));
                $notify_tag->add(array(
                'userid' => $user['id'],
                'tag' => "BUG",
                'p_sign' => 1,
                'f_sign' => 1
            ));
            }
        }
	else{
	 echo get_result(20001,'账号或密码错误','账号或密码错误');
         return ;
	} 
        echo get_result(10001,$info);
    }

    public function getuserinfo($data)
    {
        $mod = M("user");
        //查询是否已签到
        $user = $mod->where("id=".$data['userid'])->find();
        $signtime=$user['sign_date'];
        $date = strtotime(date('Ymd'));
        $ds=intval($date-$signtime); 
        $userinfo = [];
        if($ds <= 0) {
            //已签到
            $userinfo['is_sign'] = 1;
        }else{
            //未签到
            $userinfo['is_sign'] = 0;
        }
        $userinfo['gender'] = $user['gender'];
        $userinfo['score'] = $user['score'];
        $userinfo['exp'] = $user['exp'];
        $userinfo['coin'] = $user['coin'];
        $userinfo['offer'] = $user['offer'];
        $userinfo['mobile'] = $user['mobile'];
        $userinfo['email'] = $user['email'];

        echo get_result(10001,$userinfo);
    }

    /**
     * 重置密码
     */
    public function resetpassword($data) {
        $passlen = strlen($data['password']);
        if ($passlen < 6 || $passlen > 20) {
            echo get_result(20001,[], '原密码长度不能小于6或大于20');return ;
        }
        $passlen = strlen($data['new_password']);
        if ($passlen < 6 || $passlen > 20) {
            echo get_result(20001,[], '新密码长度不能小于6或大于20');return ;
        }

        //连接用户中心
        $passport = $this->_user_server();
        $result = $passport->edit($data['userid'], $data['password'], array('password'=>$data['new_password']));
        if ($result) {
            echo get_result();;
        } else {
            echo get_result(20001,[], '原密码错误');return;
        }

    }

    //手机号码重置密码
    public function forgetpassword($data)
    {

        $passlen = strlen($data['password']);
        if ($passlen < 6 || $passlen > 20) {
            echo get_result(20001,[], '新密码长度不能小于6或大于20');return ;
        }

        $smscode = M('codesms')->where(['mobile'=>$data['mobile']])->find();

        if($smscode){
            if($smscode['out_time'] < time()){
                echo get_result(20001,[], "验证码超时");return ;
            }
            if($data['captcha'] != $smscode['code']){
                echo get_result(20001,[], "验证码错误");return ;
            }
        }else{
            echo get_result(20001,[], "未请求验证码");return ;
        }
        M('codesms')->where(['mobile'=>$data['mobile']])->delete();
        $uid = M("user")->where("mobile = '".$data['mobile']."'")->getField("id");

        //连接用户中心
        $passport = $this->_user_server();
        $result = $passport->edit($uid, '', array('password'=>$data['password']),true);
        if ($result) {
            echo get_result();
        } else {
            echo get_result(20001,[], '重置失败');return;
        }

    }

    /**
     * 获取用户头像
     */
    public function getimg($data) {
        $img = avatar($data['userid'],'32');
        echo get_result(10001,$img);
    }

    //改变收货地址
    public function setaddress($data) {
        $user_address_mod = M('user_address');
        $userid = $data['userid'];
        $id = $data['addressid']; //编辑地址id
        $type = $data['type']; //新增修改删除

        if ($type == 'del') {
            $user_address_mod->where(array('id'=>$id, 'uid'=>$userid))->delete();
            echo get_result(10001,'删除成功!');
            return ;
        }


        $consignee = $data['consignee'];
        $address = $data['address'];
        $zip = $data['zip'];
        $mobile = $data['mobile'];

        if ($type == 'edit') {
            $result = $user_address_mod->where(array('id'=>$id, 'uid'=>$userid))->save(array(
                'consignee' => $consignee,
                'address' => $address,
                'zip' => $zip,
                'mobile' => $mobile,
            ));
            if ($result) {
                echo get_result(10001,'修改成功!');
            } else {
                echo get_result(10001,'修改失败!');
            }
        }

        if ($type == 'add') {
            $result = $user_address_mod->add(array(
                'uid' => $userid,
                'consignee' => $consignee,
                'address' => $address,
                'zip' => $zip,
                'mobile' => $mobile,
            ));
            if ($result) {
                echo get_result(10001, '新增成功!');
            } else {
                echo get_result(10001, '新增失败!');
            }

        }
    }

    //创建推送tag

    public function notify_tag_create($data){
        $tag['userid'] = $data['userid'];
        $tag['tag'] = $data['tag'];
        $notify_tag = M("notify_tag");
        $list = $notify_tag->where(array('userid' => $tag['userid'],'tag'=> $tag['tag'] ))->find();
        if(count($list)>0){
            $list['p_sign'] = 1;
            $notify_tag->save($list);
            echo get_result(10001, $list['id']);
        }
        else{
        $result = $notify_tag->add(array(
            'userid' => $tag['userid'],
            'tag' => $tag['tag'],
            'p_sign' => 1,
            'f_sign' => 1
            ));
        if ($result) {
                echo get_result(10001, $result);
            } else {
                echo get_result(20001, '设置推送失败!');
            }
            }
    }

        //创建关注tag

    public function follow_tag_create($data){
        $tag['userid'] = $data['userid'];
        $tag['tag'] = $data['tag'];
        $notify_tag = M("notify_tag");
        $list = $notify_tag->where(array('userid' => $tag['userid'],'tag'=> $tag['tag'] ))->find();
        if(count($list)>0){
            $list['f_sign'] = 1;
            $notify_tag->save($list);
            echo get_result(10001, $list['id']);
        }
        else{
        $result = $notify_tag->add(array(
            'userid' => $tag['userid'],
            'tag' => $tag['tag'],
            'f_sign' => 1
            ));
        if ($result) {
                echo get_result(10001, $result);
            } else {
                echo get_result(20001, '设置关注失败!');
            }
            }
    }

      //查询某个用户的推送时段

    public function push_range_byuser($data){
        $userid = $data['userid'];
        $user = M("user");
        $list = $user->where(array('id'=>$userid))->field("push_range")->select();
       $code = 10001;
        if(count($list) < 1){
            $code = 10002;
        }
        echo get_result($code,$list);return ;
    }

    //更新推送时段

    public function push_range_modify($data){
        $push_info['push_range'] = $data['push_range'];
        $push_info['id'] = $data['userid'];
        $user = M("user");
        $user->save($push_info);
        echo get_result(10001, '更新成功!');
    }

     //查询某个用户下面所有推送tag

    public function notify_tag_byuser($data){
        $userid = $data['userid'];
        $notify_tag = M("notify_tag");
        $list = $notify_tag->where(array('userid'=>$userid))->select();
        $code = 10001;
        if(count($list) < 1){
            $code = 10002;
        }
        echo get_result($code,$list);return ;
    }

    //更新推送tag

    public function notify_tag_modify($data){
        $tag['id'] = $data['id'];
        $tag['tag'] = $data['tag'];
        $tag['userid'] = $data['userid'];
        $notify_tag = M("notify_tag");
        $list = $notify_tag->where(array('userid' =>$userid,'tag'=>$tag ))->select();
        if(count($list)>0){
            echo get_result(10001, '标签已存在!');
        }
        else{
        $notify_tag->save($tag);
        echo get_result(10001, '更新成功!');
        }
    }

     //删除推送tag

    public function notify_tag_del($data){
        $notify_tag = M("notify_tag");
        $tag['userid'] = $data['userid'];
        $tag['p_sign'] = 0;
        $tag['id'] = $data['id'];
        $notify_tag->save($tag);
        echo get_result(10001,'删除推送成功!');
    }

    //是否选中推送
    public function is_notify_tag($data){
        $notify_tag = M("notify_tag");
        $list = $notify_tag->where(array("tag"=>$data['tag'],'userid' =>$data['userid'],'p_sign'=>1))->select();
         if(count($list)>0){
            echo get_result(10001, '已关注推送!');
        }
        else{
            echo get_result(10002, '未关注推送!');
        }
    }

     //是否选中关注
    public function is_follow_tag($data){
        $notify_tag = M("notify_tag");
        $list = $notify_tag->where(array("tag"=>$data['tag'],'userid' =>$data['userid'],'f_sign'=>1))->select();
         if(count($list)>0){
            echo get_result(10001, '已关注!');
        }
        else{
            echo get_result(10002, '未关注!');
        }
    }

    //删除关注tag

    public function follow_tag_del($data){
        $notify_tag = M("notify_tag");
        $notify_tag->where(array("tag"=>$data['tag'],"userid"=>$data['userid']))->delete();
        echo get_result(10001,'删除关注成功!');
    }

    //查询收货地址
    public function getaddress($data)
    {
        $user_address_mod = M('user_address');
        $userid = $data['userid'];
        $address_list = $user_address_mod->where(array('uid'=>$userid))->field(['id','consignee','zip','mobile','address'])->select();
        if(!empty($address_list)){
            echo get_result(10001,$address_list);
            return ;
        }
        echo get_result(10002);

    }


    //用户等级
    public function grade($data){
        $page = $data['page'] * 10;
        $t=$data['type'];
        $pagesize=10;
        //经验值、等级
        $user = M("user")->field('id,score,coin,offer,exp')->where(['id'=>$data['userid']])->find();
        $grade_mod = M("grade");
        $log_mod = M("score_log");
        $user['grade'] = $grade_mod->where("min<=$user[exp] and max>=$user[exp]")->getField("grade");
        if($t=='score'){
            $num = $user['score'];
            //积分变更
            $list = $log_mod->where("uid=$user[id] and score<>0")
                ->field('action,score,add_time')
                ->order("add_time desc")
                ->limit($page-10, $pagesize)
                ->select();

        }
        if($t=='coin'){
            $num = $user['coin'];
            $list = $log_mod->where("uid=$user[id] and coin<>0")
                ->field('action,coin,add_time')
                ->order("add_time desc")
                ->limit($page-10, $pagesize)
                ->select();

        }
        if($t=='offer'){
            $num = $user['offer'];
            $list = $log_mod->where("uid=$user[id] and offer<>0")
                ->field('action,offer,add_time')
                ->order("add_time desc")
                ->limit($page-10, $pagesize)
                ->select();

        }
        if($t=='exp'){
            $num = $user['exp'];
            $list = $log_mod->where("uid=$user[id] and exp<>0")
                ->field('action,exp,add_time')
                ->order("add_time desc")
                ->limit($page-10, $pagesize)
                ->select();

        }

        foreach($list as $key=>$val){
            if($list[$key]['action'] == "register"){
               $list[$key]['action'] = "注册";
            }
            elseif($list[$key]['action'] == "login"){
               $list[$key]['action'] = "登陆";
            }
            elseif($list[$key]['action'] == "sign"){
               $list[$key]['action'] = "签到";
            }
            elseif($list[$key]['action'] == "shixin"){
               $list[$key]['action'] = "私信";
            }
            elseif($list[$key]['action'] == "exchange"){
               $list[$key]['action'] = "兑换";
            }
            elseif($list[$key]['action'] == "upload_avator"){
               $list[$key]['action'] = "更新照片";
            }
            elseif($list[$key]['action'] == "comment" OR $list[$key]['action'] == "comment1"){
               $list[$key]['action'] = "评论";
            }
            elseif($list[$key]['action'] == "publish_item"){
               $list[$key]['action'] = "爆料";
            }
            elseif($list[$key]['action'] == "hit_share"){
               $list[$key]['action'] = "点击分享";
            }
            elseif($list[$key]['action'] == "share"){
               $list[$key]['action'] = "分享";
            }
            elseif($list[$key]['action'] == "pubitem"){
               $list[$key]['action'] = "发布分享";
            }
            elseif($list[$key]['action'] == "ssbx"){
               $list[$key]['action'] = "小编修改";
            }
            elseif(stristr($list[$key]['action'],"publish_article") !== false){
               $list[$key]['action'] = "晒单";
            }
            }

        $code = 10001;
        if(count($list) < 1){
            $code = 10002;
        }
        echo get_result($code,['grade'=>$user['grade'],'num'=>$num,'list'=>$list]);return ;


    }


    //签到
    public function sign($data){
        $mod = M("user");
        //查询是否已签到
        $user = $mod->where("id=".$data['userid'])->find();
        $time = time();
        $signtime=$user['sign_date'];
        $date = strtotime(date('Ymd'));
        $ds=intval(($time-$signtime)/86400); //60s*60min*24h
        $data['id']=$user['id'];
        $data['sign_date']=$time;
        if($ds>1){//如果大于1，则签到清零+1,积分+8
            $data['score']=$user['score']+8;
            $data['exp']=$user['exp']+8;
            $data['sign_num']=1;
            $data['all_sign']=$user['all_sign']+1;
            $mod->save($data);
            //积分日志
            set_score_log($user,'sign',8,'','',8);
            echo get_result(10001,'您已连续签到1天，成功获取8个积分！');
        } elseif($signtime >= $date){//当天以签到
            echo get_result(10001,'您今天已签到');
        }else{//否则在原基础上+1
            $max_score = $user['sign_num']+8;
            $data['sign_num']=$user['sign_num']+1;
            $data['all_sign']=$user['all_sign']+1;
            if($max_score>15){$max_score=15;}
            $data['score']=$user['score']+$max_score;
            $data['exp']=$user['exp']+$max_score;
            $mod->save($data);
            //积分日志
            set_score_log($user,'sign',$max_score,'','',$max_score);
            echo get_result(10001,'您已连续签到'.$user['sign_num'].'天，成功获取'.$max_score.'个积分！');
        }


    }


    //绑定个人信息
    public function bindinfo($data){
        $mod = M("user");
        $save_data['id']= $data['userid'];
        $user = $mod->where("id=".$data['userid'])->find();

        if(!empty($data['mobile'])){
//            if(!empty($user['mobile'])){
//                echo get_result(10001,'绑定失败');
//                return ;
//            }
            $smscode = M('codesms')->where(['mobile'=>$data['mobile']])->find();

            if($smscode){
                if($smscode['out_time'] < time()){
                    echo get_result(20001,[], "验证码超时");return ;
                }
                if($data['captcha'] != $smscode['code']){
                    echo get_result(20001,[], "验证码错误");return ;
                }
            }else{
                echo get_result(20001,[], "未请求验证码");return ;
            }
            $save_data['mobile']=$data['mobile'];
            M('codesms')->where(['mobile'=>$data['mobile']])->delete();
        }
        if(!empty($data['email'])){
//            if(!empty($user['email'])){
//                echo get_result(10001,'绑定失败');
//                return ;
//            }
            $save_data['email']=$data['email'];
        }

        if($mod->save($save_data)){
            echo get_result(10001,'成功');return ;
        } else {
            echo get_result(10001,'失败');return ;
        }

    }

    //优惠券列表
    public function ticklist($data) {
        $mod_orig=M("item_orig");
        $mod = M('tick');
        //获取所有有优惠券的购物平台
        $arr_list = $mod_orig->where("id in(select distinct orig_id from try_tick where DATEDIFF(now() ,start_time)>0 and DATEDIFF(end_time,now())>0 )")->order("ordid asc,id asc")->select();
        $orig_list=array();
        foreach($arr_list as $key=>$val){
            $orig_list[$val['id']]=$val;
        }
        //获取所有的优惠券
        $page=$data['page'] * 10;
        $pagesize=10;
        $list = $mod->where("DATEDIFF(now() ,start_time)>0 and DATEDIFF(end_time,now())>0")->order("ordid asc,id asc")->limit($page-10, $pagesize)->select();
        foreach($list as $key=>$val){
            $list[$key]['days']=round(abs(strtotime($val['end_time'])-time())/3600/24);
            $list[$key]['img']=$orig_list[$val['orig_id']]['img_url'];
        }
        $code = 10001;
        if(count($list) < 1){
            $code = 10002;
        }
        echo get_result($code,$list);return ;

    }

    //优惠券详情
    public function tickshow($data) {
        $id = $data['tickid'];
        $mod_orig = M("item_orig");
        $mod = M("tick");
        $mod_tk = M('tk');
        $info=$mod->where("id=$id")->find();
        if(!$info){
            echo get_result(10001,'ID不存在');return ;
        }
        $info['zj']=intval($info['sy'])+intval($info['yl']);
        $info['intro'] = str_replace(chr(13),'<br>',$info['intro']);

        $orig_info = $mod_orig->where("id=$info[orig_id]")->find();

        //领取记录
        $pagesize=20;
        $count = $mod_tk->where("tick_id=$id and status=1")->count();
        $pager = $this->_pager($count,$pagesize);
        $lq = $mod_tk->where("tick_id=$id and status=1")->limit($pager->firstRow.",".$pager->listRows)->select();
        foreach($lq as $key=>$val){
            $lq[$key]['gk']= ((time()-$val['get_time'])>3600*24)?1:0;
            $lq[$key]['uname']=str_pad(substr(get_uname($val['uid']),-3),6,'*',STR_PAD_LEFT);
        }

        $info['lq'] = $lq;
        echo get_result(10001,$info);return ;


    }

    //兑换优惠券
    public function tkdh($data){
        $id = $data['tickid'];
        $mod = M("user");
        $info = $mod->where("id=".$data['userid'])->find();

        //查询用户积分是否足够
        $yhq = M("tick")->where("id=$id and sy>0")->find();
        if(!$yhq){
            echo get_result(10001,'该优惠券已领完');return ;

        }
        if(intval($info['score'])<intval($yhq['dhjf'])){
            echo get_result(10001,'您的积分不够');return ;
        }
        if($yhq['xl'] > 0){
            $x=M('tk')->where("tick_id=$id and status=1 and uid=".$info['id'])->count();
            if($x>=$yhq['xl']){
                echo get_result(10001,'很抱歉，该优惠券每个账户仅限领取'.$x.'次哦，请让些机会给其他菜油吧！');return ;
            }
        }

        //兑换
        $qid = M('tk')->where("tick_id=$id and status=0")->getField('tk_id');
        $data['uid']=$info['id'];
        $data['get_time']=time();
        $data['status']=1;
        M('tk')->where("tk_id=$qid")->save($data);//更新优惠券信息
        $yl = M('tk')->where("tick_id=$id and status='1'")->count();//已领
        $sy = M('tk')->where("tick_id=$id and status='0'")->count();//未领
        M("tick")->where("id=$id")->save(array('yl'=>"$yl",'sy'=>"$sy"));
        M()->query("update try_user set score=score-$yhq[dhjf] where id=$info[id] limit 1");//更新用户积分
        //积分日志
        $score_log_mod = D('score_log');
        $score_log_mod->create(array(
            'uid' => $info['id'],
            'uname' => $info['username'],
            'action' => 'exchange',
            'score' => -$yhq['dhjf'],
        ));
        $score_log_mod->add();
        $xc = array();
        $xc['ftid']=$info['id'];
        $xc['to_id']=$info['id'];
        $xc['to_name']=$info['username'];
        $xc['from_id']=0;
        $xc['from_name']='tryine';
        $xc['add_time']=time();
        $xc['info'] ='领取优惠券：'. M('tick')->where("id=$id")->getField('name');

        M('message')->add($xc);
        echo get_result(10001,'兑换成功!快去个人中心-我的优惠卷看看吧！');return ;
    }

    //我的优惠券
    public function tick($data){
        $page = $data['page'] * 10;
        $pagesize=10;

        $mod_tick = M('tk');
        $where ="uid=".$data['userid']." ";
        $gq = $data['gq'];
        $mod_xs= M('tick')->where(' DATE_SUB( CURDATE( ) , INTERVAL 1 MONTH ) > DATE( end_time )')->field('id')->select();
        if($mod_xs){
            foreach($mod_xs as $v){
                $arr[]=$v['id'];
            }
            $mod_tick->where(array('tick_id'=>array('in',$arr),'uid'=>$data['userid']))->delete();
        }


        if($gq==1){$where .=" and t.end_time<NOW() ";$tab=1;}



        $join = " try_tick t ON t.id = try_tk.tick_id ";
        //$field= "t.orig_id,t.name,t.start_time,t.end_time,t.id，*";
        $list = $mod_tick->where($where)
            ->join($join)->field('name,end_time,tk_code,tk_psw,ljdz')
            ->order("get_time desc, tk_id desc")
            ->limit($page-10, $pagesize)
            ->select();

        foreach($list as $k=>$v){
            if(strtotime($list[$k]['end_time'])<time()){
                $list[$k]['sssss']=1;
            }
        }
        $code = 10001;
        if(count($list) < 1){
            $code = 10002;
        }
        echo get_result($code,$list);return ;

    }

    //积分兑换
    public function scorelist($data) {

    //    $cid = $data['cid'];
    //    $type = $data['type'];
        $sort_order = 'id DESC';
        $page = $data['page'] * 10;
        $pagesize = 10;
        if(!empty($data['title'])){
            $where['title'] = array('like', '%' . $data['title'] . '%');
        }
        $where['status'] = 1;
      if(!empty($data['type']) || $data['type']=="0"){
        $where['type'] =$data['type'];
    }
        $score_item = M('score_item');
        $item_list = $score_item->where($where)->field('title,coin,score,img,id')->order($sort_order)->limit($page-10, $pagesize)->select();
        $code = 10001;
        if(count($item_list) < 1){
            $code = 10002;
        }
        echo get_result($code,$item_list);return ;
    }

    //积分详情
    public function scoredetails($data) {

        $id = $data['scoreid'];
        $item_mod = M('score_item');
        $item = $item_mod->field('id,title,img,buy_num,user_num,stock,score,coin,desc')->find($id);
        //兑换记录(首页不做分页)
        $list = M("score_order")->where("item_id=$id")->field('uid,add_time')->order('add_time desc,id desc')->limit(20)->select();
        foreach($list as $key=>$val){
            $list[$key]['uname']=get_uname($val['uid']);
            unset($list[$key]['uid']);
        }
        $item['list'] = $list;

        echo get_result(10001,$item);return ;
    }

    //我的文章
    public function publish($data){
        $pagesize=10;
        $page= $data['page'] * 10;
        $user['id'] = $data['userid'];
        $t = $data['type'];
        !$t&&$t='gn';
        $item_mod=M('item');
        $article_mod = M('article');
        $zr_mod=M("zr");
        switch($t){
            case "gn":
                $list = $item_mod->where("o.ismy=0 and try_item.uid='$user[id]' and try_item.status=1")->join("try_item_orig o ON o.id=try_item.orig_id")->field("try_item.zan,try_item.comments,try_item.id,try_item.title,try_item.img,try_item.intro,try_item.price,try_item.add_time,try_item.zan,try_item.likes")->order('try_item.add_time desc,try_item.id desc')->limit($page-10, $pagesize)->select();
                break;
            case "ht":
                $list = $item_mod->where("o.ismy=1 and try_item.uid='$user[id]' and try_item.status=1")->join("try_item_orig o ON o.id=try_item.orig_id")->field("try_item.zan,try_item.comments,try_item.id,try_item.title,try_item.img,try_item.intro,try_item.price,try_item.add_time,try_item.zan,try_item.likes")->order('try_item.add_time desc,try_item.id desc')->limit($page-10, $pagesize)->select();
                break;
            case "qb":
                $list = $item_mod->where("try_item.uid='$user[id]' and try_item.status=1")->join("try_item_orig o ON o.id=try_item.orig_id")->field("try_item.zan,try_item.comments,try_item.id,try_item.title,try_item.img,try_item.intro,try_item.price,try_item.add_time,try_item.zan,try_item.likes")->order('try_item.add_time desc,try_item.id desc')->limit($page-10, $pagesize)->select();
                break;
            case "best":
                $list = $item_mod->where("isbest=1 and uid='$user[id]' and try_item.status=1")->field("try_item.zan,try_item.comments,try_item.id,try_item.title,try_item.img,try_item.intro,try_item.price,try_item.add_time,try_item.zan,try_item.likes")->order('try_item.add_time desc,try_item.id desc')->limit($page-10, $pagesize)->select();
                break;
            case "zr":
                $list = $zr_mod->where("uid='$user[id]' and (status=1 or status=4)")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "sd":
                $list=$article_mod->where("uid=$user[id] and cate_id=10 and status=1")->field("id,uid,title,author,img,intro,likes,add_time,comments,zan")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "gl":
                $list=$article_mod->where("uid=$user[id] and cate_id in(select id from try_article_cate where pid=9 or id=9) and status=1")->field("id,uid,title,author,img,intro,likes,add_time,comments,zan")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "icg"://草稿
                $list=$item_mod->where("status=2 and uid='$user[id]'")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "ids"://待审商品
                $list=$item_mod->where("status=0 and uid='$user[id]'")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "ith"://退回商品
                $list=$item_mod->where("status=3 and uid='$user[id]'")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "zcg"://转让草稿
                $list=$zr_mod->where("status=2 and uid='$user[id]'")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "zds"://待审转让
                $list=$zr_mod->where("status=0 and uid='$user[id]'")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "zth"://退回转让
                $list=$zr_mod->where("status=3 and uid='$user[id]'")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "scg"://晒单草稿
                $list=$article_mod->where("status=2 and uid=$user[id] and cate_id=10")->field("id,title,comments,zan,intro,img")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "sds"://晒单待审
                $list=$article_mod->where("status=0 and uid=$user[id] and cate_id=10")->field("id,title,comments,zan,intro,img")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "sth"://晒单退回
                $list=$article_mod->where("status=3 and uid=$user[id] and cate_id=10")->field("id,title,comments,zan,intro,img")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "gcg"://攻略草稿
                $list=$article_mod->where("uid=$user[id] and cate_id in(select id from try_article_cate where pid=9 or id=9) and status=2")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "gds"://攻略待审
                $list=$article_mod->where("uid=$user[id] and cate_id in(select id from try_article_cate where pid=9 or id=9) and status=0")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "gth"://攻略退回
                $list=$article_mod->where("uid=$user[id] and cate_id in(select id from try_article_cate where pid=9 or id=9) and status=3")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
        }

        $code = 10001;
        if(count($list) < 1){
            $code = 10002;
        }
        echo get_result($code,$list);return ;
    }

    //我的文章——不分晒单攻略的待审核草稿状态
    public function new_publish($data){
        $pagesize=10;
        $page= $data['page'] * 10;
        $user['id'] = $data['userid'];
        $t = $data['type'];
        !$t&&$t='gn';
        $item_mod=M('item');
        $article_mod = M('article');
        $zr_mod=M("zr");
        switch($t){
            case "gn":
                $list = $item_mod->where("o.ismy=0 and try_item.uid='$user[id]'")->join("try_item_orig o ON o.id=try_item.orig_id")->field("try_item.status,try_item.zan,try_item.comments,try_item.id,try_item.title,try_item.img,try_item.intro,try_item.price,try_item.add_time,try_item.zan,try_item.likes")->order('try_item.add_time desc,try_item.id desc')->limit($page-10, $pagesize)->select();
                break;
            case "ht":
                $list = $item_mod->where("o.ismy=1 and try_item.uid='$user[id]'")->join("try_item_orig o ON o.id=try_item.orig_id")->field("try_item.status,try_item.zan,try_item.comments,try_item.id,try_item.title,try_item.img,try_item.intro,try_item.price,try_item.add_time,try_item.zan,try_item.likes")->order('try_item.add_time desc,try_item.id desc')->limit($page-10, $pagesize)->select();
                break;
            case "qb":
                $list = $item_mod->where("try_item.uid='$user[id]'")->join("try_item_orig o ON o.id=try_item.orig_id")->field("try_item.status,try_item.zan,try_item.comments,try_item.id,try_item.title,try_item.img,try_item.intro,try_item.price,try_item.add_time,try_item.zan,try_item.likes")->order('try_item.add_time desc,try_item.id desc')->limit($page-10, $pagesize)->select();
                break;
            case "best":
                $list = $item_mod->where("isbest=1 and uid='$user[id]'")->field("try_item.zan,try_item.comments,try_item.id,try_item.title,try_item.img,try_item.intro,try_item.price,try_item.add_time,try_item.zan,try_item.likes")->order('try_item.add_time desc,try_item.id desc')->limit($page-10, $pagesize)->select();
                break;
            case "zr":
                $list = $zr_mod->where("uid='$user[id]' and (status=1 or status=4)")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "sd":
                $list=$article_mod->where("uid=$user[id] and cate_id=10")->field("id,uid,title,author,img,intro,likes,add_time,comments,zan,status")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "gl":
                $list=$article_mod->where("uid=$user[id] and cate_id in(select id from try_article_cate where pid=9 or id=9)")->field("id,uid,title,author,img,intro,likes,add_time,comments,zan")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "icg"://草稿
                $list=$item_mod->where("status=2 and uid='$user[id]'")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "ids"://待审商品
                $list=$item_mod->where("status=0 and uid='$user[id]'")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "ith"://退回商品
                $list=$item_mod->where("status=3 and uid='$user[id]'")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "zcg"://转让草稿
                $list=$zr_mod->where("status=2 and uid='$user[id]'")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "zds"://待审转让
                $list=$zr_mod->where("status=0 and uid='$user[id]'")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "zth"://退回转让
                $list=$zr_mod->where("status=3 and uid='$user[id]'")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "scg"://晒单草稿
                $list=$article_mod->where("status=2 and uid=$user[id] and cate_id=10")->field("id,title,comments,zan,intro,img")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "sds"://晒单待审
                $list=$article_mod->where("status=0 and uid=$user[id] and cate_id=10")->field("id,title,comments,zan,intro,img")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "sth"://晒单退回
                $list=$article_mod->where("status=3 and uid=$user[id] and cate_id=10")->field("id,title,comments,zan,intro,img")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "gcg"://攻略草稿
                $list=$article_mod->where("uid=$user[id] and cate_id in(select id from try_article_cate where pid=9 or id=9) and status=2")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "gds"://攻略待审
                $list=$article_mod->where("uid=$user[id] and cate_id in(select id from try_article_cate where pid=9 or id=9) and status=0")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
            case "gth"://攻略退回
                $list=$article_mod->where("uid=$user[id] and cate_id in(select id from try_article_cate where pid=9 or id=9) and status=3")->order('add_time desc,id desc')->limit($page-10, $pagesize)->select();
                break;
        }

        $code = 10001;
        if(count($list) < 1){
            $code = 10002;
        }
        echo get_result($code,$list);return ;
    }

    //我的分享
//    public function share($data){
//        $pagesize=10;
//        $page= $data['page'] * 10;
//        $user['id'] = $data['userid'];
//        $t = $data['type'];
//        !$t&&$t='gn';
//        $mod=M("share");
//
//        switch($t){
//            case "gn":
//                $list = $mod->field("i.*,try_share.dm")->where("o.ismy=0 and try_share.uid='$user[id]' and try_share.xid=1")->join(array("try_item i ON i.id=try_share.item_id ","try_item_orig o ON o.id=i.orig_id"))->limit($page-10, $pagesize)->order("i.id desc")->select();
//                break;
//            case "ht":
//                $list = $mod->field("i.*,try_share.dm")->where("o.ismy=1 and try_share.uid='$user[id]' and try_share.xid=1")->join(array("try_item i ON i.id=try_share.item_id ","try_item_orig o ON o.id=i.orig_id"))->limit($page-10, $pagesize)->order("i.id desc")->select();
//                break;
//            case "best":
//                $list = $mod->field("i.*,try_share.dm")->where(" i.isbest=1 and try_share.uid='$user[id]' and try_share.xid=1")->join("try_item i ON i.id=try_share.item_id")->limit($page-10, $pagesize)->order("i.id desc")->select();
//                break;
//            case "zr":
//                $list = $mod->field("z.*,try_share.dm")->where("try_share.uid=$user[id] and try_share.xid=2")->join("try_zr z ON z.id=try_share.item_id")->limit($page-10, $pagesize)->order("z.id desc")->select();
//                break;
//            case "sd":
//                $list=$mod->field("a.*,try_share.dm")->where("try_share.uid=$user[id] and try_share.xid=3 and a.cate_id=10")->join("try_article a ON a.id=try_share.item_id")->limit($page-10, $pagesize)->order("a.id desc")->select();
//                break;
//            case "gl":
//                $list=$mod->field("a.*,try_share.dm")->where("try_share.uid=$user[id] and try_share.xid=3 and a.cate_id in(select id from try_article_cate where pid=9 or id=9)")->join("try_article a ON a.id=try_share.item_id")->limit($page-10, $pagesize)->order("a.id desc")->select();
//                break;
//        }
//        $code = 10001;
//        if(count($list) < 1){
//            $code = 10002;
//        }
//        echo get_result($code,$list);return ;
//    }

    //我的收藏
    public function likes($data){
        $pagesize=10;
        $page= $data['page'] * 10;
        $user['id'] = $data['userid'];
        $t = $data['type'];
        !$t&&$t='gn';
        $mod = M("likes");
        switch($t){
            case "gn":
                $list = $mod->where("try_likes.uid = '$user[id]' and try_likes.xid=1 and o.ismy=0")->join("try_item i on i.id=try_likes.itemid")->join("try_item_orig o on o.id=i.orig_id")->field("i.id,i.title,i.img,i.comments,i.intro")->order("i.add_time desc")->limit($page-10, $pagesize)->select();
                break;
            case "ht":
                $list = $mod->where("try_likes.uid = '$user[id]' and try_likes.xid=1 and o.ismy=1")->join("try_item i on i.id=try_likes.itemid")->join("try_item_orig o on o.id=i.orig_id")->field("i.id,i.title,i.img,i.comments,i.intro")->order("i.add_time desc")->limit($page-10, $pagesize)->select();
                break;
            case "best":
                $list = $mod->where("try_likes.uid = '$user[id]' and try_likes.xid=1 and i.isbest=1")->join("try_item i on i.id=try_likes.itemid")->field("i.id,i.title,i.img,i.comments,i.intro")->order("i.add_time desc")->limit($page-10, $pagesize)->select();
                break;
            case "sd":
                $list = $mod->where("try_likes.uid = '$user[id]' and try_likes.xid=3 and a.cate_id=10")->join("try_article a on a.id=try_likes.itemid")->field("a.id,a.title,a.img,a.comments,a.intro")->order("a.add_time desc")->limit($page-10, $pagesize)->select();
                break;
            case "gl":
                $list = $mod->where("try_likes.uid = '$user[id]' and try_likes.xid=3 and a.cate_id in(select id from try_article_cate where pid=9 or id=9)")->join("try_article a on a.id=try_likes.itemid")->field("a.id,a.title,a.img,a.comments,a.intro")->order("a.add_time desc")->limit($page-10, $pagesize)->select();
                break;
            case "zr":
                $list = $mod->where("try_likes.uid = '$user[id]' and try_likes.xid=2")->join("try_zr z on z.id=try_likes.itemid")->field("z.id,z.title,z.img,z.comments,z.intro")->order("a.add_time desc")->limit($page-10, $pagesize)->select();
                break;

        }
        $code = 10001;
        if(count($list) < 1){
            $code = 10002;
        }
        echo get_result($code,$list);return ;
    }

    //系统消息
    public function getmsg($data) {
        $uid = $data['userid'];
        $page = $data['page'] * 10;
        $message_mod = M('message');
        $pagesize = 10;
        $map = array();
        $map['from_id'] = '0';
        $map['to_id'] = array('in', '0,'.$uid);
        $xx = $message_mod->where($map)->limit($page-10, $pagesize)->field('id,add_time,info')->order("add_time desc")->select();

        $code = 10001;
        if(count($xx) < 1){
            $code = 10002;
        }
        echo get_result($code,$xx);return ;
    }

    //系统消息
    public function getmsg_user($data) {
        $uid = $data['userid'];
        $page = $data['page'] * 10;
        $message_mod = M('message');
        $pagesize = 10;
        $map = array();
        $map['from_id'] =array('NEQ','0');
        $map['to_id'] = array('in', '0,'.$uid);
        $xx = $message_mod->where($map)->limit($page-10, $pagesize)->field('id,add_time,info')->order("add_time desc")->select();

        $code = 10001;
        if(count($xx) < 1){
            $code = 10002;
        }
        echo get_result($code,$xx);return ;
    }

    //系统消息
    public function getmsg_userdetail($data) {

        $ftid = $data['ftid'];
        $uid = $data['userid'];
        $message_mod = M('message');
        $map = "ftid='".$ftid."' AND ((from_id = '".$uid."' AND status<>2) OR (to_id = '".$uid."' AND status<>3))";
        //更新状态
        $message_mod->where($map)->setField('status', 0);
        //显示列表
        $pagesize = 10;
        $page = $data['page'] * $pagesize;
        $count = $message_mod->where($map)->order('id DESC')->count('id');
        $pager = $this->_pager($count, $pagesize);
        $message_list = $message_mod->where($map)->order('id DESC')->limit($page-10,$pagesize)->select();

        M()->query("update try_message set ck_status=1 where ftid='".$ftid."' AND to_id='".$uid."'");//更新消息查看状态

        $code = 10001;
        if(count($message_list) < 1){
            $code = 10002;
        }
        echo get_result($code,$message_list);return ;
    }

    //我的评论
    public function comments($data){
        $page = $data['page'] *10;
        $userid = $data['userid'];
        $t = $data['t'] ? 'r' : '';
        $mod_comment = M("comment");
        $time = time()-24*3600*10;
        $pagesize = 5;
        $where =" 1=1 and uid=$userid ";
        if($t=="r"){$where .=" and add_time>$time ";}


        $list = $mod_comment->where($where)->order("add_time desc,id desc")->field('id,xid,itemid,info,add_time')->limit($page - 10,$pagesize)->select();
        foreach($list as $key=>$val){
            $arr=array();
            switch($val['xid']){
                case "1":$mod=M('item');$path="item";break;
                case "2":$mod=M("zr");$path="zr";break;
                case "3":$mod=M("article");$path="article";break;
            }
            $arr = $mod->where("id=$val[itemid]")->field("title,img")->find();
            $list[$key]['title']=$arr['title'];
            if($val['xid']=='1'){$list[$key]['img']=attach($arr['img'],$path);}else{$list[$key]['img']=attach($arr['img'],$path);}
            $list[$key]['commentid'] = $list[$key]['id'];
            unset($list[$key]['id']);
        }

        $code = 10001;
        if(count($list) < 1){
            $code = 10002;
        }
        echo get_result($code,$list);return ;
    }
    //删除评论
    public function del_comment($data){
        $id = $data['commentid'];
        $item = M("comment")->where("id=$id")->field('xid,itemid')->find();
        $r=M("comment")->where("id=$id")->delete();
        //减少对应项目的评论数量
        if($r){
            switch($item['xid']){
                case "1":$mod = M("item");break;
                case "2":$mod = M("zr");break;
                case "3":$mod = M("article");break;
            }
            $mod->where("id=$item[itemid]")->setDec("comments");
            echo get_result(10001,'删除成功');return ;
        }else{
            echo get_result(10001,'删除失败');return ;
        }

    }





    //对商品点赞
    public function zan($data) {
        $id = $data['id'];
        $mod = 'item';
        switch ($data['type']){
            case 1:$mod = 'item';break;
            case 2:$mod = 'article';break;
            case 3:$mod = 'comment';break;
        }

        if(M($mod)->where("id=$id")->setInc('zan')){
            echo get_result(10001,'点赞成功');return ;
        }else{
            echo get_result(10001,'点赞失败');return ;
        }
    }

    //收藏商品
    public function setlikes($data){
        $userid = $data['userid'];
        $id = $data['id'];
        $xid = $data['xid'];
        $i_mod = get_mod($xid);
        //验证对象
        $item = $i_mod->where("id=$id")->find();
        if(!$item){
            echo get_result(10001,'收藏错误');return ;
        }
        $mod = D("likes");
        //查找是否已收藏
        $islike=$mod->where("uid=$userid and xid=$xid and itemid=$id")->find();
        if($islike){//如果已经收藏则取消收藏
            $r=$mod->where("uid=$userid and xid=$xid and itemid=$id")->delete();
            if($r){
                $i_mod->where("id=$id")->setDec("likes");
                $res['likes']=intval($item['likes'])-1;
                $res['t']='qx';
                echo get_result(10001,'您已成功取消收藏');return ;
            }else{
                echo get_result(10001,'操作失败');return ;
            }
        }else{
            $r=$mod->add(array('itemid'=>$id,'xid'=>$xid,'addtime'=>time(),'uid'=>$userid));
            if($r){
                $i_mod->where("id=$id")->setInc("likes");
                $res['likes']=intval($item['likes'])+1;
                $res['t']='sc';
                echo get_result(10001,'收藏成功');return ;
            }else{
                echo get_result(10001,'操作失败');return ;
            }
        }
    }

    //评论
    public function comment($data){
        $st = strtotime("today");
        $ed = strtotime(date('Y-m-d',strtotime('+1 day')));
        //查询当天评论次数
        $num = M("comment")->where("uid=".$data['userid']." and add_time>$st and add_time<$ed ")->count();
        if(!(D("user")->phone_binded($data['userid']))){
            echo get_result(10002,[],'请先绑定手机号,再评论!');return ;
        }
        if($num>49){
            echo get_result(10001,'您今天评论的次数已达上限');return ;
        }
        $data['info'] = Input::deleteHtmlTags($data['info']);
        //过滤字符
        $kill_word = C("pin_kill_word");
        $kill_word = explode(",",$kill_word);
        if(in_array($data['info'],$kill_word)){
            echo get_result(10001,'您发表的内容有非法字符');return ;
        }

        //敏感词处理
        $check_result = D('badword')->check($data['info']);
        switch ($check_result['code']) {
            case 1: //禁用。直接返回
                echo get_result(10001,'您发表的内容有敏感字符');return ;
                break;
            case 3: //需要审核
                $data['status'] = 0;
                break;
        }
        $data['info'] = $check_result['content'];
        $data['uid'] = $data['userid'];
        $data['uname'] = D('user')->where(['id'=>$data['userid']])->getField('username');

        $data['add_time'] = time();
        //验证评论对象
        switch($data['xid']){
            case "1":$item_mod=M("item");break;
            case "2":$item_mod=M("zr");break;
            case "3":$item_mod=M("article");break;
        }
        $itemid = $data['id'];
        unset($data['id']);
        $item = $item_mod->where(array('id' => $itemid, 'status' => '1'))->find();

        if(!$item){
            echo get_result(10001,'错误的id');return ;
        }
        $data['lc']=intval($item['comments'])+1;
        //写入评论
        $comment_mod = D('comment');
        $data['itemid'] = $itemid;
        if (false === $comment_mod->create($data)) {
            echo get_result(10001,$comment_mod->getError());return ;
        }
        $comment_id = $comment_mod->add();
        if ($comment_id) {
            $item_mod->where(array('id'=>$itemid))->setInc('comments');//评论数量加1
            M("user")->where("id=$data[uid]")->setInc("score",2);
            M("user")->where("id=$data[uid]")->setInc("exp",2);
            //积分日志
            set_score_log(array('id'=>$data['uid'],'username'=>$data['uname']),'comment',2,'','',2);

            echo get_result(10001,[],"感谢您的回复,系统给您奖励积分：2，经验：2.");return ;
        } else {
            echo get_result(10001,'评论失败');return ;
        }
    }

    //修改性别
    public function updatesex($data)
    {
        $mod = M("user");

        if($mod->where("id=".$data['userid'])->save(['gender'=>$data['gender']])){
            echo get_result(10001,'修改成功');return ;
        } else {
            echo get_result(10001,'修改失败');return ;
        }

    }

    //我的积分兑换商品
    public function myscore($data)
    {
        $page = $data['page'] *10;
        $userid = $data['userid'];
        $mod_score = M("score_order");
        $pagesize = 9;
        $where ="uid=$userid ";

        $list = $mod_score->where($where)->order("add_time desc,id desc")->field('order_sn,item_id,item_num,item_name,consignee,address,mobile,remark,add_time')->limit($page - 10,$pagesize)->select();
      /*  foreach($list as $key=>$val){
            $list[$key]['shopid'] = $list[$key]['itemid'];
            $list[$key]['commentid'] = $list[$key]['id'];

        }*/

        $code = 10001;
        if(count($list) < 1){
            $code = 10002;
        }
        echo get_result($code,$list);return ;
    }

    //获取已绑定的社交平台
    public function getbind($data)
    {
        $bind = M('user_bind');
        $list = $bind->where(['uid'=>$data['userid']])->field('uid,type')->select();
        $code = 10001;
        if(count($list) < 1){
            $code = 10002;
        }
        echo get_result($code,$list);return ;
    }
    //解除绑定的社交平台
    public function removebind($data)
    {
        $bind = M('user_bind');
        $result = $bind->where(['uid'=>$data['userid'],'type'=>$data['type']])->delete();
        if($result){
            echo get_result(10001,'解除绑定成功');return ;
        } else {
            echo get_result(10001,'解除绑定失败');return ;
        }

    }

    //绑定社交平台
    public function bind($data)
    {
        if(empty($data['userid'])){
            echo get_result(10001,'绑定失败');return ;
        }
        $bind = M('user_bind');
        $info = $bind->where(['uid'=>$data['userid'],'type'=>$data['type']])->find();
        if($info){
            echo get_result(10001,'绑定成功');return ;
        }

        $result = $bind->add(['uid'=>$data['userid'],'type'=>$data['type'],'keyid'=>$data['keyid'],'info'=>serialize($data['info'])]);
        if($result){
             $notify_tag_count = M('notify_tag')->where(array("userid"=>$data['userid']))->count('id');
            if($notify_tag_count == 0){
                $notify_tag = M('notify_tag');
                $notify_tag->add(array(
                'userid' => $data['userid'],
                'tag' => "白菜",
                'p_sign' => 1,
                'f_sign' => 1
            ));
                $notify_tag->add(array(
                'userid' => $data['userid'],
                'tag' => "手快有",
                'p_sign' => 1,
                'f_sign' => 1
            ));
                $notify_tag->add(array(
                'userid' => $data['userid'],
                'tag' => "神价格",
                'p_sign' => 1,
                'f_sign' => 1
            ));
                $notify_tag->add(array(
                'userid' => $data['userid'],
                'tag' => "BUG",
                'p_sign' => 1,
                'f_sign' => 1
            ));
            }
            echo get_result(10001,'绑定成功');return ;
        } else {
            echo get_result(10001,'绑定失败');return ;
        }
    }

    //从第三方平台登录
    public function checkbind($data)
    {
        $bind = M('user_bind');
        $info = $bind->where(['keyid'=>$data['keyid'],'type'=>$data['type']])->find();
        if(!$info){
            echo get_result(10001,'未绑定社交平台');return ;
        }
        //连接用户中心
        $passport = $this->_user_server();
        $userinfo = $passport->get($info['uid']);
        $info = [
            'userid'    =>  $userinfo['id'],
            'username'    =>  $userinfo['username'],
            'gender'    =>  $userinfo['gender'],
            'score'    =>  $userinfo['score'],
        ];

        $notify_tag_count = M('notify_tag')->where(array("userid"=>$userinfo['id']))->count('id');
            if($notify_tag_count == 0){
                $notify_tag = M('notify_tag');
                $notify_tag->add(array(
                'userid' => $userinfo['id'],
                'tag' => "白菜",
                'p_sign' => 1,
                'f_sign' => 1
            ));
                $notify_tag->add(array(
                'userid' => $userinfo['id'],
                'tag' => "手快有",
                'p_sign' => 1,
                'f_sign' => 1
            ));
                $notify_tag->add(array(
                'userid' => $userinfo['id'],
                'tag' => "神价格",
                'p_sign' => 1,
                'f_sign' => 1
            ));
                $notify_tag->add(array(
                'userid' => $userinfo['id'],
                'tag' => "BUG",
                'p_sign' => 1,
                'f_sign' => 1
            ));
            }

        //登陆
        echo get_result(10001,$info);
    }


    //分享接口 积分+10 金币+1 贡献+1 经验+1
    public function share($data)
    {
        $start_time = strtotime(date('Y-m-d'));
        $end_time = strtotime(date('Y-m-d 23:59:59'));
        $log_where['uid'] = $data['userid'];
        $log_where['action'] = 'share';
        $log_where['add_time'] = [['gt',$start_time],['lt',$end_time]];

        $score_log = M('score_log')->where($log_where)->select();
        if(count($score_log) >= 3){
            echo get_result(10001,"超过三次，无法获得奖励");return ;
        }
        M("user")->where("id=$data[userid]")->setInc("score",10);
        M("user")->where("id=$data[userid]")->setInc("exp");
        M("user")->where("id=$data[userid]")->setInc("coin");
        M("user")->where("id=$data[userid]")->setInc("offer");
        $username = M("user")->where("id=$data[userid]")->getField('username');
        //积分日志
        set_score_log(array('id'=>$data['userid'],'username'=>$username),'share',10,1,1,1);
        echo get_result(10001,"请求成功");return ;
    }

    //上传头像
    public function setimg($data) {
        $uid = $data['userid'];
        $data= $data['base64'];
        $data = substr(strstr($data,','),1);
        $img=base64_decode($data);
        $file = 'upload/'.md5($uid).'.jpg';
        file_put_contents($file, $img);
        $suid = sprintf("%09d", $uid);
        $dir1 = substr($suid, 0, 3);
        $dir2 = substr($suid, 3, 2);
        $dir3 = substr($suid, 5, 2);
        $avatar_dir = $dir1.'/'.$dir2.'/'.$dir3.'/';
        $upload_path = '/'.C('pin_attach_path') . 'avatar/'. $avatar_dir .md5($uid).'.jpg';
        $upyun = new UpYun2('baicaiopic', '528', 'lzw123456');
        $fh = fopen($file, 'rb');
        $rsp = $upyun->writeFile($upload_path, $fh, True);   // 上传图片，自动创建目录
        fclose($fh);
        $upyun1 = new UpYun1('baicaiopic', '528', 'lzw123456');
        $data = IMG_ROOT_PATH.'/data/upload/avatar/'.$avatar_dir.md5($uid).'.jpg';
        $url = $data."\n";
        $upyun1->purge($url);
        @ unlink($file);
        $score_log = M('score_log')->where(['uid'=>$uid,'action'=>'upload_avator'])->select();
        if(count($score_log) < 1){
            M("user")->where("id=$uid")->setInc("score",10);
            M("user")->where("id=$uid")->setInc("exp",10);
            $username = M("user")->where("id=$uid")->getField('username');
            set_score_log(array('id'=>$uid,'username'=>$username),'upload_avator',10,'','',10);
        }

        echo get_result(10001,"请求成功");return ;
    }

    /**
     * 兑换
     */
    public function ec($data) {
        $id = $data['scoreid'];
        $num = $data['num'];
        if (!$id || !$num) {
            echo get_result(10001,"数据为空");return ;
        }
        $item_mod = M('score_item');
        $user_mod = M('user');
        $order_mod = D('score_order');
        $uid = $data['userid'];
        $uname = M("user")->where("id=$uid")->getField('username');
        $item = $item_mod->find($id);
        if(!$item){
            echo get_result(10001,"不存在的商品");return ;
        }
        if($item['sign_date']<time()){
            echo get_result(10001,"抽奖已过期,不能再兑换,请关注其他抽奖商品");return ;
        }
        if(!$item['stock']){
            echo get_result(10001,"库存不足");return ;
        }
        //金币够不
        $user_coin = $user_mod->where(array('id'=>$uid))->getField('coin');
        if($user_coin < $item['coin']){
            echo get_result(10001,"没有足够的金币");return ;
        }
        //积分够不
        $user_score = $user_mod->where(array('id'=>$uid))->getField('score');
        if($user_score < $item['score']){
            echo get_result(10001,"没有足够的积分");return ;
        }
        //限额
        $eced_num = $order_mod->where(array('uid'=>$uid, 'item_id'=>$item['id']))->sum('item_num');

        $luck_num = $order_mod->where(array('item_id'=>$item['id']))->sum('item_num');
        if ($item['user_num'] && $eced_num + $num > $item['user_num']) {
            echo get_result(10001,"兑换超出限制");return ;
        }
        $order_coin = $num * $item['coin'];
        $order_score = $num * $item['score'];
        $data_create = array(
            'uid' => $uid,
            'uname' => $uname,
            'item_id' => $item['id'],
            'item_name' => $item['title'],
            'item_num' => $num,
            'order_coin' => $order_coin,
            'order_score' => $order_score,
        );
        if(!empty($data['consignee'])){
            $data_create['consignee'] = $data['consignee'];

        }
        if(!empty($data['address'])){
            $data_create['address'] = $data['address'];

        }
        if(!empty($data['zip'])){
            $data_create['zip'] = $data['zip'];

        }
        if(!empty($data['mobile'])){
            $data_create['mobile'] = $data['mobile'];

        }
        if($item['cate_id'] == 8){
            $data_create['order_coin'] = $item['coin'];
            $data_create['order_score'] = $item['score'];
            $data_create['item_num'] = 1;
            for ($x=1; $x<=$num; $x++) {
                 $data_create['luckdraw_num'] = ++$luck_num;
                if (false === $order_mod->create($data_create)) {
                    echo get_result(10001,"兑换失败");return ;
                }
                $order_id = $order_mod->add();
            } 
        //    $this->ajaxReturn(0, '进入抽奖兑换');
        }
        else{
        if (false === $order_mod->create($data_create)) {
            echo get_result(10001,"兑换失败");return ;
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
        echo get_result(10001,"兑换成功");return ;

    }


    /**
     * 问题反馈接口
     */
    public function report_bug($data) {
        $uid= $data['userid'];

        $to_id = $data['to_id'];
        $content = $data['content'];
        $form_name = get_uname($uid);
        $to_name = M('user')->where(array('id'=>$to_id))->getField('username');
        $ftid = $uid + $to_id;
        $data = array(
            'ftid' => $ftid,
            'from_id' => $uid,
            'from_name' => $form_name,
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
            set_score_log(array('id'=>$uid,'username'=>$form_name),'report_bug',1,'','','');
            echo get_result(10001,"反馈成功");return ;
        } else {
            echo get_result(10001,"反馈失败");return ;
        }
    }

    //完善用户信息接口
    public function register_open($data)
    {
        //连接用户中心
        $passport = $this->_user_server();

        //注册
        $uid = $passport->register($data['username'], $data['password'], $data['email'], $data['gender']);
        if(!$uid){
            echo get_result(20001,[],"用户名已存在");return ;
        }

        //给用户加积分
        M("user")->where("id=$uid")->setField(array('score'=>10,'exp'=>10));
        //积分日志
        set_score_log(array('id'=>$uid,'username'=>$data['username']),'register',10,'','',10);


        echo get_result(10001,['userid'=>$uid]);
    }

    /**
     * 私信列表
     */
    public function msglist($data) {
        $uid = $data['userid'];
        //以人为单位 查找与我对话的人
        $message_mod = M('message');
        $pagesize = 10;
        $page = $data['page'] * 10;
        $map = "from_id > 0 AND ((from_id = '".$uid."' AND status<>2) OR (to_id = '".$uid."' AND status<>3))";
        $result = $message_mod->field('id')->where($map)->group('ftid')->select();
        $count = count($result);
        if ($count) {
            $res_list = $message_mod->field('MAX(id) as id,COUNT(id) as num')->where($map)->group('ftid')->order('id DESC')->limit($page-10,$pagesize)->select();
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
            }
            D('user_msgtip')->clear_tip($uid, 3);
        }

        $code = 10001;
        if(count($talk_list) < 1){
            $code = 10002;
        }
        echo get_result($code,$talk_list);return ;
    }

    /**
     * 消息详细
     */
    public function msgtalk($data) {
        $ftid = $data['ftid'];
        $uid = $data['userid'];
        $message_mod = M('message');
        $map = "ftid='".$ftid."' AND ((from_id = '".$uid."' AND status<>2) OR (to_id = '".$uid."' AND status<>3))";
        //更新状态
        $message_mod->where($map)->setField('status', 0);
        //显示列表
        $pagesize = 10;
        $page = $data['page']*10;
        $message_list = $message_mod->where($map)->order('id DESC')->limit($page-10,$pagesize)->select();
        if ($message_list[0]['from_id'] == $uid) {
            $ta_id = $message_list[0]['to_id'];
            $ta_name = $message_list[0]['to_name'];
        } else {
            $ta_id = $message_list[0]['from_id'];
            $ta_name = $message_list[0]['from_name'];
        }
        M()->query("update try_message set ck_status=1 where ftid='".$ftid."' AND to_id='".$uid."'");//更新消息查看状态
        $code = 10001;
        if(count($message_list) < 1){
            $code = 10002;
        }
        echo get_result($code,$message_list);return ;

    }

    /**
     * 发起新会话或者回复
     */
    public function msgpublish($data) {
        $uid= $data['userid'];
   //     if( grade($uid) < 3){
   //         echo get_result(10001,'您的等级还不够，需要升到 3 级才能发送私息！');return ;
   //     }
        $score = M('user')->where(array('id'=>$uid))->getField('score');
        if($score <1){
            echo get_result(10001,'您的积分不够了');return ;

        }

        $to_id = $data['to_id'];
        $content = $data['content'];
        if (!$content) {
            echo get_result(10001,'不能发送空消息');return ;
        }
        $to_name = M('user')->where(array('id'=>$to_id))->getField('username');
        $ftid = $uid + $to_id;
        $username = get_uname($uid);
        $data = array(
            'ftid' => $ftid,
            'from_id' => $uid,
            'from_name' => $username,
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
            M("user")->where("id=$uid")->setDec("score",1);
            //积分日志
            set_score_log(array('id'=>$uid,'username'=>$username),'shixin',-1,'','','');
            echo get_result(10001,'发送成功');return ;
        } else {
            $this->ajaxReturn(0, L('illegal_parameters'));
            echo get_result(10001,'发送失败');return ;
        }
    }

    /**
     * 删除系统短信
     */
    public function msgdel($data) {

        $message_mod = D('message');
        $message_mod->user_delete($data['mid'], $data['userid']);
        echo get_result(10001,'删除成功');return ;

        /*
        $datas=array();
        $datas['mid'] = $data['mid'];
        $message_mod = D('ssb');
        $datas['uid'] = $data['userid'];
        $datas['type'] = 1;
        $r = $message_mod->add($datas);
        if ($r) {
            echo get_result(10001,'删除成功');return ;
        } else {
            echo get_result(10001,'删除失败');return ;
        }
        */
    }

    /**
     * 删除整个对话
     */
    public function msgdelall($data) {
        $ftid = $data['ftid'];

        $message_mod = D('message');
        $res_list = $message_mod->field('id')->where(array('ftid'=>$ftid))->select();
        $mid_arr = array();
        foreach ($res_list as $val) {
            $mid_arr[] = $val['id'];
        }
        $message_mod->user_delete($mid_arr, $data['userid']);
        echo get_result(10001,'删除成功');return ;
    }
    /**
     * 删除单条私信
     */
    public function msgdelitme($data) {

        $message_mod = D('message');

        $message_mod->user_delete($data['msgid'], $data['userid']);

        echo get_result(10001,'删除成功');return ;
    }


    //邮箱找回密码
    public function findpwd($data)
    {
        $username = $data['username'];
        $user = M('user')->where("username='".$username."' or email='".$username."'")->find();
        if(!$user){
            echo get_result(20001,[],"用户名不存在");return ;
        }
        $tpl_data['username'] =$user['username'];
//        //生成随机码
        $time = time();
        $activation = md5($user['reg_time'] . substr($user['password'], 10) . $time);
        $url_args = array('username'=>$user['username'], 'activation'=>$activation, 't'=>$time);
        $tpl_data['reset_url'] = U('home/findpwd/reset', $url_args, '', '', true);
//        //解析邮件模板
        $mail_body = D('message_tpl')->get_mail_info('findpwd', $tpl_data);
//        //发送邮件
        $this->_mail_queue($user['email'], L('findpwd'), $mail_body);
        echo get_result();
    }
    //回复
    public function hf($data){
        $info = array();
        $id = $data['commentid'];
        //查找上级评论xid 和itemid
        $info=M("comment")->where("id=$id")->field("xid,itemid")->find();
        $info['info'] = $data['content'];
        if(empty($info['info'])){
            echo get_result(20001,[], "没有评论内容");return ;
        }
        //敏感词处理
        $check_result = D('badword')->check($info['info']);
        switch ($check_result['code']) {
            case 1: //禁用。直接返回
                echo get_result(20001,[], "含义敏感词");return ;
                break;
            case 3: //需要审核
                $info['status'] = 0;
                break;
        }
        $info['info'] = $check_result['content'];
        $info['uid'] = $data['userid'];
        $info['uname'] = get_uname($data['userid']);
        $info['add_time'] = time();
        //验证评论对象
        switch($info['xid']){
            case "1":$item_mod=M("item");break;
            case "2":$item_mod=M("zr");break;
            case "3":$item_mod=M("article");break;
        }
        $item = $item_mod->where(array('id' => $info['itemid'], 'status' => '1'))->find();
        $info['lc']=intval($item['comments'])+1;
        $info['pid']=$id;
        //写入评论
        $comment_mod = D('comment');
        if (false === $comment_mod->create($info)) {
            echo get_result(20001,[], "评论失败");return ;
        }
        $comment_id = $comment_mod->add();

        if ($comment_id) {
            $item_mod->where(array('id'=>$itemid))->setInc('comments');//评论数量加1
            M("user")->where("id=".$info['uid'])->setInc("score",2);
            M("user")->where("id=".$info['uid'])->setInc("exp",2);
            //积分日志
            set_score_log(array('id'=>$info['uid'],'username'=>$info['uname']),'comment',2,'','',2);

            echo get_result(10001,[],'"感谢您的回复,系统给您奖励积分：2，经验：2."');return ;
        } else {
            echo get_result(10001,[],'评论失败');return ;
        }
    }

    /**
     * 我的抽奖
     */
    public function myluckys($data) {
        $userid = $data['userid'];
        empty($data['pagesize']) && $data['pagesize'] = 10;
        $page = $data['page'] * $data['pagesize'];
        $map['uid'] = $userid;
        $score_order_mod = M('score_order');
        $map['luckdraw_num'] = array('NEQ','');
        $order_list = $score_order_mod->field('id,order_sn,item_id,item_name,order_score,order_coin,status,add_time,remark,luckdraw_num')->where($map)->limit($page-$data['pagesize'], $data['pagesize'])->order('id DESC')->select();

        foreach($order_list as $key=>$val){
            $score_item = M('score_item')->field('img,win')->where('id='.$order_list[$key]['item_id'])->find();
        $order_list[$key]['img'] = $score_item['img'];
        $order_list[$key]['win'] = $score_item['win'];
            }

         $code = 10001;
         if(count($order_list) < 1){
            $code = 10002;
        }
        echo get_result($code,$order_list);return ;
    }

    /**
     * 抽奖详情页
     */
    public function lucky_list($data) {
        $where['cate_id'] = 8;
        $where['win'] = '';
        empty($data['pagesize']) && $data['pagesize'] = 10;
        $page = $data['page'] * $data['pagesize'];
        $sort_order = 'sign_date DESC';
        $score_item = M('score_item')->where($where)->order($sort_order)->limit($page-$data['pagesize'], $data['pagesize'])->select();
         $code = 10001;
         if(count($score_item) < 1){
            $code = 10002;
        }
        echo get_result($code,$score_item);return ;
    }

    /**
     * 往期抽奖详情页
     */
    public function lucky_list_past($data) {
        $where['cate_id'] = 8;
        $where['win'] = array('NEQ','');
        empty($data['pagesize']) && $data['pagesize'] = 10;
        $page = $data['page'] * $data['pagesize'];
        $sort_order = 'sign_date DESC';
        $score_item = M('score_item')->where($where)->order($sort_order)->limit($page-$data['pagesize'], $data['pagesize'])->select();
        $code = 10001;
        if(count($score_item) < 1){
            $code = 10002;
        }
        echo get_result($code,$score_item);return ;
    }

     /**
     * 积分商品详细页
     */
    public function exchange_detail($data) {
        $id = $data['id'];
        $item_mod = M('score_item');
        $item = $item_mod->field('id,title,img,score,coin,stock,user_num,buy_num,desc,cate_id,win,sign_date')->find($id);

        $list = M("score_order")->where("item_id=$id")->order('add_time desc')->select();
        $item['list'] = $list;
        $code = 10001;
        echo get_result($code,$item);return ;

    }

     /**
     * ios version
     */
    public function ios_version($data) {
        $ios_version = $data['version'];
        $ios_latest_version = M('setting')->where("name = 'ios_version'")->field('data')->find();
        if($ios_version === $ios_latest_version['data']){
            $code = 10001;
        }
        else{
            $code = 10002;
        }
        
        echo get_result($code);return ;

    }

     /**
     * android version
     */
    public function android_version($data) {
        $android_version = $data['version'];
        $android_latest_version = M('setting')->where("name = 'android_version'")->field('data')->find();
        if($android_version === $android_latest_version['data']){
            $code = 10001;
        }
        else{
            $code = 10002;
        }
        
        echo get_result($code);return ;

    }

}
