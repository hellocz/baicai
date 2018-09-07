<?php
require LIB_PATH . 'Pinlib/autoload.php';
       use JPush\Client  as JPush;

class crontabAction extends frontendAction {

    public function _initialize() {
        parent::_initialize();
        //访问者控制
        if (!$this->visitor->is_login && in_array(ACTION_NAME, array('share_item', 'fetch_item', 'publish_item', 'like', 'unlike', 'delete', 'comment','publish'))) {
            IS_AJAX && $this->ajaxReturn(0, L('login_please'));
            $this->redirect('user/login');
        }
    }

    /**
     * 商品详细页
     */
    public function wb() {
        $time =time();
        $fivemin_before = $time -300;
        $map['add_time'] = array('between',"$fivemin_before, $time");
        $map['status'] =1;
        $map['orig_id'] = array("not in",array(3,5,29,50,268,297,841,987));
        $items= M('item')->field('id,title,img,content,price')->where($map)->select();
        $oauth = new oauth('sina');
        foreach ($items as $item) {
        $status = strip_tags($item['title']) . $item['price'] ."|" .substr(strip_tags($item['content']),0,200) . "http://www.baicaio.com/item/".$item['id']. ".html";
        $url = $item['img'];
        $oauth->uploaddocument($status,$url);
        } 

    }
/**
    * 熊掌号推送
    */

    public function bear_push(){
        $time = time();
        $fivemin_before = $time -300;
        $map['add_time'] = array('between',"$fivemin_before, $time");
        $map['status'] = 1;
        $map['isoriginal'] = 1;
        $items= M('item')->field('id')->where($map)->select();

        $article_map['status'] = 1;
        $article_map['add_time'] = array('between',"$fivemin_before, $time");
        $articles= M('article')->field('id')->where($article_map)->select();

        if(!empty($items) || !empty($articles)){
          $urls = array();
          foreach ($items as $item) {
            $url = 'http://www.baicaio.com/item/' . $item['id'] . '.html';
            array_push($urls, $url);
          }
          foreach ($articles as $article) {
            $url = 'http://www.baicaio.com/article/' . $article['id'] . '.html';
            array_push($urls, $url);
          }
      $api = 'http://data.zz.baidu.com/urls?appid=1587722983761931&token=IkgnHbK6nGfqsqGB&type=realtime';
      $ch = curl_init();
      $options =  array(
          CURLOPT_URL => $api,
          CURLOPT_POST => true,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_POSTFIELDS => implode("\n", $urls),
          CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
      );
      curl_setopt_array($ch, $options);
      $result = curl_exec($ch);
      echo $result;
        }

    }
    public function baicai_push_generator(){
      $time =time();
        $fivemin_before = $time -300;
        $map['add_time'] = array('between',"$fivemin_before, $time");
        $map['status'] =1;
     //   $map['id'] = 277567;
        $items= M('item')->field('id,title,content,tag_cache,price,img')->where($map)->select();
        $item_tags = M('notify_tag')->distinct(true)->field('tag')->select();
     //    var_dump($items);
      //   var_dump($item_tags);
        foreach ($items as $item) {
          $item['title'] = strip_tags($item['title']);
          $tags=array();
            foreach ($item_tags as $item_tag) {
                     if(stristr($item['title'],$item_tag['tag']) !== FALSE) {
                        array_push($tags,$item_tag['tag']);
                      }
                    }
                    $tag_list = unserialize($item['tag_cache']);
                    $tags = array_merge($tags,$tag_list);
                    if(count($tags)>0){

                      foreach ($tags as $key => $value) {
                       if(stristr($tags[$key],' ') !== FALSE) {
                         $tags[$key] = str_replace(' ', '|',$tags[$key]);
                       }
                       if(stristr($tags[$key],'-') !== FALSE) {
                         $tags[$key] = str_replace('-', '|',$tags[$key]);
                       }
                      }

                //    var_dump($tags);
                //    var_dump($item);
                      $this->baicai_push(strip_tags($item['title']),$tags,$item['id'],mb_substr(strip_tags($item['content']),0,40,"utf-8"),$item['price'],$item['img']);
                      $h_tags=array();
                      foreach ($tags as $tag) {
                       array_push($h_tags,$tag . "_" . date("H",time()));
                      }
                //       var_dump($h_tags);
                       $this->baicai_push_h(strip_tags($item['title']),$h_tags,$item['id'],mb_substr(strip_tags($item['content']),0,40,"utf-8"),$item['price']);
                      $silent_h_tags=array();
                      foreach ($tags as $tag) {
                       array_push($silent_h_tags,$tag . "_silent_" . date("H",time()));
                      }
                //       var_dump($silent_h_tags);
                       $this->baicai_push_silent(strip_tags($item['title']),$silent_h_tags,$item['id'],mb_substr(strip_tags($item['content']),0,40,"utf-8"),$item['price']);
                    }
        } 
    }

  public function baicai_push($title,$tags,$id,$content,$price,$img) {
       $app_key="7c7de5f8d6948b005eb91a50";
       $master_secret="bb7352129924918f7d53f144";
       $client = new JPush($app_key, $master_secret);
        $pusher = $client->push();
        $pusher->setPlatform('all')->options(['apns_production'=>true]);
      //  $pusher->addAllAudience();
        $pusher->addTag($tags);
        $pusher->setNotificationAlert($title) ->iosNotification($title . " " . $price, [
      'sound' => 'default',
      'badge' => '0',
      'extras' => [
        'type' => 'shop',
        'data' => $id
      ]
    ])->androidNotification($price, [
      'title' => $title,
      'build_id' => 1,
      'style' => 3,
      "big_pic_path"=>$img,
      'content_type' => 'text',
      'extras' => [
         'type' => 'shop',
        'data' =>$id
      ]
    ]);
        try {
    $pusher->send();
} catch (\JPush\Exceptions\APIConnectionException $e) {
    // try something here
    print $e;
} catch (\JPush\Exceptions\APIRequestException $e) {
    // try something here
    print $e;
}
    }

    public function baicai_push_silent($title,$tags,$id,$content,$price) {
       $app_key="7c7de5f8d6948b005eb91a50";
       $master_secret="bb7352129924918f7d53f144";
       $client = new JPush($app_key, $master_secret);
        $pusher = $client->push();
        $pusher->setPlatform('all')->options(['apns_production'=>true]);
      //  $pusher->addAllAudience();
        $pusher->addTag($tags);
        $pusher->setNotificationAlert($title) ->iosNotification($title . " " . $price, [
      'sound' => 'wusheng.mp3',
      'badge' => '0',
      'extras' => [
        'type' => 'shop',
        'data' => $id
      ]
    ]);
        try {
    $pusher->send();
} catch (\JPush\Exceptions\APIConnectionException $e) {
    // try something here
    print $e;
} catch (\JPush\Exceptions\APIRequestException $e) {
    // try something here
    print $e;
}
    }

    public function baicai_push_h($title,$tags,$id,$content,$price) {
       $app_key="7c7de5f8d6948b005eb91a50";
       $master_secret="bb7352129924918f7d53f144";
       $client = new JPush($app_key, $master_secret);
        $pusher = $client->push();
        $pusher->setPlatform('all')->options(['apns_production'=>true]);
      //  $pusher->addAllAudience();
        $pusher->addTag($tags);
        $pusher->setNotificationAlert($title) ->iosNotification($title . " " . $price, [
      'sound' => 'default',
      'badge' => '0',
      'extras' => [
        'type' => 'shop',
        'data' => $id
      ]
    ]);
        try {
    $pusher->send();
} catch (\JPush\Exceptions\APIConnectionException $e) {
    // try something here
    print $e;
} catch (\JPush\Exceptions\APIRequestException $e) {
    // try something here
    print $e;
}
    }

   
}
