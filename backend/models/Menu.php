<?php

namespace backend\models;

use Yii;
use common\models\AdminModel;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "menu".
 *
 * @property integer $id
 * @property integer  $parent
 * @property string  $name
 * @property string  $route
 * @property string  $data
 * @property integer $order
 *
 *
 */
class Menu extends ActiveRecord
{
    public static $title = '首页';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'menu';
    }


    public static function getMenus($userid,$active = ''){
        $navigation = [];
        if($userid == 1){
            $menus = self::find()->orderBy(['order'=>SORT_ASC])->asArray()->all();
        }else{
            $arrAuth = Yii::$app->getAuthManager()->getPermissionsByUser($userid);
            $menus = [];
            if ($arrAuth) {
                $menus = self::find()->where(['route' => array_keys($arrAuth)])
                    ->orderBy(['order'=>SORT_ASC])->asArray()->all();//->indexBy('id')
                // 有导航栏信息
                if ($menus) {
                    $parent = [];
                    // 获取父类信息
                    foreach ($menus as $key => $value) {
                        if ($value['parent'] != 0 && !in_array($value['parent'],$parent)) {
                            $parent[] = $value['parent'];
                        }
                    }
                    // 获取主要栏目信息
                    $parent = self::find()->where([ 'parent' => 0, 'id' => $parent])
                        ->orderBy(['order'=>SORT_ASC])->asArray()->all();//->indexBy('id')
                    $menus  = array_merge($menus , $parent);
                }
            }
        }
        // 处理导航栏信息
        if ($menus) {
            foreach ($menus as $value) {
                $id = $value['parent'] == 0 ? $value['id'] : $value['parent'];
                if ( ! isset($navigation[$id])) $navigation[$id] = ['child' => []];
                $value['active'] = '';
                if ($value['parent'] == 0) {
                    if(in_array($active,$value)){
                        $value['active'] = 'active';
                        self::$title = $value['name'];
                    }
                    if(!empty($navigation[$id]['active'])){
                        unset($value['active']);
                    }
                    $navigation[$id] = array_merge($navigation[$id], $value);
                } else {
                    if(in_array($active,$value)){
                        $value['active'] = 'active';
                        $navigation[$id]['active'] = 'active open';
                        self::$title = $value['name'];
                    }
                    $navigation[$id]['child'][] = $value;
                }
            }
        }
        return $navigation;
    }
}
