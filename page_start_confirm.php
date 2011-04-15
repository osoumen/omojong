<?php
require_once 'globals.php';
require_once 'common.php';

$pagetitle = '中断';
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->display( 'tpl/header.tpl' );
?>
<hr>
進行中のゲームを中断して最初からやり直しますか？<br>
<a href="page_start.php?p=<?php echo $_REQUEST[$gameid_param_name] ?>" target=_top>[ＯＫ]</a><br>
<a href="<?php echo $g_script; ?>" target=_top>[戻る]</a>
<?php
$smarty->display( 'tpl/footer.tpl' );
