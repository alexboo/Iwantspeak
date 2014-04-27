<?php
class Model_Translate_Abbyy implements Model_Translate_Interface
{
	public function translate($source, $target, $text)
	{
		$filesModel = new Model_Files();
		
		//$url = 'http://lingvopro.abbyyonline.com/ru/Search/' . $source . '-' . $target . '/' . $text;

		$url = 'http://lingvopro.abbyyonline.com/ru/Translate/' . $source . '-' . $target . '/' . $text;

		$file = $filesModel -> load($url, Model_Files::FILE_HTML);
		
		return $this -> getTranslateFromFile($file['path']);
	}
	
	private function getTranslateFromFile($file = null)
	{
		if ( is_readable($file) ) {
			$data = file_get_contents($file);
			
			if ( preg_match_all('/<span class="translation">(.{1,})<\/span>/i', $data, $translates) ) 
			{
			    //return array_map('strip_tags', $translates[1]);

			    $results = array();

			    foreach ( $translates[1] as $_value) {
				$results = array_merge($results, array_map('trim', explode(',', str_replace(';', ',', strip_tags($_value)))));
			    }

			    return $results;
			}
			
			return null;
		}
	}
}