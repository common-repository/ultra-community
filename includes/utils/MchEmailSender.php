<?php
namespace UltraCommunity\MchLib\Utils;
use Exception;

class MchEmailSender
{
	private $to 			 = array();
	private $cc 			 = array();
	private $bcc 			 = array();
	private $headers 		 = array();
	private $attachments 	 = array();

	private $sendAsHTML 	 = TRUE;

	private $subject 		 = '';
	private $from 			 = '';

	private $headerTemplate  = FALSE;
	private $headerVariables = array();

	private $template 		 = FALSE;
	private $variables 		 = array();

	private $afterTemplate   = FALSE;
	private $footerVariables  = array();


	private $message           = null;
	private $arrParseVariables = array();


	private function __construct()
	{
		$this->arrParseVariables['site.url']  = wp_parse_url( home_url(), PHP_URL_HOST );
		$this->arrParseVariables['site.name'] = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );


		$this->arrParseVariables['site.address'] = $this->arrParseVariables['site.url'];
		$this->arrParseVariables['site.title']   = $this->arrParseVariables['site.name'];


	}

	public static function getInstance(){
		return new self();
	}

	public function setParseVariables(array $arrParseVariables = array())
	{
		$this->arrParseVariables = array_merge($this->arrParseVariables, $arrParseVariables);
		return $this;
	}

	public function setMessage($message) //array $arrParseVariables = array()
	{
		$message = wp_kses_decode_entities((string)$message);
		$this->message = str_replace("\n.", "\n..", $message);
		return $this;
	}

	public function getMessage($parsed = true)
	{
		return  $parsed ? $this->parseAsMustache($this->message)  : $this->message;
	}


	public function setFrom($senderEmail, $senderName = null)
	{
		$this->addMailHeader('From', $senderEmail, $senderName);
		return $this;
	}

	public function setTo($recipientEmail, $recipientName = null)
	{
		$this->to[] = $this->formatHeader($recipientEmail, $recipientName);

		return $this;
	}


	public function getTo(){
		return $this->to;
	}


	public function setCc($ccPairs)
	{
		return $this->addMailHeaders('Cc', $ccPairs);
	}

	public function setBcc($bccPairs)
	{
		return $this->addMailHeaders('Bcc', (array)$bccPairs);
	}

	public function setReplyTo($email, $name = null)
	{
		return $this->addMailHeader('Reply-To', $email, $name);
	}


	public function setSubject($subject)
	{
		$subject = wp_kses_decode_entities((string) $subject);

		$this->subject = $this->filterOther($subject);

		return $this;
	}

	public function getSubject($parsed = true)
	{
		//

		return $parsed ? $this->parseAsMustache($this->subject)  : $this->subject;
	}


//	public function headers($headers)
//	{
//		$this->headers = (array)$headers + $this->headers;
//		return $this;
//	}

//	private function getHeaders(){
//		return $this->headers;
//	}


	public function sendAsHTML($yesORNo ){
		$this->sendAsHTML = !!$yesORNo;
		return $this;
	}

	private function addGenericHeader($header, $value)
	{
		$this->headers[] = sprintf('%s: %s', (string) $header, (string) $value);
		return $this;
	}

	private function addMailHeader($header, $email, $name = null)
	{
		$address = $this->formatHeader($email, $name);
		$this->headers[] = sprintf('%s: %s', (string) $header, $address);
		return $this;
	}

	private function addMailHeaders($header, array $pairs)
	{
		if(empty($pairs))
			return $this;

		$addresses = array();

		foreach ($pairs as $email => $name)
		{
			if( MchValidator::isNumeric($email) && MchValidator::isEmail($name) ) // array( 0 => test@test.com)
			{
				$email = $name;
				$addresses[] = $this->formatHeader($email, null);
				continue;
			}

			if(MchValidator::isEmail($email)) // array( test@test.com => 'Mihai Chelaru')
			{
				!empty($name) && is_string($name) ?:  $name = null;
				$addresses[] = $this->formatHeader($email, $name);
				continue;
			}

			if(MchValidator::isEmail($name)) // array( 'Mihai Chelaru' => test@test.com => )
			{
				!empty($email) && is_string($email) ?:  $email = null;
				$addresses[] = $this->formatHeader($name, $email);
				continue;
			}


		}

		empty($addresses) ?: $this->addGenericHeader($header, implode(',', $addresses));

		return $this;
	}


	public function attach($path)
	{
		if(is_array($path))
		{
			$this->attachments = array();
			foreach($path as $path_) {
				if(!file_exists($path_)){
					throw new Exception("Attachment not found at $path");
				}else{
					$this->attachments[] = $path_;
				}
			}
		}
		else
		{
			if(!file_exists($path)){
				throw new Exception("Attachment not found at $path");
			}
			$this->attachments = array($path);
		}

		return $this;
	}


	public function templateHeader($template, $variables = NULL)
	{
		if(!file_exists($template)){
			throw new Exception('Template file not found');
		}

		if(is_array($variables)){
			$this->headerVariables = $variables;
		}

		$this->headerTemplate = $template;
		return $this;
	}


	public function template($template, $variables = NULL)
	{
		if(!file_exists($template)){
			throw new Exception('File not found');
		}

		if(is_array($variables)){
			$this->variables = $variables;
		}

		$this->template = $template;
		return $this;
	}


	public function templateFooter($template, $variables = NULL)
	{
		if(!file_exists($template))
		{
			throw new Exception('Template file not found');
		}

		if(is_array($variables)){
			$this->footerVariables = $variables;
		}

		$this->afterTemplate = $template;
		return $this;
	}


	private function renderTemplate()
	{
		return $this->renderTemplatePart('before') . $this->renderTemplatePart('main') . $this->renderTemplatePart('after');
	}


	private function renderTemplatePart($part = 'main')
	{
		switch($part){
			case 'before':
				$templateFile = $this->headerTemplate;
				$variables    = $this->headerVariables;
				break;

			case 'after':
				$templateFile = $this->afterTemplate;
				$variables    = $this->footerVariables;
				break;

			case 'main':
			default:
				$templateFile = $this->template;
				$variables    = $this->variables;
				break;
		}

		if(empty($templateFile))
		{
			return null;
		}


		$extension = strtolower(pathinfo($templateFile, PATHINFO_EXTENSION));
		if($extension === 'php'){

			ob_start();
			ob_clean();

			foreach($variables as $key => $value){
				$$key = $value;
			}

			include $templateFile;

			$html = ob_get_clean();

			return $html;

		}elseif($extension === 'html'){

			$template = file_get_contents($templateFile);

			if(!is_array($variables) || empty($variables)){
				return $template;
			}

			return $this->parseAsMustache($template, $variables);

		}else{
			throw new Exception("Unknown extension {$extension} in path '{$templateFile}'");
		}
	}

//	private function buildSubject()
//	{
//		return $this->parseAsMustache(
//			$this->subject, $this->arrParseVariables
//			//array_merge($this->headerVariables, $this->variables, $this->footerVariables)
//		);
//	}

	private function parseAsMustache($string, $variables = array())
	{
		preg_match_all('/\{\{\s*.+?\s*\}\}/', $string, $matches);

		if(empty($matches[0]))
			return $string;

		!empty($variables) ?: $variables = $this->arrParseVariables;

		foreach($matches[0] as $match)
		{
			$var = str_replace('{', '', str_replace('}', '', preg_replace('/\s+/', '', $match)));

			if(isset($variables[$var]) && !is_array($variables[$var])){
				$string = str_replace($match, $variables[$var], $string);
			}
		}

		return $string;
	}



	public function send()
	{
		function_exists('wp_mail') || require_once( ABSPATH . 'wp-includes/pluggable.php' );

		$this->sendAsHTML && $this->addGenericHeader('Content-Type', 'text/html; charset="utf-8"');

		$emailMessage = $this->renderTemplate();
		if(empty($emailMessage))
		{
			$emailMessage = $this->getMessage();
			$emailMessage = nl2br("$emailMessage");
			$emailMessage = make_clickable($emailMessage);

		}


		$toAddress = implode(', ', $this->to);

		$subject = $this->encodeUtf8($this->getSubject());

		$headers = empty($this->headers) ? '' : implode(PHP_EOL, $this->headers);


		return @wp_mail($toAddress, $subject, $emailMessage, $headers, $this->attachments);
	}

	private function formatHeader($email, $name = null)
	{
		$email = $this->filterEmail((string) $email);
		if (empty($name)) {
			return $email;
		}
		$name = $this->encodeUtf8($this->filterName((string) $name));
		return sprintf('"%s" <%s>', $name, $email);
	}

	private function encodeUtf8($value)
	{
		$value = trim($value);
		if (preg_match('/(\s)/', $value)) {
			return $this->encodeUtf8Words($value);
		}
		return $this->encodeUtf8Word($value);
	}

	private function encodeUtf8Word($value)
	{
		return sprintf('=?UTF-8?B?%s?=', base64_encode($value));
	}

	private function encodeUtf8Words($value)
	{
		$words = explode(' ', $value);
		$encoded = array();
		foreach ($words as $word) {
			$encoded[] = $this->encodeUtf8Word($word);
		}

		return join($this->encodeUtf8Word(' '), $encoded);
	}

	private function filterEmail($email)
	{
		$rule = array(
			"\r" => '',
			"\n" => '',
			"\t" => '',
			'"'  => '',
			','  => '',
			'<'  => '',
			'>'  => ''
		);
		$email = strtr($email, $rule);
		$email = filter_var($email, FILTER_SANITIZE_EMAIL);

		return MchWpUtils::sanitizeEmail($email);

	}

	private function filterName($name)
	{
		$rule = array(
			"\r" => '',
			"\n" => '',
			"\t" => '',
			'"'  => "'",
			'<'  => '[',
			'>'  => ']',
		);

		$filtered = filter_var(
			$name,
			FILTER_SANITIZE_STRING,
			FILTER_FLAG_NO_ENCODE_QUOTES
		);

		return MchWpUtils::sanitizeText(trim(strtr($filtered, $rule)));
	}

	private function filterOther($data)
	{
		return filter_var($data, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
	}


}