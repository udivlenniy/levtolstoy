<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Александр
 * Date: 14.11.12
 * Time: 10:49
 * To change this template use File | Settings | File Templates.
 */
/*
 * класс для проверки плотности вхождения ключей
 */
class Density{
    // текст для проверки на ошибки, текст для обработки
    public $sourceText;


    // запуск проверки по поиску ошибок уникальности  в тексте
    public function run(){
        return array('result'=>true, 'msg'=>'ok');
    }
}