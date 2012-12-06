<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Александр
 * Date: 14.11.12
 * Time: 10:46
 * To change this template use File | Settings | File Templates.
 */
/*
 * класс для проверки орфографии в тексте
 */
class Spelling{

    // текст для проверки на ошибки, текст для обработки
    public $sourceText;
    public $title; // заголовок поля, которое проверяем классом

    // запуск проверки по поиску ошибок орфографии  в тексте
    public function run(){

        return array('result'=>false, 'msg'=>'Очень много орфографических ошибок обнаружено при проверке текста');
    }
}