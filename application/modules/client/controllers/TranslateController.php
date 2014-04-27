<?php
class TranslateController extends Dict_Controller_Action
{
	public function translateAction()
	{
		$source = $this -> _getParam('source', false);
		
		$target = $this -> _getParam('target', false);
		
		$text = $this -> _getParam('text', false);
		
		if ( $source && $target && $text ) {
			
			$translateModel = new Model_Translate();
			
			$transcriptionModel = new Model_Transcription();
			
			$speechModel = new Model_Speech();
			
			$data['translates'] = $translateModel -> translate($source, $target, $text);
			
			$_t = $transcriptionModel -> transcription($source, $target, $text);
			
			if ( is_array($_t) && !empty($_t['url']) )
				$data['transcription'] = array('url' => $_t['url']);
			else 
				$data['transcription'] = $_t;
			
			$_s = $speechModel -> speech($source, $target, $text);
			$data['speech'] = $_s['url'];
			
			return $this -> _helper -> json($data);
		}
	}
	
	public function blockAction()
	{
		
		$languageTable = new Model_Table_Language();
		
		$this -> view -> languages = $languageTable->getList()->toArray();
		
	}
}