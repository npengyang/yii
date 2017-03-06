<?php
namespace backend\controllers;

use common\models\User;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post())) {
            $msg = [
                'flag'=>1,
                'msg'=>'登录成功',
                'data'=>''
            ];
            if(!$model->login()){
                $msg = [
                    'flag'=>0,
                    'msg'=>'登录失败',
                    'data'=>''
                ];
            }else{
                $admin = User::findOne(Yii::$app->user->id);
                if ($admin) {
                    $admin->last_time = $admin->current_time ? $admin->current_time : time();
                    $admin->current_time = time();
                    $admin->last_ip = $admin->current_ip ? $admin->current_ip : $_SERVER["REMOTE_ADDR"];
                    $admin->current_ip = $_SERVER["REMOTE_ADDR"];
                    $admin->save();
                }
            }
            return json_encode($msg);
        } else {
            $this->assign('title','登陆');
            return $this->display('login.html',array('a'=>123));
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
    
}
