<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Александр
 * Date: 23.11.12
 * Time: 10:13
 * To change this template use File | Settings | File Templates.
 */
class AmountText{
    // текст для проверки на ошибки, текст для обработки
    public $sourceText;
    public $title; // заголовок поля, которое проверяем классом

    // запуск проверки по поиску ошибок уникальности  в тексте
    public function run(){

        return array('result'=>true, 'msg'=>'ok');
    }
}