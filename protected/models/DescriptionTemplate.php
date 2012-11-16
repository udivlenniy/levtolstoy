<?php

/**
 * This is the model class for table "{{description_template}}".
 *
 * The followings are the available columns in table '{{description_template}}':
 * @property integer $id
 * @property integer $category_id
 * @property string $title
 * @property string $title_job
 * @property string $type_job
 * @property string $description
 * @property integer $deadline
 * @property integer $price_th
 * @property integer $uniqueness
 *
 * The followings are the available model relations:
 * @property Category $category
 */
class DescriptionTemplate extends CActiveRecord
{
    public $uploadfile_temp; // переменная для валидации формы при добавлении формы шаблона

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return DescriptionTemplate the static model class
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
		return '{{description_template}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			//array('category_id, title, title_job, type_job, description, deadline, price_th, uniqueness', 'required'),
            array('title,category_id', 'required'),
			array(' category_id,  price_th, uniqueness', 'numerical', 'integerOnly'=>true),
			array('deadline, title, title_job, type_job', 'length', 'max'=>255),
            array('description','length', 'max'=>6000),

            array('uploadfile_temp', 'uploaded_file'),

			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, category_id, title, title_job, type_job, description, deadline, price_th, uniqueness', 'safe', 'on'=>'search'),
		);
	}


    /*
     * метод валидации формы, т.е. перед сохранением НОВОЙ модели, необходимо загрузить файл и проставить соотвествия
     */
    public function uploaded_file(){
        // не было других ошибок, и это НОВОЕ добавление модели
        if(!$this->hasErrors() && $this->isNewRecord){
            if(!isset($_POST['ImportVarsShema'])){
                $this->addError('uploadfile_temp', 'Необходимо загрузить файл шаблона и проставить поля соотвествий');
                //return false;
            }
        }

        //return true;
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
			'category_id' => 'Рубрика',
			'title' => 'Название шаблона',
			'title_job' => 'Название задания',
			'type_job' => 'Тип работы',
			'description' => 'Описание заказа',
			'deadline' => 'Срок сдачи заказа',
			'price_th' => 'Стоимость 1 тыс. знаков без пробелов',
			'uniqueness' => 'Уникальность',
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
		$criteria->compare('category_id',$this->category_id);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('title_job',$this->title_job,true);
		$criteria->compare('type_job',$this->type_job,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('deadline',$this->deadline);
		$criteria->compare('price_th',$this->price_th);
		$criteria->compare('uniqueness',$this->uniqueness);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

//,
    protected function beforeSave(){

        //$this->attributes

        if(parent::beforeSave()){

            // преобразовываем выбранную дату в числовой формат
            if($this->isNewRecord){


                //$this->author_id=Yii::app()->user->id;
            }else{
                //$this->update_time=time();
            }

            //echo 'deadline='.$this->deadline.'<br>'; die();

            if(!empty($this->deadline)){
                $parse_date = explode('/',$this->deadline);
                $this->deadline = mktime(0, 0, 0,  $parse_date[0],$parse_date[1], $parse_date[2]);
            }

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