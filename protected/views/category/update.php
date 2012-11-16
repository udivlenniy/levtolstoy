<?php
/* @var $this CategoryController */
/* @var $model Category */

$this->breadcrumbs=array(
    Yii::t('msg','Categories')=>array('index'),
	$model->title=>array('view','id'=>$model->id),
    Yii::t('msg','Update'),
);

$this->menu=array(
	array('label'=>'List Category', 'url'=>array('index')),
	array('label'=>'Create Category', 'url'=>array('create')),
	array('label'=>'View Category', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage Category', 'url'=>array('admin')),
);
?>

<h1>Обновить категорию <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>