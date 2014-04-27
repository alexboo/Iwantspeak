<?php
class Model_Translate_Google implements Model_Translate_Interface
{
	public function translate($source, $target, $text)
	{
		$filesModel = new Model_Files();

		//$url = "http://translate.google.com/translate_a/t?client=t&text=".urlencode($text)."&sl=" . $source . "&tl=" . $target . "&pc=0&oc=1";

		$url = "http://www.google.com/dictionary/json?callback=dict_api.callbacks.id100&q=".urlencode($text)."&sl=" . $source . "&tl=" . $target . "";
		
		$file = $filesModel -> load($url, Model_Files::FILE_HTML);
		
		return $this -> getTranslateFromFile($file['path']);
	}
	
	private function getTranslateFromFile($file = null)
	{
		if ( is_readable($file) ) {
			
			$data = file_get_contents($file);
			
			if ( ( substr($data, 0, 25) == "dict_api.callbacks.id100(" ) && ( substr($data, -10) == ",200,null)" ) )
			{
				$data = substr($data, 25, -10);
				
				$data = str_replace('\x22', '\"', $data);
				
				$data = $this->decode_phpstring($data);
				
				$translates = Zend_Json::decode($data);
				
				$results = array();
				
				if ( !empty($translates['primaries']) ) {
					
					foreach ( $translates['primaries'] as $primary ) {
						
						if ( !empty($primary['type']) && $primary['type'] == 'headword' ) {
							
							foreach ( $primary['entries'] as $entry ) {
								
								if ( !empty($entry['type']) && $entry['type'] == 'container' ) {
	
									foreach ( $entry['entries'] as $translate ) {
	
										if ( !empty($translate['terms'][0]['text']) )
										
											$results[] = $translate['terms'][0]['text'];
										
									}
									
								}
							}
							
						}
						
					}
				}
				
				if ( !empty($results) )
				
					return $results;
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