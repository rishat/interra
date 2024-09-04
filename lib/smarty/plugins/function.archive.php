<?
	/**
	 * smarty template function
	 * purpose: entry archive fetch resource, returns an array of year/month for which entries are available
	 *          in the blog. These can be then formatted with smarties date_format to fit the needed situation
	 *          accordingly.
	 * 
	 * package: InTerra Blog Machine
	 * author: Kulikov Alexey <alex [at] inses [dot] ru
	 * 
	 * params:
	 *   optional:		'var' smarty variable name (string) 		default: archive
	 *
	 * @example 
	 *  {archive var="myArch"}
	 *  {if $myArch}
	 *  <h3>Archive</h3>
	 *  <ul>
	 *	  {foreach from=$myArch item=arch}
	 *		<li><a href="{$SERVER_ROOT}{$arch|date_format:'%Y/%m/'}">{$arch|date_format:'%b. %Y'}</a><li>
	 *	  {/foreach}
	 *  </ul>
	 *  {/if}
	 *
	 * @version 1.0
	 * @copyright 2005, InSES Corporation and Alexey Kulikov
	 **/
	 
 	function smarty_function_archive($params, &$smarty){
		global $db;
			
		//check for optional variables
		//smarty variable name
		if(!empty($params['var'])){
			$variableName = (string)$params['var'];
		}else{
			$variableName = "archive";
		}
				
		//give data to smarty and terminate
		$smarty->assign($variableName,$db->getCol("SELECT DISTINCT FROM_UNIXTIME(intime,'%Y/%m/01') as myMonth FROM ".PREFIX."entry ORDER BY myMonth DESC"));		
		$smarty->assign("TOTALDBQUERIES",$db->totq);
		return;
	}
?>