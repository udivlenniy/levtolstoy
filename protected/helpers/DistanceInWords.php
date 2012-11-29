<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Александр
 * Date: 14.11.12
 * Time: 10:54
 * To change this template use File | Settings | File Templates.
 */
/*
 * класс для проверки расстояни между словами в ключевике
 */
class DistanceInWords{
    // текст для проверки на ошибки, текст для обработки
    public $sourceText;
    public $title; // заголовок поля, которое проверяем классом

    // запуск проверки по поиску ошибок пунктуации в тексте
    public function run(){

        return array('result'=>true, 'msg'=>'ok');
    }
}