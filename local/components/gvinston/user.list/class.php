<?
use \Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\UserTable;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
    die();
}

class UserList extends \CBitrixComponent
{
    public function GetUsers()
    {
       $select = !empty($this->arParams['USERS_PROP']) ? $this->arParams['USERS_PROP'] : array('*');
       $arUsers = $this->GetUserDB($select);

       $this->ResizeUserImages($arUsers);
       $this->SetFullName($arUsers);

       $this->arResult['ITEMS'] = $arUsers;
    }

    private function GetUserDB($select = array('*'), $filter = array(), $limit = 4, $order = array())
    {
        $nav = new \Bitrix\Main\UI\PageNavigation("nav-more-users");
        $nav->allowAllRecords(true)
            ->setPageSize(4)
            ->initFromUri();


        $UsersList = Bitrix\Main\UserTable::getList(
            array(
                'select'  => $select,
                'filter'  => $filter,
                'limit'   => $nav->getLimit(),
                'order'   => $order,
                'count_total' => true,
                'offset' => $nav->getOffset()
            )
        );

        $nav->setRecordCount($UsersList->getCount());

        $this->arResult['NAVIGATION'] = $nav;

        $arUsers = $UsersList->fetchAll();

        return $arUsers;
    }

    /*
     * Формирование массива для экспорта
     */
    private function GetUserForExport($exportAll)
    {
        if($exportAll == 'Y')
        {
            $this->arResult['ITEMS_EXPORT'] = Bitrix\Main\UserTable::getList(
                array(
                    'select'  => !empty($this->arParams['USERS_PROP']) ? $this->arParams['USERS_PROP'] : array('*'),
                    'filter'  => $filter = array(),
                    'order'   => $order = array(),
                )
            )->fetchAll();
        }
        else
            $this->arResult['ITEMS_EXPORT'] = $this->arResult['ITEMS'];
    }

    /*
     * Масштабирование фотографий
     */
    private function ResizeUserImages(&$arUsers)
    {
        $width = !empty($this->arParams['IMG_WIDTH']) ? $this->arParams['IMG_WIDTH'] : 205;
        $height = !empty($this->arParams['IMG_HEIGHT']) ? $this->arParams['IMG_HEIGHT'] : 205;

        foreach ($arUsers as &$arUser)
        {
            if(empty($arUser['PERSONAL_PHOTO']))
                continue;

            $img = CFile::ResizeImageGet($arUser['PERSONAL_PHOTO'], array('width'=>$width, 'height'=>$height), BX_RESIZE_IMAGE_EXACT, false, false,false, 75);

            $arUser['PERSONAL_PHOTO'] = array(
              'SRC' => $img['src'],
            );
        }
        unset($arUser);
    }

    /*
     * Обработка вывода полного имени без лишних пробелов=)
     */
    private function SetFullName(&$arUsers)
    {
        foreach ($arUsers as &$arUser)
        {
            $arNamePartOrder = array($arUser['LAST_NAME'], $arUser['NAME'], $arUser['SECOND_NAME']);
            $arNamePartCleaned = array_diff($arNamePartOrder, array(''));
            $arUser['FULL_NAME'] = implode(' ', $arNamePartCleaned);
        }
        unset($arUser);
    }

    private function ExportCsv($arToExport)
    {
        $fp = fopen($this->getPathForFile()['SERVER'] . '.csv', 'w');

        if(!$fp)
            return;

        foreach ($arToExport as $arItem) {
            fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($fp, $arItem);
        }

        fclose($fp);

        $this->arResult['FILE_SRC'] = $this->getPathForFile()['RELATIVE'] . '.csv';
    }

    private function ExportXml($arToExport)
    {
        $dom = new domDocument("1.0", "utf-8");
        $users = $dom->createElement("users");
        $dom->appendChild($users);

        foreach ($arToExport as $arItem)
        {
            $user = $dom->createElement('user');
            foreach ($arItem as $key => $value)
            {
                if(empty($value))
                    continue;

                $$key = $dom->createElement($key, $value);
                $user->appendChild($$key);
            }
            $users->appendChild($user);
        }

        $dom->save($this->getPathForFile()['SERVER'] . '.xml');

        $this->arResult['FILE_SRC'] = $this->getPathForFile()['RELATIVE'] . '.xml';
    }

    /*
     * Фильтра массива экспорта по ключам
     */
    private function modArrToExport($needPropsForExport)
    {
        $modArr = array();

        foreach ($this->arResult['ITEMS_EXPORT'] as $key => $item)
        {
            foreach ($needPropsForExport as $keyValue)
            {
                if(isset($item[$keyValue]))
                    $modArr[$key][$keyValue] = $item[$keyValue];
            }
        }

        return $modArr;
    }

    /*
     * Формирование пути к файлам
     */
    private function getPathForFile()
    {
        $request = Application::getInstance()->getContext()->getRequest();

        $arValueForFileName = array(
            $this->arResult['NAVIGATION']->getPageCount(),
            $this->arResult['NAVIGATION']->getCurrentPage(),
            $this->arResult['NAVIGATION']->getOffset(),
            $this->arResult['NAVIGATION']->getLimit(),
            $this->arResult['NAVIGATION']->getRecordCount(),
            $request->get('exportAll')
        );

        $relative = '/upload/export/users/users_' . implode('_', $arValueForFileName);

        return array(
            'SERVER' => $_SERVER['DOCUMENT_ROOT'] . $relative,
            'RELATIVE' => $relative,
        );
    }

    private function checkDirForFiles($checkDir)
    {
        if(!is_dir($checkDir))
            mkdir($checkDir, 0700, true);
    }

    private function countUsers()
    {
        return Bitrix\Main\UserTable::getCount();
    }

    public function ExportRoute($ExportName)
    {
        $needPropsForExport = array(
            'ID',
            'NAME',
            'SECOND_NAME'
        );

        $arToExport = $this->modArrToExport($needPropsForExport);

        switch ($ExportName) {
            case 'xml':
                $this->ExportXml($arToExport);
                break;
            case 'csv':
                $this->ExportCsv($arToExport);
                break;
        }
    }

    public function executeComponent()
    {
        global $APPLICATION;
        $this->checkDirForFiles($_SERVER['DOCUMENT_ROOT'] . '/upload/export/users/');

        $request = Application::getInstance()->getContext()->getRequest();

        $arParams["CACHE_TIME"] = empty($this->arParams["CACHE_TIME"]) ? 60*60*24 : $this->arParams["CACHE_TIME"];

        $cache_id = serialize(array($arParams, $request->getQueryList(), $request->getPostList(), array('users-' . $this->countUsers())));
        $obCache = new CPHPCache;
        if ($obCache->InitCache($this->arParams['CACHE_TIME'], $cache_id, '/'))
        {
            $vars = $obCache->GetVars();
            $this->arResult = $vars['arResult'];
        }
        elseif ($obCache->StartDataCache())
        {
            $this->GetUsers();
            $this->GetUserForExport($request->get('exportAll'));

            if(!empty($request->get('export')))
                $this->ExportRoute($request->get('export'));

            $obCache->EndDataCache(array(
                'arResult' => $this->arResult,
            ));
        }

        $catchedAjax = $request->isAjaxRequest() && $request->getPost('component_name') == $this->GetName() ? true : false;

        if ($catchedAjax)
            $APPLICATION->RestartBuffer();

            if(empty($request->get('export')))
            {
                $this->includeComponentTemplate();

                $APPLICATION->IncludeComponent(
                    "bitrix:main.pagenavigation",
                    "",
                    array(
                        "NAV_OBJECT" => $this->arResult['NAVIGATION'],
                        "SEF_MODE" => "N",
                    ),
                    false
                );
            }
            else
            {
                echo json_encode(array('href' => $this->arResult['FILE_SRC']));
            }

        if ($catchedAjax)
            die();
    }
}