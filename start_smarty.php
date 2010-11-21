<?php

define('SMARTY_DIR', '/usr/local/lib/smarty/');
define('SMARTY_TEMP', '/var/www/smarty/');
require_once(SMARTY_DIR . 'Smarty.class.php');

//Smartyのインスタンスを作成
$smarty = new Smarty();

//各ディレクトリの指定
$smarty->template_dir = SMARTY_TEMP . 'templates';
$smarty->compile_dir = SMARTY_TEMP . 'templates_c';
$smarty->config_dir = SMARTY_TEMP . 'configs';
$smarty->cache_dir = SMARTY_TEMP . 'cache';

$smarty->default_modifiers = array('escape');

//キャッシュ機能の有効化
//$smarty->caching = true;
