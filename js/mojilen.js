function mojilen(str,ansnum,tweetmsg) {
	limitS=tweetmsg.length;
	val=str.length + limitS;
	if (val<=limitS) {
		document.getElementById('twitter-button'+ansnum).disabled = true;
	}
	else {
		document.getElementById('twitter-button'+ansnum).disabled = false;
	}
	//文字数表示
	document.getElementById('msg'+ansnum).innerHTML=
	"<span>"+val+"</span>文字";
}

function mojirest(str,ansnum,tweetmsg) {
	limitM=140;
	limitS=tweetmsg.length;
	val=str.length + limitS;
	if (val<=limitS || val>limitM) {
		document.getElementById('twitter-button'+ansnum).disabled = true;
	}
	else {
		document.getElementById('twitter-button'+ansnum).disabled = false;
	}
	//残り文字数表示
	document.getElementById('msg'+ansnum).innerHTML=
	"残り<span>"+(limitM-val)+"</span>文字";
}
