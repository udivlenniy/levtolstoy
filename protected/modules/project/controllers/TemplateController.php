<?php
Yii::import("application.modules.user.UserModule");

/**
 * Created by JetBrains PhpStorm.
 * User: Александр
 * Date: 22.10.12
 * Time: 17:09
 * To change this template use File | Settings | File Templates.
 */
class TemplateController extends Controller{
    public $defaultAction = 'index';
    public $layout='//layouts/column2';

    private $_model;

    /**
     * @return array action filters
     */
    public function filters()
    {
        return CMap::mergeArray(parent::filters(),array(
            'accessControl', // perform access control for CRUD operations
        ));
    }
    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions'=>array('admin','delete','create','update','view', 'index','uploadfile'),
                'users'=>UserModule::getAdmins(),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    /**
   	 * Displays a particular model.
   	 * @param integer $id the ID of the model to be displayed
   	 */
   	public function actionView($id)
   	{
   		$this->render('view',array(
   			'model'=>$this->loadModel($id),
   		));
   	}

   	/**
   	 * Creates a new model.
   	 * If creation is successful, the browser will be redirected to the 'view' page.
   	 */
   	public function actionCreate(){

   		$model = new DescriptionTemplate;

        $this->performAjaxValidation($model);

        // заполнили данные формы шаблона и выставили соотвествия полей, предварительно загрузив файл импорта
   		if(isset($_POST['DescriptionTemplate']) ){//&& isset($_POST['ImportVarsShema'])

   			$model->attributes = $_POST['DescriptionTemplate'];

   			if($model->validate()){

               $model->save();
               // цикл по полям и их соотвествиям
               $cnt = 1;
               foreach($_POST['ImportVarsShema'] as $i=>$shemVar){
                   $shema = new ImportVarsShema();
                   // ID внутреннй переменной
                   $shema->import_var_id = $shemVar;
                   // ID модели, к котор. подвязываем схему импорта
                   $shema->num_id = $model->id;
                   // положение по порядку
                   $shema->num = $cnt;
                   //тип схемы - для проекта или шаблона(сейчас для ШАБЛОНА)
                   $shema->shema_type = ImportVarsShema::SHEMA_TYPE_TEMPLATE;

                   // название столбца в файле импорта
                   $shema->label = $_POST['label'][$i];

                   // галочки по полям
                   if($_POST['edit'][$i]==1){// редактирование
                        $shema->edit = 1;
                   }else{
                       $shema->edit = 0;
                   }
                   // видимость поля
                   if($_POST['visible'][$i]==1){
                        $shema->visible = 1;
                   }else{
                       $shema->visible = 0;
                   }
                   // ВИЗИВИГ - редактор
                   if($_POST['wysiwyg'][$i]==1){
                        $shema->wysiwyg = 1;
                   }else{
                       $shema->wysiwyg = 0;
                   }

                   $shema->save();

                   $cnt++;
               }

               $this->redirect(array('view','id'=>$model->id));
            }
   		}

   		$this->render('create',array(
   			'model'=>$model,
   		));
   	}

    /*
     * контроллер загрузки файла импорта
     */
    public function actionUploadFile(){

        Yii::import("ext.EAjaxUpload.qqFileUploader");

        $folder=Yii::getPathOfAlias('webroot').'/upload/';// folder for uploaded files
        $allowedExtensions = array("csv");//array("jpg","jpeg","gif","exe","mov" and etc...
        $sizeLimit = 7 * 1024 * 1024;// maximum file size in bytes
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        $result = $uploader->handleUpload($folder);

        $result = json_encode($result);

        echo $result;// it's array
    }

   	/**
   	 * Updates a particular model.
   	 * If update is successful, the browser will be redirected to the 'view' page.
   	 * @param integer $id the ID of the model to be updated
   	 */
   	public function actionUpdate($id)
   	{
   		$model = $this->loadModel($id);

   		// Uncomment the following line if AJAX validation is needed
   		$this->performAjaxValidation($model);

   		if(isset($_POST['DescriptionTemplate'])){

   			$model->attributes = $_POST['DescriptionTemplate'];

   			if($model->validate()){
               $model->save();
               // цикл по полям и их соотвествиям
               $cnt = 1;
               foreach($_POST['ImportVarsShema'] as $i=>$shemVar){

                   $shema = ImportVarsShema::model()->findByPk($i);
                   // ID внутреннй переменной
                   $shema->import_var_id = $shemVar;

                   // галочки по полям
                   if($_POST['edit'][$cnt]==1){
                        $shema->edit = 1;
                   }else{
                       $shema->edit = 0;
                   }

                   if($_POST['visible'][$cnt]==1){
                        $shema->visible = 1;
                   }else{
                       $shema->visible = 0;
                   }

                   if($_POST['wysiwyg'][$cnt]==1){
                        $shema->wysiwyg = 1;
                   }else{
                       $shema->wysiwyg = 0;
                   }

                   $shema->save();

                   $cnt++;
               }
               $this->redirect(array('view','id'=>$model->id));
            }
   		}

   		$this->render('update',array(
   			'model'=>$model,
   		));
   	}

   	/**
   	 * Deletes a particular model.
   	 * If deletion is successful, the browser will be redirected to the 'admin' page.
   	 * @param integer $id the ID of the model to be deleted
   	 */
   	public function actionDelete($id)
   	{
   		$this->loadModel($id)->delete();

   		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
   		if(!isset($_GET['ajax']))
   			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
   	}

   	/**
   	 * Lists all models.
   	 */
   	public function actionIndex()
   	{
       $model=new DescriptionTemplate('search');
        $model->unsetAttributes();  // clear any default values
        if(isset($_GET['DescriptionTemplate']))
            $model->attributes=$_GET['DescriptionTemplate'];

        $this->render('admin',array(
            'model'=>$model,
        ));
   	}

   	/**
   	 * Manages all models.
   	 */
   	public function actionAdmin()
   	{
   		$model=new DescriptionTemplate('search');
   		$model->unsetAttributes();  // clear any default values
   		if(isset($_GET['DescriptionTemplate']))
   			$model->attributes=$_GET['DescriptionTemplate'];

   		$this->render('admin',array(
   			'model'=>$model,
   		));
   	}

   	/**
   	 * Returns the data model based on the primary key given in the GET variable.
   	 * If the data model is not found, an HTTP exception will be raised.
   	 * @param integer the ID of the model to be loaded
   	 */
   	public function loadModel($id)
   	{
   		$model=DescriptionTemplate::model()->findByPk($id);
   		if($model===null)
   			throw new CHttpException(404,'The requested page does not exist.');
   		return $model;
   	}

   	/**
   	 * Performs the AJAX validation.
   	 * @param CModel the model to be validated
   	 */
   	protected function performAjaxValidation($model)
   	{
   		if(isset($_POST['ajax']) && $_POST['ajax']==='description-template-form')
   		{
   			echo CActiveForm::validate($model);
   			Yii::app()->end();
   		}
   	}
}