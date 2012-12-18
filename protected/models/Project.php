<?php

/**
 * This is the model class for table "{{project}}".
 *
 * The followings are the available columns in table '{{project}}':
 * @property integer $id
 * @property string $title
 * @property string $type_job
 * @property string $description
 * @property integer $deadline
 * @property integer $price_th
 * @property integer $total_cost
 * @property integer $total_num_char
 * @property integer $uniqueness
 * @property integer $category_id
 *
 * The followings are the available model relations:
 * @property Category $category
 */
class Project extends CActiveRecord
{

    public $UseTemplate; // использовать выбранный из списка шаблон, для заполнения формы добавления задания
    public $uploadFile; // файл импорта с данными, для формирования задания копирайтору

    public $zipArchive;//Архив с проектом, ссылка на скачивание
    public $keyWordsProject; //Ключевые слова проекта


    // список статусов для задания, их много ))
    const CREATE_TASK = 1;//Задание создано – задание создано админом, исполнитель и редактор выбраны
    const PERFORMER = 2; //Заданию назначен исполнитель
    const PERFORMED = 3; //Выполняется исполнителем
    const POSTED_TO_PERFORMED = 4;//Отправлено на проверку исполнителем
    const TASK_CHEKING_REDACTOR = 5;//Задание проверяется редактором
    const TASK_POSTED_TO_REWORK = 6 ;//Задание отправлено на доработку редактором
    const TASK_AGREE_REDACTOR = 7;//Задание принято редактором
    const TASK_CHEKING_ADMIN = 8;//Задание проверяется администратором
    const TASK_CANCEL_ADMIN = 9;//Задание отклонено администратором
    const TASK_AGREE_ADMIN = 10;//Задание принято администратором
    /*
     * получаем статус проекта
     * возвращаем его понятное описание
     */
    static function getStatus($status){
        if($status==self::CREATE_TASK){ return "Создано"; }
        if($status==self::PERFORMER){ return "Выбран исполнитель"; }
        if($status==self::PERFORMED){ return "Выполняется исполнителем"; }
        if($status==self::POSTED_TO_PERFORMED){ return "Отправлено исполнителем"; }
        if($status==self::TASK_CHEKING_REDACTOR){ return "Проверяется редактором"; }
        if($status==self::TASK_POSTED_TO_REWORK){ return "Отправлено на доработку редактором"; }
        if($status==self::TASK_AGREE_REDACTOR){ return "Принято редактором"; }
        if($status==self::TASK_CHEKING_ADMIN){ return "Проверяется администратором"; }
        if($status==self::TASK_CANCEL_ADMIN){ return "Отклонено администратором"; }
        if($status==self::TASK_AGREE_ADMIN){ return "Приянто администратором"; }
    }

    /*
     * определяем степень готовности проекта
     */
    static function percentReady($status){
        if($status==self::CREATE_TASK || $status==self::PERFORMER){ return '0%'; }
        if($status==self::PERFORMED || $status==self::POSTED_TO_PERFORMED){ return '30%'; }
        if($status==self::TASK_CHEKING_REDACTOR || $status==self::TASK_AGREE_REDACTOR){ return '60%'; }
        if($status==self::TASK_CHEKING_ADMIN){ return '80%'; }
        if($status==self::TASK_AGREE_ADMIN){ return '100%'; }
        return '';
    }

    /*
     * степень готовности проекта, расчитываем по статусам
     */
    public static function readinessProject($status){
        if($status==self::CREATE_TASK || $status==self::PERFORMER){
            return 0;
        }
        if($status==self::PERFORMED || $status==self::POSTED_TO_PERFORMED){
            return 30;
        }
        if($status==self::TASK_CHEKING_REDACTOR || $status==self::TASK_POSTED_TO_REWORK || $status==self::TASK_AGREE_REDACTOR){
            return 60;
        }
        if($status==self::TASK_CHEKING_ADMIN || $status==self::TASK_CANCEL_ADMIN){
            return 90;
        }
        if($status==self::TASK_AGREE_ADMIN){
            return 100;
        }
    }

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Project the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{project}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
            // обязательные параметры при добавлении и редактировании проекта
			array('title, type_job, description, deadline, price_th, total_cost, total_num_char, uniqueness, category_id', 'required', 'on'=>'create, update'),
            array('UseTemplate', 'required', 'on'=>'create'),
            array('check_editor,check_copywriter', 'boolean'),
			array('price_th, total_cost, total_num_char, uniqueness, category_id, status, total_num_char_fact,count_texts', 'numerical', 'integerOnly'=>true, 'on'=>'create, update'),//deadline,
			array('title ,type_job, description, site', 'length', 'max'=>255, 'on'=>'create, update'),
            array('performer_login, performer_pass', 'length', 'max'=>255),

            array('upload_project_in_system,output_project_to_copy,deadline_copy_to_redactor, deadline_redactor_to_admin, accept_project_admin', 'numerical', 'integerOnly'=>true,),

            array('uploadFile', 'file', 'types'=>'csv', 'maxSize'=>1024 * 1024 * 10, 'on'=>'create'),
            // при создании проекта, проверяем есть ли активные редакторы
            array('title', 'issetActiveRedactor'),
			array('id, title, type_job, description, deadline, price_th, total_cost, total_num_char, uniqueness, category_id, total_num_char_fact,count_texts, site', 'safe', 'on'=>'search'),
		);
	}

    /*
     * проверяем существуют ли в системе активные редакторы, чтобы было на кого подвязать проект
     */
    public function issetActiveRedactor(){
        if(!$this->hasErrors()){
            $sql = 'SELECT id
                    FROM {{users}}
                    WHERE role="'.User::ROLE_EDITOR.'"
                        AND status="1"';
            $data = Yii::app()->db->createCommand($sql)->queryRow();

            if(empty($data)){
                $this->addError('title','Необходимо добавить хотя бы одного активного редактора в систему');
                return false;
            }else{
                return true;
            }
        }
    }

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'category' => array(self::BELONGS_TO, 'Category', 'category_id'),
//            'admin'=>array(self::MANY_MANY,'User',
//                'tbl_project_users(project_id,user_id)',
//                'on'=>'admin.user_id='.Yii::app()->user->getId(),
//            ),

            //условие по поиска проектов которые подвязаны к админу, т.е. текущему пользователю
            // отображаем список проектов, текущего админа
            'admin'=>array(self::MANY_MANY,'User','tbl_project_users(project_id,user_id)',
                'condition'=>'admin_user.user_id='.Yii::app()->user->getId(),
            ),

		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'title' => 'Название проекта',
			'type_job' => 'Тип работы',
			'description' => 'Описание заказа',
			'deadline' => 'Срок сдачи заказа',
			'price_th' => 'Стоимость 1 тыс. знаков без пробелов',
			'total_cost' => 'Стоимость проекта',
			'total_num_char' => 'Кол-во знаков в проекте',
			'uniqueness' => 'Уникальность',
			'category_id' => 'Тематика',
            'UseTemplate'=>'Использовать шаблон для заполнения полей',
            'uploadFile'=>'Файл с данными',
            'performer_login'=>'Логин(исполнителя)',
            'performer_pass'=>'Пароль(исполнителя)',
            'check_editor'=>'Включить автопроверки для редактора',
            'check_copywriter'=>'Включить автопроверки для копирайтора',
            'zipArchive'=>'CSV-файл с проектом',
            'keyWordsProject'=>'Ключевые слова проекта',
            'total_num_char_fact'=>'Кол-во знаков в проекте(факт.)',
            'count_texts'=>'Кол-во текстов в проекте',
            'site'=>'Сайт проекта',


            'upload_project_in_system'=>'Дата и время загрузки проекта в систему', //
            'output_project_to_copy'=>'Дата и время выдачи проекта копирайтеру',
            'deadline_copy_to_redactor'=>'Дата сдачи проекта копирайтером редактору',
            'deadline_redactor_to_admin'=>'Дата сдачи проекта редактором администртору',
            'accept_project_admin'=>'Дата принятия проекта администратором',
            //''=>'',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('type_job',$this->type_job,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('deadline',$this->deadline);
		$criteria->compare('price_th',$this->price_th);
		$criteria->compare('total_cost',$this->total_cost);
		$criteria->compare('total_num_char',$this->total_num_char);
		$criteria->compare('uniqueness',$this->uniqueness);
		$criteria->compare('category_id',$this->category_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
            'pagination'=>array(
                'pageSize'=>Yii::app()->params['perPage'],
            ),
		));
	}

    protected function beforeSave(){

        //$this->attributes

        if(parent::beforeSave()){

            // новая запись
            if($this->isNewRecord){

                //$this->author_id=Yii::app()->user->id;
                $this->status = self::CREATE_TASK;
                // установим дату загрузки проекта в систему
                $this->upload_project_in_system = time();
            }else{// редактирование записи
                //$this->update_time=time();
            }
            // преобразовываем выбранную дату в числовой формат
            $parse_date = explode('/',$this->deadline);

            $this->deadline = mktime(0, 0, 0, $parse_date[1], $parse_date[0], $parse_date[2]);


            return true;
        }else{
            return false;
        }
    }

    protected function afterFind(){
        //10/06/2012
        parent::afterFind();
        $this->deadline = date('d/m/Y',$this->deadline);

        $this->upload_project_in_system = ($this->upload_project_in_system==0)? '': date('d-m-Y H:i:s',$this->upload_project_in_system);
        $this->output_project_to_copy = ($this->output_project_to_copy==0)? '': date('d-m-Y H:i:s',$this->output_project_to_copy);
        $this->deadline_copy_to_redactor = ($this->deadline_copy_to_redactor==0)? '': date('d-m-Y H:i:s',$this->deadline_copy_to_redactor);
        $this->deadline_redactor_to_admin = ($this->deadline_redactor_to_admin==0)? '': date('d-m-Y H:i:s',$this->deadline_redactor_to_admin);
        $this->accept_project_admin = ($this->accept_project_admin==0)? '': date('d-m-Y H:i:s',$this->accept_project_admin);

    }

    /*
     * получаем список получателей личного сообщения для разных типов аккаунтов
     * на основании роли пользователя - формируем список получателей его личного сообщения
     */
    public static function listRecipientFor($project_id){

        // возможность отправить всем кроме себя
        $sql  = 'SELECT {{users}}.id, {{users}}.role
                FROM {{project_users}}, {{users}}
                WHERE {{project_users}}.project_id="'.$project_id.'"
                    AND {{users}}.id={{project_users}}.user_id
                    AND {{users}}.id!="'.Yii::app()->user->id.'"';
        $data = Yii::app()->db->createCommand($sql)->queryAll();
        $result = array();
        foreach($data as $row){
            $result[$row['id']] = UserModule::t($row['role']);
        }

        return $result;
    }

    /*
     * устанавливаем кол-во текстов(заданий) по проекту,
     * обновляем эти данные к проекте
     */
    public static function updateCount_texts($project_id, $count){
        $sql = 'UPDATE {{project}} SET count_texts="'.(int)$count.'" WHERE id="'.$project_id.'"';
        Yii::app()->db->createCommand($sql)->execute();
    }

    /*
     * после сохранения задания, обновляем общее фактическое кол-во символов в проекте
     * т.е. это то кол-во символов по тем полям, которые доступны для редактирования копирайтором
     * вначале получаем список полей доступных для редактирования копирайтором в проекте
     * очищаем от пробелов и HTMl кода и считает кол-во символов
     */
    static function updateCountTextFact($project_id){
        // выбираем все задания
        $sql = 'SELECT {{text_data}}.import_var_value
                FROM {{text}},{{text_data}},{{import_vars_shema}}
                WHERE {{text}}.project_id="'.$project_id.'"
                    AND {{text_data}}.text_id={{text}}.id
                    AND {{import_vars_shema}}.import_var_id={{text_data}}.import_var_id
                    AND {{import_vars_shema}}.shema_type="1"
                    AND {{import_vars_shema}}.num_id="'.$project_id.'"
                    AND {{import_vars_shema}}.visible="1"
                    AND {{import_vars_shema}}.edit="1"';

        $data = Yii::app()->db->createCommand($sql)->queryAll();
        // перебираем в цикле список полей доступных для редактирования копирайтору и очищаем их от пробелов и тегов
        $count = 0;
        foreach($data as $row){
            $text = str_replace(' ', '', strip_tags($row['import_var_value']));
            $count+=strlen($text);
        }
        // теперь обновим фактическое кол-во символов в проекте
        Yii::app()->db->createCommand('UPDATE {{project}} SET total_num_char_fact="'.$count.'" WHERE id="'.$project_id.'"')->execute();
    }

    /*
     * метод заполняет соотвеств. поля по разных ситуацих при изменении данных в проекте
     * статик событие после сохранения данных в проекта
     * вызываем везде где изменяем данные проекта
     * в самом методе прописываем условия обнуления данных по статистике
     * СУТЬ - если в каком задании изменились данные, то нужно обработать
     * все вытекающие из этого события ситуации и обновление статусов и счётчиков
     */
    //TODO вызывать во всех местах изменения проекта или заданий из проекта+дописать все условия изменения и обновления полей статусов+доп. полей по датам
    static function afterChangeDataInProject($project_id){
        // обновляем  кол-во символов ФАКТИЧЕСКОЕ по всём проекте
        Project::updateCountTextFact($project_id);
    }
    /*
     * логин+ссылка на профайл пользователя
     */
    public static function getLinkUserOfProjectToProfile($project_id, $role){
        // формируем условие WHERE по полю ROLE
        if($role==User::ROLE_ADMIN){
            $where = '({{users}}.role="'.User::ROLE_ADMIN.'" OR {{users}}.role="'.User::ROLE_SA_ADMIN.'")';
        }else{
            $where = '{{users}}.role="'.$role.'"';
        }


        $sql = 'SELECT {{users}}.username,{{users}}.id
                FROM {{users}},{{project_users}}
                WHERE '.$where.'
                    AND {{users}}.id={{project_users}}.user_id
                    AND {{project_users}}.project_id="'.$project_id.'"';
        $find = Yii::app()->db->createCommand($sql)->queryRow();

        return CHtml::link($find['username'], array('/user/admin/view','id'=>$find['id']));
    }

    /*
     * подсчитываем кол-во проектов для пунктов меню РЕДАКТОРА
     * $type - 1 - всего проектов обработано редатоктором
     * $type - 2 - проектов в проверке
     * $type - 3 - проектов на проверке у админа
     */
    public static function getCountProjectOfRedactorMenu($type){
        if($type==1){
            $sql='SELECT {{project}}.id AS count
                  FROM {{project_users}},{{project}}
                  WHERE  {{project_users}}.user_id="'.Yii::app()->user->id.'"
                    AND {{project}}.id={{project_users}}.project_id
                    AND  {{project}}.status="'.Project::TASK_AGREE_ADMIN.'"';
        }elseif($type==2){
            $sql='SELECT {{project}}.id AS count
                  FROM {{project_users}},{{project}}
                  WHERE  {{project_users}}.user_id="'.Yii::app()->user->id.'"
                    AND {{project}}.id={{project_users}}.project_id
                    AND  {{project}}.status="'.Project::TASK_CHEKING_REDACTOR.'"';
        }elseif($type==3){
            $sql='SELECT {{project}}.id AS count
                  FROM {{project_users}},{{project}}
                  WHERE  {{project_users}}.user_id="'.Yii::app()->user->id.'"
                    AND {{project}}.id={{project_users}}.project_id
                    AND  {{project}}.status="'.Project::TASK_CHEKING_ADMIN.'"';
        }else{
            return '';
        }
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if(empty($row['count'])){
            return 0;
        }else{
            $row['count'];
        }
    }
}