<?php
/**
 * Created by PhpStorm.
 * User: Andrej
 * Date: 25.6.15
 * Time: 18.21
 */
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'CRAPP - Login screen';
?>
<div class = "container container-login">
    <div class = "row">
        <div class = "col-md-3 hidden-xs"></div>
        <div class = "col-md-6">
            <div class = "container container-loginform">
                <?php
                    $form = ActiveForm::begin([
                        'id' => 'login-form',
                        'errorCssClass' => '',
                    ]);
                    $userNameField = $form->field($model, 'username',[
                        'labelOptions' => [
                            'label' => $model->attributeLabels()['username'].':'
                        ],
                        'inputOptions'=>[
                            'class' => 'form-control',
                            'placeholder' => 'Enter your name'
                        ],
                        'errorOptions' => [
                            'class' => 'alert alert-warning'
                        ]

                    ]);
                    $userNameField->template = "{error}\n{label}{input}";
                    echo $userNameField;
                    echo Html::submitButton('Login',[
                        'class' => 'btn btn-default',
                        'name' => 'login-button'
                    ]);
                    ActiveForm::end();
                ?>
                </div>
        </div>
        <div class = "col-md-3 hidden-xs"></div>
    </div>
</div>