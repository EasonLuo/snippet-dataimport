<?php
class FileHelper {
	
	/**
	 * rename the uploaded excel file in case it has been exists
	 *
	 * @param string $filename        	
	 * @return string
	 */
	public static function rename($filename) {
		$info = pathinfo ( $filename );
		$dirname = $info ['dirname'];
		$lastname = $info ['filename'] . time ();
		$extension = $info ['extension'];
		return $dirname . $lastname . "." . $extension;
	}
	
	/**
	 * upload excel file and store to a destination before parsing it
	 *
	 * @param string $file        	
	 * @return boolean success = true , fail = false
	 */
	public static function upload($file) {
		if ($file ["error"] != 0) {
			$this->handleError ( $file ["error"] );
			return false;
		}
		
		$filename = $file ['name'];
		$tmpname = $file ['tmp_name'];
		$newFileName = dirname(__FILE__).'/../../res/upload/' . $filename;
		if (file_exists ( $newFileName )) {
			$newFileName = self::rename ( $newFileName );
		}
		return move_uploaded_file ( $tmpname, $newFileName );
	}
	
	public static function write($filename, $mode, $content){
		$file = fopen($filename, $mode);
		fwrite($file, $content);
		fclose($file);
	}
}