<?php
$this->widget('bootstrap.widgets.TbMenu',array(
    'type'=>'pills',
    'items'=>array(
        //'class' => 'bootstrap.widgets.TbMenu',
        array('label'=>'Профиль', 'url'=>array('/site/index')),
        array('label'=>'Сообщения', 'url'=>array('/messages/')),
        array('label'=>'Проекты', 'items'=> array(
            array('label'=>'Всего проектов обработано('.Project::getCountProjectOfRedactorMenu(1).')', 'url'=>'/project/redactor/all_make/'),
            array('label'=>'Проектов в проверке('.Project::getCountProjectOfRedactorMenu(2).')', 'url'=>'/project/redactor/check/'),
            array('label'=>'Проектов на проверке у администратора('.Project::getCountProjectOfRedactorMenu(3).')', 'url'=>'/project/redactor/check_admin/'),
        )),
        //array('label'=>'Статистика редактора', 'url'=>array('/project/redactor/statistics')),
        array('label'=>'Выход ('.Yii::app()->user->name.')', 'url'=>array('/user/logout'), 'visible'=>!Yii::app()->user->isGuest)
    ),
));