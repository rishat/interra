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
	
	function adds($text){
		if (!get_magic_quotes_gpc()) return addslashes($text);
		return $text;
	}
	
	//INIT
	$forVersion = 183; //yeah!
	
	
	//figure out section settings (if any)
	//check access
	if(!$_SESSION['admin']){
		include("actions/404.php");	
	}else{	
	    //breadcrumbs
		$breadCrumbs[] = 'Config'; //this needs to change of course
		
		
		#### POST! ####
		#
		if($_POST){
		    
		    //init
		    $settings   = array();
		    $checkBoxes = array('ALLOW_FILES','ALLOW_KEYWORDS','ALLOW_TEASER_IMAGE','ENABLE_LJ','ALLOW_HANDINPUT','ENABLE_TICKER_C',
		                        'WYSIWYG','ENABLE_BMMAIL','ANTISPAM','COMMENT_TREE','SMARTY_DEBUG','SMARTY_ALLOW_CACHE',
		                        'SMARTY_COMPILE_CHECK','SMARTY_SUBDIRS','ALLOW_NICEURLS','ENABLE_PAGERS');
		                        
		    //common concept
		    foreach($checkBoxes as $checkBox){
		        if(!isset($_POST[$checkBox])){
		            $settings[$checkBox] = "false";
		        }else{
		            $settings[$checkBox] = "true";
		        }
		    }
		    
		    //now the _other_ exceptions that need more precise measures to deal with
		    if(strlen(trim($_POST['BLOGMASTER_P'])) == 0 or ($_POST['BLOGMASTER_P'] != $_POST['BLOGMASTER_P_2'])){
		        $settings['BLOGMASTER_P'] = "'".BLOGMASTER_P."'";
		    }else{
		        $settings['BLOGMASTER_P'] = "'".trim($_POST['BLOGMASTER_P'])."'";
		    }
		    
		    if(strlen(trim($_POST['BLOGMASTER_U'])) == 0){
		        $settings['BLOGMASTER_U'] = "'".BLOGMASTER_U."'";
		    }else{
		        $settings['BLOGMASTER_U'] = "'".trim($_POST['BLOGMASTER_U'])."'";
		    }
		    
		    if(strlen(trim($_POST['BLOGMASTER'])) == 0){
		        $settings['BLOGMASTER'] = "'".BLOGMASTER."'";
		    }else{
		        $settings['BLOGMASTER'] = "'".trim($_POST['BLOGMASTER'])."'";
		    }
		    
		    if($settings['ENABLE_LJ'] and (strlen(trim($_POST['LJ_USER'])) == 0 or strlen(trim($_POST['LJ_PASS'])) == 0)){
                $settings['ENABLE_LJ'] = 'false';   
                $settings['LJ_USER'] = "'".LJ_USER."'";
                $settings['LJ_PASS'] = "'".LJ_PASS."'";
		    }else{
                if(strlen(trim($_POST['LJ_USER'])) == 0){
                    $settings['LJ_USER'] = "'".LJ_USER."'";
                }else{
                    $settings['LJ_USER'] = "'".trim($_POST['LJ_USER'])."'";
                }
                
                if(strlen(trim($_POST['LJ_PASS'])) == 0){
                    $settings['LJ_PASS'] = "'".LJ_PASS."'";
                }else{
                    $settings['LJ_PASS'] = "'".trim($_POST['LJ_PASS'])."'";
                }
		    }
		    
		    if(strlen(trim($_POST['MAILBOT'])) == 0){
		        $settings['MAILBOT'] = "'".MAILBOT."'";
		    }else{
		        $settings['MAILBOT'] = "'".trim($_POST['MAILBOT'])."'";
		    }
		    
		    if(strlen(trim($_POST['COMMENT_DAYS'])) == 0){
		        $settings['COMMENT_DAYS'] = COMMENT_DAYS;
		    }else{
		        $settings['COMMENT_DAYS'] = (int)$_POST['COMMENT_DAYS'];
		    }
		    
		    if(strlen(trim($_POST['COMMENT_TREE_DEPTH'])) == 0){
		        $settings['COMMENT_TREE_DEPTH'] = COMMENT_TREE_DEPTH;
		    }else{
		        $settings['COMMENT_TREE_DEPTH'] = (int)$_POST['COMMENT_TREE_DEPTH'];
		    }
		    
		    if(strlen(trim($_POST['SMARTY_CACHE_TIME'])) == 0){
		        $settings['SMARTY_CACHE_TIME'] = SMARTY_CACHE_TIME;
		    }else{
		        $settings['SMARTY_CACHE_TIME'] = (int)$_POST['SMARTY_CACHE_TIME'];
		    }
		    
		    if(strlen(trim($_POST['PER_PAGE'])) == 0){
		        $settings['PER_PAGE'] = PER_PAGE;
		    }else{
		        $settings['PER_PAGE'] = (int)$_POST['PER_PAGE'];
		    }
		    
		    if(!$_POST['IMAGE_WIDTH'] or (int)$_POST['IMAGE_WIDTH'] == 0){
		        $_POST['IMAGE_WIDTH'] = 100;
		    }
		    $settings['IMAGE_WIDTH']  = (int)$_POST['IMAGE_WIDTH'];
		    
		    
		    //append parameters that we cannot change in the panel
		    $settings['DB_HOST'] = "'".DB_HOST."'";
		    $settings['DB_NAME'] = "'".DB_NAME."'";
		    $settings['DB_USER'] = "'".DB_USER."'";
		    $settings['DB_PASS'] = "'".DB_PASS."'";
		    
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
			
		    $settings['SERVER_ROOT']        = "'".$webRoot."'";
		    $settings['TEMPLATE_ROOT']      = "'templates/".$_POST['TEMPLATE_ROOT']."/'";
		    $settings['SMARTY_FORCE_FLUSH'] = SMARTY_FORCE_FLUSH;
		    
		    //this is cool too!
		    if(!defined('AUTO_KILL_COMMENTS')){
		        define('AUTO_KILL_COMMENTS',0);
		    }
		    $settings['AUTO_KILL_COMMENTS'] = AUTO_KILL_COMMENTS;
		    
		    $settings['RSS_HEADER']         = "'".adds(RSS_HEADER)."'";
		    $settings['RSS_DESC']           = "'".adds(RSS_DESC)."'";
		    $settings['TI_WIDTH']           = TI_WIDTH;
		    $settings['TI_HEIGHT']          = TI_HEIGHT;
		    $settings['USE_MAGICK']         = ((USE_MAGICK)?"true":"false");
		    $settings['MAGICK_PATH']        = "'".MAGICK_PATH."'";
		    
		    $settings['CHARSET']            = "'".CHARSET."'";
		    
		    if(!defined('PREFIX')){
		        define('PREFIX','int_');
		    }
		    $settings['PREFIX']             = "'".PREFIX."'";
		    $settings['SESSION_EXPIRE']     = SESSION_EXPIRE;
		    $settings['OFFSET']             = (int)OFFSET;
		    $settings['MY_LOCALE']          = "'".MY_LOCALE."'";
		    $settings['NOW']                = "time()";
		    $settings['ROOT_DIR']           = "substr(dirname(__FILE__),0,-6)";
		    
		    
		    $settings['PLANG']              = "'".PLANG."'";
		    
		    if(ENABLE_UMAIL){
                $settings['ENABLE_UMAIL'] = "true";
            }else{
                $settings['ENABLE_UMAIL'] = "false";
            }
            
            
            /****** DB UPGRADE ******/
            //this is cool too!
		    if(!defined('VERSION')){
		        define('VERSION',170);
		    }
		    
		    if(VERSION <= 170){
		        $db->Query("alter table int_comment add senderURL varchar(255) after senderMail");
		    }
		    $settings['VERSION'] = $forVersion;
            /************************/
		    
		    
		    ## CONFIG PREPROCESSORS ##
            #
            foreach(glob('actions/config_pre/*.php') as $entry) {
                include_once($entry);
            }
            #
            ## //
            
            
		    //write temp file
		    $tempContent = "<?\n";
		    foreach($settings as $key => $set){
		      $tempContent .= "define('".$key."',".$set.");\n";
		    }
		    $tempContent .= "?>";
		    
		    $tmpfname = tempnam(ROOT_DIR.'templates_c','interra_cf');
		    $handle = fopen($tmpfname, "w");
            fwrite($handle, $tempContent);
            fclose($handle);
            
            //move temp file to the current location
            if(stristr($_SERVER['SERVER_SOFTWARE'],'Win')) {
                unlink(ROOT_DIR.'common/config.inc.php');
            }
		    
		    
            //figure out permissions		    
		    $configfowner = fileowner('common/config.inc.php');

            
            
            ## CONFIG PREPROCESSORS ##
            #
            foreach(glob('actions/config_post/*.php') as $entry) {
                include_once($entry);
            }
            #
            ## //
            
            
		    if(!@rename($tmpfname,ROOT_DIR.'common/config.inc.php')){
		        
		        //redirect
                header("Location: ".SERVER_ROOT."config/?error=true");
                exit;
		        
		    }else{		    
            
                //make them cool
                @chmod ('common/config.inc.php',0644);
                @chown ('common/config.inc.php',$configfowner);

                //clear all cache
                $smarty->clear_compiled_tpl();
                $smarty->clear_all_cache();
                
                //redirect
                header("Location: ".SERVER_ROOT."config/?confirm=true");
                exit;
		    }
		    
		}
		#
		#### /POST ####
		
		
		//get a list of available templates
		$templates = array();
		$d = dir("templates");
        while (false !== ($entry = $d->read())) {
            if($entry != '.' and $entry != '..' and $entry != 'CVS' and $entry != '.svn' and $entry != 'system' and is_dir('templates'.DIRECTORY_SEPARATOR.$entry)){
                $templates[$entry] = $entry;
            }
        }
        $d->close();
        
        //get current
        $currentTemplate = explode('/',TEMPLATE_ROOT);
        
        $smarty->assign(array(
                                'templates'        => $templates,
                                'currentTemplate'  => $currentTemplate[1]
                            ));
		
		//tiny_mce present?
		if(is_dir(ROOT_DIR.'lib/tiny_mce')){
		   $smarty->assign('tiny_mce',true);
		}
		
        //select smarty template
        $smarty->assign("template","config.htm");	
	}
?>