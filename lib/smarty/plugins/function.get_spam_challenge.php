<?
	/***
		@author Kulikov Alexe <alex@pvl.at, alex@essentialmind.com>
	***/
 	function smarty_function_get_spam_challenge($params, &$smarty){

        $_SESSION['challenge'] = strval(rand(1111,9999));
        $_SESSION['challenge'] = str_replace("0","2",$_SESSION['challenge']);
        $_SESSION['challenge'] = str_replace("5","6",$_SESSION['challenge']);

		echo '<img src="'.SERVER_ROOT.'antispam/'.rand(0,10000).'/" alt="antispam challenge" />';
 	}
?>