<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;

/**
 * Site controller
 */
class ActivityController extends Controller
{

    public function actionIndex()
    {
        return $this->display('index.html');
    }
}
