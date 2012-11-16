<?php
/* @var $this ProjectController */
/* @var $model Project */
$this->breadcrumbs=array(
    Yii::t('msg','Projects')=>array(''),
    Yii::t('msg','Manage'),
);

$this->menu=array(
	array('label'=>'List Project', 'url'=>array('index')),
	array('label'=>'Create Project', 'url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('project-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h2>Управление проектами</h2>

<?php
//echo CHtml::link('Advanced Search','#',array('class'=>'search-button'));

?>

<?php echo CHtml::link('Создать проект',array('create')); ?>

<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<?php $this->widget('bootstrap.widgets.TbGridView', array(
	'id'=>'project-grid',
	'dataProvider'=>$model->search(),
    'template'=>'{items}{pager}',
	'filter'=>$model,
	'columns'=>array(
		'id',
		//'title',
        array(
            'name'=>'title',
            'type'=>'raw',
            'value'=>'CHtml::link($data->title,array("admin/view","id"=>$data->id))'
        ),
//        array(
//            'name'=>'type_job',
//            'type'=>'raw',
//            'value'=>'CHtml::link($data->type_job,array("admin/view","id"=>$data->id))'
//        ),
//        array(
//            'name'=>'total_cost',
//            'type'=>'raw',
//            'value'=>'CHtml::link($data->total_cost,array("admin/view","id"=>$data->id))'
//        ),
        array(
            'name'=>'deadline',
            'type'=>'raw',
            'value'=>'CHtml::link($data->deadline,array("admin/view","id"=>$data->id))'
        ),
		//'type_job',
        //'total_cost',
      	//'total_num_char',
		//'description',
		//'deadline',
		//'price_th',
        'uniqueness',
        'performer_login',
        'performer_pass',
		/*
		'total_cost',
		'total_num_char',
		'uniqueness',
		'category_id',
		*/
		array(
            'class'=>'bootstrap.widgets.TbButtonColumn',
		),
	),
)); ?>
