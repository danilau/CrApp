<?php
/**
 * Created by PhpStorm.
 * User: Andrej
 * Date: 25.6.15
 * Time: 18.18
 */
namespace app\controllers;

use app\components\forms\LoginForm;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
//Remove
use app\models\User;

class SiteController extends Controller{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['members','logout'],
                        'roles' => ['@'],
                        'allow' => true,
                    ]
                ]
            ]
        ];
    }

    public function actionIndex(){

        if(!Yii::$app->user->isGuest)
            $this->redirect('members');

        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) and $model->login())
             $this->redirect('members');

        return $this->render('index', compact('model'));
    }

    public function actionMembers(){

        $members = [];

        Yii::$app->redis->open();
        $currentUsers = User::findAll(null);

        foreach($currentUsers as $currentUser){
            $members[] = [
                'name' => $currentUser->getAttribute('username'),
                'isHandUp' => $currentUser->getAttribute('handState')
            ];
        }

        $username = Yii::$app->user->identity->getAttribute('username');
        $handState = Yii::$app->user->identity->getAttribute('handState')==0?'up':'down';

        return $this->render('members', compact('members','username','handState'));
    }

    public function actionLogout(){

        User::deleteAll(['id'=>[Yii::$app->session->id]]);

        Yii::$app->user->logout();

        Yii::$app->redis->close();

        return $this->goHome();
    }
}