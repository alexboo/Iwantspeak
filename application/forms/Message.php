<?php
class Form_Message extends Zend_Form
{
	public function init()
	{		
		$this -> setMethod('post');

		$this -> addElement(
			$this -> createElement('text', 'name') -> setLabel('Имя') -> setRequired(true)
		);
		
		$this -> addElement(
			$this -> createElement('text', 'email')
			 -> setLabel('Емайл')
			 -> setRequired(true)
			 -> addValidator('EmailAddress')
		);
		
		$this -> addElement(
			$this -> createElement('text', 'theme') -> setLabel('Тема') -> setRequired(true)
		);
		
		$this -> addElement(
			$this -> createElement('textarea', 'text') -> setLabel('Сообщение') -> setRequired(true)
		);
		
		$this -> addElement(
			$this -> createElement('submit', 'send') -> setLabel('Отправить')
		);
		
		
	}
}