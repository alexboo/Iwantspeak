<?php
class Model_Translate_Sozdikkz implements Model_Translate_Interface
{	
	public function translate($source, $target, $text)
	{
		$filesModel = new Model_Files();
		
		//$url = "http://sozdik.kz/suggest/" . $source . "/" . $target . "/" . urlencode($text) . "/?tm=1317716407844";
		
		$url = "http://sozdik.kz/ru/dictionary/translate/" . $source . "/" . $target . "/" . urlencode($text) . "/";
		
		$file = $filesModel -> load($url, Model_Files::FILE_HTML, true);
		
		$data = file_get_contents($file['path']);
		
		if ( preg_match_all('/<a[^<>]{1,}langfrom="' . $target . '"[^<>]{1,}>([^>]{1,})<\/a>/i', $data, $translates) ) {
			
			if ( !empty($translates[1]) )
				
				return $translates[1];
			
		}
		
		return null;
	}
}