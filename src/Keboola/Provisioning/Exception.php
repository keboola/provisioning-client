<?php
/**
 *
 * User: Ondrej Hlavacek
 * Date: 8.4.13
 *
 */

namespace Keboola\Provisioning;


class Exception extends \Exception
{
	protected $_stringCode="APPLICATION_ERROR";

	protected $_contextParams;

	public $_previous = null;

	public function __construct($message = NULL, $code = NULL, $previous = NULL, $stringCode = NULL, $params = NULL)
	{
		$this->setStringCode($stringCode);
		$this->setContextParams($params);
		if (version_compare(PHP_VERSION, '5.3.0', '<')) {
			parent::__construct($message, (int) $code);
			$this->_previous = $previous;
		} else {
			parent::__construct($message, (int) $code, $previous);
		}
	}


	public function getStringCode()
	{
		return $this->_stringCode;
	}

	/**
	 * @param $stringCode
	 * @return Exception
	 */
	public function setStringCode($stringCode)
	{
		if ($stringCode) {
			$this->_stringCode = (string) $stringCode;
		} else {
			$this->_stringCode = "APPLICATION_ERROR";
		}
		return $this;
	}

	public function getContextParams()
	{
		return $this->_contextParams;
	}

	/**
	 * @param array $contextParams
	 * @return Exception
	 */
	public function setContextParams($contextParams)
	{
		$this->_contextParams = (array) $contextParams;
		return $this;
	}
}