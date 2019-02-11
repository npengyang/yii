<?php

namespace backend\controllers;
use common\models\User;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\widgets\LinkPager;

class AdminController extends BaseController
{
    public function actionIndex()
    {
        $keyword = Yii::$app->request->post('keyword','') ? Yii::$app->request->post('keyword','') : Yii::$app->request->get('keyword','');
        $data = User::find()->where(['isdel'=>0])
        ->filterWhere(['or',['like','username',"$keyword"],['like','email',"$keyword"]])
            ->orderBy(['role'=>SORT_ASC,'created_at'=>SORT_DESC]);
        $pages = new Pagination(['totalCount' => $data->count(), 'pageSize' => 10]);
        $_GET['keyword'] = $keyword;
        $result = $data->offset($pages->offset)->limit($pages->limit)->asArray()->all();
        $this->assign([
            "result" => $result,
            'pages' => LinkPager::widget(['pagination' => $pages]),
            'num' => (($pages->getPage() ? $pages->getPage() : 1) - 1 ) * $pages->getPageSize() + 1,
            'keyword' => $keyword,
        ]);
        return $this->display('index.html');
    }

    public function actionAdd()
    {
        $post = Yii::$app->request->post();
        if($post){
            $data = new User();
            if(empty($post['password'])){
                ajaxReturn(0,'密码不能为空');
            }
            if(empty($post['qrPwd'])){
                ajaxReturn(0,'确认密码不能为空');
            }
            if($post['password'] !== $post['qrPwd']){
                ajaxReturn(0,'俩次输入密码不一致');
            }
            $data->username = $post['username'];
            $data->setPassword($post['password']);
            $data->email = $post['email'];
            $data->generateAuthKey();
            $begin = $data->getDb()->beginTransaction();//开启事物
            try{
                if(!$data->validate()){
                    foreach ($data->getFirstErrors() as $key => $e){
                        ajaxReturn(0,$e);
                    }
                }
                $data->save();
                if($data->primaryKey){//保存成功后续添加角色
                    $admin = Yii::$app->authManager->createRole('admin_'.$data->id);//创建角色
                    Yii::$app->authManager->add($admin);
                    Yii::$app->authManager->assign($admin,$data->id);//为用户分配角色
                }
                $begin->commit();
                ajaxReturn(1,'添加成功');
            }catch (Exception $e){
                $begin->rollBack();
                ajaxReturn(0,'添加失败');
            }
        }
        return $this->display('add.html');
    }

    /**修改
     * @return string
     */
    public function actionUp()
    {
        $id = Yii::$app->request->get('id',0);
        $user = User::findOne($id);
        if(Yii::$app->request->isPost){
            $post = Yii::$app->request->post();
            if(!empty($post['status'])){
                if(empty($post['password'])){
                    ajaxReturn(0,'密码不能为空');
                }
                if(empty($post['qrPwd'])){
                    ajaxReturn(0,'确认密码不能为空');
                }
                if($post['password'] !== $post['qrPwd']){
                    ajaxReturn(0,'俩次输入密码不一致');
                }
                $user->setPassword($post['password']);
                $user->generateAuthKey();
            }
            $user->username = $post['username'];
            $user->email = $post['email'];
            $begin = $user->getDb()->beginTransaction();//开启事物
            try{
                if(!$user->validate()){
                    foreach ($user->getFirstErrors() as $key => $e){
                        ajaxReturn(0,$e);
                    }
                }
                $user->save();
                $begin->commit();
                ajaxReturn(1,'修改成功');
            }catch (Exception $e){
                $begin->rollBack();
                ajaxReturn(0,'修改失败');
            }
        }
        $this->assign('user',$user);
        return $this->display('up.html');
    }

    /**
     * 删除
     */
    public function actionDel()
    {
        $id = Yii::$app->request->post('id',0);
        if(!$id){
            ajaxReturn(0,'系统错误，请稍受再试');
        }
        if($id == 1){
            ajaxReturn(0,'不能删除超级管理员！');
        }
        $model = User::findOne($id);
        $model->updated_at = time();
        $model->isdel = 1;
        if($model->save()){
            ajaxReturn(1,'操作成功!');
        }
        ajaxReturn(0,'操作失败!');
    }

    /**
     * 权限处理
     */
    public function actionAuthItemChild(){
        $id = Yii::$app->request->get('id',0);
        if(!$id){echo "系统错误，请稍受再试";exit;}
        if($id == 1){echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;超级管理员拥有所有权限！";exit;}
        $userAuth = Yii::$app->getAuthManager()->getPermissionsByUser($id);//管理员拥有权限
        $arrAuth = Yii::$app->getAuthManager()->getPermissions();//所有权限
        $arr = [];
        if($userAuth){
            $userKey = array_keys($userAuth);
            foreach ($arrAuth as $key => $auth){
                $akey = strstr($key,"/",true);
                if(!isset($arr[$akey]))$arr[$akey] = ['child'=>[]];
                if(preg_match("/^\/index/",strstr($key,"/"))){
                    $arr[$akey] = array_merge($arr[$akey],['name'=>$auth->name,'description'=>$auth->description,'checked'=>in_array($key,$userKey) ? 'checked' : '']);
                }else{
                    $arr[$akey]['child'][] = ['name'=>$auth->name,'description'=>$auth->description,'checked'=>in_array($key,$userKey) ? 'checked' : ''];
                }
                //$arr[] = ['name'=>$auth->name,'description'=>$auth->description,'checked'=>in_array($key,$userKey) ? 'checked' : ''];
            }
        }else{
            foreach ($arrAuth as $key => $auth){
                $akey = strstr($key,"/",true);
                if(!isset($arr[$akey]))$arr[$akey] = ['child'=>[]];
                if(preg_match("/^\/index/",strstr($key,"/"))){
                    $arr[$akey] = array_merge($arr[$akey],['name'=>$auth->name,'description'=>$auth->description,'checked'=> '']);
                }else{
                    $arr[$akey]['child'][] = ['name'=>$auth->name,'description'=>$auth->description,'checked'=> ''];
                }
            }
        }
        $this->assign(['userid'=>$id,'auths'=>$arr]);
        return $this->display('auth.html');
    }

    /**
     * 保存权限
     */
    public function actionAuthAdd(){
        $id = Yii::$app->request->get('id',0);
        if(!$id){echo "系统错误，请稍受再试";exit;}
        $auth = Yii::$app->request->post('auth',null);
        if(empty($auth)) {
            ajaxReturn(0, '请选择用户权限','');
        }
        $role_str = "admin_".$id;
        $role = Yii::$app->authManager->getRole($role_str); // 获取角色
        if($role)Yii::$app->authManager->remove($role);//如果存在删除
        $admin = Yii::$app->authManager->createRole($role_str);//创建角色
        Yii::$app->authManager->add($admin);
        Yii::$app->authManager->assign($admin,$id);//为用户分配角色
        foreach ($auth as $v) {
            $permission = Yii::$app->authManager->getPermission($v); //获取具体权限
            Yii::$app->authManager->addChild($admin, $permission); // 添加权限
        }
        ajaxReturn(1,'操作成功');
    }
}
