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
	error_reporting(E_ALL ^ E_NOTICE);
	session_start();
	
	require("common/config.inc.php");
	require("lib/smarty/Smarty.class.php");
	require("common/mySmarty.class.php");
	
	//security focus =)
	if(!$_SESSION['admin']){
		header("Location: " . SERVER_ROOT);
		exit;	
	}
	
	/*
	echo '<pre>';
    print_r($_FILES);
    print_r($_POST);
	exit;
	*/
	
	/***
	 Now, initialize some mandatory resources, such as the template engine =)
	***/
	$smarty = new mySmarty();
	$smarty->caching = false;
	
	/***
	 Process POST request =)
	***/
	if($_POST){
        
		if($_FILES['myFile']['tmp_name']){
		
		    ## FILE ADD PREPROCESSORS ##
            #
            foreach(glob('actions/file_pre/*.php') as $entry) {
                include_once($entry);
            }
            #
            ## //
		
			//init library
			require("lib/uploader.class.php");			# image uploader
			
			//create uploader
			$myUpload = new uploader();		
			
			//check for existence of root path
			$uploadPath = "files" . DIRECTORY_SEPARATOR . $_SESSION['tempEntryID'] . DIRECTORY_SEPARATOR;
			$myUpload->preparePath($uploadPath);
			
			//create url transformation handler
			require("lib/translit/php/translit.php");
			$myTrans = new Translit();
		
			//prepare file name
			foreach($_FILES['myFile']['name'] as $key => $val){
			    if(!$_FILES['myFile']['error'][$key]){
			    
			        $_FILES['myFile']['name'][$key] = $myTrans->UrlTranslit($_FILES['myFile']['name'][$key],false,true);
			
                    //bogus check
                    if(empty($_FILES['myFile']['name'][$key])){
                        $_FILES['myFile']['name'][$key] = "no_name";	
                    }
                    
                    //check if file with this name exists
                    if(!file_exists($uploadPath.$_FILES['myFile']['name'][$key])){
                        //check file to be uploaded is a jpeg, and create a thumbnail for it
                        $myUpload->copyFile($_FILES['myFile']['tmp_name'][$key],$uploadPath.$_FILES['myFile']['name'][$key]);
                        
                        //chmod
                        @chmod($uploadPath.$_FILES['myFile']['name'][$key], 0776);
                        
                        //now, if this is a picture --> make a thumbnail!
                        if($myUpload->isImage($uploadPath.$_FILES['myFile']['name'][$key])){
                            $myUpload->copyJPEG($uploadPath.$_FILES['myFile']['name'][$key], $uploadPath.'THUMB_'.$_FILES['myFile']['name'][$key], IMAGE_WIDTH, IMAGE_WIDTH);
                        }
                    }   
			    }
			}			
			
			
			## FILE ADD POSTPROCESSORS ##
            #
            foreach(glob('actions/file_post/*.php') as $entry) {
                include_once($entry);
            }
            #
            ## //
		}
		
		//redirect
		header("Location: ".SERVER_ROOT."fileManager.php");
		exit;
	}
	
	if($_GET['action'] == 'delete'){
		@unlink("files" . DIRECTORY_SEPARATOR . (int)$_GET['entry'] . DIRECTORY_SEPARATOR . $_GET['file']);	
	}
	
	/***
	 Read File List =)
	***/
	$myFiles = array();
	
	if(is_dir("files" . DIRECTORY_SEPARATOR . $_SESSION['tempEntryID'] . DIRECTORY_SEPARATOR)){
		$myDir = dir("files" . DIRECTORY_SEPARATOR . $_SESSION['tempEntryID'] . DIRECTORY_SEPARATOR);
		while (false !== ($entry = $myDir->read())) {
			$data = array();
			if($entry != "." and $entry != ".."){
				$data['size'] = ceil(filesize("files" . DIRECTORY_SEPARATOR . $_SESSION['tempEntryID'] . DIRECTORY_SEPARATOR . $entry) / 1024);
				$data['name'] = $entry;
				$data['path'] = "files" . DIRECTORY_SEPARATOR . $_SESSION['tempEntryID'] . DIRECTORY_SEPARATOR . $entry;
				
				//thumb check
				if(strstr($data['name'],'THUMB_') !== false){
				    $data['thumb'] = true;
				}
				
				$myFiles[] = $data;
			}
		}
		$myDir->close();
		
		$smarty->assign("myFiles",$myFiles);
	}
	
	$smarty->assign("frameSize",count($myFiles)+1);
	$smarty->display("addFiles.htm");
?>