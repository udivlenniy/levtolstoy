<?php

/**
 * This is the model class for table "{{checking_import_vars}}".
 *
 * The followings are the available columns in table '{{checking_import_vars}}':
 * @property integer $id
 * @property integer $type
 * @property integer $model_id
 * @property integer $import_var_id
 * @property integer $selected
 * @property integer $checked_id
 */
class CheckingImportVars extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return CheckingImportVars the static model class
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
		return '{{checking_import_vars}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('type, model_id, import_var_id, selected, checked_id', 'required'),
			array('type, model_id, import_var_id, selected, checked_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, type, model_id, import_var_id, selected, checked_id', 'safe', 'on'=>'search'),
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
			'type' => 'Type',
			'model_id' => 'Model',
			'import_var_id' => 'Import Var',
			'selected' => 'Selected',
			'checked_id' => 'Checked',
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
		$criteria->compare('type',$this->type);
		$criteria->compare('model_id',$this->model_id);
		$criteria->compare('import_var_id',$this->import_var_id);
		$criteria->compare('selected',$this->selected);
		$criteria->compare('checked_id',$this->checked_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /*
     * проверка  - выбрана ли галочка по данной проверке, у конкретного поля в шаблоне|проекте
     * checked_id - ID проверки из таблицы проверок
     * $modelID - ID модели проекта или шаблона, зависит от типа
     * $type - тип настройки - шаблон или проект
     * $importVarId - ID внутренней переменной
     */
    static function isSelectedCheking($type, $modelID, $importVarId, $checked_id){
        $sql = 'SELECT {{checking_import_vars}}.*
                FROM {{checking_import_vars}}
                WHERE type="'.$type.'"
                    AND import_var_id="'.$importVarId.'"
                    AND checked_id="'.$checked_id.'"
                    AND model_id="'.$modelID.'"
                    ';
        //echo $sql.'<br>';
        $result = Yii::app()->db->createCommand($sql)->queryRow();
        return $result;
    }

    /*
     * формируем форму выбора или уже установленных галочек выборок по полю в выбранном проекте или шаблоне
     * т.е. форма скрыта - кликаем на ссылку - появляется диалоговое окно выбора списка проверок
     * $importVarId - ID поля для которого устанавливаем список проверок
     * $type - тип т.е. для проекта эта запись или для шаблона эта настройка
     * $modelID - ID записи в моделе(ID проекта либо ID шаблона, если ID пустой, значит данных нет по полю - НОВЫЙ список)
     */
    public static function getFormChekingByField($type='',$modelID='',$importVarId='',$column=''){
        if(!empty($modelID) && !empty($type)){
            // проверка на существование уже выбранных позиций(выбранных галочек) по нкжному полю-столбцу
            $sql = 'SELECT *
                    FROM {{checking_import_vars}}
                    WHERE type="'.$type.'"
                        AND import_var_id="'.$importVarId.'"
                        AND model_id="'.$modelID.'"';
            $data = Yii::app()->db->createCommand($sql)->queryAll();
            //echo $sql.'<br>';
            //echo '<pre>'; print_r($data);
            // получаем список проверок и формируем по ним список чекбоксов
            $chekingList = CheckingImportVars::getChekingList();
            $checkboxListForm = '';
            foreach($chekingList as $j=>$box){

                $selected = CheckingImportVars::isSelectedCheking($type, $modelID, $importVarId, $box['id']);

                //$row = $data[$j+1];

                if($selected['selected']==1){
                    $checked = true;
                }else{
                    $checked = false;
                }

                $checkboxListForm.=CHtml::checkBox('ChekingVarID['.$importVarId.']['.$selected['checked_id'].']',$checked).$box['title'].'<br>';
            }

            // формируем форму диалогового окна
            $id = uniqid();
            $link = CHtml::link('Проверки', '#', array('class'=>$id, 'style'=>'margin-left:20px;', 'data-toggle'=>'modal', 'data-target'=>'#'.$id));//,'onclick'=>'js:$("#'.$id.'").show();'

            $forma = '<div id="'.$id.'" class="modal fade">
                        <div class="modal-header"><a class="close" data-dismiss="modal">×</a>
                        <h4>Список проверок по полю:'.$column.'</h4>'.$checkboxListForm.'
                     </div>';

            $forma_ = $link.$forma;

            return $forma_;
        }else{
            // получаем список проверок и формируем по ним список чекбоксов
            $chekingList = CheckingImportVars::getChekingList();

            $checkboxListForm = '';
            foreach($chekingList as $box){//
                $checkboxListForm.=CHtml::checkBox('ChekingVarID['.$importVarId.']['.$box['id'].']',true).$box['title'].'<br>';
            }

            // формируем форму диалогового окна
            $id = uniqid();
            $link = CHtml::link('Проверки', '#', array('class'=>$id,'style'=>'margin-left:20px;','data-toggle'=>'modal', 'data-target'=>'#'.$id));//,'onclick'=>'js:$("#'.$id.'").show();'

            $forma = '<div id="'.$id.'" class="modal fade">
                        <div class="modal-header"><a class="close" data-dismiss="modal">×</a>
                        <h4>Список проверок по полю:'.$column.'</h4>'.$checkboxListForm.'
                     </div>';

            $forma_ = $link.$forma;//'<div style="display:none;" id="'.$id.'">'.$checkboxListForm.'</div>';

            return $forma_;
        }

    }

    /*
     * получаем список проверок - весь
     */
    public static function getChekingList(){
        $sql = 'SELECT {{check}}.*
                FROM {{check}}';
        return Yii::app()->db->createCommand($sql)->queryAll();
    }

    /*
     * получаем список проверок по ID проекта
     * т.е. находим  какие настройки указаны по проекту
     */
    public static function getChekingListByProject($text_id){
        $sql = 'SELECT'
    }
}