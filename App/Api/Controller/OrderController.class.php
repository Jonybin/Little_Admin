<?php
// 本类由系统自动生成，仅供测试用途
namespace Api\Controller;
use Think\Controller;
class OrderController extends PublicController {
	//***************************
	//  用户获取订单信息接口
	//***************************
	public function index(){
		$uid = intval($_REQUEST['uid']);
		if (!$uid) {
			echo json_encode(array('status'=>0,'err'=>'登录状态异常'));
			exit();
		}
        $info = M('order_product')->where('order_id='.$uid)->select();
        foreach ($info as $key => $value) {
            $info[$key]['photo_x']=__DATAURL__.$value['photo_x'];
            $info[$key]['date']=date('Y-m-d',$value['addtime']);
        }
        echo json_encode(array('status'=>1,'list'=>$info));
		//分页
		

	}

    //-******************
    //
    //下载量的记录1
    //
    public function recoder(){
        $uid = intval($_REQUEST['uid']);
        $pid = intval($_REQUEST['pid']);
        $mon = array(
            'pid'=>$pid,
            'order_id'=>$uid,
            );
        $info = M('order_product')->where($mon)->find();//检测是否已经下载过改商品。

        if(!empty($info)){
            $arr = array(
                'num'=>$info['num']+1,
                );
            $res = M('order_product')->where($mon)->save($arr);
        }else{
            $mess = M('product')->where('id='.$pid)->find();
            $array=array(
                'pid'=>$pid,
                'order_id'=>$uid,
                'name'=>$mess['name'],
                'photo_x'=>$mess['photo_x'],
                'addtime'=>time(),
                'num'=>1,
                );
            $res = M('order_product')->add($array);
        }
        if($res){
            echo json_encode(array('status'=>1));exit;
        
        }else{
            echo json_encode(array('status'=>0,'err'=>'记录操作失败'));exit;
        }

    }
    

}