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
        'id',
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
    $this->renderPartial('msg', array('msg'=>$msg, 'model'=>$model));
echo CHtml::link('Отправить личное',
    '#',
    array(
        'data-toggle'=>'modal',
        'data-target'=>'#myModal',
        'style'=>'margin-left:30px;'
    )
);
?>
<?php
//$this->beginWidget('bootstrap.widgets.TbModal', array('id'=>'myModal')); ?>
<!---->
<!--<div class="modal-header">-->
<!--    <a class="close" data-dismiss="modal">×</a>-->
<!--    <h4>Отправка личного сообщения:</h4>-->
<!--</div>-->
<!---->
<!--<div class="modal-body">-->
<!--    <div class="form">-->
<!--        --><?php //$form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
//        'id'=>'messages-form',
//        'enableAjaxValidation'=>true,
//        'clientOptions'=>array(
//            'validateOnSubmit'=>true,
//        ),
//    )); ?>
<!--        <div class="row">-->
<!--            --><?php //echo $form->labelEx($msg,'recipient_id'); ?>
<!--            --><?php //echo $form->dropDownList($msg,'recipient_id', Project::listRecipientFor($model->id)); ?>
<!--            --><?php //echo $form->error($msg,'recipient_id'); ?>
<!--        </div>-->
<!--        <div class="row">-->
<!--            --><?php //echo $form->labelEx($msg,'msg_text').'<br>'; ?>
<!--            --><?php //echo $form->textArea($msg,'msg_text',array('rows'=>6, 'cols'=>50, 'style'=>'width:500px')); ?>
<!--            --><?php //echo $form->error($msg,'msg_text'); ?>
<!--        </div>-->
<!--        <div class="modal-footer">-->
<!--            --><?php //$this->widget('bootstrap.widgets.TbButton', array(
//                'type'=>'primary',
//                'buttonType'=>'submit',
//                'label'=>'Отправить',
//                'url'=>'#',
//                'htmlOptions'=>array(
//                    //'data-dismiss'=>'modal'
//                ),
//            )); ?>
<!--            --><?php //$this->widget('bootstrap.widgets.TbButton', array(
//                'label'=>'Закрыть',
//                'url'=>'#',
//                'htmlOptions'=>array('data-dismiss'=>'modal'),
//            )); ?>
<!--        </div>-->
<!--        --><?php //$this->endWidget(); ?>
<!--    </div><!-- form -->
<!--</div>-->
<?php //$this->endWidget(); ?>