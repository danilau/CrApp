<?php
/**
 * Created by PhpStorm.
 * User: Andrej
 * Date: 25.6.15
 * Time: 15.38
 */
// comment out the following two lines when deployed to production
ini_set('display_errors', true);

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require_once(__DIR__.'/../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__.'/../config/web.php');
(new yii\web\Application($config))->run();
