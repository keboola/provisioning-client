<?php
/**
 *
 * User: Ondrej Hlavacek
 * Date: 10.7.2014
 *
 */
require_once ROOT_PATH . "/tests/Test/ProvisioningTestCase.php";

use Keboola\Provisioning\Client;

class Keboola_ProvisioningClient_WrdbTest extends \ProvisioningTestCase
{

    public static function setUpBeforeClass()
    {
        // PRE cleanup
        \ProvisioningTestCase::cleanUp("wrdb", "read", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("wrdb", "write", PROVISIONING_API_TOKEN);
    }

    public static function tearDownAfterClass()
    {
        // POST cleanup
        \ProvisioningTestCase::cleanUp("wrdb", "read", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("wrdb", "write", PROVISIONING_API_TOKEN);
    }

	public function setUp()
	{
		$this->client = new Client("wrdb", PROVISIONING_API_TOKEN, "ProvisioningApiTest", PROVISIONING_API_URL);
	}

	/**
	 *
	 */
	public function testCreateWriteCredentials()
	{
		$result = $this->client->getCredentials("write");
        $this->assertArrayHasKey("id", $result["credentials"]);
        $this->assertArrayHasKey("hostname", $result["credentials"]);
        $this->assertArrayHasKey("db", $result["credentials"]);
        $this->assertArrayHasKey("password", $result["credentials"]);
        $this->assertArrayHasKey("user", $result["credentials"]);
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("touch", $result);
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        $conn->exec("CREATE TABLE test (id int(11) NOT NULL);");
        $conn->exec("DROP TABLE test;");

        $conn->close();

        $result2 = $this->client->getCredentials("write");
        $this->assertEquals($result["credentials"], $result2["credentials"]);
        $this->assertEquals($result["id"], $result2["id"]);

        $this->client->dropCredentials($result["id"]);
	}

    public function testWriteWithWriteCredentials()
    {
        $result = $this->client->getCredentials("write");
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        $conn->exec("CREATE TABLE test (id INT NOT NULL);");
        $conn->exec("DROP TABLE test;");
        $conn->close();
        $this->client->dropCredentials($result["id"]);
    }

	/**
	 *
	 */
	public function testCreateReadCredentials()
	{
		$result = $this->client->getCredentials("read");
        $this->assertArrayHasKey("id", $result["credentials"]);
        $this->assertArrayHasKey("hostname", $result["credentials"]);
        $this->assertArrayHasKey("db", $result["credentials"]);
        $this->assertArrayHasKey("password", $result["credentials"]);
        $this->assertArrayHasKey("user", $result["credentials"]);
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("touch", $result);
        $conn = $this->connect($result["credentials"]);
        $this->assertTrue($this->dbQuery($conn));
        $conn->close();

        $result2 = $this->client->getCredentials("read");
        $this->assertEquals($result["credentials"], $result2["credentials"]);
        $this->assertEquals($result["id"], $result2["id"]);

        $this->client->dropCredentials($result["id"]);

	}

    /**
     * @expectedException Doctrine\DBAL\DBALException
     * @expectedExceptionMessageRegExp /^(.)*access violation(.)*$/m
     */
    public function testWriteWithReadCredentials()
    {
        $result = $this->client->getCredentials("read");
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        $conn->exec("CREATE TABLE test (id INT NOT NULL);");
    }

	/**
	 *
	 */
	public function testGetCredentials()
	{
		$result = $this->client->getCredentials("write");
		$id = $result["id"];
		$result = $this->client->getCredentialsById($id);
        $this->assertArrayHasKey("id", $result["credentials"]);
        $this->assertArrayHasKey("hostname", $result["credentials"]);
        $this->assertArrayHasKey("db", $result["credentials"]);
        $this->assertArrayHasKey("password", $result["credentials"]);
        $this->assertArrayHasKey("user", $result["credentials"]);
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("touch", $result);
        $conn = $this->connect($result["credentials"]);

        $this->assertTrue($this->dbQuery($conn));
        $conn->close();
        $this->client->dropCredentials($result["id"]);
	}

    /**
   	 *
   	 */
   	public function testGetExistingCredentials()
   	{
        $this->assertFalse($this->client->getExistingCredentials("write"));
        $this->client->getCredentials("write");
        $result = $this->client->getExistingCredentials("write");
        $this->assertArrayHasKey("id", $result["credentials"]);
        $this->assertArrayHasKey("hostname", $result["credentials"]);
        $this->assertArrayHasKey("db", $result["credentials"]);
        $this->assertArrayHasKey("password", $result["credentials"]);
        $this->assertArrayHasKey("user", $result["credentials"]);
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("touch", $result);
        $this->client->dropCredentials($result["id"]);
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
		$result = $this->client->getCredentials("write");
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        $id = $result["id"];
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
		$result = $this->client->getCredentials("write");
        $conn = $this->connect($result["credentials"]);
        $this->assertTrue($this->dbQuery($conn));
		$id = $result["id"];
		$result = $this->client->dropCredentials($id);
		$this->assertTrue($result);
        $this->assertFalse($this->dbQuery($conn));
        $conn->close();

		$result = $this->client->getCredentials("read");
		$id = $result["id"];
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
        $resultFirst = $this->client->getCredentials("write");
        $resultSecond = $this->client->getCredentials("read");

        $this->assertEquals($resultFirst["credentials"]["db"], $resultSecond["credentials"]["db"]);


        $this->client->dropCredentials($resultFirst["id"]);
        $this->client->dropCredentials($resultSecond["id"]);
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
     *
     */
    public function testKeepDatabase()
    {
        $write = $this->client->getCredentials("write");
        $read = $this->client->getCredentials("read");
        $this->assertTrue($this->dbConnection($write["credentials"]));
        $this->assertTrue($this->dbConnection($read["credentials"]));
        $this->client->dropCredentials($write["id"]);
        $this->assertFalse($this->dbConnection($write["credentials"]));
        $this->assertTrue($this->dbConnection($read["credentials"]));
        $this->client->dropCredentials($read["id"]);
        $this->assertFalse($this->dbConnection($read["credentials"]));
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
        } catch(\PDOException $e) {
            return false;
        }
        return true;
    }
}
