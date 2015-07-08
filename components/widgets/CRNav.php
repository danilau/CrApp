<?php
/**
 * Created by PhpStorm.
 * User: Andrej
 * Date: 25.6.15
 * Time: 23.56
 */
namespace app\components\widgets;

use yii\helpers\ArrayHelper;
use yii\bootstrap\Dropdown;
use yii\bootstrap\Nav;

class CRNav extends Nav{

    protected function renderDropdown($items, $parentItem)
    {
        return Dropdown::widget([
            'items' => $items,
            'encodeLabels' => $this->encodeLabels,
            'clientOptions' => false,
            'view' => $this->getView(),
            'options' => ArrayHelper::getValue($parentItem, 'menuOptions', [])
        ]);
    }

}