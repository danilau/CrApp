<?php
/**
 * Created by PhpStorm.
 * User: Andrej
 * Date: 26.6.15
 * Time: 3.29
 */
namespace app\assets;

use yii\web\AssetBundle;

class MemberListAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/widgets';
    public $css = [
        'memberlist.css'
    ];
}