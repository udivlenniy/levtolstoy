<?php
Yii::import("application.modules.user.UserModule");
/**
 * Created by JetBrains PhpStorm.
 * User: Александр
 * Date: 22.10.12
 * Time: 16:51
 * To change this template use File | Settings | File Templates.
 */
class AdminController extends Controller{
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
   				'actions'=>array('index','delete','create','update','view','uploadfile','selecttemplate','text','deletekeyword'),
   				'users'=>UserModule::getAdmins(),
   			),
   			array('deny',  // deny all users
   				'users'=>array('*'),
   			),
   		);
   	}
    /*
     * просматриваем
     */

    /**
   	 *выводим список ссылок на тексты по данному заданию
     * каждая ссылка это форма для написания текста, с полями и данными для этих полей
   	 */
   	public function actionView($id)
   	{
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

        $this->render('text_list',array(
            'dataProvider'=>$dataProvider,
        ));
   	}

    /*
     * просматриваем выбранный текст по созданному заданию копирайтору
     * $id - ID текста в таблице
     */
    public function actionText($id){

        // отправили AJAX запрос на добавление нового ключевика по тексту -
        if(Yii::app()->request->isAjaxRequest){
            if(!empty($_POST['keyWordNew'])){
                $sqlInsertKeyWord = 'INSERT INTO {{text_data}}
                                    (import_var_id,import_var_value,text_id)
                                    VALUES('.intval(Yii::app()->params['key_words']).',"'.$_POST['keyWordNew'].'",'.intval($_POST['textId']).')';
                Yii::app()->db->createCommand($sqlInsertKeyWord)->execute();
            }
            // sql-запрос на выборку полей с данными для выбранного текста
            $sql = 'SELECT {{text_data}}.id, {{text_data}}.import_var_value, {{import_vars}}.title,{{text_data}}.import_var_id
                    FROM {{text_data}},{{import_vars}}
                    WHERE {{text_data}}.text_id="'.$_POST['textId'].'"
                        AND {{import_vars}}.id={{text_data}}.import_var_id';

            $data = Yii::app()->db->createCommand($sql)->queryAll();
            // получаем список ключевиков по заданию и формируем по ним таблицу
            $this->renderPartial('key_words_list', array('data'=>$data, 'textId'=>$_POST['textId']));
            Yii::app()->end();
        }

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
            // цикл по полям, с обновлением значением полей
            foreach($_POST['ImportVarsValue'] as $i=>$val){
                // SQL запрос на обновление данных
                $sql = 'UPDATE {{text_data}} SET import_var_value="'.$val.'" WHERE id="'.(int)$i.'"';
                Yii::app()->db->createCommand($sql)->execute();
            }

            $this->redirect(array('view','id'=>$model->project_id));
        }

        $this->render('text_view',array(
            'data'=>$data,
            'model'=>$model,
        ));
    }
    /*
     * удаляем ключевое слово из текста, при редактировании админом задания
     */
    public function actionDeletekeyword($id){
        if(Yii::app()->request->isAjaxRequest){
            // удаляем выбранное ключевое слово из указанного задания и возвращаем список ключевиков обратно - ОБНОВЛЁННЫЙ список
            $sqlDelete = 'DELETE FROM {{text_data}} WHERE id="'.intval($id).'"';
            Yii::app()->db->createCommand($sqlDelete)->execute();

            // sql-запрос на выборку полей с данными для выбранного текста
            $sql = 'SELECT {{text_data}}.id, {{text_data}}.import_var_value, {{import_vars}}.title,{{text_data}}.import_var_id
                    FROM {{text_data}},{{import_vars}}
                    WHERE {{text_data}}.text_id="'.$_POST['textId'].'"
                        AND {{import_vars}}.id={{text_data}}.import_var_id';

            $data = Yii::app()->db->createCommand($sql)->queryAll();
            // получаем список ключевиков по заданию и формируем по ним таблицу
            $this->renderPartial('key_words_list', array('data'=>$data, 'textId'=>$_POST['textId']));
            Yii::app()->end();
        }
    }

   	/**
   	 * Creates a new model.
   	 * If creation is successful, the browser will be redirected to the 'view' page.
   	 */
   	public function actionCreate(){

   		$model = new Project();
        $model->setScenario('create');
        // ajax валидация данных
        //$this->performAjaxValidation($model);

   		if(isset($_POST['Project'])){

   			$model->attributes = $_POST['Project'];
            $model->uploadFile = CUploadedFile::getInstance($model,'uploadFile');

            if($model->validate()){

                // находим схему полей соотвествия для импортируемого файла
                $shems = ImportVarsShema::getListFieldsByIdShema($model->UseTemplate, ImportVarsShema::SHEMA_TYPE_TEMPLATE);

                if($model->save()){

                    // решили распарсить файл на поля соотвествий и записать значения соотвествющим полям
                    $csvFile = new CsvImport($model->uploadFile->tempName);
                    $csvFile->processFileImport($shems, $model->id);

                    // копируем поля настроек и соотвествий из выбранного шаблона и подвязываем их к проекту, что записали
                    ImportVarsShema::copyTemplate($shems, $model->id);

                    // поле создания проекта - генерируем логин и пасс для исполнителя и создаём копирайтора и подвязываем его к проекту
                    $access = User::createCopywriter($model->id);

                    //допишим логин и пароль исполнителя к проекту
                    $sqlUpdate = 'UPDATE {{project}}
                                    SET performer_login="'.$access['login'].'", performer_pass="'.$access['pass'].'"
                                    WHERE id="'.$model->id.'"';

                    Yii::app()->db->createCommand($sqlUpdate)->execute();

                    $this->redirect(array('view','id'=>$model->id));
                }
            }
   		}

   		$this->render('create',array(
   			'model'=>$model,
   		));
   	}
    /*
     * ajax запрос на получение данных из шаблона
     * находим шаблон, смотрим его настройки и подгружаем выбраннёе в шаблоне настройки
     */
    public function actionSelecttemplate(){

        $data = array();

        if(!empty($_POST['Project']['UseTemplate'])){
            $template = DescriptionTemplate::model()->findByPk($_POST['Project']['UseTemplate']);
            $data['category_id']= $template->category_id;
            $data['type_job']= $template->type_job;
            $data['description']= $template->description;
            $data['deadline']= $template->deadline;
            $data['price_th']= $template->price_th;
            $data['uniqueness']= $template->uniqueness;
            $data['title'] = $template->title_job;
        }
        // return data (JSON formatted)
        echo CJSON::encode($data);
    }
   	/**
   	 * Updates a particular model.
   	 * If update is successful, the browser will be redirected to the 'view' page.
   	 * @param integer $id the ID of the model to be updated
   	 */
   	public function actionUpdate($id)
   	{
   		$model=$this->loadModel($id);
        $model->setScenario('update');
   		//$this->performAjaxValidation($model);

   		if(isset($_POST['Project'])){

            //echo '<pre>'; print_r($_POST); die();

   			$model->attributes=$_POST['Project'];
   			if($model->save()){
               // цикл по полям и их соотвествиям
               $cnt = 1;
               foreach($_POST['ImportVarsShemaID'] as $i=>$shemVar){

                   $shema = ImportVarsShema::model()->findByPk($i);
                   // ID внутреннй переменной
                   //$shema->import_var_id = $shemVar;

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

        // удаляем все связанные данные с проектом:исполнителя,задания,тексты

        // находим список текстов по проекту и удаляем подвязанные даннные по текстам
        $textList = Yii::app()->db->createCommand('SELECT id FROM {{text}} WHERE project_id="'.$id.'"');
        foreach($textList as $row){
            Yii::app()->db->createCommand('DELETE FROM {{text_data}} WHERE text_id="'.$row['id'].'"')->execute();
        }
        // удаляем пользователей подвязанных к проекту(исполнители)
        $sqlFindCopyWriter = 'SELECT tbl_project_users.*
                            FROM `tbl_project_users` , tbl_users
                            WHERE  tbl_project_users.user_id=tbl_users.id
                                 AND tbl_users.role='.User::ROLE_COPYWRITER.'
                                 AND tbl_project_users.project_id="'.$id.'"';

        // сначала находим созданного копирайтора в таблице юзеров и его удаляем, а потом подвязки юзеров к проекту
        $copyWriter = Yii::app()->db->createCommand($sqlFindCopyWriter)->queryRow();

        // удаляем пользователя из таблицы ЮЗЕРОВ(удаляем копирайтора)
        if(empty($copyWriter)){
            Yii::app()->db->createCommand('DELETE FROM {{users}} WHERE id="'.$copyWriter['user_id'].'"')->execute();
        }

        // удаляем пользователей подвязанных к проекту(исполнители) - ПОДВЯЗКИ юзеров
        Yii::app()->db->createCommand('DELETE FROM {{project_users}} WHERE project_id="'.$id.'"');

   		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
   		if(!isset($_GET['ajax']))
   			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
   	}

   	/**
   	 * Lists all models.
   	 */
   	public function actionIndex()
   	{
        $model=new Project('search');
        $model->unsetAttributes();  // clear any default values
        if(isset($_GET['Project']))
            $model->attributes=$_GET['Project'];

        $this->render('admin',array(
            'model'=>$model,
        ));
   	}

   	/**
   	 * Manages all models.
   	 */
   	public function actionAdmin()
   	{
   		$model=new Project('search');
   		$model->unsetAttributes();  // clear any default values
   		if(isset($_GET['Project']))
   			$model->attributes=$_GET['Project'];

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
   		$model=Project::model()->findByPk($id);
   		if($model===null)
   			throw new CHttpException(404,'The requested page does not exist.');
   		return $model;
   	}

    /*
     * загрузка модели для текста
     */
    public function loadModelText($id){

        $model = Text::model()->findByPk($id);

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
   		if(isset($_POST['ajax']) && $_POST['ajax']==='project-form')
   		{
   			echo CActiveForm::validate($model);
   			Yii::app()->end();
   		}
   	}
}