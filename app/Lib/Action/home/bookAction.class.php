<?php

/**
 * 逛宝贝页面
 */
class bookAction extends frontendAction {

    public function _initialize() {
        parent::_initialize();
        $this->assign('nav_curr', 'book');
    }

    /**
     * 逛宝贝首页
     */
   public function index() {
        $hot_tags = explode(',', C('pin_hot_tags')); //热门标签
        $page_max = C('pin_book_page_max'); //发现页面最多显示页数
        $sort = $this->_get('sort', 'trim'); //排序
        $tag = $this->_get('tag', 'trim'); //当前标签
        $isnice = $this->_get('isnice','intval');
    $time=time();
        $where = array();
        $where['add_time']=array('lt',$time);
        $pos   =   strpos($tag,   '-');      
        if($tag =="9-9包邮"){
            $tag ="9.9包邮";
        }
        if   ($pos   !==   false)   {   
       $tag = str_replace("-"," ", $tag);
        }
        $tag_id =  M("tag")->where(array('name'=>$tag))->getField('id'); 
        $tag_id && $tag_items = M("item_tag")->where(array('tag_id'=>$tag_id))->field("item_id")->select();
        foreach ($tag_items as $tag_item_id) {
            if($str==""){
                 $str=$tag_item_id['item_id'];
            }
            else{
               $str.=",".$tag_item_id['item_id'];
            }
        }
        $str && $where['id'] = array('in', $str);
        //排序：最热(hot)，最新(new)
        switch ($sort) {
            case 'hot':
                $order = 'hits DESC,id DESC';
                break;
            case 'new':
                $order = 'id DESC';
                break;
            default:
                $order = 'add_time DESC';
                break;
        }
         $this->waterfall($where, $order, '', $page_max);

        $this->assign('hot_tags', $hot_tags);
        $this->assign('tag', $tag );
        $this->assign('sort', $sort);
        $this->_config_seo(C('pin_seo_config.book'), array('tag_name' => $tag)); //SEO
        $strpos = ($tag)?"$tag":" 所有商品";
        if(empty($tag)){
        $page_seo['title'] = "海淘专享优惠券|海外购网站优惠劵|网易考拉海购优惠卷|海淘专享优惠券|白菜哦 ";
        $page_seo['keywords'] = "海淘专享优惠券|海外购网站优惠劵|网易考拉海购优惠卷|海淘专享优惠券|白菜哦";
        $page_seo['description'] = "白菜哦提供2018年最新海淘,商品优惠劵,专享优惠卷，20-50元天猫优惠劵，告诉你优惠劵购买技巧";
        
        }
        else{
        $page_seo['title'] = $tag . "最新优惠商品推荐_" . $tag . "怎么选_" . $tag . "品牌_白菜哦";
        $page_seo['keywords'] = $tag . "最新优惠," . $tag . "怎么选," . $tag . "哪个牌子好";
        $page_seo['description'] = "这里是白菜哦(baicaio.com)关于" . $tag . "的优惠汇总页面，特价一网打尽。本站还提供知名品牌排行榜，专属优惠券、独家优惠码，攻略晒单等，想知道" . $tag . "哪个牌子好，" . $tag . "怎么选，" . $tag . "排行榜，就来白菜哦看看吧！";
        }
        $this->assign('page_seo', $page_seo);
        $this->assign('strpos',$strpos);
        $this->display();
    }

    public function index_ajax() {
        $tag = $this->_get('tag', 'trim'); //标签
        $sort = $this->_get('sort', 'trim', 'hot'); //排序
        switch ($sort) {
            case 'hot':
                $order = 'hits DESC,id DESC';
                break;
            case 'new':
                $order = 'id DESC';
                break;
        }
        $where = array();
        $tag && $where['intro'] = array('like', '%' . $tag . '%');
        $this->wall_ajax($where, $order);
    }

    /**
     * 按分类查看
     */
    public function cate() {
        $cid = $this->_get('cid', 'intval');
        !$cid && $this->_404();
        //分类数据
        if (false === $cate_data = F('cate_data')) {
            $cate_data = D('item_cate')->cate_data_cache();
        }
        //当前分类信息
        if (isset($cate_data[$cid])) {
            $cate_info = $cate_data[$cid];
        } else {
            $this->_404();
        }
        //分类列表
        if (false === $cate_list = F('cate_list')) {
            $cate_list = D('item_cate')->cate_cache();
        }
        //分类关系
        if (false === $cate_relate = F('cate_relate')) {
            $cate_relate = D('item_cate')->relate_cache();
        }
        //获取当前分类的顶级分类
        $tid = $cate_relate[$cid]['tid'];

        //商品
        $sort = $this->_get('sort', 'trim');
        $min_price = $this->_get('min_price', 'intval');
        $max_price = $this->_get('max_price', 'intval');
        //条件
		$time=time();
        $where = array();
		$where['add_time']=array('lt',$time);
        //排序：潮流(pop)，最热(hot)，最新(new)
        switch ($sort) {
            case 'pop':
                $order = 'likes DESC';
                break;
            case 'hot':
                $order = 'hits DESC';
                break;
            case 'new':  
				$order = 'id DESC';
                break;
			default: 
				$order = 'add_time DESC';
				break;
        }
        //分类
        if ($cate_info['type'] == 0) {
            $min_price && $where['price'][] = array('egt', $min_price);
            $max_price && $where['price'][] = array('elt', $max_price); //价格
            //实物分类
            $cate_relate[$cid]['sids'][] = strval($cid);
            $where['cate_id'] = array('in', $cate_relate[$cid]['sids']);
            $this->waterfall($where, $order);
        } else {
            //标签组
            $min_price && $where['i.price'][] = array('egt', $min_price);
            $max_price && $where['i.price'][] = array('elt', $max_price); //价格
            $db_pre = C('DB_PREFIX');
            $item_tag_table = $db_pre . 'item_tag';
            $tag_ids = M('item_cate_tag')->where(array('cate_id' => $cid))->getField('tag_id', true);
            if ($tag_ids) {
                $where[$item_tag_table . '.tag_id'] = array('IN', $tag_ids);
                $pentity_id = D('item_cate')->get_pentity_id($cid); //向上找实体分类
                $cate_relate[$pentity_id]['sids'][] = $pentity_id;
                $where['i.cate_id'] = array('IN', $cate_relate[$pentity_id]['sids']); //分类条件
                $this->tcate_waterfall($where, 'i.' . $order);
            }
        }


        $this->assign('cate_list', $cate_list); //分类
        $this->assign('tid', $tid); //顶级分类ID
        $this->assign('cate_info', $cate_info); //当前分类信息
        $this->assign('sort', $sort); //排序
        $this->assign('min_price', $min_price); //最低价格
        $this->assign('max_price', $max_price); //最高价格
        $this->assign('nav_curr', 'cate'); //导航设置
        //SEO
        $level = substr_count($cate_info['spid'],"|")+1;
        if(empty($cate_info['seo_title'])){
      if($level == 2){
        $child_cates =M("item_cate")->where("pid=$cid")->field("name")->select();
        $child = "";
        foreach ($child_cates as $child_cate) {
          $child .= $child_cate['name'] . ",";
        }
        $child = rtrim($child, ',');
        $page_seo['title'] = $cate_info['name'] . "内部优惠券|" . $cate_info['name'] . "优惠活动|" . $cate_info['name'] . "海淘|白菜哦";
        $page_seo['keywords'] = $cate_info['name'] . "内部优惠券|" . $cate_info['name'] . "优惠活动|" . $cate_info['name'] . "海淘";
        $page_seo['description'] = "提供最新" . $cate_info['name'] . "相关内部优惠券大全,教你海淘" . $cate_info['name'] ."无需找代购,这里是白菜哦" . $cate_info['name'] . "优惠信息专栏,最新热门品牌榜等,下载APP不错过任何好价.包括" . $child . "等各类优惠促销";
      }
      else{
        $page_seo['title'] = $cate_info['name'] . "内部优惠券|" . $cate_info['name'] . "优惠活动|" . $cate_info['name'] . "海淘|白菜哦";
        $page_seo['keywords'] = $cate_info['name'] . "内部优惠券|" . $cate_info['name'] . "优惠活动|" . $cate_info['name'] . "海淘";
        $page_seo['description'] = "这里是白菜哦关于" . $cate_info['name'] . "的优惠信息活动专栏,提供2018年最新" . $cate_info['name'] ."十大品牌,全网" . $cate_info['name'] . "内部优惠券大全,教你海淘" . $cate_info['name'] . "无需找代购,下载APP不错过任何好价.";
      }
    }
    else{
        $page_seo['title'] = $cate_info['seo_title'];
        $page_seo['keywords'] = $cate_info['seo_keys'];
        $page_seo['description'] = $cate_info['seo_desc'];
    }

    $nav_cates =M("item_cate")->where("pid=$cid")->field("id,name")->select();
    $nav_cates_filter = array();
    foreach ($nav_cates as $key => $value) {
        $where_nav = array();
        $where_nav['add_time'] =array('lt',$time);
        $where_nav['status'] =1;
        $cate_relate[$nav_cates[$key]['id']]['sids'][] = strval($nav_cates[$key]['id']);
        $where_nav['cate_id'] = array('in', $cate_relate[$nav_cates[$key]['id']]['sids']);
        $nav_cates[$key]['count'] = M("item")->where($where_nav)->count('id');
        if($nav_cates[$key]['count'] > 0){array_push($nav_cates_filter, $nav_cates[$key]);}
    }
    usort($nav_cates_filter, 'sortByCount');
		//面包削
     $this->assign('nav_cates', $nav_cates_filter);
        $this->assign('page_seo', $page_seo);
		$this->assign("strpos",getpos($cid,''));
        $this->display();
    }
    
	public function gny(){
		$tp = $this->_get('tp',"intval");
		$isnice = $this->_get('isnice','intval');
		$isbao = $this->_get("isbao","intval");
		$ispost = $this->_get('ispost','intval',0);
		$orig_id = $this->_get('origid','intval');
		$cateid = $this->_get('cateid','intval');
		if($isnice==""&&$isbao==""){
			$isnice = 1;
		}
		if($isnice==1){
			$where .=" and isnice=1 ";
			$order =" add_time desc";
			$tab = "isnice";
		}
		if($isbao==1){
			$where .=" and isbao=1 ";
			$order =" add_time desc";
			$tab = "isbao";
		}
		if($ispost==1){
			$where .= " and ispost=1 ";
		}
		if($orig_id){
			$where .= " and orig_id=$orig_id ";
		}else{
			$orig_id=0;
		}
		if($cateid){
			$where .= " and cate_id=$cateid ";
		}else{
			$cateid=0;
		}
		$time=time();
                             $time_one_day=$time -86400;
		$db_pre = C('DB_PREFIX');
		$spage_size = C('pin_wall_spage_size'); //每次加载个数
        $spage_max = C('pin_wall_spage_max'); //每页加载次数
        //$page_size = $spage_size * $spage_max; //每页显示个数
		$page_size=18;
		$item_orig = M("item_orig");
		$count =1000;// $item_orig->where($db_pre."item_orig.ismy='$tp' and i.status=1 and i.add_time<$time ".$where)->join('inner join ' . $db_pre."item i ON i.orig_id=".$db_pre."item_orig.id")->count();
		$pager = $this->_pager($count, $page_size);
                            $field = 'id,uid,uname,title,intro,img,price,likes,content,comments,comments_cache,add_time,orig_id,url,go_link,zan,hits';

        

       if($orig_id){
         $item_list = M("item")->where(" status=1 and add_time<$time ".$where)->field($field)->order($order)->limit($pager->firstRow . ',' . $page_size)->select();
       }
        else{
            $orig_ids = $item_orig->where("ismy='$tp'")->field("distinct id")->select();
       foreach($orig_ids as $key=>$val){

          if($str==""){

            $str=$val['id'];

          }else{

            $str.=",".$val['id'];

          }

       }
        //$orig = M("item_orig")->where("id in(".$str.") and ismy='$tp'")->limit(20)->select();
          $item_list = M("item")->where("orig_id in (".$str.")   and status=1 and add_time<$time ".$where)->field($field)->order($order)->limit($pager->firstRow . ',' . $page_size)->select();
      //  $item_list = $item_orig->where($db_pre."item_orig.ismy='$tp' and i.status=1 and i.add_time<$time ".$where)->join('inner join ' . $db_pre . 'item i ON i.orig_id = ' . $db_pre . 'item_orig.id')->field($field)->order($order)->limit($pager->firstRow . ',' . $page_size)->select();
		}
		foreach ($item_list as $key => $val) {
            isset($val['comments_cache']) && $item_list[$key]['comment_list'] = unserialize($val['comments_cache']);
        }
        foreach($item_list as $key=>$val){
                
        $item_list[$key]['zan'] = $item_list[$key]['zan']   +intval($item_list[$key]['hits'] /10);
            }
		$this->assign('item_list', $item_list);//print_r($item_list);exit;
		//当前页码
        $p = $this->_get('p', 'intval', 1);
        $this->assign('p', $p);
        $this->assign('page_bar', $pager->fshow());
        
		//热门置顶
		$hot_list = $item_orig->where($db_pre."item_orig.ismy=$tp and i.status=1 and i.add_time<$time and i.add_time >$time_one_day ")->join('inner join ' . $db_pre."item i ON i.orig_id=".$db_pre."item_orig.id")->field('i.id ,i.title, i.img')->order(' i.hits desc ')->limit(10)->select();
		$this->assign("hot_list",$hot_list);
		//SEO
		if($tp==1){//海淘
		$page_seo['title'] = "海淘专区|海外代购|海淘中文网站|白菜哦";
        $page_seo['keywords'] = "海淘专区|海外代购|海淘中文网站|白菜哦";
        $page_seo['description'] = "这是白菜哦网站推荐关于海淘专区,海淘代购M海淘中文网站，信息专题，集合网友晒单,网友热评,菜油分享都在这里,下载白菜哦app了解更多优惠";
            $disp = 'gny1';
		}elseif($tp==0){
        $page_seo['title'] = "国内商品优惠劵|国内商品活动信息优惠劵";
        $page_seo['keywords'] = "国内商品优惠劵|国内商品活动信息优惠劵";
        $page_seo['description'] = "白菜哦实时更新国内电商优惠促销活动优惠劵,众多神价都是全网独家。电商自营,和品牌直营的旗舰店,在正品行货中寻觅神价格,好活动。";
            $disp = 'gny';
		}
		//表现形式
        $dss =$this->_get("dss","trim");
		$dss = empty($dss)?"lb":$dss;
		$this->assign("dss",$dss);
		$this->assign("tp",$tp);
		$this->assign("tab",$tab);
		$this->assign("origid",$orig_id);
		$this->assign("cateid",$cateid);
		$this->assign("count",$count);
        if($cateid!=0){
            $cate=M("item_cate")->where("id=$cateid")->find();
            $this->assign('cate_url',U("book/gny",array('tp'=>$tp,'origid'=>$orig_id,"ispost"=>$ispost)));
            $this->assign('cate',$cate['name']);
        $page_seo['title'] = $cate['seo_title'];
        $page_seo['keywords'] = $cate['seo_keys'];
        $page_seo['description'] = $cate['seo_desc'];
        }
		if($orig_id!=0){
			$orig=M("item_orig")->where("id=$orig_id")->find();
			$this->assign('orig_url',U("book/gny",array('tp'=>$tp,'cateid'=>$cateid,"ispost"=>$ispost)));
			$this->assign('orig',$orig['name']);
            $page_seo['title'] = $orig['seo_title'];
            $page_seo['keywords'] = $orig['seo_keys'];
            $page_seo['description'] = $orig['seo_desc'];
		}
		$this->assign('page_seo', $page_seo);
		//可直邮
		if($ispost==1){$cispost=0;}else{$cispost=1;}
        $time = time();
        $time_hour = $time - 3600;
        $time_day = $time - 86400;

        //小时榜和24小时榜
        $hour_list=M()->query("SELECT id,title,img,price from try_item  WHERE add_time between $time_hour and $time ORDER BY hits desc LIMIT 9");
        $day_list=M()->query("SELECT id,title,img,price from try_item  WHERE add_time between $time_day and $time ORDER BY hits desc LIMIT 9");
        $this->assign('hour_list',$hour_list);
        $this->assign('day_list',$day_list);
        $where1['cate_id']=16;
        $where1['status']=array("in","1,4");
        $article_list = M("article")->where($where1)->order("add_time desc")->limit(4)->select();
        $this->assign("zx_list",$article_list);
		$this->assign("lb_url",U('book/gny',array('tp'=>$tp,'tab'=>$tab,'dss'=>'lb',"$tab"=>'1',"ispost"=>$ispost)));
		$this->assign("cc_url",U('book/gny',array('tp'=>$tp,'tab'=>$tab,'dss'=>'cc',"$tab"=>'1',"ispost"=>$ispost)));
		$this->assign("post_url",U('book/gny',array('tp'=>$tp,'tab'=>$tab,'dss'=>$dss,"$tab"=>1,"ispost"=>$cispost)));
		$this->assign('ispost',$ispost);
		$this->display($disp);
	}

    public function baicai(){
        $order =" add_time desc";
        $time=time();
        $time_one_day=$time -86400;
        $db_pre = C('DB_PREFIX');
        $spage_size = C('pin_wall_spage_size'); //每次加载个数
        $spage_max = C('pin_wall_spage_max'); //每页加载次数
        //$page_size = $spage_size * $spage_max; //每页显示个数
        $page_size=18;
        $count =1000;
        $pager = $this->_pager($count, $page_size);
        $field = 'id,uid,uname,title,intro,img,price,likes,content,comments,comments_cache,add_time,orig_id,url,go_link,zan,hits';
        $item_list = M("item")->where(" status=1 and add_time<$time and (title like '%白菜%' or title like '%神价格%' or title like '%手快有%' or title like '%手慢无%' or title like '%bug价%') ")->field($field)->order($order)->limit($pager->firstRow . ',' . $page_size)->select();
        
        foreach ($item_list as $key => $val) {
            isset($val['comments_cache']) && $item_list[$key]['comment_list'] = unserialize($val['comments_cache']);
        }
        foreach($item_list as $key=>$val){
        $item_list[$key]['zan'] = $item_list[$key]['zan']   +intval($item_list[$key]['hits'] /10);
            }
        $this->assign('item_list', $item_list);//print_r($item_list);exit;
        //当前页码
        $p = $this->_get('p', 'intval', 1);
        $this->assign('p', $p);
        $this->assign('page_bar', $pager->fshow());
        
        //热门置顶
   //     $hot_list =  M("item")->where("status=1 and add_time<$time and add_time >$time_one_day  and (title like '%白菜%' or title like '%神价格%' or title like '%手快有%' or title like '%手慢无%' or title like '%bug价%')")->field('id ,title, img')->order(' hits desc ')->limit(10)->select();
    //    $this->assign("hot_list",$hot_list);
        //SEO
        $page_seo['title'] = "白菜价优惠券|白菜价优惠券官网|白菜哦";
        $page_seo['keywords'] = "白菜价优惠券|白菜价优惠券官网|白菜哦";
        $page_seo['description'] = "这里是白菜哦网站推荐的关于白菜价专题，大量优惠卷后白菜价、京东亚马逊天猫BUG价、神价手快有手慢无！";
        $this->assign('page_seo', $page_seo);
        //表现形式
        $dss =$this->_get("dss","trim");
        $dss = empty($dss)?"lb":$dss;
        $this->assign("dss",$dss);
        
        //可直邮
        $time = time();
        $time_hour = $time - 3600;
        $time_day = $time - 86400;

        //小时榜和24小时榜
        $hour_list=M()->query("SELECT id,title,img,price from try_item  WHERE add_time between $time_hour and $time ORDER BY hits desc LIMIT 9");
        $day_list=M()->query("SELECT id,title,img,price from try_item  WHERE add_time between $time_day and $time ORDER BY hits desc LIMIT 9");
        $this->assign('hour_list',$hour_list);
        $this->assign('day_list',$day_list);
        $where1['cate_id']=16;
        $where1['status']=array("in","1,4");
        $article_list = M("article")->where($where1)->order("add_time desc")->limit(4)->select();
        $this->assign("zx_list",$article_list);
        $this->assign("lb_url",U('book/baicai',array('dss'=>'lb',)));
        $this->assign("cc_url",U('book/baicai',array('dss'=>'cc')));
        $this->assign('tp',2);
        $this->assign('ispost',$ispost);
        $this->display($disp);
    }

	public function best(){		
		$db_pre = C('DB_PREFIX');
        $orig_id = $this->_get('origid','intval');
        $cateid = $this->_get('cateid','intval');
        $page_size = 16; //每页显示个数
		$item= M("item");
        $where="";
        if($orig_id){
            $where .= " and orig_id=$orig_id ";
        }else{
            $orig_id=0;
        }
        if($cateid){
            $where .= " and cate_id=$cateid ";
        }else{
            $cateid=0;
        }
		$count = $item->where("isbest='1' and status=1 $where")->count();
		$pager = $this->_pager($count, $page_size);
		$time=time();
        $item_list = $item->where("isbest='1' and status=1 and add_time<$time $where")->order("isnice desc,ishot desc,id desc")->limit($pager->firstRow . ',' . $page_size)->select();
		foreach ($item_list as $key => $val) {
            isset($val['comments_cache']) && $item_list[$key]['comment_list'] = unserialize($val['comments_cache']);
			//商品相册
			$item_list[$key]['img_list'] = M('item_img')->field('url')->where(array('item_id' => $val['id']))->order('ordid')->select();
        }
		$this->assign('item_list', $item_list);
        $this->assign('count', $count);
        $this->assign("origid",$orig_id);
        $this->assign("cateid",$cateid);
        if($orig_id!=0){
            $orig=M("item_orig")->where("id=$orig_id")->getField("name");
            $this->assign('orig_url',U("book/best",array('cateid'=>$cateid,)));
            $this->assign('orig',$orig);
        }
        if($cateid!=0){
            $cate=M("item_cate")->where("id=$cateid")->getField("name");
            $this->assign('cate_url',U("book/best",array('origid'=>$orig_id,)));
            $this->assign('cate',$cate);
        }
		//当前页码
        $p = $this->_get('p', 'intval', 1);
        $this->assign('p', $p);
        $this->assign('page_bar', $pager->fshow());        
		$this->assign("bcid","best");
		//热门置顶
		$hot_list = $item->where("isbest='1' and status=1 and add_time<$time ")->order("hits,id desc")->limit(10)->select();
		$this->assign("hot_list",$hot_list);
		$this->_config_seo(C('pin_seo_config.cate'), array(
            'cate_name' => '精品汇',
            'seo_title' => $cate_info['seo_title'],
            'seo_keywords' => $cate_info['seo_keys'],
            'seo_description' => $cate_info['seo_desc'],
			));
		$this->display();
	}
    /**
     * 标签分类瀑布
     */
    public function tcate_waterfallA($where, $order = 'i.id DESC', $field = '') {
        $db_pre = C('DB_PREFIX');
        $item_tag_table = $db_pre . 'item_tag';
        $item_tag_mod = M('item_tag');
        $where_init = array('i.status' => '1');
        $where = array_merge($where_init, $where);
        $count = $item_tag_mod->where($where)->join($db_pre . 'item i ON i.id = ' . $item_tag_table . '.item_id')->count();
        $spage_size = C('pin_wall_spage_size'); //每次加载个数
        $spage_max = C('pin_wall_spage_max'); //每页加载次数
        $page_size = $spage_size * $spage_max; //每页显示个数
        $pager = $this->_pager($count, $page_size);
        !$field && $field = 'i.id,i.uid,i.uname,i.title,i.intro,i.img,i.price,i.likes,i.comments,i.comments_cache,go_link';
        $item_list = $item_tag_mod->field($field)->where($where)->join($db_pre . 'item i ON i.id = ' . $item_tag_table . '.item_id')->order($order)->limit($pager->firstRow . ',' . $spage_size)->select();
        foreach ($item_list as $key => $val) {
            isset($val['comments_cache']) && $item_list[$key]['comment_list'] = unserialize($val['comments_cache']);
        }
        $this->assign('item_list', $item_list);
        //当前页码
        $p = $this->_get('p', 'intval', 1);
        $this->assign('p', $p);
        //当前页面总数大于单次加载数才会执行动态加载
        if (($count - ($p - 1) * $page_size) > $spage_size) {
            $this->assign('show_load', 1);
        }
        //总数大于单页数才显示分页
        $count > $page_size && $this->assign('page_bar', $pager->fshow());
        //最后一页分页处理
        if ((count($item_list) + $page_size * ($p - 1)) == $count) {
            $this->assign('show_page', 1);
        }
    }

    public function cate_ajax() {
        $cid = $this->_get('cid', 'intval');
        $sort = $this->_get('sort', 'trim', 'pop');
        $min_price = $this->_get('min_price', 'intval');
        $max_price = $this->_get('max_price', 'intval');

        //分类数据
        if (false === $cate_data = F('cate_data')) {
            $cate_data = D('item_cate')->cate_data_cache();
        }
        //分类关系
        if (false === $cate_relate = F('cate_relate')) {
            $cate_relate = D('item_cate')->relate_cache();
        }

        //条件
        $where = array();
        //排序：潮流(pop)，最热(hot)，最新(new)
        switch ($sort) {
            case 'pop':
                $order = 'likes DESC';
                break;
            case 'hot':
                $order = 'hits DESC';
                break;
            case 'new':
                $order = 'id DESC';
                break;
        }
        if ($cate_data[$cid]['type'] == 0) {
            //实物分类
            $min_price && $where['price'][] = array('egt', $min_price);
            $max_price && $where['price'][] = array('elt', $max_price); //价格

            array_push($cate_relate[$cid]['sids'], strval($cid));
            $where['cate_id'] = array('in', $cate_relate[$cid]['sids']); //分类
            $this->wall_ajax($where, $order);
        } else {
            //标签组
            $min_price && $where['i.price'][] = array('egt', $min_price);
            $max_price && $where['i.price'][] = array('elt', $max_price); //价格

            $db_pre = C('DB_PREFIX');
            $item_tag_table = $db_pre . 'item_tag';
            $tag_ids = M('item_cate_tag')->where(array('cate_id' => $cid))->getField('tag_id', true);
            if ($tag_ids) {
                $where[$item_tag_table . '.tag_id'] = array('IN', $tag_ids);
                $pentity_id = D('item_cate')->get_pentity_id($cid); //向上找实体分类
                array_push($cate_relate[$pentity_id]['sids'], $pentity_id);
                $where['i.cate_id'] = array('IN', $cate_relate[$pentity_id]['sids']); //分类条件
                $this->tcate_wall_ajax($where, 'i.' . $order);
            }
        }
    }

    public function tcate_wall_ajax($where, $order = 'i.id DESC', $field = '') {
        $db_pre = C('DB_PREFIX');
        $item_tag_table = $db_pre . 'item_tag';
        $item_tag_mod = M('item_tag');

        $spage_size = C('pin_wall_spage_size'); //每次加载个数
        $spage_max = C('pin_wall_spage_max'); //加载次数
        $p = $this->_get('p', 'intval', 1); //页码
        $sp = $this->_get('sp', 'intval', 1); //子页
        //条件
        $where_init = array('i.status' => '1');
        $where = array_merge($where_init, $where);
        //计算开始
        $start = $spage_size * ($spage_max * ($p - 1) + $sp);
        $item_mod = M('item');
        $count = $item_tag_mod->where($where)->join($db_pre . 'item i ON i.id = ' . $item_tag_table . '.item_id')->count();
        !$field && $field = 'i.id,i.uid,i.uname,i.title,i.intro,i.img,i.price,i.likes,i.comments,i.comments_cache,url';
        $item_list = $item_tag_mod->field($field)->where($where)->join($db_pre . 'item i ON i.id = ' . $item_tag_table . '.item_id')->order($order)->limit($start . ',' . $spage_size)->select();
        foreach ($item_list as $key => $val) {
            isset($val['comments_cache']) && $item_list[$key]['comment_list'] = unserialize($val['comments_cache']);
        }
		
        $this->assign('item_list', $item_list);
        $resp = $this->fetch('public:waterfall');
        $data = array(
            'isfull' => 1,
            'html' => $resp
        );
        $count <= $start + $spage_size && $data['isfull'] = 0;
        $this->ajaxReturn(1, '', $data);
    }
}
