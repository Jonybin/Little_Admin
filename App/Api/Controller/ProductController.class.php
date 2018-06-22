<?php
// 本类由系统自动生成，仅供测试用途
namespace Api\Controller;
use Think\Controller;
class ProductController extends PublicController {
	//***************************
	//  获取商品详情信息接口
	//***************************
    public function index(){
		$product=M("product");
		$pro_id = intval($_REQUEST['pro_id']);
		if (!$pro_id) {
			echo json_encode(array('status'=>0,'err'=>'商品不存在或已下架！'));
			exit();
		}
		$redis= $this->redis_();
		$proid_cache = 'product'.$pro_id;
		if(empty($redis->get($proid_cache))){//判断对应商品是否存在缓存中

			$pro = $product->where('id='.intval($pro_id).' AND del=0 AND is_down=0')->find();
			if(!$pro){
				echo json_encode(array('status'=>0,'err'=>'商品不存在或已下架！'.__LINE__));exit();
				}
			$pro['addtime'] = date('Y-m-d H:i:s',$pro['addtime']);
			$pro['photo_x'] =__DATAURL__.$pro['photo_x'];
			$pro['photo_d'] = __DATAURL__.$pro['photo_d'];
			$pro['brand'] = M('brand')->where('id='.intval($pro['brand_id']))->getField('name');
			$pro['cat_name'] = M('category')->where('id='.intval($pro['cid']))->getField('name');
			//图片轮播数组
			$img=explode(',',trim($pro['photo_string'],','));
			$b=array();
			if ($pro['photo_string']) {
				foreach ($img as $k => $v) {
					$b[] = __DATAURL__.$v;
				}
			}else{
				$b[] = $pro['photo_d'];
			}
			$pro['img_arr']=$b;//图片轮播数组

			$content = str_replace('/Data/', __DATAURL__, $pro['content']);
			$pro['content']=html_entity_decode($content, ENT_QUOTES , 'utf-8');

			//检测产品是否收藏
			$col = M('product_sc')->where('uid='.intval($_REQUEST['uid']).' AND pid='.intval($pro_id))->getField('id');
			if ($col) {
				$pro['collect']= 1;
			}else{
				$pro['collect']= 0;
			}
			$redis->set($proid_cache,json_encode($pro));
			$redis->expire($proid_cache,60*60);
		}
		$pro = $redis->get($proid_cache);
		$pro = '{'.'"status":1,'.'"pro":'.$pro.'}';
		echo $pro;
				// echo json_encode(array('status'=>1,'pro'=>$pro));
		exit();

	}

	//***************************
	//  获取商品详情接口
	//***************************
	public function details(){
		header('Content-type:text/html; Charset=utf8');
		$pro_id = intval($_REQUEST['pro_id']);
		$pro = M('product')->where('id='.intval($pro_id).' AND del=0 AND is_down=0')->find();
		if(!$pro){
			echo json_encode(array('status'=>0,'err'=>'商品不存在或已下架！'));
			exit();
		}
		//$content = preg_replace("/width:.+?[\d]+px;/",'',$pro['content']);
		$content = htmlspecialchars_decode($pro['content']);
		echo json_encode(array('status'=>1,'content'=>$content));
		exit();
	}


	//*************************
	//下载量统计接口
	//
	public function dow_num(){
		$redis= $this->redis_();
		$id = intval($_REQUEST['pid']);
		$proid_cache = 'product'.$id;
		$dow_num = intval($_REQUEST['num']);
		if(empty($redis->get($proid_cache))){
			$info = M('product')->where('id='.$id.' AND del=0 AND is_down=0')->find();
			if(empty($info)){
				echo json_encode(array('status'=>0,'err'=>'该商品不存在'));exit;
			}
		}else{
			$info = json_decode($redis->get($proid_cache),true);
		}
		
		$arr = array(
			'id'=>$id,
			'shiyong'=>$dow_num+1,
			'num'=>$info['num']-1
			);
		$res = M('product')->save($arr);
		if(!$res){
			echo json_encode(array('status'=>0,'err'=>'操作失败'));exit;
		}
		echo json_encode(array('status'=>1,'dow_num'=>$dow_num+1));exit;

	}
	///**************************
	///
	///访问量的统计接口
	public function visit_num(){
		$redis= $this->redis_();
		$id = intval($_REQUEST['pid']);
		$proid_cache = 'product'.$id;
		$visit_num = intval($_REQUEST['visit']);
		if(empty($redis->get($proid_cache))){
			$info = M('product')->where('id='.$id)->find();
			if(empty($info)){
				echo json_encode(array('status'=>0,'err'=>'该商品不存在'));exit;
			}
		}
		$arr = array(
			'id'=>$id,
			'renqi'=>$visit_num+1,
			);
		$res = M('product')->save($arr);
		if(!$res){
			echo json_encode(array('status'=>0,'err'=>'操作失败'));exit;
		}
		echo json_encode(array('status'=>1,'visit'=>$visit_num+1));exit;

	}

	//***************************
	//  获取商品列表接口
	//***************************
   	public function lists(){
 		$json="";
 		$id=intval($_POST['cat_id']);//获得分类id 这里的id是pro表里的cid
 		$brand_id = intval($_POST['brand_id']);
 		// $id=44;
 		$type=I('post.type');//排序类型

 		$page= intval($_POST['page']) ? intval($_POST['page']) : 1;
 		$all_p = intval($page*4)-4;
 		$keyword=I('post.keyword');
 		//排序
 		$order="addtime desc";//默认按添加时间排序
 		if($type=='ids'){
 			$order="id desc";
 		}elseif($type=='sale'){
 			$order="shiyong desc";
 		}elseif($type=='price'){
 			$order="price_yh desc";
 		}elseif($type=='hot'){
 			$order="renqi desc";
 		}
 		//条件
 		$where="1=1 AND pro_type=1 AND del=0 AND is_down=0";
 		if(intval($id)){
 			$where.=' AND cid LIKE "%'.intval($id).'%"';//分类id
 		}
 		if (intval($brand_id)) {
 			$where.=" AND brand_id=".intval($brand_id);
 		}
 		if($keyword) {
            $where.=' AND name LIKE "%'.$keyword.'%"';
        }
        if (isset($_REQUEST['ptype']) && $_REQUEST['ptype']=='new') {
        	$where .=' AND is_show=1'; 
        }
        if (isset($_REQUEST['ptype']) && $_REQUEST['ptype']=='hot') {
        	$where .=' AND is_hot=1'; 
        }
        if (isset($_REQUEST['ptype']) && $_REQUEST['ptype']=='zk') {
        	$where .=' AND is_sale=1'; 
        }

 		$product=M('product')->where($where)->order($order)->limit($all_p.',4')->select();
 		// $product = M('product')->select();
 		// echo M('product')->_sql();exit;
 		$json = array();$json_arr = array();
 		foreach ($product as $k => $v) {
 			$json['renqi']=$v['renqi'];
 			$json['id']=$v['id'];
 			$json['name']=$v['name'];
 			$json['photo_x']=__DATAURL__.$v['photo_x'];
 			$json['price']=$v['price'];
 			$json['price_yh']=$v['price_yh'];
 			$json['shiyong']=$v['shiyong'];
 			$json['intro']=$v['intro'];
 			$json_arr[] = $json;
 		}
 		$cat_name=M('category')->where("id=".intval($id))->getField('name');
 		echo json_encode(array('status'=>1,'pro'=>$json_arr,'cat_name'=>$cat_name));
 		exit();
    }

    //*******************************
	//  商品列表页面 获取更多接口
	//*******************************
    public function get_more(){
 		$json="";
 		$id=intval($_POST['cat_id']);//获得分类id 这里的id是pro表里的cid
 		// $id=44;
 		$type=I('post.type');//排序类型

 		$page= intval($_POST['page']);
 		if (!$page) {
 			$page=1;
 		}
 		$limit = intval($page*8)-8;

 		$keyword=I('post.keyword');
 		//排序
 		$order="addtime desc";//默认按添加时间排序
 		if($type=='ids'){
 			$order="id desc";
 		}elseif($type=='sale'){
 			$order="shiyong desc";
 		}elseif($type=='price'){
 			$order="price_yh desc";
 		}elseif($type=='hot'){
 			$order="renqi desc";
 		}
 		//条件
 		$where="1=1 AND pro_type=1 AND del=0 AND is_down=0";
 		if(intval($id)){
 			$where.=" AND cid=".intval($id);
 		}

 		if($keyword) {
            $where.=' AND name LIKE "%'.$keyword.'%"';
        }
        if (isset($_REQUEST['ptype']) && $_REQUEST['ptype']=='new') {
        	$where .=' AND is_show=1'; 
        }
        if (isset($_REQUEST['ptype']) && $_REQUEST['ptype']=='hot') {
        	$where .=' AND is_hot=1'; 
        }
        if (isset($_REQUEST['ptype']) && $_REQUEST['ptype']=='zk') {
        	$where .=' AND is_sale=1'; 
        }

 		$product=M('product')->where($where)->order($order)->limit($limit.',8')->select();
 		//echo M('product')->_sql();exit;
 		$json = array();$json_arr = array();
 		foreach ($product as $k => $v) {
 			$json['id']=$v['id'];
 			$json['name']=$v['name'];
 			$json['photo_x']=__DATAURL__.$v['photo_x'];
 			$json['price']=$v['price'];
 			$json['price_yh']=$v['price_yh'];
 			$json['shiyong']=$v['shiyong'];
 			$json['intro']=$v['intro'];
 			$json_arr[] = $json;
 		}
 		$cat_name=M('category')->where("id=".intval($id))->getField('name');
 		echo json_encode(array('pro'=>$json_arr,'cat_name'=>$cat_name));
 		exit();
    }
	//***************************
	//  会员商品收藏接口
	//***************************
	public function col(){
		$uid = intval($_REQUEST['uid']);
		$pid = intval($_REQUEST['pid']);
		if (!$uid || !$pid) {
			echo json_encode(array('status'=>0,'err'=>'请先登录.'));
			exit();
		}

		$check = M('product_sc')->where('uid='.intval($uid).' AND pid='.intval($pid).' AND status=1')->getField('id');
		if ($check) {
			$res = M('product_sc')->where('id='.intval($check))->delete();
			echo json_encode(array('status'=>0,'err'=>'取消收藏'));
			exit();
		}else{
			$data = array();
			$data['uid'] = intval($uid);
			$data['pid'] = intval($pid);
			$res = M('product_sc')->add($data);
			echo json_encode(array('status'=>1,'err'=>'已收藏'));
			exit();
		}
	}
	public function check_col(){
		$uid = intval($_REQUEST['uid']);
		$pid = intval($_REQUEST['pid']);
		$arr['pid']=$pid;
		$arr['uid']=$uid;
		$arr['status']=1;
		if(!$uid || !$pid){
			echo json_encode(array('status'=>0));
			exit();
		}
		$res = M('product_sc')->where($arr)->find();
		if($res){
			echo json_encode(array('status'=>1));exit;
		}else{
			echo json_encode(array('status'=>0));exit;
		}
	}

	//***************************
	//  删除收藏
	//***************************
	public function remove(){
		$uid = intval($_REQUEST['uid']);
		$pid = intval($_REQUEST['pid']);
		
		if (!$uid || !$pid) {
			echo json_encode(array('status'=>0,'err'=>'请先登录.'));
			exit();
		}
		$arr=array(
			'uid'=> $uid,
			'pid'=> $pid,
			// 'status'=> 0
			);

		$res = M('product_sc')->where('uid='.$uid.' AND pid='.$pid)->delete();
		if($res){
			echo json_encode(array('status'=>1));exit;
		}else{
			echo json_encode(array('status'=>0,'err'=>'操作失败'));exit;
		}
	}
	

}