<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Александр
 * Date: 14.11.12
 * Time: 10:56
 * To change this template use File | Settings | File Templates.
 */
/*
 *  класс для проверки равномерности распределения ключей
 */
class Uniformity{
    // текст для проверки на ошибки, текст для обработки
    public $sourceText;


    // запуск проверки по поиску ошибок пунктуации в тексте
    public function run(){
        return array('result'=>true, 'msg'=>'ok');
    }
}