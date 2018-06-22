<?php
namespace Api\Controller;
use Think\Controller;
use Think\Cache;
class IndexController extends PublicController {
	//***************************
	//  首页数据接口
	//***************************
    public function index(){
    	//如果缓存首页没有数据，那么就读取数据库
    	/***********获取首页顶部轮播图************/
        $redis = $this->redis_();
        $gg = M('guanggao');
        if(empty($redis->get('index_ggtop'))){//判断缓存是否有
            $ggtop=$gg->order('sort desc,id asc')->field('id,name,photo')->select();
            foreach ($ggtop as $k => $v) {
                $ggtop[$k]['photo']=__DATAURL__.$v['photo'];
                $ggtop[$k]['name']=urlencode($v['name']);
            }
            $redis->set('index_ggtop',json_encode($ggtop));
            $redis->expire('index_ggtop',60*10);
        }
        
            
    	/***********获取首页顶部轮播图 end************/
        
        if(empty($redis->get('index_cate_l')) || empty($redis->get('index_list'))){
            $list = M('category')->where('tid=1')->field('id,tid,name')->order('id')->select();
            $category = M('category')->field('id,tid,name,bz_1')->where('bz_2=1')->limit(7)->select();
            foreach ($category as $key => $value) {
                foreach ($list as $k_l => $v) {
                    if($value['tid'] == $v['id']){
                        $value['index'] = $k_l;
                    }
                }
                $cate_l[$key]['index'] = $value['index'];
                $cate_l[$key]['iconUrl'] = __DATAURL__.$value['bz_1'];
                $cate_l[$key]['id']      =  $value['id'];
                $cate_l[$key]['iconText']      =  $value['name'];
            }
            $cate_l[] =array(
                'index'=>-1,
                'iconUrl'=>'/images/fenlei/all.jpg',
                'iconText'=>'全部宝贝'
               );
            $redis->set('index_cate_l',json_encode($cate_l));
            $redis->set('index_list',json_encode($list));
            $redis->expire('index_cate_l',60*6);
            $redis->expire('index_list',60*6);
        }
        
    	//======================
    	//首页推荐产品
    	//======================
        if(empty($redis->get('index_pro_list'))){
            $pro_list = M('product')->where('del=0 AND pro_type=1 AND is_down=0 AND type=1')->order('sort desc,id desc')->field('id,name,intro,photo_x,shiyong,renqi')->limit(4)->select();
            foreach ($pro_list as $k => $v) {
                $pro_list[$k]['photo_x'] = __DATAURL__.$v['photo_x'];
            }
            $redis->set('index_pro_list',json_encode($pro_list));
            $redis->expire('index_pro_list',60);
        }

        //********************
        //
        //商品总数
        //
        $all = M('product')->where('del=0 AND is_down=0')->count();
        $all = json_encode($all);


        $pro_list = $redis->get('index_pro_list');
        $cate_l = $redis->get('index_cate_l');
        $list = $redis->get('index_list');
        $ggtop = $redis->get('index_ggtop');
        // json_encode(array('ggtop'=>$ggtop,'prolist'=>$pro_list,'cate_l'=>$cate_l,'list'=>$list,'all_num'=>$all));
        $res ='{'.'"ggtop":'.$ggtop.','.'"cate_l":'.$cate_l.','.'"prolist":'.$pro_list.','.'"all_num":'.$all.','.'"list":'.$list.'}';
    	//$res =  json_encode(array('cate_l'=>$cate_l,'list'=>$list));
        echo $res;
    	exit();
    }

    //***************************
    //  首页产品 分页
    //***************************
    public function getlist(){
        $page = intval($_REQUEST['page']);
        // //var_dump($page);exit;
        // $page=1;
        $limit = intval($page*8)-8;

        $pro_list = M('product')->where('del=0 AND pro_type=1 AND is_down=0 AND type=1')->order('sort desc,id desc')->field('id,name,photo_x,price_yh,shiyong')->limit($limit.',8')->select();
        foreach ($pro_list as $k => $v) {
            $pro_list[$k]['photo_x'] = __DATAURL__.$v['photo_x'];
        }

        echo json_encode(array('prolist'=>$pro_list));
        exit();
    }

}