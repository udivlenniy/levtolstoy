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
        $model->model_id = $this->model_id;

        $criteria=new CDbCriteria;
        $criteria->compare('model',$this->model,true);
        $criteria->compare('model_id',$this->model_id);
        $criteria->order = 'id DESC';

        // отправили POST запрос на добавление нового комментария
//        if(isset($_POST['Comments'])){
//            $model->attributes = $_POST['Comments'];
//            if($model->validate()){
//
//                $model->save();
//
//                $dataProvider=new CActiveDataProvider('Comments', array(
//                    'criteria'=>$criteria,
//                    'pagination'=>array(
//                        'pageSize'=>Yii::app()->params['perPage'],
//                    ),
//                ));
//
//                Yii::app()->user->setFlash('msg','Спасибо, ваш комментарий успешно отправлен');
//                $this->renderPartial('comments', array('model'=>$model,'dataProvider'=>$dataProvider,));
//                Yii::app()->end();
//            }else{
//                $dataProvider=new CActiveDataProvider('Comments', array(
//                    'criteria'=>$criteria,
//                    'pagination'=>array(
//                        'pageSize'=>Yii::app()->params['perPage'],
//                    ),
//                ));
//                $this->renderPartial('comments', array('model'=>$model,'dataProvider'=>$dataProvider,));
//                Yii::app()->end();
//            }
//        }

        $dataProvider=new CActiveDataProvider('Comments', array(
            'criteria'=>$criteria,
            'pagination'=>array(
                'pageSize'=>Yii::app()->params['perPage'],
            ),
        ));

        $this->render('comments', array('model'=>$model,'dataProvider'=>$dataProvider,));
    }
}