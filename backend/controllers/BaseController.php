<?php

namespace backend\controllers;

use backend\models\Menu;
use Yii;

class BaseController extends \yii\web\Controller
{

    public $userid = "";
    public $username = "";
    public function beforeAction($action)
    {
        parent::beforeAction($action);

        if(Yii::$app->user->isGuest){
            echo "<script>location.href='index.php?r=site/login'</script>";
            exit;
        }

        $this->userid = Yii::$app->user->id;
        $this->username = Yii::$app->user->identity->username;
        $action = Yii::$app->controller->getRoute();//获取当前访问的控制器以及方法site/login
        $menus = Menu::getMenus($this->userid,$action);//获取菜单

        $auth = Yii::$app->authManager;
        $UserObj = $auth->checkAccess($this->userid,'admin');//检查是否是admin权限

        $this->assign(array('menus'=>$menus,'username'=>$this->username,'title'=>Menu::$title));

        if($UserObj){
            return true;
        }else{
            if($action == "index/index"){
                return true;
            }
            if(!Yii::$app->user->can($action)){
                throw new \yii\web\UnauthorizedHttpException('对不起，您现在还没获此操作的权限');
            }
        }
        return true;
    }

}
