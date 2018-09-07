<?php

function addslashes_deep($value) {
    $value = is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
    return $value;
}
function grade($id){
	$exp = M("user")->where("id=$id")->getField('exp');
	$grade = M("grade")->where("min<=$exp and max>=$exp")->getField("grade");
	return $grade;
}
function import_item($file)
{
	set_time_limit(0);

	if (file_exists($file)) {
	} else if (file_exists(iconv('UTF-8', 'GB2312', $file))) {
		$file = iconv('UTF-8', 'GB2312', $file);
	} else {
		return false;
	}

	$Item = d('nine');
	$success = $fail = $repeat = 0;
	vendor('Excel.PHPExcel');
	vendor('Excel.PHPExcel.IOFactory');
	vendor('Excel.PHPExcel.Reader.Excel2007');
	$objReader = PHPExcel_IOFactory::createReader('Excel2007');
	$objPHPExcel = $objReader->load($file, $encode = 'utf-8');
	$sheetCount = $objPHPExcel->getSheetCount();

	for($j = 0 ; $j <= $sheetCount-1; $j++){
		$sheet = $objPHPExcel->getSheet($j);
		$highestRow = $sheet->getHighestRow();
		for ($i = 2; $i <= $highestRow; $i++) {
		$data=array();
		$data['title'] = $sheet->getCellByColumnAndRow(1, $i)->getValue() ;
		$data['img'] =  $sheet->getCellByColumnAndRow(2, $i)->getValue() ;
		$data['price'] = $sheet->getCellByColumnAndRow(5, $i)->getValue() ;
		$data['sales_volume'] =  $sheet->getCellByColumnAndRow(6, $i)->getValue() ;
		$data['end_time'] =  $sheet->getCellByColumnAndRow(13, $i)->getValue() ;
		$data['url'] =$sheet->getCellByColumnAndRow(16, $i)->getValue() ;
		$data['tick'] = $sheet->getCellByColumnAndRow(18, $i)->getValue() ;
		$data['is_stick'] = $sheet->getCellByColumnAndRow(19, $i)->getValue() ;
			if($data['end_time'])$data['end_time'] =strtotime($data['end_time']);
			$data['cat_id']=$j+1;
		if($data && $data['title'] && $data['img'] && $data['url']){
			$Item->add($data);
		}
	}
	}
	return true;
}
function time2string($time){
	if($time < $time)return "已结束";
	$second = $time-time();
	$day = floor($second/(3600*24));
	$second = $second%(3600*24);//除去整天之后剩余的时间
	$hour = floor($second/3600);
	$second = $second600;//除去整小时之后剩余的时间
	$minute = floor($second/60);
	$second = $second;
    //返回字符串
    return $day.'天'.$hour.'小时';
}
function stripslashes_deep($value) {
    if (is_array($value)) {
        $value = array_map('stripslashes_deep', $value);
    } elseif (is_object($value)) {
        $vars = get_object_vars($value);
        foreach ($vars as $key => $data) {
            $value->{$key} = stripslashes_deep($data);
        }
    } else {
        $value = stripslashes($value);
    }

    return $value;
}
function sortByAddTime($a, $b) {

if ($a['add_time'] == $b['add_time']) {

return 0;

} else {

return ($a['add_time'] < $b['add_time']) ? 1 : -1;

}
}
 function sortByVolume($a, $b) {

        if ($a['volume'] == $b['volume']) {

        return 0;

        } else {

        return ( $a['volume'] < $b['volume']) ? 1 : -1;

        }
    }

    function sortByCoupon($a, $b) {

        if ($a['coupon'] == $b['coupon']) {

        return 0;

        } else {

        return ( $a['coupon']< $b['coupon']) ? 1 : -1;

        }
    }

    function sortByPrice($a, $b) {

        if ( $a['price'] == $b['price']) {

        return 0;

        } else {

        return ( $a['price'] > $b['price']) ? 1 : -1;

        }
    }

    function sortByZK($a, $b) {

        if ($a['price']/$a['zk_final_price'] == $b['price']/$b['zk_final_price'] ) {

        return 0;

        } else {

        return ( $a['price']/$a['zk_final_price']  > $b['price']/$b['zk_final_price'] ) ? 1 : -1;

        }
    }

    function sortByCount($a, $b) {

        if ($a['count'] == $b['count']) {

        return 0;

        } else {

        return ( $a['count'] < $b['count']) ? 1 : -1;

        }
    }


function sbbtime($time){
	if($time>time())return true;
	return false;
}
function todaytime() {
    return mktime(0, 0, 0, date('m'), date('d'), date('Y'));
}

function mdate($time = NULL) {
	$text = '';
	$time = $time === NULL || $time > time() ? time() : intval($time);
	$date = strtotime(date('Ymd'));
	$t = time() - $time; //时间差 （秒）
	$y = date('Y', $time)-date('Y', time());//是否跨年
	switch($t){
		case $t < time() - $date:
			$text =date('H:i', $time);; // 一天内
			break;
		case $t < 60 * 60 * 24 * 365&&$y==0:
			$text = date('m-d H:i', $time); //一年内
			break;
		default:
			$text = date('Y-m-d H:i', $time); //一年以前
			break;
	}

	return $text;
}

function mdateA($time = NULL) {
	$text = '';
	$text=date("Y-m-d H:i:s",$time);

	return $text;
}
/**
 * 友好时间
 */

function fdate($time) {
    if (!$time)
        return false;
    $fdate = '';
    $d = time() - intval($time);
    $ld = time() - mktime(0, 0, 0, 0, 0, date('Y')); //年
    $md = time() - mktime(0, 0, 0, date('m'), 0, date('Y')); //月
    $byd = time() - mktime(0, 0, 0, date('m'), date('d') - 2, date('Y')); //前天
    $yd = time() - mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')); //昨天
    $dd = time() - mktime(0, 0, 0, date('m'), date('d'), date('Y')); //今天
    $td = time() - mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')); //明天
    $atd = time() - mktime(0, 0, 0, date('m'), date('d') + 2, date('Y')); //后天
    if ($d == 0) {
        $fdate = '刚刚';
    } else {
        switch ($d) {
            case $d < $atd:
                $fdate = date('Y年m月d日', $time);
                break;
            case $d < $td:
                $fdate = '后天' . date('H:i', $time);
                break;
            case $d < 0:
                $fdate = '明天' . date('H:i', $time);
                break;
            case $d < 60:
                $fdate = $d . '秒前';
                break;
            case $d < 3600:
                $fdate = floor($d / 60) . '分钟前';
                break;
            case $d < $dd:
                $fdate = floor($d / 3600) . '小时前';
                break;
            case $d < $yd:
                $fdate = '昨天' . date('H:i', $time);
                break;
            case $d < $byd:
                $fdate = '前天' . date('H:i', $time);
                break;
            case $d < $md:
                $fdate = date('Y年m月d日 H:i', $time);
                break;
            case $d < $ld:
                $fdate = date('Y年m月d日 H:i', $time);
                break;
            default:
                $fdate = date('Y年m月d日 H:i', $time);
                break;
        }
    }
    return $fdate;
}

function fpubdate($time) {
    if (!$time)
        return false;
     $fdate = date('Y-m-d',$time) . 'T' . date('H:i:s',$time);
     return $fdate;
}

/**
 * 获取用户头像
 */
function avatar($uid, $size) {
	  $avatar_size = explode(',', C('pin_avatar_size'));
    $size = in_array($size, $avatar_size) ? $size : '100';
    $avatar_dir = avatar_dir($uid);
    $user = M("user")->where(array('id'=>$uid))->find();
    if($user['is_avator'] == 1){
    	return $user['img_url'];
    }
    else{
    	$avatar_file = "default_{$size}.jpg";
    	return __ROOT__.'/' . C('pin_attach_path') . 'avatar/' . $avatar_file;
    }
}

function avatar_dir($uid) {
    $uid = abs(intval($uid));
    $suid = sprintf("%09d", $uid);
    $dir1 = substr($suid, 0, 3);
    $dir2 = substr($suid, 3, 2);
    $dir3 = substr($suid, 5, 2);
    return $dir1 . '/' . $dir2 . '/' . $dir3 . '/';
}
function shortUrl($input){
    $base32 = array (
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
        'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
        'q', 'r', 's', 't', 'u', 'v', 'w', 'x',
        'y', 'z', '0', '1', '2', '3', '4', '5'
    );

    $hex = md5($input);
    $hexLen = strlen($hex);
    $subHexLen = $hexLen / 8;
    $output = array();

    for ($i = 0; $i < $subHexLen; $i++) {
//把加密字符按照8位一组16进制与0x3FFFFFFF(30位1)进行位与运算
        $subHex = substr ($hex, $i * 8, 8);
        $int = 0x3FFFFFFF & (1 * ('0x'.$subHex));
        $out = '';

        for ($j = 0; $j < 6; $j++) {

//把得到的值与0x0000001F进行位与运算，取得字符数组chars索引
            $val = 0x0000001F & $int;
            $out .= $base32[$val];
            $int = $int >> 5;
        }

        $output[] = $out;
    }
    $show=implode('_',$output);
	return $show;
}

function shortUrlCreate($input){
	    $base32 = array (
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
        'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
        'q', 'r', 's', 't', 'u', 'v', 'w', 'x',
        'y', 'z', '0', '1', '2', '3', '4', '5'
    );

    $hex = md5($input);
    $hexLen = strlen($hex);
    $subHexLen = $hexLen / 8;
    $output = array();

    for ($i = 0; $i < $subHexLen; $i++) {
//把加密字符按照8位一组16进制与0x3FFFFFFF(30位1)进行位与运算
        $subHex = substr ($hex, $i * 8, 8);
        $int = 0x3FFFFFFF & (1 * ('0x'.$subHex));
        $out = '';

        for ($j = 0; $j < 6; $j++) {

//把得到的值与0x0000001F进行位与运算，取得字符数组chars索引
            $val = 0x0000001F & $int;
            $out .= $base32[$val];
            $int = $int >> 5;
        }

        $output[] = $out;
    }
    $show=implode('_',$output);
	$map=array('show'=>$show);
	$str = M("go")->where($map)->find();
	$data=array();
	if(empty($str)){
		$data['url']=mysql_real_escape_string($input);
		$data['show'] = $show;
		M("go")->add($data);
		
	}elseif($str && $str['url'] != $input){
		$data['url']=$input;
		$data['show'] = $show;
		M("go")->where(array('id'=>$str['id']))->save($data);
	}
	return $show;
}
function attach($attach, $type) {
	if($attach==""){
		return "/images/nopic.jpg";
	}else{
		if(strpos($attach, 'http://') !== false || strpos($attach, 'https://') !== false || strpos($attach, 'base64') !== false){
			return $attach;
		}
		else{
			return __ROOT__ . '/' . C('pin_attach_path') . $type . '/' . $attach;
		}
		
	}
}
function get_rout_img($attach,$type,$rout=IMG_ROOT_PATH){
	if($attach==""){
		return "/images/nopic.jpg";
	}else{
		if(false === strpos($attach, 'http://')) {
			//本地附件
			return $rout.'/' . C('pin_attach_path') . $type . '/' . $attach;
			//远程附件
			//todo...
		} else {
			//URL链接
			return $attach;
		}
	}
}
/*
 * 获取缩略图
 */

function get_thumb($img, $suffix = '_thumb') {
    if (false === strpos($img, 'http://')) {
        $ext = array_pop(explode('.', $img));
        $thumb = str_replace('.' . $ext, $suffix . '.' . $ext, $img);
    } else {
        if (false !== strpos($img, 'taobaocdn.com') || false !== strpos($img, 'taobao.com')) {
            //淘宝图片 _s _m _b
            switch ($suffix) {
                case '_s':
                    $thumb = $img . '_100x100.jpg';
                    break;
                case '_m':
                    $thumb = $img . '_210x1000.jpg';
                    break;
                case '_b':
                    $thumb = $img . '_480x480.jpg';
                    break;
            }
        }
    }
    return $thumb;
}

/**
 * 对象转换成数组
 */
function object_to_array($obj) {
    $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
    foreach ($_arr as $key => $val) {
        $val = (is_array($val) || is_object($val)) ? object_to_array($val) : $val;
        $arr[$key] = $val;
    }
    return $arr;
}
/*获取商品来源*/
function getly($orig_id){
	$str = M("item_orig")->where("id=$orig_id")->getField('name');
	return $str;
}
/*获取活动名称*/
function get_activityname($activity_id){
	$str = M("activity")->where("id=$activity_id")->getField('name');
	return $str;
}
/*获取用户名*/
function get_uname($uid){
	$uname = M("user")->where("id='$uid'")->getField("username");
	return $uname;
}
/*获取商城导航*/
function getdh(){
	$dh=M("item_orig")->order("ordid asc")->limit(12)->select();
	$str = "";
	foreach($dh as $key=>$val){
		$str .= "<a href='".U('go/index',array("to"=>base64_encode($val['url'])))."' title='$val[name]' target='_blank'>".$val['name']."</a>";
	}
	return $str;
}
/*面包削*/
function getpos($id,$str=''){
	$pstr=M("item_cate")->where("id=$id")->field("id,name,pid")->find();
	if($pstr['pid']!='0'){
		if($str==""){
			$str = "<a href='".U('book/cate',array('cid'=>$id))."'>".$pstr['name']."</a>";
		}else{
			$str = "<a href='".U('book/cate',array('cid'=>$id))."'>".$pstr['name']."</a> > ".$str;
		}
		$str = getpos($pstr['pid'],$str);
	}else{
		if($str==""){
			$str = "<a href='".U('book/cate',array('cid'=>$id))."'>".$pstr['name']."</a> ";
		}else{
			$str = "<a href='".U('book/cate',array('cid'=>$id))."'>".$pstr['name']."</a> > ".$str;
		}
	}
	return $str;
}
/*文章类面包削*/
function getapos($id,$str=''){
	$pstr=M("article_cate")->where("id='$id'")->find();
	if($pstr['pid']!='0'){
		if($str==""){
			$str = "<a href='".U('article/index',array('id'=>$id))."'>".$pstr['name']."</a>";
		}else{
			$str = "<a href='".U('article/index',array('id'=>$id))."'>".$pstr['name']."</a> > ".$str;
		}
		$str = getapos($pstr['pid'],$str);
	}else{
		if($str==""){
			$str = "<a href='".U('article/index',array('id'=>$id))."'>".$pstr['name']."</a> ";
		}else{
			$str = "<a href='".U('article/index',array('id'=>$id))."'>".$pstr['name']."</a> > ".$str;
		}
	}
	return $str;
}
//获取IP地址
function getip(){
	if(!empty($_SERVER["HTTP_CLIENT_IP"])){
	  $cip = $_SERVER["HTTP_CLIENT_IP"];
	}
	elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
	  $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	}
	elseif(!empty($_SERVER["REMOTE_ADDR"])){
	  $cip = $_SERVER["REMOTE_ADDR"];
	}
	else{
	  $cip = "无法获取！";
	}
	return $cip;
}
//文章类根栏目
function getbcid($id){
	$myid=M("article_cate")->where("id='$id'")->field('id,pid')->find();
	if($myid['pid']!='0'){
		$bcid = getbcid($myid['pid']);
	}else{
		$bcid = $myid['id'];
	}
	return $bcid;
}
//商品栏目名
function getcate($id){
	$str = M("item_cate")->where("id=$id")->getField('name');
	return $str;
}
//获取积分商品图片
function score_item_img($id){
	$img = M("score_item")->where("id=$id")->getField("img");
	$img = attach($img,'score_item');
	return $img;
}
//获取商城图片
function orig_img($id){
	$img = M("item_orig")->where("id=$id")->getField("img_url");
	if($img){
		$img = attach($img,"item_orig");
		return $img;
	}else{
		return '/images/nopic.jpg';
	}
}
//获取评论对象名称
function get_item_name($id,$xid){
	switch($xid){
		case "1":$mod=M('item');$name="title";break;
		case "2":$mod=M("zr");$name="title";break;
		case "3":$mod=M("article");$name="title";break;
		case "4":$mod=M("comment");$name="info";break;
	}
	$str = $mod->where("id=$id")->getField($name);
	return $str;
}
//获取模型对象
function get_mod($xid){
	switch($xid){
		case "1":$mod_s=M("item");break;
		case "2":$mod_s=M("zr");break;
		case "3":$mod_s=M("article");break;
	}
	return $mod_s;
}
//获取是否收藏
function islikes($itemid,$xid,$uid){
	$islike=M("likes")->where("uid=$uid and itemid=$itemid and xid=$xid")->find();
	if($islike){return "sz_11_l";}else{return "sz_11";}
}
//积分日志
function set_score_log($user,$action,$score,$coin,$offer,$exp){
	$score_log_mod = D('score_log');
	$score_log_mod->create(array(
		'uid' => $user['id'],
		'uname' => $user['username'],
		'action' => $action,
		'score' => $score,
		'coin' => $coin,
		'offer' => $offer,
		'exp' => $exp,
	));
	$score_log_mod->add();
}
//设置积分，金币等
function set_score($user,$score,$coin,$offer,$exp){
	$data['score']=$user['score']+$score;
	$data['coin']=$user['coin']+$coin;
	$data['exp']=$user['exp']+$exp;
	$data['offer']=$user['offer']+$offer;
	M("user")->where("id=$user[id]")->save($data);
}
//设置SEO
function set_seo($str){
	return array('title'=>$str."-".C('pin_site_name'),'keywords'=>$str."-".C('pin_site_name'),'description'=>$str."-".C('pin_site_name'));
}
function getHTTPS($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_REFERER, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_ENCODING, "");
	$result = curl_exec($ch);
	curl_close($ch);
	return mb_convert_encoding($result, 'utf-8', 'GBK,UTF-8,ASCII');
}
function http($url, $method, $postfields = null, $headers = array(), $debug = false)
{
$ci = curl_init();
/* Curl settings */
curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ci, CURLOPT_TIMEOUT, 30);
curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);

switch ($method) {
case 'POST':
curl_setopt($ci, CURLOPT_POST, true);
if (!empty($postfields)) {
curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
$this->postdata = $postfields;
}
break;
}
curl_setopt($ci, CURLOPT_URL, $url);
curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ci, CURLINFO_HEADER_OUT, true);

$response = curl_exec($ci);
$http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);

if ($debug) {
echo "=====post data======\r\n";
var_dump($postfields);

echo '=====info=====' . "\r\n";
print_r(curl_getinfo($ci));

echo '=====$response=====' . "\r\n";
print_r($response);
}
curl_close($ci);
return array($http_code, $response);
}

function get_result($state = 10001, $data , $msg="请求成功"){
    $result  = [
        'state' => $state,
        'msg'   =>  $msg,
        'data'  =>  $data,
   ];
    return json_encode($result);
}
function getgoods_info($url,$orig_id){
        switch ($orig_id){
        case 358:
         preg_match("/(\d+)\.html/", $url,$match_id);
         if(empty($match_id[1])){
                return "";
            }
            return array("goods_id"=>$match_id[1],"url"=>"https://item.jd.com/" . $match_id[1] . ".html");
        case 393:
         preg_match("/(\d+)\.html/", $url,$match_id);
         if(empty($match_id[1])){
                return "";
            }
        return array("goods_id"=>$match_id[1],"url"=>"https://item.jd.hk/" . $match_id[1] . ".html");
        case 29;
        case 5;
        case 3:
        $goods_id = get_tb_id($url);
        if($goods_id){
        	return array("goods_id"=>$goods_id,"url"=>"https://detail.tmall.com/item.htm?id=" . $goods_id);
        }
        else{
        	return "";
        }
        case 506;
        case 2:
        $pattern = '/product\/(([a-zA-Z]|\d){10})/';
        $pattern_num = preg_match($pattern,$url,$match_id);
            if($pattern_num!=0){
                $goods_id = $match_id[1];
                return array("goods_id"=>$goods_id,"url"=>"https://www.amazon.cn/dp/" . $goods_id);
             }
              else{
           $pattern1 = '/dp\/(([a-zA-Z]|\d){10})/';
           $pattern_num1 = preg_match($pattern1,$url,$match_id);
             if($pattern_num1!=0){
                 $goods_id = $match_id[1];
                return array("goods_id"=>$goods_id,"url"=>"https://www.amazon.cn/dp/" . $goods_id);
             }
             else{
             	return "";
             }
         }
        return "";
    }
}
function tb_uland_parse($url){
	$appkey = "4799843";
	$uland_params_url = parse_url($url);
    parse_str($uland_params_url['query'],$uland_url);
    $e = $uland_url['e'];
    if(!$e){
        $e = $uland_url['me'];
    	}
    if($e){
        $applinzi_parse_url = "http://1.alimama.applinzi.com/getCouponParm.php?appkey=" . $appkey . "&e={$e}";
        $applinzi_parse_data = http($applinzi_parse_url);
        if($applinzi_parse_data[0] == 200)
	        {
	        	$applinzi_parse_result = json_decode($applinzi_parse_data[1], TRUE);
	        }
        return array("goods_id"=>$applinzi_parse_result['data']['result']['item']['itemId'],"retStatus"=>$applinzi_parse_result['data']['result']['retStatus'],"discountPrice"=>$applinzi_parse_result['data']['result']['item']['discountPrice'],"amount"=>$applinzi_parse_result['data']['result']['amount'],"uland_url"=>$url,"title"=>$applinzi_parse_result['data']['result']['item']['title']);
    	}
    	return null;   
    }
function get_tb_id($url){
	$goods_id = tb_uland_parse($url)['goods_id'];
	if($goods_id){
		return $goods_id;
	}
	else{
		$uland_params_url = parse_url($url);
		parse_str($uland_params_url['query'],$uland_url);
		if($uland_url['itemId']){
			return $uland_url['itemId'];
		}

		$redrect_content =file_get_contents($url);
    //    $headers = get_headers('http://guangdiu.com/' . $e->href, TRUE);
        $pattern = '/((https%3A%2F%2Fuland.taobao.com.*?)\")/';
        $pattern_num = preg_match($pattern, $redrect_content,$pattern_result);
    //    if(empty($pattern_result[2])){
    //        $pattern_num = preg_match($pattern, $headers['Location'],$pattern_result);
    //    }
        $url = urldecode($pattern_result[2]);
        return tb_uland_parse($url)['goods_id'];
	}
}

function generater_ulan_by_id($goods_id,$mm_pid = "mm_27883119_3410238_144058484"){
	$appkey = "4799843";
	$mm_pid = trim($mm_pid);
	$applinzi_high_url = "http://1.taoketool.applinzi.com/getHighapi.php?appkey=" . $appkey . "&pid=" . $mm_pid . "&goodsId=" . $goods_id;
    $applinzi_high_data = http($applinzi_high_url);
    if($applinzi_high_data[0] == 200)
        {
        $applinzi_high_result = json_decode($applinzi_high_data[1], TRUE);
        return $applinzi_high_result['result']['coupon_click_url'];
        }
    return null;
}

function jd_price($goods_id){
        include_once LIB_PATH . 'Pinlib/jd/JdClient.php';
        include_once LIB_PATH . 'Pinlib/jd/request/WarePriceGetRequest.php';

        $c = new JdClient();

        $c->appKey = "01E4158966092DC5F5E95A0ED39F2C64";

        $c->appSecret = "5a0beb8b45964b43a5e0e98bd455afa4";

        $c->accessToken = "53fd32f5-27fb-404d-90f2-b70a12df4936";

        $req = new WarePriceGetRequest();

        $req->setSkuId($goods_id);

        $resp = $c->execute($req, $c->accessToken);
        $resp_json = json_encode($resp);
        $result = json_decode($resp_json,true);
        if($result['code'] == 0){
        return floatval($result['price_changes'][0]['price']);
    }
    else
        return 0;
}

function tb_price($goods_id){
	 $uland_url = generater_ulan_by_id($goods_id);
	 if($uland_url){
	 	$taobao_info = tb_uland_parse($uland_url);
	 	if($taobao_info){
	 		return $taobao_info;
	 	}
	 	return null;
	 }
	 return null;
}

function amazon_price($goods_ids){

$access_key_id = "AKIAJARJMU6KON6YVZDQ";

// Your Secret Key corresponding to the above ID, as taken from the Your Account page
$secret_key = "YF0qF9GP1sFR2oMRmafVkiM+wq9q0EMd54e104oE";

// The region you are interested in
$endpoint = "webservices.amazon.cn";

$uri = "/onca/xml";

$params = array(
    "Service" => "AWSECommerceService",
    "Operation" => "ItemLookup",
    "AWSAccessKeyId" => "AKIAJARJMU6KON6YVZDQ",
    "AssociateTag" => "abaicaiozb-23",
    "ItemId" => $goods_ids,
    "IdType" => "ASIN",
    "ResponseGroup" => "OfferFull"
);

// Set current timestamp if not set
if (!isset($params["Timestamp"])) {
    $params["Timestamp"] = gmdate('Y-m-d\TH:i:s\Z');
}

// Sort the parameters by key
ksort($params);

$pairs = array();

foreach ($params as $key => $value) {
    array_push($pairs, rawurlencode($key)."=".rawurlencode($value));
}

// Generate the canonical query
$canonical_query_string = join("&", $pairs);

// Generate the string to be signed
$string_to_sign = "GET\n".$endpoint."\n".$uri."\n".$canonical_query_string;

// Generate the signature required by the Product Advertising API
$signature = base64_encode(hash_hmac("sha256", $string_to_sign, $secret_key, true));

// Generate the signed URL
$request_url = 'https://'.$endpoint.$uri.'?'.$canonical_query_string.'&Signature='.rawurlencode($signature);

$i = 0;

$applinzi_high_data = http($request_url);
    if($applinzi_high_data[0] == 200)
        {
        $applinzi_high_result = json_decode($applinzi_high_data[1], TRUE);
		$xml = simplexml_load_string($applinzi_high_data[1]);
		//$result = $xpath->query("//Item")->item(0);
		$prices = array();
		foreach ($xml->Items->Item as $item) {
			$price =floatval($item->Offers->Offer->OfferListing->Price->Amount / 100);
			var_dump($price);
			 if(!$price){
      			$price = 0;
      		}
      		array_push($prices, $price);
		}
      	

      	// for($i=0 ; i<$price_list->length; $i++){
      	// 	$price = floatval($price_list->item($i)->firstChild->nodeValue) / 100;
      	// 	if(!$price){
      	// 		$price = 0;
      	// 	}
      	// 	var_dump($price);
      	// 	$item_list[$i]['price'] = $price;
      	//}
		//$price = floatval($vals[$index['AMOUNT'][0]]['value']) / 100;
        return $prices;
        }
        else{
        	var_dump($applinzi_high_data[0]);
        	return null;
        }
    return null;
}


?>