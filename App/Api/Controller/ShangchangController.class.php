<?php
// 本类由系统自动生成，仅供测试用途
namespace Api\Controller;
use Think\Controller;
class ShangchangController extends PublicController {

	//***************************
	//  获取所有商场的数据
	//***************************
    public function index(){
    	//查询条件
    	//根据店铺分类id查询
       //获取页面显示条数
        $page = intval($_REQUEST['page']);
        if (!$page) {
            $page = 1;
        }
        $limit = intval($page*6)-6; //
    	$pro_list = M('product')->where('del=0 AND pro_type=1 AND is_down=0 AND type=1 AND is_sale=1')->order('sort desc,id desc')->field('id,name,intro,photo_x,price_yh,price,shiyong,renqi')->limit($limit.',6')->select();
        foreach ($pro_list as $k => $v) {
            $pro_list[$k]['photo_x'] = __DATAURL__.$v['photo_x'];
        }
        echo json_encode(array('status'=>1,'pro'=>$pro_list));exit;
    }

}