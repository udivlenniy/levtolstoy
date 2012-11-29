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
			array('price_th, total_cost, total_num_char, uniqueness, category_id, status', 'numerical', 'integerOnly'=>true, 'on'=>'create, update'),//deadline,
			array('title ,type_job, description', 'length', 'max'=>255, 'on'=>'create, update'),
            array('performer_login, performer_pass', 'length', 'max'=>255),
            array('uploadFile', 'file', 'types'=>'csv', 'maxSize'=>1024 * 1024 * 10, 'on'=>'create'),
            // при создании проекта, проверяем есть ли активные редакторы
            array('title', 'issetActiveRedactor'),
			array('id, title, type_job, description, deadline, price_th, total_cost, total_num_char, uniqueness, category_id', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'title' => 'Название задания',
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
		));
	}

    protected function beforeSave(){

        //$this->attributes

        if(parent::beforeSave()){

            // новая запись
            if($this->isNewRecord){

                //$this->author_id=Yii::app()->user->id;
                $this->status = self::CREATE_TASK;
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
    }
}