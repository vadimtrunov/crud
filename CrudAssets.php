<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 12.11.15
 * Time: 16:58
 */

namespace tv88dn\crud;


use yii\web\AssetBundle;

class CrudAssets extends AssetBundle
{

    public $sourcePath = '@tv88dn/crud';

    public $js = [
        'assets/ajax_loader.js'
    ];
}