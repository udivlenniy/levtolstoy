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
   				'actions'=>array('textlist', 'check','view', 'text'),
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

        //проверяем есть ли доступ у редактора к выбранному проекту
        $sql = 'SELECT id FROM {{project_users}} WHERE project_id="'.$id.'" AND user_id="'.Yii::app()->user->id.'"';
        $find = Yii::app()->db->createCommand($sql)->queryRow();

        //$projectUser = ProjectUsers::model()->findByAttributes(array('user_id'=>Yii::app()->user->id));
        if(empty($find)){
           throw new CHttpException(404,'The requested page does not exist.');
        }

        // находим текст с учётом, что к данному тексту подвязан именно текущий юзер-копирайтор
   		$model = Project::model()->findByPk($id);

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
                    // редактор принял выполненное задание копирайтором, всё отлично обновим статус
                    $model->status = Text::TEXT_ACCEPT_EDITOR;
                    $model->save();
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
                $this->redirect(array('index'));
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
     * страница просмотра проектов - редактора
     */
    public function  actionCheck(){

        //выводим список проектов по редактору, к которому они подвязаны
        $sql = 'SELECT {{project}}.*
                FROM {{project}},{{project_users}}
                WHERE {{project_users}}.user_id="'.Yii::app()->user->id.'"
                    AND {{project}}.id={{project_users}}.project_id
                ORDER BY {{project}}.id DESC';

        $dataProvider = Yii::app()->db->createCommand($sql)->queryAll();

        $dataProvider =  new CActiveDataProvider('Project', array(
        ));
        $this->render('project_list', array('dataProvider'=>$dataProvider));
    }
    /*
     * отображаем информацию редактору о выбранном проекте
     */
    public function actionView($id){
        $model=$this->loadModel($id);
        $this->render('view', array('model'=>$model));
    }
}