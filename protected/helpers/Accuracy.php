<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Александр
 * Date: 14.11.12
 * Time: 10:51
 * To change this template use File | Settings | File Templates.
 */
/*
 * класс для проверки точности вхождения ключей
 */
class Accuracy{
    // текст для проверки на ошибки, текст для обработки
    public $sourceText;


    // запуск проверки по поиску ошибок пунктуации в тексте
    public function run(){
        return array('result'=>true, 'msg'=>'ok');
    }
}