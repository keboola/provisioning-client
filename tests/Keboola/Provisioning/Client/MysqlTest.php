<?php
/**
 *
 * User: Ondrej Hlavacek
 * Date: 10.7.2014
 *
 */
require_once ROOT_PATH . "/tests/Test/ProvisioningTestCase.php";

use Keboola\Provisioning\Client;

class Keboola_ProvisioningClient_MysqlTest extends \ProvisioningTestCase
{
    public function setUp()
    {
        $this->client = new Client("mysql", PROVISIONING_API_TOKEN, "ProvisioningApiTest", PROVISIONING_API_URL);
    }

    /**
     * @expectedException Keboola\Provisioning\Exception
     * @expectedExceptionMessage MySQL is no longer supported.
     */
    public function testGetCredentials()
    {
        $this->client->getCredentials();
    }

    /**
     * @expectedException Keboola\Provisioning\Exception
     * @expectedExceptionMessage MySQL is no longer supported.
     */
    public function testGetExistingCredentials()
    {
        $this->client->getExistingCredentials();
    }

    /**
     * @expectedException Keboola\Provisioning\Exception
     * @expectedExceptionMessage MySQL is no longer supported.
     */
    public function testGetCredentialsById()
    {
        $this->client->getCredentialsById("123456");
    }

    /**
     * @expectedException Keboola\Provisioning\Exception
     * @expectedExceptionMessage MySQL is no longer supported.
     */
    public function testKillProcesses()
    {
        $this->client->killProcesses('123456');
    }

    /**
     * @expectedException Keboola\Provisioning\Exception
     * @expectedExceptionMessage MySQL is no longer supported.
     */
    public function testExtendCredentials()
    {
        $this->client->extendCredentials("123456");
    }

    /**
     * @expectedException Keboola\Provisioning\Exception
     * @expectedExceptionMessage MySQL is no longer supported.
     */
    public function testDropCredentials()
    {
        $this->client->dropCredentials("123456");
    }
}
