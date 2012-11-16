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
			array('project_id, status', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, project_id, status', 'safe', 'on'=>'search'),
		);
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
}