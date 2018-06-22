<?php
namespace Api\Controller;
use Think\Controller;
class SearchController extends PublicController {
	//***************************
	//  获取会员 搜索记录接口
	//***************************
    public function index(){
    	$uid = intval($_REQUEST['uid']);
        $redis = $this->redis_();
    	//获取热门搜索内容
        if(empty($redis->get('hot_sear'))){
            $remen = M('search_record')->group('keyword')->field('keyword')->order('SUM(num) desc')->limit(10)->select();
            $redis->set('hot_sear',json_encode($remen));
            $redis->expire('hot_sear',60*30);
        }
        
        //获取历史搜索记录
        $history = [];
        if ($uid) {
            $history = 'history_'.$uid;
            if(empty($redis->get($history))){
                $history = M('search_record')->where('uid='.intval($uid))->order('addtime desc')->field('keyword')->limit(20)->select();
                $redis->set($history,json_encode($history));
                $redis->expire($history,60);
            }
            $history = $redis->get($history);
            
        }
        $remen = $redis->get('hot_sear');
        echo '{'.'"remen":'.$remen.','.'"history":'.$history.'}';
        // echo json_encode(array('remen'=>$remen,'history'=>$history));
        exit();
    }

    //***************************
    //  产品商家搜索接口
    //***************************
    public function searches(){
        $uid = intval($_REQUEST['uid']);

        $keyword = trim($_REQUEST['keyword']);
        if (!$keyword) {
            echo json_encode(array('status'=>0,'err'=>'请输入搜索内容.'));
            exit();
        }

        if ($uid) {
            $check = M('search_record')->where('uid='.intval($uid).' AND keyword="'.$keyword.'"')->find();
            if ($check) {
               $num = intval($check['num'])+1;
               M('search_record')->where('id='.intval($check['id']))->save(array('num'=>$num));
            }else{
               $add = array();
               $add['uid'] = $uid;
               $add['keyword'] = $keyword;
               $add['addtime'] = time();
               M('search_record')->add($add);
            }
        }
        $page=intval($_REQUEST['page']);
        if (!$page) {
            $page=0;
        }
        $redis = $this->redis_();
        if(empty($redis->get('search_product'))){
            $prolist = M('product')->where('del=0 AND pro_type=1 AND is_down=0 AND name LIKE "%'.$keyword.'%"')->order('addtime desc')->field('id,name,photo_x,shiyong,price,price_yh')->select();
            foreach ($prolist as $k => $v) {
                $prolist[$k]['photo_x'] = __DATAURL__.$v['photo_x'];
            }
            $redis->set('search_product',json_encode($prolist));
            $redis->expire('search_product',60*10);
        }
        $prolist = $redis->get('search_product');

        echo '{'.'"status":1,'.'"pro":'.$prolist.'}';exit;
        //获取所有的商家数据
    }


}