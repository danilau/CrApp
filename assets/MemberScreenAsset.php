<?php
/**
 * Created by PhpStorm.
 * User: Andrej
 * Date: 28.6.15
 * Time: 14.40
 */
namespace app\assets;

use yii\web\AssetBundle;

class MemberScreenAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/widgets';
    public $js = [
        'http://autobahn.s3.amazonaws.com/js/autobahn.min.js',
        'websocketclient.js'
    ];
}