<?php
/* 
* @Author: anchen
* @Date:   2018-04-30 17:05:02
* @Last Modified by:   anchen
* @Last Modified time: 2018-04-30 21:01:17
*/
namespace Admin\Controller;
use Think\Controller;
class NewsController extends PublicController{


    public function index(){
        $where=array();
        define('rows',5);
        $count=M('zixun')->where($where)->count();
        $rows=ceil($count/rows);
        $page=(int)$_GET['page'];
        $page<0?$page=0:'';
        $limit=$page*rows;
        $page_index=$this->page_index($count,$rows,$page);
        $list=M('zixun')->where($where)->where('del=0')->order('addtime desc')->limit($limit,rows)->select();
        $this->assign('list',$list);
        $this->assign('page_index',$page_index);
        $this->display();
    }
    public function add(){
        $id = intval($_REQUEST['id']);
        if($_POST['submit']){
            try{
                $array= array(
                    'name'=>$_POST['name'] ,
                    'content'=>$_POST['content'],

                );
                //判断产品详情页图片是否有设置宽度，去掉重复的100%
                if(strpos($array['content'],'width="100%"')){
                    $array['content']=str_replace(' width="100%"','',$array['content']);
                }
                //为img标签添加一个width
                $array['content']=str_replace('alt=""','alt="" width="100%"',$array['content']);
                //上传产品小图
                if (!empty($_FILES["photo_x"]["tmp_name"])) {
                        //文件上传
                        $info = $this->upload_images($_FILES["photo_x"],array('jpg','png','jpeg'),"zixun/".date(Ymd));
                        if(!is_array($info)) {// 上传错误提示错误信息
                            $this->error($info);
                            exit();
                        }else{// 上传成功 获取上传文件信息
                            $array['photo_x'] = 'UploadFiles/'.$info['savepath'].$info['savename'];//这里显示数
                        }
                }
                if(is_array($array)){

                    if(!empty($id)){
                        $sql = M('zixun')->where('id='."$id")->save($array);
                    }
                    else{
                        $array['addtime']=time();
                        $sql = M('zixun')->add($array);
                    }
                    
                }else{
                    $this->error('上传数据异常');
                    exit();
                }

                if($sql){//name="guige_name[]
                    $this->success('操作成功.','index');
                    exit();
                }else{
                    throw new \Exception('操作失败.');
                }
              

            }
            catch(\Exception $e){
                echo "<script>alert('".$e->getMessage()."');location='{:U('index')}';</script>";
            }

        }

        $zx_allinfo= $id>0 ? M('zixun')->where('id='.$id)->find() : "";
        $this->assign('zx_allinfo',$zx_allinfo);
        $this->display();
    }

    public function del(){
        $id = intval($_REQUEST['did']);
        if(empty($id)){
            $this->error('信息错误.'.__LINE__);
            exit();
        }
        $info = M('zixun')->where('id='."$id")->find('del');
        if($info['del']==1){
            $this->success('删除成功');
        }
        $array['del'] = 1;
        $array['del_time'] = time();
        $res = M('zixun')->where('id='."$id")->save($array);
        if($res){
            $this->redirect('index',array('page'=>intval($_REQUEST['page'])));
            exit();
        }else{
            $this->error('删除失败');
            exit;
        }

    }
}