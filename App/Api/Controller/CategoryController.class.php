<?php
// 本类由系统自动生成，仅供测试用途
namespace Api\Controller;
use Think\Controller;
class CategoryController extends PublicController {
	//***************************
	// 产品分类
	//***************************
    public function index(){
        
            $list = M('category')->where('tid=1')->field('id,tid,name')->order('id')->select();
            $catList = M('category')->where('tid='.intval($list[0]['id']))->field('id,name,bz_1')->select();
            foreach ($catList as $k => $v) {
                $catList[$k]['bz_1'] = __DATAURL__.$v['bz_1'];
            }
    	//json加密输出
		//dump($json);
		echo json_encode(array('status'=>1,'list'=>$list,'catList'=>$catList));
        exit();
    }

    //***************************
    // 产品分类
    //***************************
    public function getcat(){
        $redis = $this->redis_();
        $catid = intval($_REQUEST['cat_id']);
        if (!$catid) {
            echo json_encode(array('status'=>0,'err'=>'没有找到产品数据.'));
            exit();
        }
        $getcat = 'getcat_'.$catid;
        if(empty($redis->get($getcat))){
            $catList = M('category')->where('tid='.intval($catid))->field('id,name,bz_1')->select();
            foreach ($catList as $k => $v) {
                $catList[$k]['bz_1'] = __DATAURL__.$v['bz_1'];
            }
            $redis->set($getcat,json_encode($catList));
            $redis->expire($getcat,60);
         }
        $catList = $redis->get($getcat);
        echo '{'.'"status":1,'.'"catList":'.$catList.'}';exit;
        //json加密输出
        //dump($json);
        // echo json_encode(array('status'=>1,'catList'=>$catList));
        // exit();
    }

}