vali = {}
function validateMsg(id, msg, status){
	$('#' + id + 'msg').html(msg);
	$('#' + id + 'msg').attr('class', status? 'loginB1': 'false');
}
function valiForm(){
	for(var i in vali){
		$('#' + i).focus(); $('#' + i).blur();
		if(!vali[i]) return false;
	}
	return true;
}