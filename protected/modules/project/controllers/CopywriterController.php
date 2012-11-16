<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Александр
 * Date: 09.11.12
 * Time: 12:32
 * To change this template use File | Settings | File Templates.
 */
class CopywriterController extends  Controller{
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
   				'actions'=>array('index','update','downloadfile','text'),
   				'users'=>array('@'), //UserModule::getAdmins(),
   			),
   			array('deny',  // deny all users
   				'users'=>array('*'),
   			),
   		);
   	}

    // выводим список текстов для исполнителя
    public function actionIndex(){

        // выясняем к какому проекту подвязан текущий пользователь-копирайтер
        $projectUser = ProjectUsers::model()->findByAttributes(array('user_id'=>Yii::app()->user->id));

        // формируем запрос на выборку списка текстов с заголовками(TITLE)
        $sql = 'SELECT {{text}}.*,{{text_data}}.import_var_value as title
                FROM {{text}}, {{text_data}}
                WHERE {{text}}.id={{text_data}}.text_id
                  AND {{text}}.project_id="'.$projectUser->project_id.'"
                  AND {{text_data}}.import_var_id='.Yii::app()->params['title'].'
                GROUP BY tbl_text.id';
           //import_var_id=3 - это у нас внутренняя переменная "TITLE"

        // получаем массив данных, для отображения в таблице
        $data = Yii::app()->db->createCommand($sql)->queryAll();

        $result = array();
        // перебираем массив для формирования ссылок на задания, чтобы копирайтер последовательно их мог выполнять
        // т.е.выполнил 2 задания перешёл на третье, а не любое на выбор
        $beforeStatus = Text::TEXT_NEW;
        foreach($data as $i=>$row){
            // первый текст в списке это ВСЕГДА ссылка
            if($i==0){
                $row['title'] = CHtml::link($row["title"],array("copywriter/text","id"=>$row["id"]));
            }else{
                // если предыдущий текст был со статусом НЕ новый, тогда ссылка, иначе просто заголовок
                if($beforeStatus!=Text::TEXT_NEW){
                    $row['title'] = CHtml::encode(CHtml::link($row["title"],array("copywriter/text","id"=>$row["id"])));
                }
            }

            $beforeStatus = $row['status'];

            $result[] = $row;
        }

        $dataProvider=new CArrayDataProvider($result, array(
            'pagination'=>array(
                'pageSize'=>count($data),
            ),
        ));

        $this->render('text_list',array(
            'dataProvider'=>$dataProvider,
        ));
    }

    /*
     * просматриваем выбранный текст по созданному заданию копирайтору
     * $id - ID текста в таблице
     */
    public function actionText($id){
        Yii::app()->bootstrap->registerAssetCss('redactor.css');
      	Yii::app()->bootstrap->registerAssetJs('redactor.min.js');
        Yii::app()->bootstrap->registerAssetJs('locales/redactor.ru.js');
        // используем данные из модели, для проверки соотвествия - проекта - тексту и доступов по юзеру
        $model = $this->loadModel($id);

        // sql-запрос на выборку полей с данными для выбранного текста
        $sql = 'SELECT {{text_data}}.id, {{text_data}}.import_var_value, {{import_vars}}.title,{{text_data}}.import_var_id
                FROM {{text_data}},{{import_vars}}
                WHERE {{text_data}}.text_id='.$id.'
                    AND {{import_vars}}.id={{text_data}}.import_var_id';

        $data = Yii::app()->db->createCommand($sql)->queryAll();

        // отправили POST на обновление данных по данному тексту
        if(isset($_POST['Text'])){
            $model->attributes = $_POST['Text'];
            // цикл по полям, с обновлением значением полей
            foreach($_POST['ImportVarsValue'] as $i=>$val){
                // SQL запрос на обновление данных
                $sql = 'UPDATE {{text_data}} SET import_var_value="'.$val.'" WHERE id="'.(int)$i.'"';
                Yii::app()->db->createCommand($sql)->execute();
            }

            $this->redirect(array('index'));
        }

        $this->render('text_view',array(
            'data'=>$data,
            'model'=>$model,
        ));
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