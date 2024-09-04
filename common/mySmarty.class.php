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
	* 							All Rights Reserved. 							*
	* 																			*
	* Read the full license in the license.rtf file distributed along with		*
	* this software package.													*
	*****************************************************************************/

	/*
	* Class: mySmarty extends Smarty
	* Purpose: To prepare a global usage framework for the smarty class
	* @Author: Kulikov Alexey <alex@pvl.at, alex@ita-studio.com>
	***/
	class mySmarty extends Smarty{
		var $timer;
		
		/**
		 * mySmarty::MySmarty() - constructor
		 * 
		 * 							Set smarty environment
		 * 
		 * @return 
		 */
		function MySmarty(){
			$this->caching 	         = SMARTY_ALLOW_CACHE;		//disabled
			$this->cache_lifetime    = SMARTY_CACHE_TIME;		//1 hour
			$this->compile_check     = SMARTY_COMPILE_CHECK;	//check if needs recompiling
			$this->debugging         = SMARTY_DEBUG;			//start debugger	
			$this->template_dir      = array(TEMPLATE_ROOT,ROOT_DIR.'templates/system');			//template root
			$this->compile_dir	     = "templates_c";	        //templatec dir
			$this->config_dir        = array(TEMPLATE_ROOT,ROOT_DIR.'templates/system');			//config files directory
			$this->cache_dir         = "cache";		            //cache dir
			$this->use_sub_dirs      = SMARTY_SUBDIRS;			//safemode
			$this->config_booleanize = false;                   //templates look funny otherwise
			
			//temporarily
			$this->cache_modified_check = true;
			
			//start timer and init smarty
			$this->startTiming();
			$this->Smarty();

			$this->register_block("dynamic", "smarty_block_dynamic", false);
			$this->autoload_filters = array('output' => array('trimwhitespace'));
			
		}
		
		
		/**
		 * mySmarty::display()
		 * 
		 * Purpose: wrapper of the original function to set global variables
		 * 
		 * @param $template (string)
		 * @return (void)
		 */
		function display($template,$cacheid = ""){
			//global assigns
			$this->assignDefaults();
			
			//fetch template
			parent::display($template,$cacheid);
		}
		
		
		/**
		 * mySmarty::preFetch()
		 * 
		 * @param template
		 * @param string $cacheid
		 * @return 
		 **/
		function preFetch($template,$cacheid = ""){
			//global assigns
			$this->assignDefaults();
			
			//fetch template
			return parent::fetch($template,$cacheid);
		}
		
		
		/**
		 * mySmarty::assignDefaults()
		 * 
		 * Purpose: assign default values to global vars
		 * 
		 * @return 
		 */
		function assignDefaults(){
			global $db;
			
			$this->assign("CACHING",$this->caching);
			$this->assign("TEMPLATE_ROOT",SERVER_ROOT . TEMPLATE_ROOT);
			$this->assign("SERVER_ROOT",SERVER_ROOT);
			$this->assign("TIME",$this->stopTiming());
			$this->assign("TOTALDBQUERIES",(int)$db->totq);			# total DB queries needed to build page
			$this->assign("SELF",SERVER_ROOT . $_GET['data']);
			$this->assign("VERSION","1.80");
			
			if(function_exists('memory_get_usage')){
			   $this->assign("MEMORY",round(memory_get_usage()/(1024*1024),2));
		    }
		}
		
		
  		/**
 	 	* mySmarty::startTiming()
  		* 
		* Purpose: Start the page generation timer
		* 
  		* @return (void)
  		*/
  		function startTiming(){
	  		$microtime = microtime();
	  		$microsecs = substr($microtime, 2, 8);
	  		$secs = substr($microtime, 11);
	  		$this->timer = "$secs.$microsecs";
  		}
	
	
		/**
		 * mySmarty::stopTiming()
		 * 
		 * Purpose: Stop the page generation timer
		 * 
		 * @return (float)
		 */
		function stopTiming(){
    		$microtime = microtime();
  	 		$microsecs = substr($microtime, 2, 8);
   			$secs = substr($microtime, 11);
 	  		$endTime = "$secs.$microsecs";
  	  		return round(($endTime - $this->timer),4);
  		}
	}
	
	################ my own smarty data modifiers #################
	
	/**
	 * smarty_block_dynamic()
	 * 
	 * @param param (string)
	 * @param content (string)
	 * @param smarty (object)
	 * @return 
	 **/
	function smarty_block_dynamic($param, $content, &$smarty) {
    	return $content;
	}
?>