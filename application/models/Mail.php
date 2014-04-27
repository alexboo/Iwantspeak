<?php
class Model_Files
{
	public function send($subject, $to, $message, array $params = null)
	{
		$mail = new Zend_Mail('utf-8');
		
		if (is_array($message)) {

			// Pass as array

			if (! empty($message['html'])) {
				$mail -> setBodyHtml($message['html']);
			}

			if (! empty($message['text'])) {
				$mail -> setBodyText($message['text']);
			}
		}

		$mail -> setFrom($this -> config['from']['email'], $this -> config['from']['name']);

		if (is_array($to)) {
			$mail -> addTo($to['email'], $to['name']);
		}
		else {
			$mail -> addTo($to);
		}

		if (! empty($params['replyTo'])) {
			if (is_array($params['replyTo'])) {
				$mail -> setReplyTo($params['replyTo']['email'], $params['replyTo']['name']);
			}
			else {
				$mail -> setReplyTo($params['replyTo']);
			}
		}

		$mail -> setSubject($subject);
		
		try {

			// Send from transport
			return !! $mail -> send();
		}
		catch (Exception $e) {

			// @TODO Catch error's
			throw $e;
		}
	}
}