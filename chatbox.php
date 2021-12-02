<?php
	session_start();
	require_once("inc/functions.php");
	require_once("inc/model.php");
	require_once("inc/config.php");
	
	$action = filter_input(INPUT_GET,'a',FILTER_SANITIZE_STRING);
	switch ($action) {
		case 'savemsg':
			if (isLoggedIn()){
				$text = filter_input(INPUT_POST,'msgtext',FILTER_SANITIZE_STRING);
				saveChatMsg($text);
			}
			break;
		case 'getlasts':
			if (isLoggedIn()){
				echo json_encode(getChatMsgs());

			}
			break;
		case 'getmsg':
			if (isLoggedIn()){
				$id = filter_input(INPUT_GET,'id',FILTER_SANITIZE_NUMBER_INT);
				echo json_encode(getChatMsg($id));
			}
		case 'delmsg':
			if (isLoggedIn()){
				$id = filter_input(INPUT_GET,'id',FILTER_SANITIZE_NUMBER_INT);
				$msg = getChatMsg($id);
				if (canEdit($msg['uid'])){
					deleteChatMsg($id);
				}
			}
			break;
		default:	
			break;
	}
?>