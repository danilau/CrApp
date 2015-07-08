<?php
/**
 * Created by PhpStorm.
 * User: Andrej
 * Date: 26.6.15
 * Time: 1.05
 */
namespace app\components\widgets;

use app\assets\MemberListAsset;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap\Alert;

class MemberListWidget extends Widget{
    public $items;

    private $title = 'Class members';
    private $message = 'There are no class members';

    public function init(){
        parent::init();
    }

    public function run(){
        if(empty($this->items)){
            echo Alert::widget([
                'body' => $this->message,
                'options' => ['class' => 'alert-warning'],
                'closeButton' => false
            ]);
            return;
        }

        MemberListAsset::register($this->getView());

        echo HTML::tag('h5',$this->title);

        echo $this->renderTable();
    }

    public function renderTable(){

        $rows = [];
        foreach($this->items as $item){
            $rows []= $this->renderItem($item);
        }
        $bodyOutput = HTML::tag('tbody',implode('',$rows));

        return HTML::tag('table',$bodyOutput,[
            'class' => 'table table-striped table-bordered'
        ]);
    }

    public function renderItem($item){
        $name = ArrayHelper::getValue($item, 'name');
        $isHandUp = ArrayHelper::getValue($item, 'isHandUp');
        $rowOutput = HTML::tag('td',$name,[
            'class' => 'col-md-11'
        ]);
        $handUpClass = $isHandUp?'handup':'';
        $rowOutput .= HTML::tag('td','',[
            'class' => 'col-md-1 '.$handUpClass
        ]);

        return HTML::tag('tr', $rowOutput);
    }
}