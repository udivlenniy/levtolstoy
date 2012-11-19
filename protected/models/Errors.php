<?php

/**
 * This is the model class for table "{{errors}}".
 *
 * The followings are the available columns in table '{{errors}}':
 * @property integer $id
 * @property integer $user_id
 * @property string $model
 * @property string $error_text
 * @property integer $model_id
 * @property integer $create
 */
class Errors extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Errors the static model class
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
		return '{{errors}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, model, error_text, model_id, create', 'required'),
			array('user_id, model_id, create', 'numerical', 'integerOnly'=>true),
			array('model', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, model, error_text, model_id, create', 'safe', 'on'=>'search'),
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
			'user_id' => 'User',
			'model' => 'Model',
			'error_text' => 'Error Text',
			'model_id' => 'Model',
			'create' => 'Create',
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
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('model',$this->model,true);
		$criteria->compare('error_text',$this->error_text,true);
		$criteria->compare('model_id',$this->model_id);
		$criteria->compare('create',$this->create);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}