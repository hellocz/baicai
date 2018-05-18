<?php
return array(
    'URL_MODEL' => 2,
    'URL_HTML_SUFFIX' => '',
    'URL_PATHINFO_DEPR' => '-',
    'URL_ROUTER_ON' => true,
    'URL_ROUTE_RULES' =>
        array(



            '/^topics\/$/' => 'book/index',
            '/^topics\/p(\d+)$/' => 'book/index?p=:1',
            '/^topics\/c(\d+)\/$/' => 'book/cate?cid=:1',
			
			//����
			'/^topics\/ghhz\/$/' => 'book/cate?cid=333',  //������ױ 
			'/^topics\/bjys\/$/' => 'book/cate?cid=334',  //��������  
			'/^topics\/tsyx\/$/' => 'book/cate?cid=336',  //ͼ������  
			'/^topics\/smjd\/$/' => 'book/cate?cid=116',  //����ҵ�  
			'/^topics\/lyxx\/$/' => 'book/cate?cid=338',  //��������  
			'/^topics\/rybh\/$/' => 'book/cate?cid=335',  //���ðٻ�  
			'/^topics\/fzxm\/$/' => 'book/cate?cid=1',    //��װЬñ  
			'/^topics\/mywj\/$/' => 'book/cate?cid=115',  //ĸӤ���  
			'/^topics\/dyzx\/$/' => 'book/cate?cid=339',  //��Ӱ��Ѷ  
			'/^topics\/yzps\/$/' => 'book/cate?cid=340',  //�۾�����  
			'/^topics\/xbsd\/$/' => 'book/cate?cid=50',   //����ִ�  
			'/^topics\/ydhw\/$/' => 'book/cate?cid=102',  //�˶�����  
			'/^topics\/zbss\/$/' => 'book/cate?cid=114',  //�ӱ�����  
			'/^topics\/spjy\/$/' => 'book/cate?cid=337',  //ʳƷ����  
			'/^topics\/zqzb\/$/' => 'book/cate?cid=341',  //�����Ӱ�  
		    '/^topics\/notice\/$/' => 'book/cate?cid=342',//�����  
            '/^topics\/finance\/$/' => 'book/cate?cid=349',//�����  
                                '/^topics\/oversea-guide\/$/' =>'article/index?id=9',
			//END
			
            '/^topics\/c(\d+)\/p(\d+)$/' => 'book/cate?cid=:1&p=:2',
            '/^topics\/gny\/(\d+)\/$/' => 'book/gny?tp=:1',
            '/^topics\/gny\/(\d+)\/p(\d+)$/' => 'book/gny?tp=:1&p=:2',
            '/^topics\/gny\/(\d+)\/isbao(\d+)$/' => 'book/gny?tp=:1&isbao=:2',
            '/^topics\/gny\/(\d+)\/isbao(\d+)\/p(\d+)$/' => 'book/gny?tp=:1&isbao=:2&p=:3',
            '/^topics\/gny\/(\d+)\/isnice(\d+)$/' => 'book/gny?tp=:1&isnice=:2',
            '/^topics\/gny\/(\d+)\/isnice(\d+)\/p(\d+)$/' => 'book/gny?tp=:1&isnice=:2&p=:3',
            '/^topics\/gny\/(\d+)\/(\S+)\/(\S+)\/isnice(\d+)$/' => 'book/gny?tp=:1&tab=:2&dss=:3&isnice=:4',
            '/^topics\/gny\/(\d+)\/(\S+)\/(\S+)\/isnice(\d+)\/p(\d+)$/' => 'book/gny?tp=:1&tab=:2&dss=:3&isnice=:4&p=:5',
            '/^topics\/gny\/(\d+)\/(\S+)\/(\S+)\/isbao(\d+)$/' => 'book/gny?tp=:1&tab=:2&dss=:3&isbao=:4',
            '/^topics\/gny\/(\d+)\/(\S+)\/(\S+)\/isbao(\d+)\/p(\d+)$/' => 'book/gny?tp=:1&tab=:2&dss=:3&isbao=:4&p=:5',
            '/^topics\/best$/' => 'book/best',

        //����
           

            '/^article\/(\d+).html$/' => 'article/show?id=:1',
            '/^zr\/(\d+).html$/' => 'zr/show?id=:1',
            '/^zr\/$/' => 'zr/index',
            '/^zr\/p(\d+)$/' => 'zr/index?p=:1',
            '/^zr\/c(\d+)$/' => 'zr/index?id=:1',
            '/^zr\/c(\d+)\/p(\d+)$/' => 'zr/index?id=:1&p=:2',
            '/^item\/(\d+).html$/' => 'item/index?id=:1',
            '/^bao\/(\d+).html$/' => 'bitem/index?isbao=1&id=:1',
            '/^tag\/(\S+)$/' => 'book/index?tag=:1',
            '/^p\/(\d+)\/tag\/(\S+)$/' => 'book/index?p=:1&tag=:2',
            '/^topics\/oversea-guide\/p(\d+)$/' => 'article/index?id=9&p=:1',
            '/^shaidan\/$/' => 'article/index?id=10',
            '/^shaidan\/p(\d+)$/' => 'article/index?id=10&p=:1',
            '/^baicai\/$/' => 'book/baicai',
            '/^baicai\/p(\d+)$/' => 'book/baicai?p=:1',
            '/^ec\/$/' => 'exchange/index',
            '/^ec\/p(\d+)$/' => 'exchange/index?p=:1',
            '/^space$/' => 'space/index',
            '/^space\/(\d+)$/' => 'space/index?uid=:1',
            '/^space\/(\d+)\/(\S+)$/' => 'space/index?uid=:1&t=:2',
            '/^share\/(\S+)$/' => 'ajax/g_share?tg=:1',
            '/^sitemap.html$/' => 'sitemap/index',
            '/^go\/(\S+)$/' => 'go/index?to=:1',
            '/^rss$/' => 'rss/index',
            '/^index-qrcode?url=(\S+)$/' => 'index/qrcode?url=:1',
              


        ),
);
