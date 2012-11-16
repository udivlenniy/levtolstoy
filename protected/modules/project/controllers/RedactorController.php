<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Александр
 * Date: 16.11.12
 * Time: 17:35
 * To change this template use File | Settings | File Templates.
 */
class RedactorController extends  Controller{
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
   				'actions'=>array('index'),
   				'users'=>array('@'), //UserModule::getAdmins(),
   			),
   			array('deny',  // deny all users
   				'users'=>array('*'),
   			),
   		);
   	}
  /*
     * подгружаем модель с проверкой прав
     */
    public function loadModel($id){

        //смотреть тексты может копирайтор если он подвязан к этому тексту и он выполнил предыдущее задание
        $projectUser = ProjectUsers::model()->findByAttributes(array('user_id'=>Yii::app()->user->id));
        if($projectUser===null){
           throw new CHttpException(404,'The requested page does not exist.');
        }

        // находим текст с учётом, что к данному тексту подвязан именно текущий юзер-копирайтор
   		$model = Text::model()->findByPk($id, 'project_id=:project_id', array(':project_id'=>$projectUser->project_id));

   		if($model===null){
            throw new CHttpException(404,'The requested page does not exist.');
        }

   		return $model;
   	}
}