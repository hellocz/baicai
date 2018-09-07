<?php
/**
 * 前台控制器基类
 *
 * @author andery
 */

class newfrontendAction extends baseAction {

    protected $visitor = null;
    
    public function _initialize() {
        parent::_initialize();
        //网站状态
        if (!C('pin_site_status')) {
            header('Content-Type:text/html; charset=utf-8');
            exit(C('pin_closed_reason'));
        }
        //初始化访问者
        $this->_init_visitor();
        //第三方登陆模块
        $this->_assign_oauth();
        //网站导航选中
        $this->assign('nav_curr', '');
    }
    
    /**
    * 初始化访问者
    */
    private function _init_visitor() {
        $this->visitor = new user_visitor();
        $this->assign('visitor', $this->visitor->info);
    }

    /**
     * 第三方登陆模块
     */
    private function _assign_oauth() {
        if (false === $oauth_list = F('oauth_list')) {
            $oauth_list = D('oauth')->oauth_cache();
        }
        $this->assign('oauth_list', $oauth_list);
    }

    /**
     * SEO设置
     */
    protected function _config_seo($seo_info = array(), $data = array()) {
        $page_seo = array(
            'title' => C('pin_site_title'),
            'keywords' => C('pin_site_keyword'),
            'description' => C('pin_site_description')
        );
        $page_seo = array_merge($page_seo, $seo_info);
        //开始替换
        $searchs = array('{site_name}', '{site_title}', '{site_keywords}', '{site_description}');
        $replaces = array(C('pin_site_name'), C('pin_site_title'), C('pin_site_keyword'), C('pin_site_description'));
        preg_match_all("/\{([a-z0-9_-]+?)\}/", implode(' ', array_values($page_seo)), $pageparams);
        if ($pageparams) {
            foreach ($pageparams[1] as $var) {
                $searchs[] = '{' . $var . '}';
                $replaces[] = $data[$var] ? strip_tags($data[$var]) : '';
            }
            //符号
            $searchspace = array('((\s*\-\s*)+)', '((\s*\,\s*)+)', '((\s*\|\s*)+)', '((\s*\t\s*)+)', '((\s*_\s*)+)');
            $replacespace = array('-', ',', '|', ' ', '_');
            foreach ($page_seo as $key => $val) {
                $page_seo[$key] = trim(preg_replace($searchspace, $replacespace, str_replace($searchs, $replaces, $val)), ' ,-|_');
            }
        }
        $this->assign('page_seo', $page_seo);
    }

    /**
    * 连接用户中心
    */
    protected function _user_server() {
        $passport = new passport(C('pin_integrate_code'));
        return $passport;
    }

    /**
     * 前台分页统一
     */
    protected function _pager($count, $pagesize,$parameter='',$url='') {
        $pager = new Page($count, $pagesize,$parameter,$url);
        $pager->rollPage = 5;
        $pager->setConfig('prev', '<i class="icon5 icon5-a_14" style="margin-top: 5px;"></i>');
        $pager->setConfig('next', '<i class="icon5 icon5-a_15" style="margin-top: 5px;"></i>');
        // $pager->setConfig('theme', '%upPage% %first% %linkPage% %end% %downPage%');
        $pager->setConfig('theme', '%upPage% %first% %linkPage% %downPage%');
        return $pager;
    }

    protected function _404($url = '') {
        if ($url) {
            redirect($url);
        } else {
            send_http_status(404);
            // $this->display(TMPL_PATH . '404.html');
            $this->display('public:404');
            exit;
        }
    }

    protected function param_encode($str = '') {
        //字符串处理，避免URL获取参数出错
        // $str = urlencode($str);
        $search = array("-",  ".");
        $replace = array("_minus_",  "_dot_");
        return str_replace ($search,  $replace,  $str);
    }

    protected function param_decode($str = '') {
        //字符串处理，避免URL获取参数出错
        // $str = urldecode($str);
        $search = array("_minus_",  "_dot_");
        $replace = array("-",  ".");
        return str_replace ($search,  $replace,  $str);
    }

    /**
     * 天猫淘宝搜券
     */
    public function search_quan($q = ''){
        if(empty($q)) return false;

        include_once LIB_PATH . 'Pinlib/taobao/TopSdk.php';
        $c = new TopClient;
        $c->appkey = "25060252";
        $c->secretKey = "xxxxxxxxxxxxxxxxxx";
        $req = new TbkDgItemCouponGetRequest;
        $req->setAdzoneId("14718353");
       // $req->setPlatform(1);
        $req->setPageSize("100");
        $req->setPageNo("1");

        $item_list = array();

        if(is_array($q)){  //q为数组            
            foreach ($q as $key => $value) {
                $req->setQ(trim($value));
                $resp = $c->execute($req);
                $lists=$resp->results->tbk_coupon; 
                $item_list[$key] = array();               
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
                    $item_list[$key][] = $item;
                }
                usort($item_list[$key], 'sortByVolume');
            }
        }else{  //q为字符串
                $req->setQ(trim($q));
                $resp = $c->execute($req);
                $lists=$resp->results->tbk_coupon;
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
        }
        return $item_list;
    }

    /**
     * 页面右边-热门优惠（文章页、优惠券详情页）
     */
    public function right_hot_item(){
        //热门优惠
        $time = time();
        $queryArr = array();
        $queryArr['where']=" and isnice=1 ";
        $queryArr['order'] =" add_time desc";
        $item_list = M('item')->where("status=1 and add_time<$time ".$queryArr['where'])->limit(5)->order($queryArr['order'])->select();
        if(count($item_list) > 0){
          foreach ($item_list as $key => $val) {
            $item_list[$key]['zan'] = $item_list[$key]['zan']+intval($item_list[$key]['hits'] /10);
            $pos = strpos($val['price'], '（');
            if($pos > 0){
              $item_list[$key]['price'] = substr($val['price'] , 0, $pos);
            }            
          }
        }
        $this->assign('right_hot_item_list', $item_list);
    }
   

    /**
     * 筛选过滤及结果查询
     */
    public function filter($params = array(), $where = ''){

        $p = $this->_get('p', 'intval', 1);
        $type = $this->_get('type','trim');
        if(!is_array($type)){
            $type = array_flip(explode(',', $type));
        }
        $property = $this->_get('property','trim');
        $cateid = $this->_get('cateid','intval',0);
        // $cpid = $this->_get('cpid','intval',0);
        $orig = $this->_get('orig','trim');
        if(!is_array($orig)){
            $orig = array_flip(explode(',', $orig));
        }
        $tag = $this->_get('tag','trim');
        if(!is_array($tag)){
            $tag = array_flip(explode(',', $tag));
        }
        $price = $this->_get('price','intval',0);
        $pricemin = $this->_get('pricemin','intval',0);
        $pricemax = $this->_get('pricemax','intval',0);
        $period = $this->_get('period','intval',0);
        $sortby = $this->_get('sortby','trim');
        // $dss = $this->_get('dss','trim');
        $dss_l = $_COOKIE['dss_l'];
        if($p<1){$p=1;}

        $time=time();
        $time = strtotime('2018-05-31 23:59:59'); //测试

        $options = array();
        //文章类型
        $options['type'] = array(
            1 => array('name' => '首页推荐', 'count' => 0),
            2 => array('name' => '热门优惠', 'count' => 0),
            3 => array('name' => '全网特价', 'count' => 0),
        ); 
        //按属性，国内，国外
        $options['ismy'] = array(
            '1' => array('name' => '海淘', 'count' => 0),
            '0' => array('name' => '国内', 'count' => 0),
        );

        //标签列表
        $options['tag'] = array(
            1 => "凑单品", 
            2 => "包邮", 
            3 => "PRIME会员", 
            4 => "PLUS会员", 
            5 => "历史低价", 
            6 => "新低价", 
            7 => "可直邮", 
            8 => "需转运",
        );

        //价格列表
        $options['price'] = array(
            1 => array('min' => 0, 'max' => 50), 
            2 => array('min' => 50, 'max' => 100), 
            3 => array('min' => 100, 'max' => 150), 
            4 => array('min' => 150, 'max' => 200), 
            5 => array('min' => 200, 'max' => 500), 
            6 => array('min' => 500), 
        ); 

        //时间列表
        $options['period'] = array(
            '0' => "过去一周", 
            '1' => "过去一月", 
            '2' => "过去一年", 
        ); 

        //排序
        $options['sortby'] = array(
            'newest' => " add_time desc", 
            'hottest' => " hits desc", 
        );

        //商城列表
        $origs = M("item_orig")->order("ordid asc")->select();
        $options['orig'] = array();
        if(count($origs) > 0){
            foreach ($origs as $k => $val) {
                $options['orig'][$val['id']] = $val;
            }
        }

        //分类列表
        if (false === $cate_list = F('cate_list')) {
            $cate_list = D('item_cate')->cate_cache();
        }
        $options['cate'] = $cate_list;
        //分类，取两级目录
        if(count($options['cate']) > 0){
            foreach ($options['cate']['s'] as $k => $val) {
                if(!isset($options['cate']['p'][$k])){
                    unset($options['cate']['s'][$k]);
                }
            }
        }
        //分类数据
        if (false === $cate_data = F('cate_data')) {
          $cate_data = D('item_cate')->cate_data_cache();
        }

        //默认过滤选项
        $default_filters = array(
            'type' => array('1' => 'on'),  //必选
            // 'property' => array('1' => 'on', '0' => 'on'),  //国外，国内
            // 'cateid' => '',
            // 'orig' => array(),
            // 'tag' => array(),
            // 'price' => '',
            // 'pricemin' => '',
            // 'pricemax' => '', 
            'period' => 0,  //必选
            // 'sortby' => '',
        );

        $filters = $default_filters;

        //查询条件
        $mod=M("item");
        $queryAllArr = array();
        $queryArr = array();
        $all_where1 = "status=1 ";
        $where1 = "status=1 ";

        //过滤条件, 固定条件，选项不会变化
        //时间
        if(array_key_exists($period, $options['period'])){
            $filters['period'] = $period;
        }
        switch ($filters['period']) {
            case '1':
                $time_s = strtotime("-1 month", strtotime(date("Y-m-d 00:00:00", $time))+86400);
                $time_e = $time;
                break;
            case '2':
                $time_s = strtotime("-1 year", strtotime(date("Y-m-d 00:00:00", $time))+86400);
                $time_e = $time;
                break;                
            default:
                $time_s = strtotime("-7 day", strtotime(date("Y-m-d 00:00:00", $time))+86400);
                $time_e = $time;
                break;
        }
        $all_where1.="and add_time between $time_s and $time_e ";
        $where1.="and add_time between $time_s and $time_e ";
        //文章类型
        if(count($type) > 0){
            foreach ($type as $k => $val) {
                if(!isset($options['type'][$k])) continue;
                if(isset($filters['type'])) unset($filters['type']);  //有选择并且type在type list列表内时，清掉默认选择
                $filters['type'][$k] = $val;
                break;  //单选
            }
        }
        //属性：国内，国外
        if(count($property) > 0){
            if(isset($filters['property'])) unset($filters['property']);
            foreach ($property as $k => $val) {
                if(!isset($options['ismy'][$k])) continue;
                $filters['property'][$k] = $val;
            }
        }
        //分类过滤
        if($cateid > 0 && count($options['cate']) > 0){
            foreach ($options['cate']['s'] as $k => $val) {
                if(isset($options['cate']['s'][$k][$cateid])){  //判断传过来的是否二级分类
                    $filters['cateid']= $cateid;
                    $arr = array($cateid);
                    if(isset($cate_list['s'][$cateid])){
                        $arr = array_merge($arr, array_keys($cate_list['s'][$cateid]));
                    }
                    $where1.="and cate_id in(". implode(', ', $arr) .") ";
                }
            }
        }
        //商城过滤
        if(count($orig) > 0){
            if(isset($filters['orig'])) unset($filters['orig']);
            foreach ($orig as $k => $val) {
                if(!isset($options['orig'][$k])) continue;
                $filters['orig'][$k] = $k;
            }
            if(count($filters['orig']) > 0){
                $where1.="and orig_id in(". implode(', ', array_keys($filters['orig'])) .") ";
            }
        }
        $options['orig_more'] = false;
        $arr = array_slice($options['orig'], 5, null, TRUE);
        if(isset($filters['orig']) and count($filters['orig'])>0){
            foreach ($filters['orig'] as $k => $v) {
                if(array_key_exists($k, $arr)){
                    $options['orig_more'] = true;
                    break;
                }
            }
        }
        //标签
        if(count($tag) > 0){
            if(isset($filters['tag'])) unset($filters['tag']);
            foreach ($tag as $k => $val) {
                if(!array_key_exists($k, $options['tag'])) continue;
                $filters['tag'][$k] = $options['tag'][$k]; 
            }
            if(count($filters['tag']) > 0){
                $where1.="and (`title` like '%". implode("%' OR `title` like '%", $filters['tag']) ."%') ";
            }
        }
        //价格        
        if($price > 0 && array_key_exists($price, $options['price'])){
            $filters['price'] = $price;
            if(isset($options['price'][$price]['min'])){
                $where1.="and pure_price>={$options['price'][$price]['min']} ";
            }
            if(isset($options['price'][$price]['max'])){
                $where1.="and pure_price<{$options['price'][$price]['max']} ";
            }
        }else{
            if($pricemin>0){
                $filters['pricemin'] = $pricemin;
                $where1.="and pure_price>={$pricemin} ";
            }
            if($pricemax>0){
                $filters['pricemax'] = $pricemax;
                $where1.="and pure_price<={$pricemax} ";
            }
        }

        //添加条件
        if(!empty($where)){
            $queryAllArr['where']['_complex'] = $where;
            $queryArr['where']['_complex'] = $where;
        }
        $queryAllArr['where']['_string'] = $all_where1;
        $queryArr['where']['_string'] = $where1;

        $queryAllArr['where']['_logic'] = 'and';
        $queryArr['where']['_logic'] = 'and';

        // echo "<pre>";print_r($queryAllArr['where']);print_r($queryArr['where']);echo "</pre>"; exit;

        //排序
        $queryArr['order'] =" add_time desc"; 
        if(array_key_exists($sortby, $options['sortby'])){
            $filters['sortby'] = $sortby;
            $queryArr['order'] = $options['sortby'][$sortby]; 
        }

        //统计字段
        //tag字段
        $queryAllArr['group']['tag'] = '(case ';
        foreach ($options['tag'] as $key => $value) {
            $queryAllArr['group']['tag'] .= "when `title` like '%{$value}%' then {$key} ";
        }
        $queryAllArr['group']['tag'] .= "else -1 end)";

        //price字段
        $queryAllArr['group']['price'] = '(case ';
        foreach ($options['price'] as $key => $value) {
            $queryAllArr['group']['price'] .= "when `pure_price` >= {$value['min']} ";
            if(isset($value['max'])){
                $queryAllArr['group']['price'] .= "and `pure_price` < {$value['max']} ";
            }
            $queryAllArr['group']['price'] .= "then {$key} ";
        }
        $queryAllArr['group']['price'] .= "else -1 end)";

        //判断是否在价格范围内
        if(isset($filters['pricemin']) && isset($filters['pricemax'])){
            $queryAllArr['group']['price_range'] = "(case when pure_price>={$filters['pricemin']} and pure_price<={$filters['pricemax']} then 1 else 0 end)";
        }else if(isset($filters['pricemin'])){
            $queryAllArr['group']['price_range'] = "(case when pure_price>={$filters['pricemin']} then 1 else 0 end)";
        }else if(isset($filters['pricemax'])){
            $queryAllArr['group']['price_range'] = "(case when pure_price<={$filters['pricemax']} then 1 else 0 end)";
        }else{
            $queryAllArr['group']['price_range'] = "1";
        }

        //统计按推荐、按国内外、按分类、按商场数据
        $data =  $mod->field("orig_id, cate_id, {$queryAllArr['group']['tag']} as tag, {$queryAllArr['group']['price']} as price, {$queryAllArr['group']['price_range']} as price_range, count(id) as item_count")->where($queryAllArr['where'])->group("orig_id, cate_id, {$queryAllArr['group']['tag']}, {$queryAllArr['group']['price']}, {$queryAllArr['group']['price_range']}")->select();

        //统计
        $count = 0;
        if(count($data) > 0){
            foreach ($data as $k => $val) {

                //国内外字段 //商场ID
                $ismy = -1;
                $orig_id = -1;
                if(isset($options['orig'][$val['orig_id']])){
                    $ismy = $options['orig'][$val['orig_id']]['ismy'];
                    $orig_id = $val['orig_id'];     
                }

                //一级和二级分类的id
                $cate_pid= -1;
                $cate_spid = -1;
                if(isset($cate_data[$val['cate_id']])){
                    list($p1,$p2) = explode('|', $cate_data[$val['cate_id']]['spid']."||");
                    //计算一级分类的商品数量
                    if($cate_data[$val['cate_id']]['pid']==0){
                        $cate_pid= $val['cate_id'];
                    }else if(!empty($p1)){
                        $cate_pid= $p1; 
                        //计算二级分类的商品数量
                        if(!empty($p2)){
                            $cate_spid = $p2; 
                        }
                    }
                }

                //tag, price
                $tag_id = $val['tag'];
                $price_id = $val['price'];
                $price_range = $val['price_range'];

                $item_count = $val['item_count'];

                $list_count = true;
                $ismy_count = true;
                $orig_count = true;
                $cate_count = true;
                if(isset($filters['property']) && !isset($filters['property'][$ismy])){
                    // $ismy_count = false;
                    $orig_count = false;
                    $cate_count = false;
                    $list_count = false;
                }
                if(isset($filters['cateid']) && $filters['cateid'] != $cate_spid){
                    $ismy_count = false;
                    // $cate_count = false;
                    $orig_count = false;
                    $list_count = false;
                }
                if(isset($filters['orig']) && !isset($filters['orig'][$orig_id])){
                    $ismy_count = false;
                    $cate_count = false;
                    // $orig_count = false;
                    $list_count = false;
                }
                if(isset($filters['tag']) && !isset($filters['tag'][$tag_id])){
                    $ismy_count = false;
                    $cate_count = false;
                    $orig_count = false;
                    $list_count = false;
                }
                if(isset($filters['price']) && !isset($filters['price'][$price_id])){
                    $ismy_count = false;
                    $cate_count = false;
                    $orig_count = false;
                    $list_count = false;
                }
                if($price_range == 0){
                    $ismy_count = false;
                    $cate_count = false;
                    $orig_count = false;
                    $list_count = false;
                }

                //全部
                $options['type'][1]['count'] += $item_count;

                if($list_count){
                    $count += $item_count;
                }

                //按国内外统计
                if($ismy_count){
                    if(isset($options['ismy'][$ismy])){
                            $options['ismy'][$ismy]['count'] += $item_count;
                    }
                }

                //按商城计算商品数量
                if($orig_count){
                    $options['orig'][$orig_id]['count'] = (isset($options['orig'][$orig_id]['count']) ? $options['orig'][$orig_id]['count'] : 0) + $item_count;
                }

                //计算一级和二级分类的商品数量
                if($cate_count){
                    if($cate_pid > 0){
                        $options['cate']['p'][$cate_pid]['count'] = (isset($options['cate']['p'][$cate_pid]['count']) ? $options['cate']['p'][$cate_pid]['count'] : 0) + $item_count;
                        if($cate_spid > 0){
                            $options['cate']['s'][$cate_pid][$cate_spid]['count'] = (isset($options['cate']['s'][$cate_pid][$cate_spid]['count']) ? $options['cate']['s'][$cate_pid][$cate_spid]['count'] : 0) + $item_count;
                        }
                    }
                }

            }
        }
        // print_r($options['orig']);

        //过滤没有商品的分类、商城等
        if(count($options['orig']) > 0){
            foreach ($options['orig'] as $k => $val) {
                if(!isset($val['count'])){
                    //若无数据，但是为过滤条件，显示0
                    if(isset($filters['orig']) && isset($filters['orig'][$k])){
                        $options['orig'][$k]['count'] = 0;
                    }else{
                        unset($options['orig'][$k]);
                    }
                }
            }
        }
        if(count($options['cate']) > 0){
            foreach ($options['cate']['p'] as $k => $val) {
                $check = 0;              
                if(isset($options['cate']['s'][$k])){
                    foreach ($options['cate']['s'][$k] as $k2 => $val2) {
                        if(!isset($val2['count'])){
                            //若无数据，但是为过滤条件，显示0
                            if(isset($filters['cateid']) && $filters['cateid'] == $k2){
                                $options['cate']['s'][$k][$k2]['count'] = 0;
                                $check = 1;
                            }else{
                                unset($options['cate']['s'][$k][$k2]);
                            }
                        }
                    }
                }
                if(!isset($val['count'])){
                    if($check){
                        $options['cate']['p'][$k]['count'] = 0;
                    }else{
                        unset($options['cate']['p'][$k]);
                        unset($options['cate']['s'][$k]);
                    }
                }
            }
        }
        // print_r($options['cate']);exit;

         //生成URL
        $path = (defined('GROUP_NAME')?GROUP_NAME:'')."/".MODULE_NAME."/".ACTION_NAME;
        $urls['raw_pure'] = U($path, $params);
        $arr = $filters;
        $urls['raw'] = U($path, $params) . "?" . http_build_query($arr);
        $arr = $filters;
        $arr['t']= array('1' => 'on');
        $urls['tuijan'] = U($path, $params) . "?" . http_build_query($arr);
        $arr['t']= array('2' => 'on');
        $urls['remen'] = U($path, $params) . "?" . http_build_query($arr);
        $arr['t']= array('3' => 'on');
        $urls['quanwang'] = U($path, $params) . "?" . http_build_query($arr);
        $arr = $filters;
        $arr['sortby'] = 'newest';
        $urls['newest'] = U($path, $params) . "?" . http_build_query($arr);
        $arr['sortby'] = 'hottest';
        $urls['hottest'] = U($path, $params) . "?" . http_build_query($arr);
        // echo "<br>$urls";exit;

        // 生成URL, param is array
        $arr = array();
        $all_params = array_merge($params, $filters);
        foreach ($all_params as $key => $value) {
            if($value === '' || (is_array($value) && count($value) == 0)) continue;
            if(is_array($value)){
                $v= implode(',', array_keys($value));
            }else{
                $v = $value;
            }
            $arr[$key] = $key . "=" . $v;
        }
        $parameter = implode('&', $arr);
        // echo $parameter;exit;

        //分页
        $pagesize=24;
        $pager = $this->_pager($count,$pagesize,$parameter);

        //查询列表
        $list = $mod->where($queryArr['where'])->order($queryArr['order'])->limit($pager->firstRow.",".$pager->listRows)->select();
        
        if(count($list)>=1){
            foreach($list as $key=>$val){
                $list[$key]['zan'] = $list[$key]['zan']   +intval($list[$key]['hits'] /10);

                // //商品一级分类
                // $cate_id = $list[$key]['cate_id'];
                // $cate_name = '';
                // if(isset($cate_data[$cate_id]) && $cate_data[$cate_id]['pid']==0){
                //     $cate_name = $cate_data[$cate_id]['name'];
                // }else if(isset($cate_data[$cate_id])){
                //     list($p1,$p2) = explode('|', $cate_data[$cate_id]['spid']."||");
                //     if(isset($cate_data[$p1])){
                //         $cate_name = $cate_data[$p1]['name'];
                //     }
                // }
                // $list[$key]['cate_name'] = $cate_name;
            }
        }
        // print_r($list);exit;
        
        $this->assign("urls", $urls);
        $this->assign('filters', $filters);
        $this->assign('options',$options);
        $this->assign('list',$list);

        $this->assign('p',$p);
        $this->assign('pagebar',$pager->newshow());
        $this->assign("dss_l",$dss_l);
        // echo "<pre>";print_r($filters);print_r($options);echo "</pre>"; exit;
        
    }

    /**
     * 瀑布显示
     */
    public function waterfall($where = array(), $order = 'id DESC', $field = '', $page_max = '', $target = '') {
        $time=time();
        $spage_size = C('pin_wall_spage_size'); //每次加载个数
        $spage_max = C('pin_wall_spage_max'); //每页加载次数
        $page_size = $spage_size * $spage_max; //每页显示个数
        $item_mod = M('item');
        $where_init = array('status'=>'1');
        $where = $where ? array_merge($where,$where_init) : $where_init;
        $where['add_time']=array('lt',$time);
        //echo "<pre>";
        $count = $item_mod->where($where)->count('id');
        //控制最多显示多少页
        //if ($page_max && $count > $page_max * $page_size) {
        //    $count = $page_max * $page_size;
        //}
        //查询字段
        $field == '' && $field = 'id,uid,uname,orig_id,title,intro,img,price,likes,comments,comments_cache,url,zan,hits,go_link,add_time';
        //分页
        $pager = $this->_pager($count, $page_size);
        $target && $pager->path = $target;
        $item_list = $item_mod->field($field)->where($where)->order($order)->limit($pager->firstRow.','.$page_size)->select();
    
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
        
    
        $this->assign('item_list', $item_list);
        
        /*echo "<pre>";
        var_dump($item_list);*/
        
        
        //当前页码
        $p = $this->_get('p', 'intval', 1);
        $this->assign('p', $p);
        //当前页面总数大于单次加载数才会执行动态加载
        if (($count - ($p-1) * $page_size) > $spage_size) {
            $this->assign('show_load', 1);
        }
        //总数大于单页数才显示分页
        $count > $page_size && $this->assign('page_bar', $pager->fshow());
        //最后一页分页处理
        if ((count($item_list) + $page_size * ($p-1)) == $count) {
            $this->assign('show_page', 1);
        }
        $this->assign("count",$count);
        
                            $this->assign("zh_count",$count);
    }


    /**
     * 瀑布显示
     */
    public function waterfall_xs($where = array(), $order = 'add_time DESC',$q, $field = '', $page_max = '', $target = '') {
        $time=time();
        $p = $this->_get('p', 'intval', 1);
        

        require LIB_PATH . 'Pinlib/php/lib/XS.php';
        $xs = new XS('baicai');
        $search = $xs->search;   //  获取搜索对象
        $search->setLimit(50,20*($p-1)); 
        $search->setSort('add_time',false);
        //$search->setFuzzy(true);
       // $search->addQueryTerm('title',$q,XS_CMD_QUERY_OP_OR);
       // $search->addQueryTerm(,$q,XS_CMD_QUERY_OP_OR);
       // $search->addQueryTerm('status','1',XS_CMD_QUERY_OP_AND);
       // $search->addRange('status',2,null);
        $search->setQuery($q);

        $docs = $search->search();
    //    $ss_cate = $search->getExpandedQuery($q);
     //   var_dump( $ss_cate);
     //   $this->assign('ss_cate', $ss_cate);
        //$docs = $search->search();
        $count = $search->count();
        //echo "<pre>";
        //var_dump($where);
        //$count = $item_mod->where($where)->count('id');
        //控制最多显示多少页
        //if ($page_max && $count > $page_max * $page_size) {
        //    $count = $page_max * $page_size;
        //}
        //查询字段
        $field = 'id,uid,uname,orig_id,title,intro,img,price,likes,comments,comments_cache,url,zan,hits,go_link,add_time';
        //分页
        $item_mod = M('item');
        $pager = $this->_pager($count, 20);
        $target && $pager->path = $target;

        foreach ($docs as $doc) {
            if($str==""){
                 $str=$doc->id;
            }
            else{
               $str.=",".$doc->id;
            }
        }
        $str && $where1['id'] = array('in', $str);
        $where1['add_time'] = array('lt',time());
        $str && $item_list = $item_mod->field($field)->where($where1)->order($order)->limit(20)->select();
    
        //$prefix = "/usr/local/xunsearch";  
        //require_once("$prefix/sdk/php/lib/XS.php");
       /* 

        $item_list = Array();
        foreach ($docs as $doc) {

            $item['title'] = $doc->title;
            $item['img'] = $doc->img;
            $item['id'] = $doc->id;
            $item['price'] = $doc->price;
            $item['add_time'] = $doc->add_time;
            $item['zan'] = $doc->zan;
            $item['hits'] = $doc->hits;
            $item['comments_cache'] = $doc->comments_cache;
            $item['orig_id'] = $doc->orig_id;
            $item['go_link'] = $doc->go_link;
            array_push($item_list,$item);
            # code...
        }
*/
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
        
    
        $this->assign('item_list', $item_list);
        
        /*echo "<pre>";
        var_dump($item_list);*/
        
        
        //当前页码
        
        $this->assign('p', $p);
        //当前页面总数大于单次加载数才会执行动态加载
        if (($count - ($p-1) * $page_size) > $spage_size) {
            $this->assign('show_load', 1);
        }
        //总数大于单页数才显示分页
        $count > $page_size && $this->assign('page_bar', $pager->fshow());
        //最后一页分页处理
        if ((count($item_list) + $page_size * ($p-1)) == $count) {
            $this->assign('show_page', 1);
        }
        $this->assign("count",$count);
        
        $this->assign("zh_count",$count);
        if($count == 0 ){
            $this->waterfall($where, $order);
        }
    }


    /**
     * 瀑布加载
     */
    public function wall_ajax($where = array(), $order = 'id DESC', $field = '') {
        $spage_size = C('pin_wall_spage_size'); //每次加载个数
        $spage_max = C('pin_wall_spage_max'); //加载次数
        $p = $this->_get('p', 'intval', 1); //页码

        //条件
        $where_init = array('status'=>'1');
        $where = array_merge($where_init, $where);
        //计算开始
        $start = $spage_size * $spage_max * ($p - 1);
        $item_mod = M('item');
       // print_r($where);
        $count = $item_mod->where($where)->count('id');
        $field == '' && $field = 'id,uid,uname,title,intro,img,price,likes,comments,comments_cache,url,add_time,orig_id,hits,zan';
        $item_list = $item_mod->field($field)->where($where)->order($order)->limit($start.','.$spage_size)->select();
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
        $this->assign('item_list', $item_list);
        $resp = $this->fetch('public:item_list');
        $data = array(
            'isfull' => 1,
            'html' => $resp
        );
        $count <= $start + $spage_size && $data['isfull'] = 0;
        $this->ajaxReturn(1, '', $data);
    }
    
    /**
     * 瀑布显示,搜索国内或海淘
     */
    public function waterfall_tp($where = array(), $order = 'id DESC', $tp='', $isbest='', $share =array() , $field = '', $page_max = '', $target = '') {
        $spage_size = C('pin_wall_spage_size'); //每次加载个数
        $spage_max = C('pin_wall_spage_max'); //每页加载次数
        $page_size = $spage_size * $spage_max; //每页显示个数
        $item_mod = M('item_orig');
        $where_init = array('status'=>'1');
        $where = $where ? array_merge($where_init, $where) : $where_init;
        $gn_where = $gw_where = $where;
        $count = $item_mod->count('id');//$count = $item_mod->where($where)->count('id');
        
        
        $db_pre = C('DB_PREFIX');
   /*       $zh_where = $gn_where = $gw_where = $jp_where =$where;

       $zh_where['_string'] = $zh_where['_string'] . ' and ';
      $gn_where['_string'] =$db_pre."item_orig.ismy=0 and i.status=1";
        $gw_where['_string'] = $db_pre."item_orig.ismy=1 and i.status=1";
        $jp_where['_string'] = "i.isbest=1";
*/

        if($tp != '') {
            if($tp != '9' and $tp != '10'){
            $where['_string'] = $where['_string'] . ' and ' . $db_pre . "item_orig.ismy='$tp' and i.status=1";
            }
            else{
            $where['_string'] = $where['_string'] . $db_pre . "item_orig.ismy='$tp' and i.status=1"; 
            }
        }else{
            $where['_string']=$where['_string'];
        }
        if($isbest != ''){
            $where['_string'] = $where['_string'] . " and i.isbest=1";
        }

        $count = $item_mod->where($where)->join($db_pre."item i ON i.orig_id=".$db_pre."item_orig.id")->count();
        //显示各个数量
/*
        $zh_count = $item_mod->where($zh_where)->join($db_pre."item i ON i.orig_id=".$db_pre."item_orig.id")->count();
        $gn_count = $item_mod->where($gn_where)->join($db_pre."item i ON i.orig_id=".$db_pre."item_orig.id")->count();
        $gw_count = $item_mod->where($gw_where)->join($db_pre."item i ON i.orig_id=".$db_pre."item_orig.id")->count();
        $jp_count = $item_mod->where($jp_where)->join($db_pre."item i ON i.orig_id=".$db_pre."item_orig.id")->count();
        $article = M('article');
        $share['cate_id'] = 10;
        $sd_count = $article->where($share)->count('id');
        $share['cate_id'] = 9;
        $gl_count = $article->where($share)->count('id');
        */
        //控制最多显示多少页
        //if ($page_max && $count > $page_max * $page_size) {
        //    $count = $page_max * $page_size;
        //}
        //查询字段
        $field == '' && $field = 'i.id,i.uid,i.uname,i.orig_id,i.title,i.intro,i.img,i.price,i.likes,i.comments,i.comments_cache,i.url,i.zan,i.go_link,i.add_time';
        //分页
        $pager = $this->_pager($count, $page_size);
        $target && $pager->path = $target;
       // $item_list = $item_mod->field($field)->where($where)->join($db_pre . 'item i ON i.orig_id = ' . $db_pre . 'item_orig.id')->order($order)->limit($pager->firstRow.','.$page_size)->select();
        
       // $prefix = "/usr/local/xunsearch";  
        //加载XS.php，这步是必须的  
        //require_once("$prefix/sdk/php/lib/XS.php");
        //require_once("$prefix/sdk/php/lib/XS.php");
        require LIB_PATH . 'Pinlib/php/lib/XS.php';

        $xs = new XS('baicai');
        $search = $xs->search;   //  获取搜索对象
        $search->setLimit(20); 
        $item_list = $search->setQuery("白菜哦")->search();
        
        foreach ($item_list as $key=>$val) {
            isset($val['comments_cache']) && $item_list[$key]['comment_list'] = unserialize($val['comments_cache']);
        }
        $this->assign('item_list', $item_list);
        //当前页码
        $p = $this->_get('p', 'intval', 1);
        $this->assign('p', $p);
        //当前页面总数大于单次加载数才会执行动态加载
        if (($count - ($p-1) * $page_size) > $spage_size) {
            $this->assign('show_load', 1);
        }
        //总数大于单页数才显示分页
        $count > $page_size && $this->assign('page_bar', $pager->fshow());
        //最后一页分页处理
        if ((count($item_list) + $page_size * ($p-1)) == $count) {
            $this->assign('show_page', 1);
        }
        $this->assign("count",$count);
        /*
        $this->assign("zh_count",$zh_count);
        $this->assign("gn_count",$gn_count);
        $this->assign("gw_count",$gw_count);
        $this->assign("jp_count",$jp_count);
        $this->assign("sd_count",$sd_count);
        $this->assign("gl_count",$gl_count);
*/
    }
}
