<?php
class Model_Speech_Google implements Model_Speech_Interface
{
	public function speech($source, $target, $text)
	{
		$filesModel = new Model_Files();
		
		$url = "http://www.google.com/dictionary/json?callback=dict_api.callbacks.id100&q=".urlencode($text)."&sl=" . $source . "&tl=" . $target . "&client=te";
		
		$file = $filesModel -> load($url, Model_Files::FILE_HTML);
		
		$file = $this -> getSpeechFromFile($file['path']);
		
		if ( !empty($file) ) {
			
			return $filesModel -> load($file, Model_Files::FILE_MEDIA);
			
		}
		
		return null;
	}
	
	private function getSpeechFromFile($file = null)
	{
		if ( is_readable($file) ) {
			$data = file_get_contents($file);
			
			if ( ( substr($data, 0, 25) == "dict_api.callbacks.id100(" ) && ( substr($data, -10) == ",200,null)" ) )
			{
				$data = substr($data, 25, -10);
				
				$data = str_replace('\x22', '\"', $data);
				
				$data = $this->decode_phpstring($data);
				    
				$translates = Zend_Json::decode($data);
				
				if ( !empty($translates['primaries']) ) {
					
					foreach ( $translates['primaries'] as $primary ) {
						
						if ( !empty($primary['type']) && $primary['type'] == 'headword' ) {
							
							foreach ( $primary['terms'] as $term ) {
								
								if ( !empty($term['type']) && $term['type'] == 'sound' ) {
	
									return $term['text'];
									
								}
							}
							
						}
						
					}
				}
			}
			
			return null;
		}
	}
	
	private function decode_phpstring($str)
	{
		$str = preg_replace_callback('~\\\\([0-7]{1,3})~', create_function('$match', 'return chr(octdec($match[1]));'), $str);
		
		$str = preg_replace_callback('~\\\\(x[0-9A-Fa-f]{1,2})~', create_function('$match', 'return chr(hexdec($match[1]));'), $str);
		
		return $str;
	} 
}