<?php
namespace Admin\Controller;
use Think\Controller;
class OrderController extends PublicController{

	/*
	*
	* 构造函数，用于导入外部文件和公共方法
	*/
	public function _initialize(){
		$this->order = M('Order');
		$this->order_product = M('Order_product');

		$order_status = array('10'=>'待付款','20'=>'待发货','30'=>'待收货','40'=>'已收货','50'=>'交易完成');
		$this->assign('order_status',$order_status);
	}


	/*
	*
	* 获取、查询所有订单数据
	*/
	public function index(){
		$start_time = intval(strtotime($_REQUEST['start_time'])); //订单状态
		$end_time = intval(strtotime($_REQUEST['end_time'])); //订单状态
		//构建搜索条件
		$condition = array();
		$where = '1=1';
	
		//根据下单时间搜索
		if ($start_time) {
			$condition['addtime'] = array('gt',$start_time);
			$where .=' AND addtime>'.$start_time;
			//搜索内容输出
			$this->assign('start_time',date("Y-m-d",$start_time));
		}
		//根据下单时间搜索
		if ($end_time) {
			$condition['addtime'] = array('lt',$end_time);
			$where .=' AND addtime<'.$end_time;
			//搜索内容输出
			$this->assign('end_time',date("Y-m-d",$end_time));
		}

		//分页
		$count   = $this->order_product->where($where)->count();// 查询满足要求的总记录数
		$Page    = new \Think\Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)

		//分页跳转的时候保证查询条件
		foreach($condition as $key=>$val) {
			$Page->parameter[$key]  =  urlencode($val);
		}
		if ($start_time && $end_time) {
			$addtime = 'addtime>'.$start_time.' AND addtime<'.$end_time;
			$Page->parameter['addtime']  =  urlencode($addtime);
		}

		//头部描述信息，默认值 “共 %TOTAL_ROW% 条记录”
		$Page->setConfig('header', '<li class="rows">共<b>%TOTAL_ROW%</b>条&nbsp;第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页</li>');
		//上一页描述信息
	    $Page->setConfig('prev', '上一页');
	    //下一页描述信息
	    $Page->setConfig('next', '下一页');
	    //首页描述信息
	    $Page->setConfig('first', '首页');
	    //末页描述信息
	    $Page->setConfig('last', '末页');
	    /*
	    * 分页主题描述信息 
	    * %FIRST%  表示第一页的链接显示  
	    * %UP_PAGE%  表示上一页的链接显示   
	    * %LINK_PAGE%  表示分页的链接显示
	    * %DOWN_PAGE% 	表示下一页的链接显示
	    * %END%   表示最后一页的链接显示
	    */
	    $Page->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');

		$show    = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$order_list = $this->order_product->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		foreach ($order_list as $k => $v) {
			$order_list[$k]['photo_x']=__DATA__.'/'.$v['photo_x'];

		}
		//echo $where;
		$this->assign('order_list',$order_list);// 赋值数据集
		$this->assign('page',$show);// 赋值分页输出
		$this->assign('admin_qx',$_SESSION['admininfo']['qx']);//后台用户权限，目前设置为超级管理员权限
		$this->display(); // 输出模板

	}
	
}