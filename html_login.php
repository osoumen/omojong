<?php
//ヘッダー
$pagetitle = 'TOP';
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->display( $g_tpl_path . 'header.tpl' );
?>
<a href="twitter_request.php">[twitterにログイン]</a><br>
<br>
<a href="page_pastlog.php">[過去ログ]</a>
<?php
//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
