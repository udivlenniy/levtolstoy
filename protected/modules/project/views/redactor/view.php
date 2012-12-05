<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Александр
 * Date: 19.11.12
 * Time: 13:41
 * To change this template use File | Settings | File Templates.
 */
?>
<h2>Информация о проекте #<?php echo $model->id; ?></h2>

<?php $this->widget('bootstrap.widgets.TbDetailView', array(
    'data'=>$model,
    'attributes'=>array(
        //'id',
        'title',
        'type_job',
        'description',
        'deadline',
        'price_th',
        'total_cost',
        'total_num_char',
        'uniqueness',
        //'category_id',
        array(
            'name'=>'category_id',
            //'type'=>'raw',
            'value'=>$model->category->title,
        )
    ),
));
// ссылка на список текстов по проекту, со статусом -Text::TEXT_AVTO_CHECK

echo CHtml::link('Перейти к редактору текстов', '/project/redactor/textlist/id/'.$model->id);
?>
<?php
    $this->beginWidget('bootstrap.widgets.TbModal', array('id'=>'myModal')); ?>

<div class="modal-header">
    <a class="close" data-dismiss="modal">×</a>
    <h4>Отправка личного сообщения:</h4>
</div>

<div class="modal-body">

<?php
    $this->renderPartial('msg', array('msg'=>$msg, 'model'=>$model));
?>
</div>
<?php $this->endWidget();
echo CHtml::link('Отправить личное сообщение',
    '#',
    array(
        'data-toggle'=>'modal',
        'data-target'=>'#myModal',
        'style'=>'margin-left:30px;'
    )
);
$this->widget('CommentsWidget',array('model_id'=>$model->id, 'model'=>get_class($model)));
?>