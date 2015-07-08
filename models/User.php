<?php
/**
 * Created by PhpStorm.
 * User: Andrej
 * Date: 26.6.15
 * Time: 18.29
 */
namespace app\models;

use Yii;
use yii\web\IdentityInterface;
use app\models\CRActiveRecord;

class User extends CRActiveRecord implements IdentityInterface{

    public $id;
    public $username;
    public $handState;

    /**
     * ActiveRecord methods overriding
     */
    public function attributes()
    {
        return ['id','username', 'handState'];
    }
    /**
     * IdentityInterface implementation
     */
    public static function findIdentity($id){
        return User::findOne(Yii::$app->session->id);
    }

    public static function findIdentityByAccessToken($token, $type = null){
        return null;
    }

    public function getId(){
        return Yii::$app->session->id;
    }

    public function getAuthKey(){
        return null;
    }

    public function validateAuthKey($authKey){
        return null;
    }

}