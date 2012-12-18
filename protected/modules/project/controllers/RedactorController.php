<?php
Yii::import("application.modules.user.UserModule");
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
   				'actions'=>array('textlist', 'check','view', 'text','all_make','check_admin','statistics'),
   				//'users'=>array('@'), //UserModule::getAdmins(),
                'expression' => 'isset($user->role) && ($user->role==="editor")',
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

        //проверяем есть ли доступ у редактора к выбранному проекту
        $criteria=new CDbCriteria;
        // для супер_админа выводим все проекты, за всё время
        $criteria->with = array('admin');
        $criteria->together = true;
        // находим текст с учётом, что к данному тексту подвязан именно текущий юзер-копирайтор
   		$model = Project::model()->findByPk($id,$criteria);

   		if($model===null){
            throw new CHttpException(404,'The requested page does not exist.');
        }

   		return $model;
   	}

    /*
     * модель текста, с проверкой на доступы
     */
    public function loadModelText($id){
        //смотреть тексты может редактор подвязан к тексту
        $sql = 'SELECT {{text}}.*
                FROM {{project_users}}, {{text}}
                WHERE {{text}}.project_id={{project_users}}.project_id
                  AND {{project_users}}.user_id="'.Yii::app()->user->id.'"
                  AND {{text}}.id="'.$id.'"';

        $find = Yii::app()->db->createCommand($sql)->queryRow();

        if($find===null){
            throw new CHttpException(404,'The requested page does not exist.');
        }

        // находим текст с учётом, что к данному тексту подвязан именно текущий юзер-копирайтор
        // редактор может открывать тексты лишь после того как они прошли автомат. проверки при обновлении записи копирайтором(статус-TEXT_AVTO_CHECK)
        $model = Text::model()->findByPk($id);//, 'status=:status',array(':status'=>Text::TEXT_AVTO_CHECK)

        if($model===null){
            throw new CHttpException(404,'The requested page does not exist.');
        }

        return $model;
    }

    /*
     * выводим задание для проверки и редактирования редактором, которое написал копирайтор
     */
    public function actionText($id){
        // форма для редактирования и принятия текста для редактора
        Yii::app()->bootstrap->registerAssetCss('redactor.css');
        Yii::app()->bootstrap->registerAssetJs('redactor.min.js');
        Yii::app()->bootstrap->registerAssetJs('locales/redactor.ru.js');
        // используем данные из модели, для проверки соотвествия - проекта - тексту и доступов по юзеру
        $model = $this->loadModelText($id);

        // sql-запрос на выборку полей с данными для выбранного текста
        $sql = 'SELECT {{text_data}}.id, {{text_data}}.import_var_value, {{import_vars}}.title,{{text_data}}.import_var_id
                FROM {{text_data}},{{import_vars}}
                WHERE {{text_data}}.text_id='.$id.'
                    AND {{import_vars}}.id={{text_data}}.import_var_id';

        $data = Yii::app()->db->createCommand($sql)->queryAll();

        // отправили POST на обновление данных по данному тексту
        if(isset($_POST['Text'])){

            $model->attributes = $_POST['Text'];

            if($_POST['Text']['status_new']=='error' && empty($_POST['Text']['status_new_text'])){
                $model->addError('status_new_text','Необходимо указать описание ошибок');
            }else{
                // если редактор выбрал статус ошибка, то проверим чтобы он указал текст ошибки
                if($_POST['Text']['status_new']=='success'){
                    // если включены автопроверки для редактора, тогда запускаем их при сохранении задания
                    if(CheckingImportVars::isEnabledChekingByUser($model->project_id)){
                        $model->setScenario('checking');
                    }
                    if($model->validate()){
                        // редактор принял выполненное задание копирайтором, всё отлично обновим статус
                        $model->status = Text::TEXT_ACCEPT_EDITOR;
                        $model->save();
                    }else{
                        $this->render('text_view',array(
                            'data'=>$data,
                            'model'=>$model,
                        ));

                        Yii::app()->end();
                    }
                }
                //===========записываем ошибку в БД по данному тексту==============
                if($_POST['Text']['status_new']=='error' && !empty($_POST['Text']['status_new_text'])){
                    // установим новый статус, что НЕ принято редактором
                    $model->status = Text::TEXT_NOT_ACCEPT_EDITOR;
                    $model->save();

                    // подвязываем ошибку к тексту
                    $error = new Errors();
                    $error->user_id = Yii::app()->user->id;
                    $error->model = 'Text';
                    $error->type = $_POST['type_error'];
                    $error->error_text = $_POST['Text']['status_new_text'];
                    $error->model_id = $model->id;
                    $error->create = time();
                    $error->save();
                }
                // цикл по полям, с обновлением значением полей
                foreach($_POST['ImportVarsValue'] as $i=>$val){
                    // SQL запрос на обновление данных
                    $sql = 'UPDATE {{text_data}} SET import_var_value="'.$val.'" WHERE id="'.(int)$i.'"';
                    Yii::app()->db->createCommand($sql)->execute();
                }
                $this->redirect(array('textlist', 'id'=>$model->project_id));
            }
        }

        $this->render('text_view',array(
            'data'=>$data,
            'model'=>$model,
        ));
    }

    /*
     * выводим список текстов по проекту,
     * после того как тексты написал копирайтор и они прошли все автомат. проверки
     */
    public function actionTextlist($id){
        //выводим список текстов по проекту, которые прошли автоматическую проверку после написания копирайтором
        // формируем запрос на выборку списка текстов с заголовками(TITLE)
        $sql = 'SELECT {{text}}.*,{{text_data}}.import_var_value as title
                FROM {{text}}, {{text_data}}
                WHERE {{text}}.id={{text_data}}.text_id
                  AND {{text}}.project_id="'.(int)$id.'"
                  AND {{text_data}}.import_var_id='.Yii::app()->params['title'].'
                GROUP BY tbl_text.id';
        //import_var_id=3 - это у нас внутренняя переменная "TITLE"

        // получаем массив данных, для отображения в таблице
        $data = Yii::app()->db->createCommand($sql)->queryAll();

        $dataProvider=new CArrayDataProvider($data, array(
            'pagination'=>array(
                'pageSize'=>count($data),
            ),
        ));
        $this->render('text_list', array('dataProvider'=>$dataProvider));
    }

    /*
     * проектов на проверке у админа
     */
    public function actionCheck_admin(){
        $criteria=new CDbCriteria;
        //если пользователь НЕ_СУПЕР_АДМИН, тогда выводим лишь его проекты
        $criteria->with = array('admin');
        $criteria->together = true;

        $criteria->compare('t.status',Project::TASK_CHEKING_ADMIN);
//        $criteria->compare('title',$model->title,true);
//        $criteria->compare('type_job',$model->type_job,true);


        $dataProvider =  new CActiveDataProvider('Project', array(
            'criteria'=>$criteria,
            'pagination'=>array(
                'pageSize'=>Yii::app()->params['perPage'],
            ),
        ));


        $this->render('statistics', array('dataProvider'=>$dataProvider));
    }
    /*
     * статистика по редактору
     */
    public function actionStatistics(){

    }

    /*
     * всего проектов обработано
     */
    public function actionAll_make(){
        $criteria=new CDbCriteria;
        //если пользователь НЕ_СУПЕР_АДМИН, тогда выводим лишь его проекты
        $criteria->with = array('admin');
        $criteria->together = true;

        $criteria->compare('t.status',Project::TASK_AGREE_ADMIN);
//        $criteria->compare('title',$model->title,true);
//        $criteria->compare('type_job',$model->type_job,true);


        $dataProvider =  new CActiveDataProvider('Project', array(
            'criteria'=>$criteria,
            'pagination'=>array(
                'pageSize'=>Yii::app()->params['perPage'],
            ),
        ));


        $this->render('statistics', array('dataProvider'=>$dataProvider));
    }

    /*
     * страница просмотра проектов - редактора
     */
    public function  actionCheck(){

        $criteria=new CDbCriteria;
        //если пользователь НЕ_СУПЕР_АДМИН, тогда выводим лишь его проекты
        $criteria->with = array('admin');
        $criteria->together = true;

        $criteria->compare('t.status',Project::TASK_CHEKING_REDACTOR);
//        $criteria->compare('title',$model->title,true);
//        $criteria->compare('type_job',$model->type_job,true);


        $dataProvider =  new CActiveDataProvider('Project', array(
            'criteria'=>$criteria,
            'pagination'=>array(
                'pageSize'=>Yii::app()->params['perPage'],
            ),
        ));


        $this->render('project_list', array('dataProvider'=>$dataProvider));
    }
    /*
     * отображаем информацию редактору о выбранном проекте
     */
    public function actionView($id){
        // модель самого проекта
        $model = $this->loadModel($id);
        // модель личных сообщений
        $msg = new Messages();
        // заполняем нужными данными, для отправки сообщения
        $msg->author_id = Yii::app()->user->id;
        $msg->model = get_class($model);// к какой моделе подвязано сообщение
        $msg->model_id = $model->id;
        $msg->is_new = 1;

        $this->performAjaxValidation($msg);

        if(isset($_POST['Messages'])){
            $msg->attributes=$_POST['Messages'];
            $msg->create = time();
            if($msg->validate()){
                $msg->save();
                Yii::app()->user->setFlash('msg','Спасибо, ваше сообщение успешно отправлено');
                $this->renderPartial('msg', array('msg'=>new Messages(), 'model'=>$model));
                Yii::app()->end();
            }else{
                $this->renderPartial('msg', array('msg'=>$msg, 'model'=>$model));
                Yii::app()->end();
            }
        }

        $this->render('view', array('model'=>$model, 'msg'=>$msg));
    }
    /*
     * ajax - валидация формы данных при оптравке личных сообщений
     */
    protected function performAjaxValidation($model)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']==='messages-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}