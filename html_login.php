<?php
//ヘッダー
$pagetitle = 'TOP';
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->display( $g_tpl_path . 'header.tpl' );
?>
<div id="content_main">
<p>参加するには</p>
<a class="login_link" href="twitter_request.php">Twitterでログイン</a><br />
</div>
<div id="pre_footer">
<a href="<?php echo $g_script.'?'.$pastlog_param_name.'=new';?>">[過去ログ]</a>
</div>
<?php
//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
