<?php
/**
 * Created by PhpStorm.
 * User: Andrej
 * Date: 25.6.15
 * Time: 22.24
 */
namespace app\components\widgets;

use Yii;
use yii\bootstrap\NavBar;
use yii\bootstrap\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class CRNavBar extends NavBar{

    public function init(){
        Widget::init();
        $this->clientOptions = false;
        Html::addCssClass($this->options, 'navbar');

        Html::addCssClass($this->brandOptions, 'navbar-brand');
        if (empty($this->options['role'])) {
            $this->options['role'] = 'navigation';
        }
        $options = $this->options;
        $tag = ArrayHelper::remove($options, 'tag', 'nav');
        echo Html::beginTag($tag, $options);
        if ($this->renderInnerContainer) {
            if (!isset($this->innerContainerOptions['class'])) {
                Html::addCssClass($this->innerContainerOptions, 'container');
            }
            echo Html::beginTag('div', $this->innerContainerOptions);
        }
        echo Html::beginTag('div', ['class' => 'navbar-header']);
        if (!isset($this->containerOptions['id'])) {
            $this->containerOptions['id'] = "{$this->options['id']}";
        }

        if ($this->brandLabel !== false) {
            Html::addCssClass($this->brandOptions, 'navbar-brand');
            echo Html::a($this->brandLabel, $this->brandUrl === false ? Yii::$app->homeUrl : $this->brandUrl, $this->brandOptions);
        }
        echo Html::endTag('div');

        Html::addCssClass($this->containerOptions, 'navbar-content');
        $options = $this->containerOptions;
        $tag = ArrayHelper::remove($options, 'tag', 'div');
        echo Html::beginTag($tag, $options);
    }

}