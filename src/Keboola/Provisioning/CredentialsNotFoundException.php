<?php
/**
 *
 * User: Ondrej Hlavacek
 * Date: 18.4.15
 *
 */

namespace Keboola\Provisioning;


class CredentialsNotFoundException extends Exception
{
	protected $_stringCode="USER_ERROR";
}