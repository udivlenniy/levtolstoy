<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Александр
 * Date: 04.12.12
 * Time: 17:03
 * To change this template use File | Settings | File Templates.
 */
    // форма для добавления комментариев
$this->beginWidget('bootstrap.widgets.TbModal', array('id'=>'commentsModal'));
?>
<div class="modal-header">
    <a class="close" data-dismiss="modal">×</a>
    <h4>Отправка комментария:</h4>
</div>

<div class="modal-body">
    <div class="form">

        <?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
        'id'=>'comments-form',
        'enableAjaxValidation'=>false,
    )); ?>

        <p class="note">Fields with <span class="required">*</span> are required.</p>

        <?php echo $form->errorSummary($model); ?>

        <div class="row">
            <?php echo $form->labelEx($model,'model'); ?>
            <?php echo $form->textField($model,'model',array('size'=>60,'maxlength'=>255)); ?>
            <?php echo $form->error($model,'model'); ?>
        </div>

        <div class="row">
            <?php echo $form->labelEx($model,'model_id'); ?>
            <?php echo $form->textField($model,'model_id'); ?>
            <?php echo $form->error($model,'model_id'); ?>
        </div>

        <div class="row">
            <?php echo $form->labelEx($model,'user_id'); ?>
            <?php echo $form->textField($model,'user_id'); ?>
            <?php echo $form->error($model,'user_id'); ?>
        </div>

        <div class="row">
            <?php echo $form->labelEx($model,'create'); ?>
            <?php echo $form->textField($model,'create'); ?>
            <?php echo $form->error($model,'create'); ?>
        </div>

        <div class="row">
            <?php echo $form->labelEx($model,'text'); ?>
            <?php echo $form->textArea($model,'text',array('rows'=>6, 'cols'=>50)); ?>
            <?php echo $form->error($model,'text'); ?>
        </div>

        <div class="row buttons">
            <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
        </div>

        <?php $this->endWidget(); ?>

    </div><!-- form -->
</div>
<?php $this->endWidget();
echo CHtml::link('Оставить комментарий',
    '#',
    array(
        'data-toggle'=>'modal',
        'data-target'=>'#commentsModal',
        'style'=>'margin-left:30px;'
    )
);
// выводим список комментариев
$this->widget('zii.widgets.CListView', array(
    'dataProvider'=>$dataProvider,
    'itemView'=>'_view_comments',
));
?>