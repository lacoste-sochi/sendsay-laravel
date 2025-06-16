<?php

namespace Rutrue\Sendsay\Exceptions;

class SendsayAuthException extends SendsayException
{
	public function __construct()
	{
		parent::__construct('Authentication failed for Sendsay API');
	}
}