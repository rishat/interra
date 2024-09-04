<?
    /* -*- Mode: PHP4; tab-width: 4; indent-tabs-mode: nil; basic-offset: 4 -*- */
    
    /*********************************************************
    *
    * Ajax Abstraction Layer (downgrade to PHP4)
    *
    * @author Kulikov Alexey <a.kulikov@gmail.com>
    * @version n/a
    * @since 23.04.2005
    *
    * @note Original Idea for this class came from Maxim "Maxx" Poltarak <maxx@e-taller.net>
    *       yet little is left here of his code, as this is a massive port to PHP4 with
    *       support for hosts with no wddx extension. If you run PHP5, then get MAX's code
    *       from http://dev.e-taller.net/ajax/
    *
    * <code>
    *    require_once("ajax.class.php");
    *    $R = new AjaxResponse;
    *    $R->set('foo','bar');
    *    $R->send();
    * </code>
    *********************************************************/
    class AjaxResponse{
        
    	/**
    	 * Data Storage
    	 *
    	 * @var unknown_type
    	 * @access private
    	 */
    	var $result;
    
    	
    	/**
    	 * Object Constructor
    	 *
    	 * @return AjaxResponse
    	 */
    	function AjaxResponse(){
    		$this->clear();
    	}
    
    	
    	/**
    	 * Resets the internal data storage
    	 *
    	 */
    	function clear(){
    		$this->result = array();
    	}
    
    	
    	/**
    	 * Returns some internal data value
    	 *
    	 * @param string $name Array Key
    	 * @return string
    	 */
    	function get($name){ 
    	    return $this->result[$name]; 
    	}
    	

    	/**
    	 * Sets some internal data into the result array
    	 *
    	 * @param string $name Array Key
    	 * @param string $value Array Value
    	 */
    	function set($name, $value){
    	    $this->result[$name] = $value; 
    	}
    	
    	
    	/**
    	 * An imitation of the wddx_serialize_value function,
    	 * in case the host does not support the packaged C
    	 * function, this one will be called instead. It is simple and
    	 * primitive and does not support ADTs
    	 *
    	 * @param array $value
    	 * @return string
    	 * @access private
    	 */
    	function wddx_serialize_value($value){
    	    //typecast
    	    $value = (array)$value;

    	    $toReturn = '<struct>';
    	    foreach($value as $key => $val){
                if(is_array($val)){
                    if(is_array($val[0])){
                        $toReturn .= '<var name="'.$key.'"><array length="'.count($val).'">'.$this->wddx_serialize_value($val).'</array></var>';
                    }else{
                        //$toReturn .= '<struct>';
                        foreach($val as $mk => $mv){
                            
                            if(is_int($mv)){
                                $type = 'number';
                            }else{
                                $type = 'string';
                            }

                            $toReturn .= '<var name="'.$mk.'"><'.$type.'>'.$mv.'</'.$type.'></var>';
                        }
                        //$toReturn .= '</struct>';
                    }
                }else{

                    if(is_int($val)){
                        $type = 'number';
                    }else{
                        $type = 'string';
                    }

    	            $toReturn .= '<var name="'.$key.'"><'.$type.'>'.$val.'</'.$type.'></var>';
                }
    	    }
    	    $toReturn .= '</struct>';
    	    
    	    return $toReturn;
    	}
    	
    	
    	/**
    	 * This function will serialize all the data gathered by the ajax server
    	 * and also prepend the data with the neccessary headers
    	 *
    	 * @return array
    	 * @access private
    	 */
    	function serializeResult(){
    	    //checking if the WDDX module is installed
    	    if(function_exists('wddx_serialize_value')){
    	        $text = wddx_serialize_value($this->result);
    	    }else{
                $text = '<wddxPacket version="1.0"><header /><data>'.$this->wddx_serialize_value($this->result).'</data></wddxPacket>';
    	    }
    	    
    	    $text = utf8_encode($text);
    		return array(
    			$text,
    			array(
    				"Content-type"		=> "text/xml"
    				//"Content-length"	=> strlen($text),
    			)
    		);
    	}
    	
    	
    	/**
    	 * As this is a port to PHP4, thus a send() method need to be called to
    	 * actually send the reply to the ajax client. In PHP5 this happened
    	 * automatically.
    	 *
    	 * @param bool $echo
    	 * @return string | echo
    	 */
    	function send($echo=true){
    	    list($result, $headers) = $this->serializeResult();
    
    		foreach($headers as $name => $content){
    		    header("$name: $content");
    		}
    		
    		if(!$echo){
                return $result;
    		}else{
                echo $result;   
    		}
    	}
    	
    }
?>