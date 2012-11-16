<?php
$this->pageTitle=Yii::app()->name . ' - '.UserModule::t("Login");
$this->breadcrumbs=array(
	UserModule::t("Login"),
);
?>

<h1><?php echo UserModule::t("Login"); ?></h1>

<?php if(Yii::app()->user->hasFlash('loginMessage')): ?>

<div class="success">
	<?php echo Yii::app()->user->getFlash('loginMessage'); ?>
</div>

<?php endif; ?>



<div class="form" style="width: 500px; margin: 0 auto;">
<?php echo CHtml::beginForm(); ?>
    <p>Заполните следующую форму с вашими Логином и паролем:</p>
	<p class="note"><?php echo UserModule::t('Fields with <span class="required">*</span> are required.'); ?></p>
	
	<?php echo CHtml::errorSummary($model); ?>
	
	<div class="row">
		<?php echo CHtml::activeLabelEx($model,'username'); ?>
		<?php echo CHtml::activeTextField($model,'username') ?>
	</div>
	
	<div class="row">
		<?php echo CHtml::activeLabelEx($model,'password'); ?>
		<?php echo CHtml::activePasswordField($model,'password') ?>
	</div>
	
<!--	<div class="row">-->
<!--		<p class="hint">-->
<!--		--><?php //echo CHtml::link(UserModule::t("Register"),Yii::app()->getModule('user')->registrationUrl); ?><!-- | --><?php //echo CHtml::link(UserModule::t("Lost Password?"),Yii::app()->getModule('user')->recoveryUrl); ?>
<!--		</p>-->
<!--	</div>-->
	
	<div class="row rememberMe">
		<?php echo CHtml::activeCheckBox($model,'rememberMe'); ?>
		<?php echo CHtml::activeLabelEx($model,'rememberMe'); ?>
	</div>

	<div class="row submit">
		<?php
        //echo CHtml::submitButton(UserModule::t("Login"));
//        $this->widget('bootstrap.widgets.TbButton',array(
//        	'label' => UserModule::t("Login"),
//        	'type' => 'primary',
//        	'size' => 'large'
//        ));
        $this->widget('bootstrap.widgets.TbButton',array('buttonType'=>'submit','type'=>'action','label'=>  UserModule::t("Login"),));
        ?>
	</div>
	
<?php echo CHtml::endForm(); ?>
</div><!-- form -->


<?php
$form = new CForm(array(
    'elements'=>array(
        'username'=>array(
            'type'=>'text',
            'maxlength'=>32,
        ),
        'password'=>array(
            'type'=>'password',
            'maxlength'=>32,
        ),
        'rememberMe'=>array(
            'type'=>'checkbox',
        )
    ),

    'buttons'=>array(
        'login'=>array(
            'type'=>'submit',
            'label'=>'Login',

        ),
    ),
), $model);
?>