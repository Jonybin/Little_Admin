<?php
// 本类由系统自动生成，仅供测试用途
namespace Api\Controller;
use Think\Controller;
class ShoppingController extends PublicController {

	//***************************
	//  咨讯列表接口
	//***************************
	public function index(){
		$qz=C('DB_PREFIX');
        $zixun=M("zixun");
		$cart = $zixun->where('del=0')->select();
        foreach ($cart as $k => $v) {
        	$cart[$k]['photo_x']=__DATAURL__.$v['photo_x'];
        	$cart[$k]['time'] = date('Y年m月d',$v['addtime']);
        }

		echo json_encode(array('status'=>1,'cart'=>$cart));
		exit();
    }
    //咨讯的详情页和总列1
    public function detail(){
    	$id = intval($_REQUEST['id']);
    	if(!$id){
    		echo json_encode(array('status'=>0,'err'=>'参数错误'));
    		exit;
    	}
    	$arr = array(
    			'del'=>0,
    			'id'=>$id
    		);
    	$info = M('zixun')->where($arr)->find();
    	$content = str_replace('/Data/', __DATAURL__, $info['content']);
		$info['content']=html_entity_decode($content, ENT_QUOTES , 'utf-8');
		if(!$info){
			echo json_encode(array('status'=>0,'err'=>'咨讯不存在，或被删除'));exit;
		}
		$lim = array('del'=>0);
		$cart = M('zixun')->where($lim)->select();
		foreach ($cart as $k => $v) {
        	$cart[$k]['photo_x']=__DATAURL__.$v['photo_x'];
        	$cart[$k]['time'] = date('Y年m月d',$v['addtime']);
        }
		echo json_encode(array('status'=>1,'info'=>$info,'carts'=>$cart));exit;

    }
    /*
       去除HTNL标签
    */
    public function html_entity($array){
    	foreach ($array as $key => $value) {
        	$array[$key]['content'] = strip_tags(html_entity_decode($value['content']));
        }
        return $array;
    }

}