<?php

class IndexController extends Dict_Controller_Action
{

    public function indexAction()
    {
	$translateModel = new Model_Translate();

    	if ( $this -> user -> isAuthenticated() ) {
    		$this->_redirect('/words');
    	}
    }
    
    public function messageAction()
    {
    	$form = new Form_Message();
    	
    	if ( $this->getRequest()->isPost() ) {
    	
    		$flashMessenger = $this->_helper->getHelper('FlashMessenger');
    		
    		if ( $form->isValid($_POST) ) {
    			
    			$mailer = new Model_Mail();
    			
    			$text = "Вам было отправленно сообщение от " . $form->getValue('name') . " email " . $form->getValue('email') . "
Текст сообщения:

" . $form->getValue('text');
    			
    			$mailer->send($form->getValue('theme'), 
					array(
					'name' => 'Булыбин Алексей', 
					'email' => 'alexboo@inbox.ru'),
					array('text' => $text));
				
		    	$flashMessenger->addMessage('Ваше сообщение отправленно');
		    	
		    	$this->back();
    	
    		}
    		else {
    			
    			$errors = $form->getErrors();
    			
    			foreach ( $errors as & $error ) {
    				
    				foreach ( $error as & $text ) {
    					
    					if ( 'isEmpty' == $text ) {
    						$text = 'Не заполнено поле';
    					}
    					
    					if ( 'emailAddressInvalidFormat' == $text ) {
    						
    						$text = 'Не правильно указан емайл';
    						
    					}
    				}
    				
    			}
    			
    			$this -> view -> errors = $errors;
    			
    			$this -> view -> values = $form -> getValues();
    			
    		}
    	
    	}
    }

}

