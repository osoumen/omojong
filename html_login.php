<?php
//ヘッダー
$pagetitle = '';
$smarty->assign( 'pagetitle', $pagetitle );
$smarty->display( $g_tpl_path . 'header.tpl' );
?>
<div id="content_main">
<div class="general_container">
あなたやあなたのフォロワーがTwitterでつぶやいた発言からランダムに取り出した単語を並べ替えて、文を作る言葉遊びサイトです。
</div>
<div class="general_container">
<h3>参加するには</h3>
<div class="login_link">
<a href="twitter_request.php">Twitterでログイン</a>
</div>
</div>
<div id="pre_footer">
<a href="<?php echo $g_script.'?'.$pastlog_param_name.'=new';?>">みんながつぶメモナイズした文を見る</a>
</div>
</div>
<?php
//フッター
$smarty->display( $g_tpl_path . 'footer.tpl' );
