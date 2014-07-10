<?php
/**
 *
 * User: Ondrej Hlavacek
 * Date: 10.7.2014
 *
 */

use Keboola\Provisioning\Client;

class Keboola_ProvisioningClient_MysqlTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var Client
	 */
	private $client;

	public function setUp()
	{
		$this->client = new Client("mysql", PROVISIONING_API_TOKEN, "ProvisioningApiTest", PROVISIONING_API_URL);
	}

	/**
	 *
	 */
	public function testCreateTransfomrationCredentials()
	{
		$result = $this->client->getCredentials();
		$this->assertArrayHasKey("credentials", $result);
		$this->assertArrayHasKey("id", $result["credentials"]);
		$this->assertArrayHasKey("hostname", $result["credentials"]);
		$this->assertArrayHasKey("db", $result["credentials"]);
		$this->assertArrayHasKey("password", $result["credentials"]);
		$this->assertArrayHasKey("user", $result["credentials"]);
	}

	/**
	 *
	 */
	public function testCreateSandboxCredentials()
	{
		$result = $this->client->getCredentials("sandbox");
		$this->assertArrayHasKey("credentials", $result);
		$this->assertArrayHasKey("id", $result["credentials"]);
		$this->assertArrayHasKey("hostname", $result["credentials"]);
		$this->assertArrayHasKey("db", $result["credentials"]);
		$this->assertArrayHasKey("password", $result["credentials"]);
		$this->assertArrayHasKey("user", $result["credentials"]);
	}

	/**
	 *
	 */
	public function testGetCredentials()
	{
		$result = $this->client->getCredentials();
		$id = $result["credentials"]["id"];
		$result = $this->client->getCredentialsById($id);
		$this->assertArrayHasKey("credentials", $result);
		$this->assertArrayHasKey("id", $result["credentials"]);
		$this->assertArrayHasKey("hostname", $result["credentials"]);
		$this->assertArrayHasKey("db", $result["credentials"]);
		$this->assertArrayHasKey("password", $result["credentials"]);
		$this->assertArrayHasKey("user", $result["credentials"]);
		$this->assertArrayHasKey("inUse", $result);
	}

	/**
	 * @expectedException Keboola\Provisioning\Exception
	 * @expectedExceptionMessage Error from Provisioning API: Credentials not found.
	 */
	public function testGetCredentialsException()
	{
		$this->client->getCredentialsById("123456");
	}

	/**
	 *
	 */
	public function testKillProcesses()
	{
		$result = $this->client->getCredentials();
		$id = $result["credentials"]["id"];
		$result = $this->client->killProcesses($id);
		$this->assertTrue($result);
	}

	/**
	 * @expectedException Keboola\Provisioning\Exception
	 * @expectedExceptionMessage Error from Provisioning API: Credentials not found.
	 */

	public function testKillProcessesException()
	{
		$this->client->killProcesses("123456");
	}

	/**
	 *
	 */
	public function testDropCredentials()
	{
		$result = $this->client->getCredentials();
		$id = $result["credentials"]["id"];
		$result = $this->client->dropCredentials($id);
		$this->assertTrue($result);

		$result = $this->client->getCredentials("sandbox");
		$id = $result["credentials"]["id"];
		$result = $this->client->dropCredentials($id);
		$this->assertTrue($result);
	}

	/**
	 * @expectedException Keboola\Provisioning\Exception
	 * @expectedExceptionMessage Error from Provisioning API: Credentials not found.
	 */
	public function testDropCredentialsException()
	{
		$this->client->dropCredentials("123456");
	}
}
