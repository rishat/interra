<?
	//security focus =)
	if(!$_SESSION['admin']){
		header("Location: " . SERVER_ROOT);
		exit;	
	}
	
	require("common/config.inc.php");
?>