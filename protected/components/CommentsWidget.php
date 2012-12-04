<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Александр
 * Date: 04.12.12
 * Time: 16:55
 * To change this template use File | Settings | File Templates.
 */
/*
 * отображаем список комментариев по указанной моделе с формой добавления новых комментариев
 */
class CommentsWidget extends CWidget{
    // объявим внутренние переменные класса, которые будут использоваться для записи новых данных и отображения
    public $model; // МОДЕЛЬ к которой подвязываем список комментариев
    public $model_id;// ID моделе к которой подвязаны комментарии
    public $showForm = true;// отображать форму добавления комментария

    public function run(){

        $model = new Comments();
        $model->model = $this->model;
        $model->create = time();
        $model->model_id = $this->model_id;
        $model->user_id = Yii::app()->user->id;

        $criteria=new CDbCriteria;

        //$criteria->compare('id',$this->id);
        $criteria->compare('model',$this->model,true);
        $criteria->compare('model_id',$this->model_id);
        //$criteria->compare('user_id',$this->user_id);
        //$criteria->compare('create',$this->create);
        //$criteria->compare('text',$this->text,true);
        $criteria->order = 'id DESC';

        $dataProvider=new CActiveDataProvider('Comments', array(
            'criteria'=>$criteria,
            'pagination'=>array(
                'pageSize'=>Yii::app()->params['perPage'],
            ),
        ));

        $this->render('comments', array('model'=>$model,'dataProvider'=>$dataProvider,));
    }
}