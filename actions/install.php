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
	*****************************************************************************/
	
	//include libraries
	define('ROOT_DIR', substr(dirname(__FILE__),0,-7) . DIRECTORY_SEPARATOR);
	require(ROOT_DIR . "lib/smarty/Smarty.class.php");
	
	$smarty = new Smarty();
	$smarty->template_dir = ROOT_DIR . "common";
	$smarty->config_dir   = ROOT_DIR . "common";
	$smarty->use_sub_dirs = false;
	$smarty->compile_dir  = ROOT_DIR . "templates_c";
	
	
	function adds($text){
		if (!get_magic_quotes_gpc()) return addslashes($text);
		return $text;
	}
	
	
	//is this a POST request? 
	if($_POST){ //yes, install the damn thing!
		
		$errors = array();
		
		## check for errors ####
		//open DB connections
		require(ROOT_DIR . "lib/pear/DB.php");
		
		$db = DB::connect(array(
	    	'phptype'  => 'mysql',
	    	'dbsyntax' => false,
	    	'protocol' => false,
	    	'hostspec' => $_POST['dbhost'],
	    	'database' => $_POST['dbname'],
	    	'username' => $_POST['dbuser'],
	    	'password' => $_POST['dbpass']
		));
		
		if(DB::isError($db)){
			$errors[1] = true;	//set database connection error to TRUE
		}
		
		//check if all data was filled out
		if(empty($_POST['blog_user']) or empty($_POST['blog_pass']) or empty($_POST['blog_mail']) or empty($_POST['mailbot']) or empty($_POST['rsshead']) or empty($_POST['rssdesc'])){
			$errors[2] = true;
		}
		
		//check if can write config file
		if($file = @fopen(ROOT_DIR . "common/temp.file","w")){
			fclose($file);	
			unlink(ROOT_DIR . "common/temp.file");
		}else{
			$errors[3] = true;
		}

		//check if can create directories
		@mkdir(ROOT_DIR."cache",0777);
		if(!file_exists(ROOT_DIR."cache")){
			$errors[4] = true;
		}
		
		//maintain prefix
		$_POST['prefix'] = str_replace(' ','',$_POST['prefix']);
		
		
		//no errors? lets dance!
		if(empty($errors)){
			$confirm = array();
			
			//create database structure
			$file = fopen(ROOT_DIR . "common/tables.sql","r");
			$sql = fread($file,filesize(ROOT_DIR . "common/tables.sql"));
			fclose($file);
			
			$sql = explode(";",$sql);
			foreach($sql as $statement){
				$statement = trim($statement);
				
				if(!empty($statement)){
					$db->query(str_replace('".PREFIX."',$_POST['prefix'],$statement));
				}
			}
			
			
			//figure out server root
			$webPath = str_replace(ROOT_DIR,"",str_replace("index.php","",$_SERVER['SCRIPT_NAME']));
			
			if(empty($webPath)){
				$webPath = "/";
			}
			
			$webServer = str_replace("http://","",$_SERVER['HTTP_REFERER']);
			$pos = strpos($webServer, "/");
		
			if ($pos === false){
				$webRoot = "http://" . $webServer . $webPath;
			}else{
				$webRoot = "http://" . substr($webServer,0,$pos) . $webPath;
			}
			
			
			//figure out lj settings
			if(!empty($_POST['lj_user']) and !empty($_POST['lj_pass'])){
				$ljEnable = 'true';	
			}else{
				$ljEnable = 'false';	
			}
			
			
			//write config file
			$smarty->assign(array(
									"db"		=> array("host"=>$_POST['dbhost'],"name"=>$_POST['dbname'],"user"=>$_POST['dbuser'],"pass"=>$_POST['dbpass'],"prefix"=>$_POST['prefix']),
									"root"		=> $webRoot,
									"mailbot"	=> $_POST['mailbot'],
									"admin"		=> array("mail"=>$_POST['blog_mail'],"pass"=>$_POST['blog_pass'],"user"=>$_POST['blog_user']),
									"rss"		=> array("header"=>adds($_POST['rsshead']),"desc"=>adds($_POST['rssdesc'])),
									"lj"		=> array("enable"=>$ljEnable,"user"=>$_POST['lj_user'],"pass"=>$_POST['lj_pass'])
									));
			
			if($_GET['lang'] == 'eng'){
			    $smarty->assign('lang','.en');
			}
			
			$myConfigFile = $smarty->fetch("config_default.inc.php");
			
			$file = fopen(ROOT_DIR . "common/config.inc.php","w");
			fwrite($file,$myConfigFile);
			fclose($file);
			
			//create directories
			@mkdir(ROOT_DIR . "cache",0777);
			@mkdir(ROOT_DIR . "files",0777);
			@mkdir(ROOT_DIR . "rss",0777);
			@chmod(ROOT_DIR . "common/config.inc.php",0644);
			
			//permission magic 1.60
			$configfperms = substr(sprintf('%o', @fileperms(ROOT_DIR.'common/install.php')), -4);
		    @chmod (ROOT_DIR.'common/config.inc.php',0644);
            @chown (ROOT_DIR.'common/config.inc.php',$configfowner);
			
			if(@!chmod(ROOT_DIR . "templates_c",0777)){
				$confirm[1] = true;	
			}else{
				$confirm = true;	//typecast
			}
			
			$smarty->assign("confirm",$confirm);
		}else{
			$smarty->assign("errors",$errors);	
		}
	}
	
	//check for the existence of iconv (V1.1)
	//if(!function_exists('iconv')){
	//	$smarty->assign("noLJ",true);	
	//}
	
	//error check
	if(!is_writable(ROOT_DIR.'templates_c') or !is_writable(ROOT_DIR.'common')){
	    echo "<b>Rus:</b> Укажите CHMOD 777 для папок templates_c и common!<br />
	          <b>Eng:</b> Please CHMOD 777 on directories templates_c and common!";
	}
	
	$smarty->display("install.htm");
?>