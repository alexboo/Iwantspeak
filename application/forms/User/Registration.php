<?

class Form_User_Registration extends Zend_Form
{
	public function init()
	{
		$this -> setMethod('post');

		$this -> addElement(
			$this -> createElement('text', 'name') -> setLabel('Ваше имя')
			-> setRequired(true)
		);
		
		$validatorNoExists = new Zend_Validate_Db_NoRecordExists('users', 'email');
		
		$this -> addElement(
			$this -> createElement('text', 'email') -> setLabel('Емайл')
			-> setRequired(true) -> addValidator('EmailAddress') -> addValidator($validatorNoExists)
		);

		$this -> addElement(
			$this -> createElement('password', 'password') -> setLabel('Пароль')
			-> setRequired(true)
		);
		
		$validate = new Dict_Validate_EqualInputs('password');
		
		$this -> addElement(
			$this -> createElement('password', 'password_approve') -> setLabel('Повторите пароль')
			-> addValidator($validate) -> setRequired(true)
		);

		/*
		$this -> addElement('submit', 'submit', array(
            'ignore' => true, 'label' => 'Зарегистрироваться',
		));
		*/
		
		$this -> addElement(
			$this -> createElement('button', 'Зарегистрироваться', array('type' => 'submit'))
				-> setValue('Registration')
				-> setOrder(15)
				-> setIgnore(true)
		);
	}
}