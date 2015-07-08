<?php
/**
 * Created by PhpStorm.
 * User: Andrej
 * Date: 27.6.15
 * Time: 17.16
 */
namespace app\controllers;

use app\models\User;
use Yii;
use yii\web\Controller;

class AjaxController extends Controller{

    function actionMovehand(){

        if (Yii::$app->request->isAjax && !Yii::$app->user->isGuest) {

            $id = Yii::$app->user->identity->getAttribute('id');
            $handState = Yii::$app->user->identity->getAttribute('handState');

            return User::UpdateAll(
                [
                   'handState' => $handState==0?1:0
                ],
                [
                    'id' => $id
                ]
            );

        }
        $this->goHome();
    }

}