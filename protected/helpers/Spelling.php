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


    // запуск проверки по поиску ошибок орфографии  в тексте
    public function run(){
        file_put_contents('Spelling.txt','');
        return array('result'=>true, 'msg'=>'ok');
    }
}