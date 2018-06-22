<?php
namespace Admin\Controller;
use Think\Controller;
/* 
* @Author: 徐彬
* @Date:   2018-04-28 20:38:05
* @Last Modified by:   anchen
* @Last Modified time: 2018-04-28 20:51:27
*/
class ShowfrontController extends PublicController{

    public function add()
    {
        //=========================
        // 查询所有一级产品分类
        //=========================
        $cate_list = M('category')->where('tid=1')->field('id,name')->select();
        $this->assign('cate_list',$cate_list);

        $this->display();
    }

    public function getcid(){
        $cateid = intval($_REQUEST['cateid']);
        $catelist = M('category')->where('tid='.intval($cateid))->field('id,name')->select();
        echo json_encode(array('catelist'=>$catelist));
        exit();
    }
}
?>
