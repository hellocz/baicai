<?php
/*
* 商品类
* Created by PhpStorm.
* Author: 初心 [jialin507@foxmail.com]
* Date: 2017/4/12
*/
class shopAction extends userbaseAction
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
     * 商品列表
     * @param $data
     */
    public function shoplist($data){
        $tp = $data['tp'];
         empty($data['page']) && $data['page'] = 1;
        $page = $data['page'] * 10;
        empty($data['pagesize']) && $data['pagesize'] = 10;
        $time=time();
        $item_orig = M("item_orig");

        if(!empty($data['cid'])){
            $where['cate_id'] = $data['cid'];
        }

        if(!empty($data['orig_id'])){
            $where['orig_id'] = $data['orig_id'];
        }

        if(!empty($data['key'])){
            $where['title'] = array("like","%" . $data['key'] . "%");
        }
        $where['add_time'] = array('lt',$time);
        $where['status'] = 1;
        $field = 'id,uid,uname,orig_id,title,intro,img,price,likes,comments,comments_cache,url,zan,hits,go_link,add_time';
        if(!empty($data['isbao'])){
           $item_list = M("item_diu")->where($where)->field($field)
            ->order("add_time desc")
            ->limit($page-10, $data['pagesize'])
            ->select();
        // $item_list = $item_orig
        //     ->where($where." and i.status=1 and i.add_time<$time ")
        //     ->join($db_pre . 'item_diu i ON i.orig_id = ' . $db_pre . 'item_orig.id')
        //     ->field($field)
        //     ->order("i.add_time desc, i.id desc")
        //     ->limit($page-10, $data['pagesize'])
        //     ->select();
        }else{

            $field = 'id,uid,uname,orig_id,title,intro,img,price,likes,comments,comments_cache,url,zan,hits,go_link,add_time';
            $thd = M("item")->where('istop = 1')->field($field)->limit(8)->order("add_time desc")->select();
            if(!empty($data['key'])){
                require LIB_PATH . 'Pinlib/php/lib/XS.php';
                $xs = new XS('baicai');
                $search = $xs->search;   //  获取搜索对象
                $search->setLimit(30,10*($data['page']-1)); 
                $search->setSort('add_time',false);
                $search->setQuery($data['key']);
                $docs = $search->search();
                $count = $search->count();
                //分页
                $item_mod = M('item');

                foreach ($docs as $doc) {
                    if($str==""){
                         $str=$doc->id;
                    }
                    else{
                       $str.=",".$doc->id;
                    }
                }
                $str && $where1['id'] = array('in', $str);
                //$where1['cate_id'] = array('not in','342,349,3672,3673,3675');
                $where1['add_time'] = array('lt',time());
                $str && $item_list = $item_mod->field($field)->where($where1)->order('add_time DESC')->limit(10)->select();
            }
            else{
                $item_list = M("item")->where($where)->field($field)
                ->order("add_time desc")
                ->limit($page-10, $data['pagesize'])
                ->select();
            }
            $item_list = array_merge($thd,$item_list);
        }
        foreach ($item_list as $key => &$val) {
            if(!isset($val['shopid'])){
                $val['shopid'] = $val['id'];
                unset($val['id']);
            }
            $item_list[$key]['zan'] = $item_list[$key]['zan']   +intval($item_list[$key]['hits'] /10);
            $val['go_link'] = array_shift(unserialize($val['go_link']));
            $item_list[$key]['name']=getly($val['orig_id']);
            if($val['cate_id'] == 349 || $val['cate_id'] == 3672 || $val['cate_id'] == 3673 || $val['cate_id'] == 3675){
          //      unset($item_list[$key]);
            }
        }
        $code = 10001;
        if(count($item_list) < 1){
            $code = 10002;
        }
        echo get_result($code,$item_list);return ;

    }

     /**
     * 商品列表
     * @param $data
     */
    public function shoplist_g($data){
        $page = $data['page'] * 10;
        empty($data['pagesize']) && $data['pagesize'] = 10;
        $time=time();
        $db_pre = C('DB_PREFIX');

        $q = $data['key'];

        if (!empty($q)){
                    $q_list=explode("|",$q);
                    $q_info="";
                    $search_content= Array();
                    $search_tags= Array();
                    $search_orig = Array();
                    foreach($q_list as $key=>$r){
              //          $and=$key == 0 ? "" : " or ";
                  //      $q_info.="$or title like '%".$r."%' or intro like '%".$r."%' ";
                  //      $q_info.="$and title like %".$r."% ";
                   //     Array_push( $where['title'],'like',"%$r%");
                   //     $where['title']=array('like',"%$r%");
                        $tag_count = M('tag')->where(array("name"=>$r))->count("id");
                        $orig = M('item_orig')->field('id')->where(array("name"=>$r))->find();
                   //     var_dump($orig);
                        
                        if($tag_count > 0 || count($orig) > 0){
                            if($tag_count > 0){
                  //           var_dump($tag_count);
                             $search_tags[$key] ="%$r%";}
                             if(count($orig) > 0){
                                array_push($search_orig,$orig['id']);
                             }
                        }
                        else{
                        $search_content[$key] ="%$r%";
                        }
                    }
                   
                    if(count($search_tags)>0){
            $where1['tag_cache']=array('like',$search_tags,'OR');
            }
                if(count($search_content)>0){
            $where1['title']=array('like',$search_content,'OR');}
              if(count($search_orig)>0){
            $where1['orig_id']=array('in',$search_orig);
        }
       
            $where1['_logic'] = 'OR';
             $where['_complex'] =  $where1;

                 //   $where['title'] =array('like',$search_content,'OR');

                 
             //         var_dump($where);
/*  
                    if(count($q_list) ==1){

                    $where1['tag_cache'] =array('like',$search_content,'AND');

                    $where1['go_link'] =array('like',$search_content,'AND');
                    }

                    $where1['_logic'] = 'or';
                    $where['_complex'] = $where1;
     */           
       }
       else{
        echo get_result(10002,[]);return ;
       }


          $where['status'] =1;
 
          $where['add_time'] =array('lt', $time);

          $where['cate_id'] = array('not in','342,349,3672,3673,3675');

//        $field = 'i.id,i.uid,i.uname,i.title,i.intro,i.img,i.price,i.likes,i.intro,i.content,i.comments,i.comments_cache,i.add_time,i.orig_id,i.url,i.go_link,i.zan';
        $field = 'id,title,img,price,comments,likes,add_time,zan,go_link,hits,orig_id,cate_id';
     
        $item_list = M("item")
            ->where($where)
            ->field($field)
            ->order("add_time desc")
            ->limit($page-10, $data['pagesize'])
            ->select();

        foreach ($item_list as $key => &$val) {
            if(!isset($val['shopid'])){
                $val['shopid'] = $val['id'];
                unset($val['id']);
            }
            $item_list[$key]['name'] = getly($item_list[$key]['orig_id']);
            $item_list[$key]['zan'] = $item_list[$key]['zan']   +intval($item_list[$key]['hits'] /10);
            $val['go_link'] = array_shift(unserialize($val['go_link']));
            if($val['cate_id'] == 349 || $val['cate_id'] == 3672 || $val['cate_id'] == 3673 || $val['cate_id'] == 3675){
            //    unset($item_list[$key]);
            }
        }
        $code = 10001;
        if(count($item_list) < 1){
            $code = 10002;
        }
        echo get_result($code,$item_list);return ;

    }

    //获取置顶商品
    public function shoplisttop($data)
    {
        $db_pre = C('DB_PREFIX');
        $item_orig = M("item_orig");
        $where = 'istop = 1';
        $time = time();
        $field = 'name,i.id,i.title,i.img,i.price,i.comments,i.likes,i.add_time,i.zan,i.go_link,i.hits,i.cate_id';
        $where['cate_id'] = array('not in','342,349,3672,3673,3675');
        $item_list = $item_orig
            ->where($where." and i.status=1 and i.add_time<$time ")
            ->join($db_pre . 'item i ON i.orig_id = ' . $db_pre . 'item_orig.id')
            ->field($field)
            ->order("i.add_time desc, i.id desc")
            ->select();

        foreach ($item_list as $key => &$val) {
            if(!isset($val['shopid'])){
                $val['shopid'] = $val['id'];
                unset($val['id']);
            }
            $item_list[$key]['zan'] = $item_list[$key]['zan']   +intval($item_list[$key]['hits'] /10);
            $val['go_link'] = array_shift(unserialize($val['go_link']));
            if($val['cate_id'] == 349 || $val['cate_id'] == 3672 || $val['cate_id'] == 3673 || $val['cate_id'] == 3675){
             //   unset($item_list[$key]);
            }
        }
        $code = 10001;
        if(count($item_list) < 1){
            $code = 10002;
        }
        echo get_result($code,$item_list);return ;
    }


    public function getorig($data)
    {
        $item_mod = M('item_orig');
        $item = $item_mod->field('id,img,name,url')->select();
        $code = 10001;
        if(count($item) < 1){
            $code = 10002;
        }
        echo get_result($code,$item);return ;
    }

    /**
     * 标签搜索
     * @param $data
     */
    public function tagsearch($data) {
        $time=time();
        $page = $data['page'] * 10;
        $where = array();
        $where['add_time']=array('lt',$time);
        $where['tag_cache'] = array('like', '%' . $data['tag'] . '%');
        $db_pre = C('DB_PREFIX');
        if(!empty($data['cid'])){
            $where['cate_id'] = $data['cid'];
        }
        
        else{
            $where['cate_id'] = array('not in','342,349,3672,3673,3675');
        }
        

        if(!empty($data['key'])){
            $where['title'] = array('like', '%' . $data['key'] . '%');
        }
        $where['status'] = 1;
        $item_mod = M('item');
        //查询字段
        $field = $db_pre . 'item.id,cate_id,title,'.$db_pre . 'item.img,price,likes,add_time,zan,go_link,'.$db_pre.'item_orig.name ';
        $item_list = $item_mod->join($db_pre . 'item_orig ON ' . $db_pre . 'item.orig_id = '.$db_pre . 'item_orig.id 

')
                            ->field($field)
                            ->where($where)
                            ->order('id DESC')
                            ->limit($page-10, 10)
                            ->select();

        foreach ($item_list as $key => &$val) {
            if(!isset($val['shopid'])){
                $val['shopid'] = $val['id'];
                unset($val['id']);
            }
            $val['go_link'] = array_shift(unserialize($val['go_link']));
            if($val['cate_id'] == 349 || $val['cate_id'] == 3672 || $val['cate_id'] == 3673 || $val['cate_id'] == 3675){
             //   unset($item_list[$key]);
            }
        }
        $code = 10001;
        if(count($item_list) < 1){
            $code = 10002;
        }
        echo get_result($code,$item_list);return ;


    }


    /**
     * 商品详细页
     */
    public function shopitem($data) {
        $id = $data['shopid'];
        if($data['type'] == "bl"){
            $item_mod = M('item_diu');
        }
        else{
        $item_mod = M('item');}
        $item = $item_mod->field('id,tag_cache,orig_id,title,img,intro,price,zan,likes,comments,add_time,content,status,go_link,hits')->where(array('id' => $id))->find();
        $item['zan'] =  $item['zan'] + intval($item['hits'] /10);
        if(!$item){
            echo get_result(20001,[], "商品不存在");return ;
        }

        if($item['status']==0){
            echo get_result(20001,[], "该信息未通过审核");return ;
        }
        if($item['add_time']>time()){
            echo get_result(20001,[], "该信息暂未发布");return ;
        }

        //来源
        $orig = M('item_orig')->field('id,name,img')->find($item['orig_id']);
        $like = M('likes')->where(['uid'=>$data['userid'],'itemid'=>$id])->field('itemid')->find();
        $item['mylike'] = 0;

        if($like){
            $item['mylike'] = 1;
        }
        $go_link = unserialize($item['go_link']);
         if($data['type'] == "bl"){
            $item['fenxiang'] = 'http://www.baicaio.com/bao/'.$id.'.html';
        }
        else{
            if($item['id'] == 287425 ){
                $item['fenxiang'] = 'http://www.baicaio.com/hongbao.html?ff=fx';
            }
            else{
                $item['fenxiang'] = 'http://www.baicaio.com/item/'.$id.'.html';
            }
        }
        
         if(strpos($item['content'], "www.baicaio.com") !== false){
              $item['content']= str_replace("www.baicaio.com","m.baicaio.com",$item['content']);
          }
          preg_match_all('/src=[\'|\"](\/\S+)[\'|\"]/i',$item['content'],$arr);
          foreach($arr[1] as $key=>$v){
            $item['content']= str_replace($v,"http://www.baicaio.com" . $v,$item['content']);
        }

        $item['go_link'] = array_shift($go_link);
        $tag_caches = unserialize($item['tag_cache']);
        $item['tag_cache'] = '';
        foreach ($tag_caches as $tag_cache){
            $item['tag_cache'] .= $tag_cache.'|';
        }
        $item['orig'] = $orig;
        unset($item['orig_id']);
        echo get_result(10001,$item);
    }

     /**
     * 商品详细页
     */
    public function shopbitem($data) {
        $id = $data['shopid'];
        $item_mod = M('item_diu');
        $item = $item_mod->field('tag_cache,orig_id,title,img,intro,price,zan,likes,comments,add_time,content,status,go_link,hits')->where(array('id' => $id))->find();
        $item['zan'] =  $item['zan'] + intval($item['hits'] /10);
        if(!$item){
            echo get_result(20001,[], "商品不存在");return ;
        }

        if($item['status']==0){
            echo get_result(20001,[], "该信息未通过审核");return ;
        }
        if($item['add_time']>time()){
            echo get_result(20001,[], "该信息暂未发布");return ;
        }

        //来源
        $orig = M('item_orig')->field('id,name,img')->find($item['orig_id']);
        $like = M('likes')->where(['uid'=>$data['userid'],'itemid'=>$id])->field('itemid')->find();
        $item['mylike'] = 0;

        if($like){
            $item['mylike'] = 1;
        }
        $go_link = unserialize($item['go_link']);
        $item['fenxiang'] = 'http://www.baicaio.com/bao/'.$id.'.html';
        $item['go_link'] = array_shift($go_link);
        $tag_caches = unserialize($item['tag_cache']);
        $item['tag_cache'] = '';
        foreach ($tag_caches as $tag_cache){
            $item['tag_cache'] .= $tag_cache.' ';
        }
        $item['orig'] = $orig;
        unset($item['orig_id']);
        echo get_result(10001,$item);
    }
    
    /**
     * 商品评论
     */
    public function commentitem($data) {

        $item_comment_mod = M('comment');
        $page = $data['page'] * 10;
        $pagesize = 10;
        $map = array('itemid' => $data['shopid'],'xid' => $data['xid'],'status'=>1 , 'pid'=>0);

        $cmt_list = $item_comment_mod->where($map)->field('id,uid,uname,info,add_time,zan,lc')->order('id DESC')->limit($page - 10, $pagesize)->select();
        foreach($cmt_list as $key=>$v){
            $cmt_list[$key]['list']=M()->query("select id,uid,uname,info,add_time,zan,lc from try_comment where status=1 and pid='".$v['id']."' order by id asc");
        }
        $code = 10001;
        if(count($cmt_list) < 1){
            $code = 10002;
        }
        echo get_result($code,$cmt_list);return ;


    }

    //1小时和24小时榜 0 为一天 1为 一小时
    public function hourday($data) {


        $time = time();
       // $time = 1495596278;
        $time_hour = $time - 3600;
        $time_day = $time - 86400;

        if($data['type'] == 1){
            $list=M()->query("SELECT id,title,orig_id,img,price,go_link,comments,likes,add_time,zan,hits from try_item  WHERE cate_id not in (349,3672,3673,3675) and add_time between $time_hour and $time ORDER BY hits desc LIMIT 9");
        }else{
            $list=M()->query("SELECT id,title,img,orig_id,price,go_link,comments,likes,add_time,zan,hits from try_item  WHERE cate_id not in (349,3672,3673,3675) and add_time between $time_day and $time ORDER BY hits desc LIMIT 9");
        }

        foreach ($list as $key => &$val) {
            if(!isset($val['shopid'])){
                $val['shopid'] = $val['id'];
                unset($val['id']);
            }
            $list[$key]['name'] = getly($list[$key]['orig_id']);
            $val['go_link'] = array_shift(unserialize($val['go_link']));
            $list[$key]['zan'] = $list[$key]['zan']   +intval($list[$key]['hits'] /10);
            if($val['cate_id'] == 349 || $val['cate_id'] == 3672 || $val['cate_id'] == 3673 || $val['cate_id'] == 3675){
           //     unset($list[$key]);
            }
        }
        $code = 10001;
        if(count($list) < 1){
            $code = 10002;
        }
        echo get_result($code,$list);return ;

    }


    public function ad($data) {
        $ad_id = $data['adid'] ? $data['adid'] : 0;
        $result = M('ad')->where(['id'=>$ad_id,'status'=>1])->field('content,url')->select();
        $code = 10001;
        if(count($result) < 1){
            $code = 10002;
        }
        echo get_result($code,$result);return ;
    }

    public function adb($data) {
        $ad_id = $data['adid'] ? $data['adid'] : 0;
        $result = M('ad')->where(['board_id'=>$ad_id,'status'=>1])->field('content,url')->select();
        $code = 10001;
        if(count($result) < 1){
            $code = 10002;
        }
        echo get_result($code,$result);return ;
    }

    //获取商品信息
    public function fetch_item($data) {
        $url = $data['url'];
       //获取商品信息
        $itemcollect = new itemcollect();
        $info = $itemcollect->url_parse($url);
        if(!empty($info)){
            $info['url'] = $url;
        }

        $encode = mb_detect_encoding($info['title'], array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
        if($encode != 'UTF-8'){
            $info['title'] = mb_convert_encoding($info['title'], 'UTF-8', $encode);
        }
        $info['item_cate']=M("item_cate")->where("pid=0")->select();

        echo get_result(10001,$info);return ;
    }

    public function uploadimg1($data){
        //上传图片
        $data = $data['base64'];
        $data = substr(strstr($data,','),1);
        $img=base64_decode($data);
        $str = uniqid(mt_rand(),1);
        $file = 'upload/'.md5($str).'.jpg';
        $art_add_time = date('ym/d');
        $upload_path = '/'.C('pin_attach_path') . 'item/'. $art_add_time.'/'.md5($str).'.jpg';
        file_put_contents($file, $img);
        $art_add_time = date('ym/d');
        $upyun = new UpYun2('baicaiopic', '528', 'lzw123456');
        $fh = fopen($file, 'rb');
        $rsp = $upyun->writeFile($upload_path, $fh, True);   // 上传图片，自动创建目录
        fclose($fh);
        @unlink ($file);
        $data = IMG_ROOT_PATH.'/data/upload/item/'. $art_add_time.'/'.md5($str).'.jpg';
        echo get_result(10001,$data);return ;
    }

    //发布商品
    public function publish_item($data) {
        $user = M("user")->where("id=".$data['userid'])->find();
        if($user['exp'] < 51){
            echo get_result(10001,"您的等级还不够，需要升到 2 级才能发布信息！");return ;
        }
        $item_mod = D('item');
        //过滤字符
        $kill_word = C("pin_kill_word");
        $kill_word = explode(",",$kill_word);
        if(in_array($data['content'],$kill_word)||in_array($data['title'],$kill_word)){
            echo get_result(10001,"您发表的内容有非法字符！");return ;
        }
        $item = $item_mod->create();
        $item['title'] = $data['title'];
        $item['info'] = Input::deleteHtmlTags($data['content']);
        $item['uid'] = $data['userid'];
        $item['price'] = $data['price'];
        $item['uname'] = get_uname($data['userid']);
        $item['isbao'] = '1';
        $item['source'] = '1';
        $status = $data['status'];
        if($status!=2){$status=0;}
        $item['status'] = $status;
        $item['img'] = $data['img'];
        //添加凑单品，活动入口链接
        $arr[] = array('name'=>'直达链接','link'=>$data['url']);
        if(empty($data["link_type"])){
            $link_type=$data["link_type"];
            $link_url =$data["link_url"];
            foreach($link_type as $key=>$val){
                $arr[]=array('name'=>$val,'link'=>$link_url[$key]);
            }
        }

        $item['go_link']=serialize($arr);
        foreach($data['imgs'] as $key=>$val){
            $item['imgs'][$key]['url']=$val['url'];

        }

//        if($data['ispost'] == 1){
            $item['ispost'] =1;
//        }
        //添加商品
        $result = $item_mod->publish($item);
        if ($result) {
            //发布商品钩子
            $tag_arg = array('uid' => $item['uid'], 'uname' => $item['uname'], 'action' => 'pubitem');
            tag('pubitem_end', $tag_arg);
            if($status == 2){
                echo get_result(10001,"保存草稿成功！");return ;
            }else{
                echo get_result(10001,"感谢您的爆料，我们会尽快审核，请关注短消息通知！");return ;
            }

        } else {
            echo get_result(10001,$item_mod->getError());return ;
        }
    }


}
