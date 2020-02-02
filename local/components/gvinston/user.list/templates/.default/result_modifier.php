<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

// Проверка на наличие фотографии
foreach ($arResult['ITEMS'] as &$arItem)
{
    if(!empty($arItem['PERSONAL_PHOTO']['SRC']))
        continue;

    /*
     * Подобные картинки желательно хранить в специальном разделе,
     * который не зависит от шаблонов и компонентов (в целях безопасности)
     *
     * Загрузил сюда, чтобы было проще проверить задание
     */
    $arItem['PERSONAL_PHOTO'] = array(
        'SRC' => $this->GetFolder()  . '/img/default_photo.jpg'
    );
}
unset($arItem);
//