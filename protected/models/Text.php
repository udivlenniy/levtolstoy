<?php

/**
 * This is the model class for table "{{text}}".
 *
 * The followings are the available columns in table '{{text}}':
 * @property integer $id
 * @property integer $project_id
 * @property integer $status
 */
class Text extends CActiveRecord
{
    // список статуосв текста, в процессе его написнаия
    const TEXT_NEW = 1; // новый текст, только что создали, после импорта файла
    const TEXT_AVTO_CHECK = 2; // прошёл автоматические проверки и нет ошибок по тексту
    const TEXT_ACCEPT_EDITOR = 3; // принят текст редактором



    public $status_new;// для установки статуса редактором при проверке задания от копирайтора
    public $status_new_text;// описание ошибки

    /*
     * выводим текстовое соответствие относительно текущего статуса текста
     */
    public static function getStatus($status){
        // новый статус текста
        if($status==self::TEXT_NEW){ return 'Новый'; }

        // прошёл автомат. проверки системой, после написания копирайтором всех полей
        if($status==self::TEXT_NEW){ return 'Новый'; }
    }


	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Text the static model class
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
		return '{{text}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('project_id, status', 'required'),
            // проверка заполнения текста ошибки, при выборе что есть ошибки в заполненной задании копирайтором
            array('status_new_text', 'description_error'),
			array('project_id, status', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, project_id, status', 'safe', 'on'=>'search'),
		);
	}
    /*
     * если редактор выбрал статус ошибки, то должен указать описание этой ошибки для проверяемого текста - задания копирайтора
     */
    public function description_error(){
        file_put_contents('error.txt',$this->status_new);
        // если редактор установил что есть ошибки, то должен указать описание ошибки
        if($this->status_new=='error'){

            // проверка на то, заполнил ли редактор описание ошибки
            if(empty($this->status_new_text)){
                $this->addError('status_new_text','Необходимо указать описание ошибок');
                return false;
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'project_id' => 'Project',
			'status' => 'Status',
            'status_new_text'=>'Описание ошибки',
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
		$criteria->compare('project_id',$this->project_id);
		$criteria->compare('status',$this->status);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /*
     * получаем кол-во текстов по проекту
     */
    public static function getCountTextByProject($project_id){
        $sql = 'SELECT COUNT(id) as count
                FROM {{text}}
                WHERE {{text}}.project_id="'.$project_id.'"';

        $data = Yii::app()->db->createCommand($sql)->queryRow();

        if(empty($data['count'])){
            return 0;
        }else{
            return $data['count'];
        }
    }
}