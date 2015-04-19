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

    public static function setUpBeforeClass()
    {
        // PRE cleanup
        \ProvisioningTestCase::cleanUp("mysql", "sandbox", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("mysql", "transformations", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("mysql", "sandbox", PROVISIONING_API_TOKEN_SECOND_PROJECT);
    }

    public static function tearDownAfterClass()
    {
        // POST cleanup
        \ProvisioningTestCase::cleanUp("mysql", "sandbox", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("mysql", "transformations", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("mysql", "sandbox", PROVISIONING_API_TOKEN_SECOND_PROJECT);
    }

	public function setUp()
	{
		$this->client = new Client("mysql", PROVISIONING_API_TOKEN, "ProvisioningApiTest", PROVISIONING_API_URL);
	}

	/**
	 *
	 */
	public function testCreateTransformationCredentials()
	{
		$result = $this->client->getCredentials();
		$this->assertArrayHasKey("credentials", $result);
		$this->assertArrayHasKey("id", $result["credentials"]);
		$this->assertArrayHasKey("hostname", $result["credentials"]);
		$this->assertArrayHasKey("db", $result["credentials"]);
		$this->assertArrayHasKey("password", $result["credentials"]);
		$this->assertArrayHasKey("user", $result["credentials"]);
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        $conn->close();

        $result2 = $this->client->getCredentials();
        $this->assertEquals($result, $result2);

        $this->client->dropCredentials($result["credentials"]["id"]);
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
        $conn = $this->connect($result["credentials"]);
        $this->assertTrue($this->dbQuery($conn));
        $conn->close();

        $result2 = $this->client->getCredentials("sandbox");
        $this->assertEquals($result, $result2);
        
        $this->client->dropCredentials($result["credentials"]["id"]);

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
        $conn = $this->connect($result["credentials"]);
        $this->assertTrue($this->dbQuery($conn));
        $conn->close();
        $this->client->dropCredentials($result["credentials"]["id"]);

	}

	/**
	 * @expectedException Keboola\Provisioning\CredentialsNotFoundException
	 * @expectedExceptionMessage Credentials not found.
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
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        $id = $result["credentials"]["id"];
		$result = $this->client->killProcesses($id);
		$this->assertTrue($result);
        $this->assertFalse($this->dbQuery($conn));
        $conn->close();
        $this->client->dropCredentials($id);
	}

	/**
	 * @expectedException Keboola\Provisioning\CredentialsNotFoundException
	 * @expectedExceptionMessage Credentials not found.
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
        $conn = $this->connect($result["credentials"]);
        $this->assertTrue($this->dbQuery($conn));
		$id = $result["credentials"]["id"];
		$result = $this->client->dropCredentials($id);
		$this->assertTrue($result);
        $this->assertFalse($this->dbQuery($conn));
        $conn->close();

		$result = $this->client->getCredentials("sandbox");
		$id = $result["credentials"]["id"];
        $conn = $this->connect($result["credentials"]);
		$result = $this->client->dropCredentials($id);
		$this->assertTrue($result);
        $this->assertFalse($this->dbQuery($conn));
        $conn->close();
	}

    /**
     *
     */
    public function testSharedCredentials()
    {
        $resultFirst = $this->client->getCredentials("sandbox");
        $clientSecond = new Client('mysql', PROVISIONING_API_TOKEN_SECOND_PROJECT, "ProvisioningApiTest", PROVISIONING_API_URL);
        $resultSecond = $clientSecond->getCredentials("sandbox");

        $this->assertEquals($resultFirst["credentials"]["user"], $resultSecond["credentials"]["user"]);
        $this->assertEquals($resultFirst["credentials"]["hostname"], $resultSecond["credentials"]["hostname"]);
        $this->assertEquals($resultFirst["credentials"]["password"], $resultSecond["credentials"]["password"]);

        $conn = $this->connect($resultFirst["credentials"]);
        $databases = $conn->fetchAll("SHOW DATABASES;");
        $dbArray = array();
        foreach($databases as $db) {
            $dbArray[] = $db["Database"];
        }
        $this->assertContains($resultFirst["credentials"]["db"], $dbArray, print_r($dbArray, true));
        $this->assertContains($resultSecond["credentials"]["db"], $dbArray, print_r($dbArray, true));
        $conn->close();

        $this->client->dropCredentials($resultFirst["credentials"]["id"]);

        $conn = $this->connect($resultSecond["credentials"]);
        $databases = $conn->fetchAll("SHOW DATABASES;");
        $dbArray = array();
        foreach($databases as $db) {
            $dbArray[] = $db["Database"];
        }
        $this->assertNotContains($resultFirst["credentials"]["db"], $dbArray, print_r($dbArray, true));
        $this->assertContains($resultSecond["credentials"]["db"], $dbArray, print_r($dbArray, true));
        $conn->close();

        $clientSecond->dropCredentials($resultSecond["credentials"]["id"]);
    }

	/**
	 * @expectedException Keboola\Provisioning\CredentialsNotFoundException
	 * @expectedExceptionMessage Credentials not found.
	 */
	public function testDropCredentialsException()
	{
		$this->client->dropCredentials("123456");
	}

    /**
     * @param $credentials
     * @return \Doctrine\DBAL\Connection
     */
    public function connect($credentials)
   	{
        $connectionParams  = array(
            'host' => $credentials["hostname"],
            'user' => $credentials["user"],
            'password' => $credentials["password"],
            'dbname' => $credentials["db"],
            "driver" => "pdo_mysql"
        );

        $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
        $conn->connect();
        return $conn;
   	}

    /**
     * @param \Doctrine\DBAL\Connection $conn
     * @return bool
     */
    public function dbQuery(\Doctrine\DBAL\Connection $conn)
    {
        try {
            $conn->fetchAll("SELECT 1;");
        } catch(\Doctrine\DBAL\DBALException $e) {
            return false;
        }
        return true;
    }

    /**
     * @param $credentials
     * @return bool
     */
    public function dbConnection($credentials)
    {
        try {
            $conn = $this->connect($credentials);
            $this->dbQuery($conn);
        } catch(\Doctrine\DBAL\DBALException $e) {
            return false;
        }
        return true;
    }
}
