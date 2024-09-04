<?
	/****************************************************************************
	* Software distributed under the License is distributed on an "AS IS" 		*
	* basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the 	*
	* License for a specific language governing rights and limitations under	*
	* the License. 																*
	* 																			*
	*	The Original Code is - InTerra Blog Machine   				            *
	* 																			*
	*	The Initial Developer of the Original Code is 							*
	* 																			*
	* 			Kulikov Alexey <alex [at] essentialmind [dot] com>	 			*
	* 																			*
	* 				All Rights Reserved // www.inses.ru							*
	* 																			*
	* Read the full license in the license.rtf file distributed along with		*
	* this software package.													*
	*****************************************************************************/
	
	session_start();
	
	if($_POST['user'] == BLOGMASTER_U and $_POST['pass'] == BLOGMASTER_P){
		$_SESSION['admin'] = true;	
		setcookie("adminExpire",md5(BLOGMASTER_U)."%%separator%%".md5(BLOGMASTER_P),time()+SESSION_EXPIRE,"/");
	}
	
	session_write_close();
	
	if(!empty($_SERVER['HTTP_REFERER'])){
		header("Location: " . $_SERVER['HTTP_REFERER']);
	}else{
		header("Location: " . SERVER_ROOT);
	}
	exit;
?>