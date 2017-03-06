<?php

namespace backend\controllers;
use Yii;

class ResourcesController extends BaseController
{
    public function actionIndex()
    {
        return $this->display('index.html');
    }

}
