<?php
//ヘッダー
$pagetitle = 'TOP';
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->display( $g_tpl_path . 'header.tpl' );
?>
<div id="content_main">
<div class="general_container">
<h3>参加するには</h3>
<div class="login_link">
<a href="twitter_request.php">Twitterでログイン</a>
</div>
</div>
<div id="pre_footer">
<a href="<?php echo $g_script.'?'.$pastlog_param_name.'=new';?>">今までの結果を見る</a>
</div>
</div>
<?php
//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
