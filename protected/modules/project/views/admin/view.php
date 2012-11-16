<?php
/* @var $this ProjectController */
/* @var $model Project */

$this->breadcrumbs=array(
    Yii::t('msg','Projects')=>array('index'),
	$model->title,
);

$this->menu=array(
	array('label'=>'List Project', 'url'=>array('index')),
	array('label'=>'Create Project', 'url'=>array('create')),
	array('label'=>'Update Project', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete Project', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Project', 'url'=>array('admin')),
);
?>

<h2>Информация о задании #<?php echo $model->id; ?></h2>

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
		'category_id',
	),
)); ?>
