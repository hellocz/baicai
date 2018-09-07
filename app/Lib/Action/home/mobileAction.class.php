<?php

class mobileAction extends frontendAction {

    /**
     * mobile
     */
    public function index() {
        $this->_config_seo();
        $this->display();
    }

    public function quan(){
            include_once LIB_PATH . 'Pinlib/taobao/TopSdk.php';
            $c = new TopClient;
            $c->appkey = "23232602";
            $c->secretKey = "xxxxxxxxxxxxxxxxxxxxxxxxxx";
            $req = new TbkDgItemCouponGetRequest;
            $req->setAdzoneId("14718353");
           // $req->setPlatform(1);
            $req->setPageSize(100);
            $req->setQ("女装");
            $req->setPageNo(1);
            $resp = $c->execute($req);
            var_dump($resp);
    } 
}
