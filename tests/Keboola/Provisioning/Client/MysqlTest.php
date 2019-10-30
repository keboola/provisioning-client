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
    public function setUp(): void
    {
        $this->client = new Client("mysql", PROVISIONING_API_TOKEN, "ProvisioningApiTest", PROVISIONING_API_URL);
    }

    /**
     * @expectedException Keboola\Provisioning\Exception
     * @expectedExceptionMessage 'mysql' not found.
     */
    public function testGetCredentials()
    {
        $this->client->getCredentials();
    }

    /**
     * @expectedException Keboola\Provisioning\Exception
     * @expectedExceptionMessage 'mysql' not found.
     */
    public function testGetExistingCredentials()
    {
        $this->client->getExistingCredentials();
    }

    /**
     * @expectedException Keboola\Provisioning\Exception
     * @expectedExceptionMessage 'mysql' not found.
     */
    public function testGetCredentialsById()
    {
        $this->client->getCredentialsById("123456");
    }

    /**
     * @expectedException Keboola\Provisioning\Exception
     * @expectedExceptionMessage 'mysql' not found.
     */
    public function testKillProcesses()
    {
        $this->client->killProcesses('123456');
    }

    /**
     * @expectedException Keboola\Provisioning\Exception
     * @expectedExceptionMessage 'mysql' not found.
     */
    public function testExtendCredentials()
    {
        $this->client->extendCredentials("123456");
    }

    /**
     * @expectedException Keboola\Provisioning\Exception
     * @expectedExceptionMessage 'mysql' not found.
     */
    public function testDropCredentials()
    {
        $this->client->dropCredentials("123456");
    }
}
