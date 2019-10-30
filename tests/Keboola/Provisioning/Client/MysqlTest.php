<?php
/**
 *
 * User: Ondrej Hlavacek
 * Date: 10.7.2014
 *
 */
require_once ROOT_PATH . "/tests/Test/ProvisioningTestCase.php";

use Keboola\Provisioning\Client;
use Keboola\Provisioning\Exception;

class Keboola_ProvisioningClient_MysqlTest extends \ProvisioningTestCase
{
    public function setUp(): void
    {
        $this->client = new Client("mysql", PROVISIONING_API_TOKEN, "ProvisioningApiTest", PROVISIONING_API_URL);
    }

    public function testGetCredentials()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('\'mysql\' not found.');
        $this->client->getCredentials();
    }

    public function testGetExistingCredentials()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('\'mysql\' not found.');
        $this->client->getExistingCredentials();
    }

    public function testGetCredentialsById()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('\'mysql\' not found.');
        $this->client->getCredentialsById("123456");
    }

    public function testKillProcesses()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('\'mysql\' not found.');
        $this->client->killProcesses('123456');
    }

    public function testExtendCredentials()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('\'mysql\' not found.');
        $this->client->extendCredentials("123456");
    }

    public function testDropCredentials()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('\'mysql\' not found.');
        $this->client->dropCredentials("123456");
    }
}
