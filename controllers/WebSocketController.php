<?php
/**
 * Created by PhpStorm.
 * User: Andrej
 * Date: 27.6.15
 * Time: 23.49
 */
namespace app\controllers;

use Yii;
use yii\console\Controller;

/**
 * Manages websocket server
 * @package app\controllers
 */
class WebSocketController extends Controller{

    /**
     * Run server
     */
    public function actionRun(){
        echo "Websocket server is run..\n";
        Yii::$app->websocket->run();
        exit(0);
    }

}