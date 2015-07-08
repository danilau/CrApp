<?php
/**
 * Created by PhpStorm.
 * User: Andrej
 * Date: 25.6.15
 * Time: 18.32
 */
use app\assets\MemberScreenAsset;
use app\components\widgets\CRNav;
use app\components\widgets\CRNavBar;
use app\components\widgets\MemberListWidget;

MemberScreenAsset::register($this);

$this->title = 'CRAPP - Members screen';

CRNavBar::begin([
    'options' => [
        'class' => 'navbar-crapp'
    ]
]);
echo CRNav::widget([
        'items' => [
            [
                'label' => 'Actions',
                'items' => [
                    [
                        'label' => 'Raise hand '.$handState,
                        'url' => '#',
                        'options' => [
                            'onclick' =>  '$.ajax({
                                            url: "/ajax/movehand",
                                            type: "post",
                                            success: function (data) {

                                                if(data == 1){

                                                    $("table tr").each(function(k,v){

                                                        var name = $(this).find(".col-md-11").text();

                                                        if(name==="'.$username.'"){
                                                            var handState = $("a:contains(\'Raise hand\')").text().split(" ")[2],
                                                                label;

                                                            if(handState=="up")
                                                                $(this).find(".col-md-1").addClass("handup");
                                                            else
                                                                $(this).find(".col-md-1").removeClass("handup");

                                                            label = "Raise hand " + (handState=="up"?"down":"up");
                                                            $("a:contains(\'Raise hand\')").text(label);

                                                        }
                                                    });
                                                }
                                            }
                                        });'
                        ]
                    ],
                ],
            ],
            [
                'label' => $username,
                'items' => [
                    [
                        'label' => 'Logout',
                        'url' => 'logout'
                    ],
                ],
                'options' => [
                    'class' => 'dropdown-user'
                ],
                'menuOptions' => [
                    'class' => 'dropdown-menu-right'
                ],

            ],

    ],
    'options' => ['class' =>'nav-pills'],
]);
CRNavBar::end();

?>
<div class = "container container-members">
    <div class = "row">
        <div class = "col-md-3 hidden-xs"></div>
        <div class = "col-md-6">
            <div class = "container-fluid">
                <?php echo MemberListWidget::widget(['items' => $members]); ?>
            </div>
        </div>
        <div class = "col-md-3 hidden-xs"></div>
    </div>
</div>

