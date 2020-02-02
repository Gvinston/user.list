<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule("iblock"))
{
    ShowMessage(GetMessage("IBLOCK_ERROR"));
    return false;
}

// Поля пользователей
$UsersMap = (array) Bitrix\Main\UserTable::getMap();
$arValueFields = array();
foreach ($UsersMap as $key => $value)
{
    if(is_string($key))
        $arValueFields[$key] = $key;
}
//

$arComponentParameters = array(
    "GROUPS" => array(
    ),
    "PARAMETERS" => array(
        "USERS_PROP"   =>  array(
            "PARENT"    =>  "BASE",
            "NAME"      =>  'Получить поля пользователей',
            "TYPE"      =>  "LIST",
            "VALUES"    =>  $arValueFields,
            "REFRESH"   =>  "Y",
            "MULTIPLE"  =>  "Y",
        ),
        "IMG_HEIGHT" =>  array(
            "PARENT"    =>  "BASE",
            "NAME"      =>  'Высота аватарки пользователя',
            "TYPE"      =>  "STRING",
        ),
        "IMG_WIDTH" =>  array(
            "PARENT"    =>  "BASE",
            "NAME"      =>  'Высота аватарки пользователя',
            "TYPE"      =>  "STRING",
        ),
    ),
);