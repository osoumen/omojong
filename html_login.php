<?php
//ヘッダー
$pagetitle = 'TOP';
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->display( $g_tpl_path . 'header.tpl' );
?>
<div id="content_main">
<a href="twitter_request.php">[twitterでログイン]</a><br />
</div>
<div id="pre_footer">
<a href="page_pastlog.php">[過去ログ]</a>
</div>
<?php
//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
