<?
	/**
	* smarty template function
	* name: gravatar fetch function
	* author: Sergey "Dargor" Korolev <to [@] dargor [dot] ru>
	* 
	* params:
	*	one of this must be declared:
	* 		'email' e-mail (string) 					default: null
	* 		'default' avatar url if empty email (string)			default: null
	*	optional:
	*		'rating' rating of avatar = G or PG or R or X (string)		default: null
	*		'size' width&height of avatar >=1 and <=80 (int)		default: null
	*		'border' RGB color (string)					default: null
	* @version 1.0
	**/
	 
	function smarty_function_gravatar($params, &$smarty)
	{
		if (!empty($params['email']) || !empty($params['default'])){
			$result = "<img class=\"gravatar\" src=\"http://http://gravatar.com/avatar.php?";
			
			if (!empty($params['email'])){
				$result.= "gravatar_id=".md5($params['email'])."&";
			}
			
			if (!empty($params['rating']) && (
				($params['rating']=="G") || 
				($params['rating']=="PG") || 
				($params['rating']=="R") || 
				($params['rating']=="X"))){
				$result.= "rating=".$params['rating']."&";
			}
		
			if (!empty($params['size']) && ($params['size'] >= 1) && ($params['size']<=80)){
				$result.= "size=".$params['size']."&";
			}
		
			if (!empty($params['default'])){
				$result.= "default=".urlencode($params['default'])."&";
			}
		
			if (!empty($params['border'])){
				$result.= "border=".$params['border'];
			}
		
			$result.= "\"";
			
			if (!empty($params['size']) && ($params['size'] >= 1) && ($params['size']<=80)){
				$result.= " width=".$params['size']." height=".$params['size'];
			}
			
			$result.= " alt=\"gravatar\" />";
		}
		return $result;
	}
?>
