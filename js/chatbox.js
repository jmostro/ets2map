var updateInterval = 15000;
var base_url = document.mybaseurl;

function decodeEntities(encodedString) {
    var textArea = document.createElement('textarea');
    textArea.innerHTML = encodedString;
    return textArea.value;
}

function displayMsgs(){
	var dmsgs = $("#messages-div");
	var cmsgs = $("#chat-messages");
	var msgs = null;

	$.getJSON(base_url+"/chatbox/getlasts", function(msgs){
		cmsgs.empty();	
		$.each(msgs, function(idx, m){	
			var htmlMsg = "<a href='"+base_url+"/users/view/info/"+m.uid+"' class='chatmsg-rank"+m.rank+"' title='"+m.fullname+"'>"+m.name+"</a>:&nbsp;<span class='chat-text' title='"+m.date+"'> "+decodeEntities(m.text)+"</span>";
			if (m.deleteable == 1){
				htmlMsg+="<a href='javascript:deleteMsg("+m.id+");' class='chatmsg-delete' title='Borrar mensaje'>&times;</a>";
			}
			var li = $("<li/>",{html: htmlMsg}).appendTo(cmsgs);
		});
		var height = dmsgs[0].scrollHeight;
  		dmsgs.scrollTop(height);
	});	
}

function updateMsgs(){
	displayMsgs();
	setTimeout(updateMsgs,updateInterval);
}

function deleteMsg(msgid){
	$.get(base_url+"/chatbox/delmsg/"+msgid).done(function() {
		updateMsgs();
	});
}

function sendMsg(){
	var inp = $("#chat_input");
	inp.prop('disabled', true);
	var msg = inp.val();
	$.post(base_url+"/chatbox/savemsg", {msgtext: msg}).done(function() {
	    inp.prop('disabled', false);
	    inp.val('');
	    updateMsgs();
	});
}

jQuery(function(){
	$('#chat_input').keyup(function(e){
    	if(e.keyCode == 13)
    	{
        	sendMsg();
    	}
	});

	updateMsgs()
});