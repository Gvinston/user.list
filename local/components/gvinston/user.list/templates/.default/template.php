<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<section class="fdb-block team-1" data-component="<?=$this->getComponent()->getName()?>">
    <?/*
    * Подключен тут jq, чтобы можно было быстро проверить задание
    */
    ?>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <div class="container">
        <div class="row text-center justify-content-center user_block-head">
            <div class="col-8">
                <h1>Users</h1>
                <a class='export_link-js' href="<?=$APPLICATION->GetCurPageParam('export=xml')?>">Выгрузить текущих пользователей в xml</a>
                <a class='export_link-js' href="<?=$APPLICATION->GetCurPageParam('export=xml&exportAll=Y')?>">Выгрузить всех пользователей в xml</a>
                <a class='export_link-js' href="<?=$APPLICATION->GetCurPageParam('export=csv')?>">Выгрузить текущих пользователей в сsv</a>
                <a class='export_link-js' href="<?=$APPLICATION->GetCurPageParam('export=csv&exportAll=Y')?>">Выгрузить всех пользователей в сsv</a>
            </div>
        </div>
        <div class="row-50"></div>
        <div class="row">
            <?foreach ($arResult['ITEMS'] as $arItem):?>
                <div class="col-sm-3 text-left">
                    <div class="fdb-box p-0">
                        <img alt="image" class="img-fluid rounded-0" src="<?=$arItem['PERSONAL_PHOTO']['SRC']?>">
                        <div class="content p-3">
                            <h3><strong><?=$arItem['FULL_NAME']?></strong></h3>
                        </div>
                    </div>
                </div>
            <?endforeach;?>
        </div>
    </div>
</section>