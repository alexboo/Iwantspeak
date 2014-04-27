<?

class Form_User_Login extends Zend_Form
{
	public function init()
	{		
		$this -> setMethod('post');

		$this -> addElement(
			$this -> createElement('text', 'email') -> setLabel('Емайл')
				-> setRequired(true) -> setOrder(0)
		);

		$this -> addElement(
			$this -> createElement('password', 'password') -> setLabel('Пароль')
				-> setRequired(true) -> setOrder(1)
		);

		/*$this -> addElement(
			$this -> createElement('captcha', 'captcha', array(
				'captcha' => 'Image')) -> setLabel('Captcha') -> setCaptcha('Image', array(
					'font' => ''
			))
		);*/

		/*
		$this -> addElement(
			$this -> createElement('hash', 'hash', array('salt' => 'unique'))
		);
		*/

		$this -> addElement(
			$this -> createElement('button', 'Авторизоваться', array('type' => 'submit'))
				-> setValue('Login')
				-> setIgnore(true)
		);
	}
}