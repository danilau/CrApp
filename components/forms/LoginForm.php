<?php
/**
 * Created by PhpStorm.
 * User: Andrej
 * Date: 26.6.15
 * Time: 16.16
 */
namespace app\components\forms;

use app\models\User;
use Yii;
use yii\base\Model;

class LoginForm extends Model{

    public $username;

    private $_user = false;

    public function rules()
    {
        return [
            ['username', 'required'],
            [
                'username',
                'match', 'not' => true,
                'pattern' => '/[^a-zA-Z _-]/',
                'message' => 'Invalid characters in username.'
            ],
            ['username', 'validateUserName']
        ];
    }

    public function attributeLabels(){
        return [
            'username' => 'Your name'
        ];
    }

    public function login(){
        if ($this->validate()) {

            $this->_user = new User();
            $this->_user->setAttribute('username',trim($this->username));
            $this->_user->setAttribute('handState',0);

            $result = Yii::$app->user->login($this->_user, 3600);

            $this->_user->setAttribute('id',Yii::$app->session->id);
            $this->_user->save();
            Yii::$app->redis->close();
            return $result;
        } else {
            return false;
        }
    }

    public function validateUserName($attribute, $params){
        Yii::$app->redis->open();
        $members = User::findAll(null);
        foreach($members as $member){
            if(strtolower($member->getAttribute('username'))==strtolower(trim($this->username))){
                $this->addError($attribute, 'User with such name is already logged in.');
                return false;
            }
        }

        return true;
    }

}
