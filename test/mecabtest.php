<?php
dl('mecab.so');

$t = new MeCab_Tagger();
$str = 'すもももももももものうち';

echo $t->parse($str);
