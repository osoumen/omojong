<?php
require_once 'globals.php';
require_once 'common.php';

$pagetitle = '中断';
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->display( 'tpl/header.tpl' );
?>
<div id="content_main">
<div class="general_container">
進行中のゲームを中断して最初からやり直しますか？<br />
<a href="page_start.php?p=<?php echo $_REQUEST[$gameid_param_name] ?>" target=_top>[ＯＫ]</a><br />
</div>
<div id="pre_footer">
<a href="<?php echo $g_script; ?>" target=_top>戻る</a>
</div>
</div>
<?php
$smarty->display( 'tpl/footer.tpl' );
