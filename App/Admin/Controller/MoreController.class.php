<?php
// |++++++++++++++++++++++++++++++++++++++++
// |-综合管理
// |---单页管理(lr_web)
// |---用户反馈(lr_fankui)
// |---首页设置
// |------首页banner(lr_guanggao)
// |------新闻栏目设置(lr_config)
// |------推荐分类(lr_category)
// |------推荐产品(lr_product)
// |------推荐商家(lr_shangchang)
// |---城市管理(lr_china_city)
// |+++++++++++++++++++++++++++++++++++++++++
namespace Admin\Controller;
use Think\Controller;
class MoreController extends PublicController{
	//*************************
	//单页设置
	//*************************
	public function pweb_gl(){
		//获取web表的数据进行输出
		$model=M('web');
		$list=$model->select();
		//dump($list);exit;
		//=================
		//将变量进行输出
		//=================
		$this->assign('list',$list);	
		$this->display();
	}

	//*************************
	//单页设置修改
	//*************************
	public function pweb(){
		if(IS_POST){
			if(intval($_POST['id'])){
				$data = array();
				$data['concent'] = $_POST['concent'];
				$data['sort'] = intval($_POST['sort']);
				$data['addtime'] = time();
				$up = M('web')->where('id='.intval($_POST['id']))->save($data);
				if ($up) {
					$this->success('保存成功！');
					exit();
				}else{
					$this->error('操作失败！');
					exit();
				}

			}else{
				$this->error('系统错误！');
				exit();
			}
		}else{
			$this->assign('datas',M('web')->where(M('web')->getPk().'='.I('get.id'))->find());
			$this->display();
		}
	}

	//*************************
	// 首页图标 设置
	//*************************
	public function indeximg(){
		$list = M('indeximg')->where('1=1')->select();

		$this->assign('list',$list);
		$this->display();
	}

	//*************************
	// 首页图标 设置
	//*************************
	public function addimg(){
		$info = M('indeximg')->where('id='.intval($_REQUEST['id']))->find();

		//获取所有二级分类
		$procat = M('category')->where('tid=1')->field('id,name')->select();

		$this->assign('info',$info);
		$this->assign('procat',$procat);
		$this->display();
	}

	//*************************
	// 首页图标 设置
	//*************************
	public function saveimg(){
		$id = intval($_REQUEST['id']);
		if (!$id) {
			$this->error('参数错误');
			exit();
		}

		$data = array();
		//上传产品分类缩略图
		if (!empty($_FILES["file"]["tmp_name"])) {
			//文件上传
			$info = $this->upload_images($_FILES["file"],array('jpg','png','jpeg'),"category/indeximg");
			if(!is_array($info)) {// 上传错误提示错误信息
				$this->error($info);
				exit();
			}else{// 上传成功 获取上传文件信息
				$data['photo'] = 'UploadFiles/'.$info['savepath'].$info['savename'];
				$xt = M('indeximg')->where('id='.intval($id))->field('photo')->find();
				if (intval($id) && $xt['photo']) {
					$img_url = "Data/".$xt['photo'];
					if(file_exists($img_url)) {
						@unlink($img_url);
					}
				}
			}
		}

		$res = M('indeximg')->where('id='.intval($id))->save($data);
		if ($res) {
			$this->success('保存成功！','indeximg');
			exit();
		}else{
			$this->error('操作失败！');
			exit();
		}
	}
	//*************************
	// 小程序配置 设置页面
	//*************************
	public function setup(){
		if(IS_POST){
			//构建数组
			M('program')->create();
			//上传产品分类缩略图
			if (!empty($_FILES["file2"]["tmp_name"])) {
				//文件上传
				$info2 = $this->upload_images($_FILES["file2"],array('jpg','png','jpeg'),"logo");
			    if(!is_array($info2)) {// 上传错误提示错误信息
			        $this->error($info2);
			    }else{// 上传成功 获取上传文件信息
				    M('program')->logo = 'UploadFiles/'.$info2['savepath'].$info2['savename'];
			    }
			}
			M('program')->uptime=time();

			$check = M('program')->where('id=1')->getField('id');
			if (intval($check)) {
				$up = M('program')->where('id=1')->save();
			}else{
				M('program')->id=1;
				$up = M('program')->add();
			}

			if ($up) {
				$this->success('保存成功！');
				exit();
			}else {
				$this->error('操作失败！');
				exit();
			}
			
		}else{
			$this->assign('info',M('program')->where('id=1')->find());
			$this->display();
		}

	}

}