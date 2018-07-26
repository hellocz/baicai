<?php

class item_monitorModel extends Model
{
    function update_price_by_id($id,$price){
    	$result = $this->where("id=$id")->setField("bottom_price",floatval($price));
    	if ($result) {
            return true;
        } else {
            return false;
        }
    }

    function create_monitor($data){
        if($data['status'] != 1 || $data['orig_id'] == 0 || $data['title'] =="" || $data['pure_price'] == 0){
            return ;
        }
        $goods_info = getgoods_info($data['url'],$data['orig_id']);
        if($goods_info == ""){
            return;
        }
        $goods_item = $this->where(array("orig_id"=>$data['orig_id'],"goods_id"=>$goods_info['goods_id']))->find();
        if(!$goods_item){
        $monitor_goods['cate_id'] = $data['cate_id'];
        $monitor_goods['orig_id'] = $data['orig_id'];
        $monitor_goods['goods_id'] = $goods_info['goods_id'];
        $monitor_goods['title'] = $data['title'];
        $monitor_goods['img'] = $data['img'];
        $monitor_goods['last_price'] = $data['pure_price'];
        $monitor_goods['bottom_price'] = $data['pure_price'];
        $monitor_goods['url'] = $goods_info['url'];
        $monitor_goods['add_time'] = $data['add_time'];
        $monitor_goods['tag_cache'] = $data['tag_cache'];
        $monitor_goods['content'] = $data['content'];
        $monitor_goods['go_link'] = $data['go_link'];
        $monitor_goods['ispost'] = $data['ispost'];
        $this->add($monitor_goods);
        }

        D("price_history")->history_price_init($goods_info['goods_id'],$data['orig_id'],$goods_info['url']);       
    }
}