<?

class Form_Dictionary_Edit extends Zend_Form
{
	public function init()
	{		
		$this -> setMethod('post');

		$this -> addElement(
			$this -> createElement('button', 'submit', array('type' => 'submit'))
				-> setLabel('Добавить')
				-> setIgnore(true)
		);
	}
	
	public function setLanguages($languages)
	{
		$values = array();
		
		foreach ( $languages as $key => $language ) {
			if ( isset($language['key']) && isset($language['title']) ) {
				$values[$language['key']] = $language['title'];
			}
			else $values[$key] = $language;
		}
		
		$this -> addElement(
			$this -> createElement('select', 'from')
				-> setRequired(true) -> setMultiOptions(array('' => '-') + $values)
				-> setLabel('Язык слова которого нужно переводить:')
		);
		
		$this -> addElement(
			$this -> createElement('select', 'to')
				-> setRequired(true) -> setMultiOptions(array('' => '-') + $values)
				-> setLabel('Язык на который будем переводить:')
		);
	}
}