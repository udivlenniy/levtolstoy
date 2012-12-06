<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Александр
 * Date: 06.12.12
 * Time: 17:57
 * To change this template use File | Settings | File Templates.
 */
class Export extends CFormModel{

    // тип экспорта, либо проект целиком=все тексты либо лишь ключев
    public $type = 'project';// по-умолчанию экспортируем проект целиком, все его тексты

    // ID проекта, который будем обрабатывать
    public $project;

    // если указано какое-то конкретное задание, экспортируем лишь это задание
    public $text;

    // экспортируем список ключевиков по зданию
    public function exportKeyWordsByText($text){

    }

    /*
     * создаём файл экспорта и записываем в нём полученный ранее массив в ввиде CSV файла
     * и возвращаем ссылку на адрес файла на диске
     * $data - массив массивов, т.е. каждый элемент маасива это массива $data[] = array("1"=>"sdfsdf","2"=>"ddddd")
     */
    public function saveToCSV($data){

        $name = time().'.csv';
        $path = Yii::getPathOfAlias('webroot.export').'/';
        $fp = fopen($path.$name, 'w');

        foreach ($data as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);

        // ссылка на
        return $path.$name;
    }
}