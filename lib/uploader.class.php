<?
	class uploader{
	
		/**
		 * uploader::uploader() - constructor
		 * 
		 * @return 
		 */
		function uploader(){
		
		}
		
		/**
		 * uploader::isJPEG() - check if the file to be uploaded in a jpeg
		 * 
		 * @param $filename
		 * @return 
		 */
		function isJPEG($filename){
			$size = @getimagesize($filename);
			if($size[2]==2){
				return $size;
			}
			return false;
		}
		
		
		/**
		 * uploader::isImage() - check if the file is an image
		 * 
		 * @param $filename
		 * @return 
		 */
		function isImage($filename){
			$size = @getimagesize($filename);
			if($size[2]==2 or $size[2]==3){
				return $size;
			}
			return false;
		}
		
		
		/**
		 * uploader::copyJPEG() - wrapper to decide between resizing image or not
		 * 
		 * @param $name
		 * @param $destination
		 * @param $width
		 * @param $height
		 * @return 
		 */
		function copyJPEG($name,$destination,$width=800,$height=600){
			$width = (int)$width;
			$height = (int)$height;
			
			if(!$width and !$height){
				return $this->copyFile($name, $destination);
			}else{
				if(!USE_MAGICK){
					return $this->resize_jpeg($name,$destination,$width,$height);
				}else{
					return $this->resize($name,$destination,$width,$height);
				}
			}
		}
		
		
		/**
		 * uploader::copyFile() - actual file uploader
		 * 
		 * @param $name
		 * @param $destination
		 * @return 
		 */
		function copyFile($name,$destination){
			if(!$result = move_uploaded_file($name,$destination)){
				$result = copy($name,$destination);
			}
			return $result; 
		}
		
		
		/**
		 * uploader::resize_jpeg() - resizes jpeg files
		 * 
		 * @param $image_file_path
		 * @param $new_image_file_path
		 * @param $max_width
		 * @param $max_height
		 * @return 
		 */
		function resize_jpeg($image_file_path, $new_image_file_path, $max_width, $max_height){ 
    		$return_val = 1; 

            $image_stats = getimagesize( $image_file_path ); 
            $FullImage_width = $image_stats[0]; 
            $FullImage_height = $image_stats[1]; 
            $img_type = $image_stats[2]; 
            
            switch( $img_type ) { 
                case 2: $src_img = ImageCreateFromJpeg($image_file_path); break; 
                case 3: $src_img = ImageCreateFromPng($image_file_path); break; 
                default: return false; 
            } 
            
            if ( !$src_img ) { 
                return false; 
            } 
            
            $ratio = ( $FullImage_width > $max_width ) ? (real)($max_width / $FullImage_width) : 1 ; 
            $new_width = ((int)($FullImage_width * $ratio)); //full-size width 
            $new_height = ((int)($FullImage_height * $ratio)); //full-size height 
            
            $ratio = ( $new_height > $max_height ) ? (real)($max_height / $new_height) : 1 ; 
            $new_width = ((int)($new_width * $ratio)); //mid-size width 
            $new_height = ((int)($new_height * $ratio)); //mid-size height 
            
            if ( $new_width == $FullImage_width && $new_height == $FullImage_height ) 
            copy ( $image_file_path, $new_image_file_path ); 
            
            $full_id = ImageCreateTrueColor( $new_width, $new_height ); 
            ImageCopyResampled( $full_id, $src_img, 0,0, 0,0, $new_width, $new_height, $FullImage_width, $FullImage_height ); 
            
            switch( $img_type ) { 
            case 2: $return_val = ImageJPEG( $full_id, $new_image_file_path, 80 ); break; 
            case 3: $return_val = ImagePNG( $full_id, $new_image_file_path ); break; 
            default: return false; exit; 
            } 
            
            ImageDestroy( $full_id ); 
            
            return ($return_val) ? TRUE : FALSE ;    
		}
		
		
		/**
		 * uploader::resize() - imageMagick resizer
		 * 
		 * @param $image_file_path
		 * @param $new_image_file_path
		 * @param $max_width
		 * @param $max_height
		 * @return 
		 **/
		function resize($image_file_path, $new_image_file_path, $max_width, $max_height){ 
			$sizes = getimagesize($image_file_path);
			$FullImage_width = $sizes[0];
			$FullImage_height = $sizes[1];
			
			// now we check for over-sized images and pare them down 
    		// to the dimensions we need for display purposes 
			$ratio =  ( $FullImage_width > $max_width ) ? (real)($max_width / $FullImage_width) : 1 ; 
    		$new_width = ((int)($FullImage_width * $ratio));    //full-size width 
    		$new_height = ((int)($FullImage_height * $ratio));  //full-size height 
    
			//check for images that are still too high 
    		$ratio =  ( $new_height > $max_height ) ? (real)($max_height / $new_height) : 1 ; 
    		$new_width = ((int)($new_width * $ratio));    //mid-size width 
    		$new_height = ((int)($new_height * $ratio));  //mid-size height 
			
			// now, before we get silly and 'resize' an image that doesn't need it... 
			$reply = system(MAGICK_PATH."convert -resize " . $new_width . "x" . $new_height . " " . $image_file_path . " " . $new_image_file_path);
			
		}
		
		
		/**
		 * uploader::preparePath() - check if the desired path exists, if not, create it
		 *
		 * @param $uploadPath 	-- actual path to be checked for existence
		 * @param $myLog 		-- reference to the Log Writing object
		 * @param $chmod		-- default chmod for the directory created
		 * @return void
		 **/
		function preparePath($uploadPath, $chmod = 0777){
			//check if root directory exists
			if(!is_dir($uploadPath)){
				@mkdir($uploadPath, $chmod);
				@chmod($uploadPath, $chmod);
			}
		}
		
		/*
		 * this function is a simple file remover in case they are being overwritten
		 */
		function checkImage($path){
			//check if file with this name exists
			if(file_exists($path."image.jpg")){
				@unlink($path."image.jpg");
				@unlink($path."thumb.jpg");
			}
		}
	}
?>