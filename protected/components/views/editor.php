<?php
$this->widget('bootstrap.widgets.TbMenu',array(
    'type'=>'pills',
    'items'=>array(
        'class' => 'bootstrap.widgets.TbMenu',

                array('label'=>'Профиль', 'url'=>array('/site/index')),

                array('label'=>'Проекты', 'items'=> array(
                    array('label'=>'Всего проектов обработано', 'url'=>'#'),
                    array('label'=>'Проектов в проверке', 'url'=>'#'),
                    array('label'=>'Проектов на проверке к администратора', 'url'=>'#'),
                )),

            //array('label'=>'Пароль', 'url'=>array('/user/profile/changepassword')),
            //array('label'=>'Пользователи', 'url'=>array('/user/admin')),
            //array('label'=>'Категории', 'url'=>array('/category/')),
            //array('label'=>'Задания', 'url'=>array('/project/copywriter/')),
            //array('label'=>'Шаблоны', 'url'=>array('/project/template/')),
            array('label'=>'Выход ('.Yii::app()->user->name.')', 'url'=>array('/user/logout'), 'visible'=>!Yii::app()->user->isGuest)

    ),
));