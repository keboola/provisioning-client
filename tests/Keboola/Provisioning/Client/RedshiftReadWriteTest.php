<?php
/**
 *
 * User: Ondrej Hlavacek
 * Date: 10.7.2014
 *
 */
require_once ROOT_PATH . "/tests/Test/ProvisioningTestCase.php";

use Keboola\Provisioning\Client;

class Keboola_ProvisioningClient_RedshiftReadWriteTest extends \ProvisioningTestCase
{

    public static function setUpBeforeClass()
    {
        // PRE cleanup
        \ProvisioningTestCase::cleanUp("redshift", "read", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("redshift", "write", PROVISIONING_API_TOKEN);
    }

    public static function tearDownAfterClass()
    {
        // POST cleanup
        \ProvisioningTestCase::cleanUp("redshift", "read", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("redshift", "write", PROVISIONING_API_TOKEN);
    }

	public function setUp()
	{
		$this->client = new Client("redshift", PROVISIONING_API_TOKEN, "ProvisioningApiTest", PROVISIONING_API_URL);
	}

	/**
	 *
	 */
	public function testCreateWriteCredentials()
	{
		$result = $this->client->getCredentials("write");
		$this->assertArrayHasKey("credentials", $result);
		$this->assertArrayHasKey("id", $result["credentials"]);
		$this->assertArrayHasKey("hostname", $result["credentials"]);
		$this->assertArrayHasKey("db", $result["credentials"]);
		$this->assertArrayHasKey("password", $result["credentials"]);
		$this->assertArrayHasKey("user", $result["credentials"]);
		$this->assertArrayHasKey("schema", $result["credentials"]);
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        $conn->close();

        $result2 = $this->client->getCredentials("write");
        $this->assertEquals($result, $result2);

        $this->client->dropCredentials($result["credentials"]["id"]);
	}

    public function testWriteWithWriteCredentials()
    {
        $result = $this->client->getCredentials("write");
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        $conn->exec("SET search_path to {$result["credentials"]["schema"]};");
        $conn->exec("CREATE TABLE test (id INT NOT NULL);");
        $conn->exec("DROP TABLE test;");
        $conn->close();
        $this->client->dropCredentials($result["credentials"]["id"]);
    }

	/**
	 *
	 */
	public function testCreateReadCredentials()
	{
		$result = $this->client->getCredentials("read");
		$this->assertArrayHasKey("credentials", $result);
		$this->assertArrayHasKey("id", $result["credentials"]);
		$this->assertArrayHasKey("hostname", $result["credentials"]);
		$this->assertArrayHasKey("db", $result["credentials"]);
		$this->assertArrayHasKey("password", $result["credentials"]);
		$this->assertArrayHasKey("user", $result["credentials"]);
		$this->assertArrayHasKey("schema", $result["credentials"]);
        $conn = $this->connect($result["credentials"]);
        $this->assertTrue($this->dbQuery($conn));
        $conn->close();

        $result2 = $this->client->getCredentials("read");
        $this->assertEquals($result, $result2);
        
        $this->client->dropCredentials($result["credentials"]["id"]);
	}

    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessageRegExp /SQLSTATE\[42501\]: Insufficient privilege: 7 ERROR:  permission denied for schema \w+/
     */
    public function testWriteWithReadCredentials()
    {
        $result = $this->client->getCredentials("read");
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        $conn->exec("SET search_path to {$result["credentials"]["schema"]};");
        $conn->exec("CREATE TABLE test (id INT NOT NULL);");
    }

	/**
	 *
	 */
	public function testGetCredentials()
	{
		$result = $this->client->getCredentials("write");
		$id = $result["credentials"]["id"];
		$result = $this->client->getCredentialsById($id);
		$this->assertArrayHasKey("credentials", $result);
		$this->assertArrayHasKey("id", $result["credentials"]);
		$this->assertArrayHasKey("hostname", $result["credentials"]);
		$this->assertArrayHasKey("db", $result["credentials"]);
		$this->assertArrayHasKey("password", $result["credentials"]);
		$this->assertArrayHasKey("user", $result["credentials"]);
		$this->assertArrayHasKey("schema", $result["credentials"]);
		$this->assertArrayHasKey("inUse", $result);
        $conn = $this->connect($result["credentials"]);
        $this->assertTrue($this->dbQuery($conn));
        $conn->close();
        $this->client->dropCredentials($result["credentials"]["id"]);
	}

    /**
   	 *
   	 */
   	public function testGetExistingCredentials()
   	{
        $this->assertFalse($this->client->getExistingCredentials("write"));
        $this->client->getCredentials("write");
        $result = $this->client->getExistingCredentials("write");
        $this->assertArrayHasKey("credentials", $result);
        $this->assertArrayHasKey("id", $result["credentials"]);
        $this->assertArrayHasKey("hostname", $result["credentials"]);
        $this->assertArrayHasKey("db", $result["credentials"]);
        $this->assertArrayHasKey("password", $result["credentials"]);
        $this->assertArrayHasKey("user", $result["credentials"]);
        $this->assertArrayHasKey("schema", $result["credentials"]);
        $this->assertArrayHasKey("inUse", $result);
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
		$result = $this->client->getCredentials("write");
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
		$result = $this->client->getCredentials("write");
        $conn = $this->connect($result["credentials"]);
        $this->assertTrue($this->dbQuery($conn));
		$id = $result["credentials"]["id"];
		$result = $this->client->dropCredentials($id);
		$this->assertTrue($result);
        $this->assertFalse($this->dbQuery($conn));
        $conn->close();

		$result = $this->client->getCredentials("read");
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
        $resultFirst = $this->client->getCredentials("write");
        $resultSecond = $this->client->getCredentials("read");

        $this->assertEquals($resultFirst["credentials"]["db"], $resultSecond["credentials"]["db"]);
        $this->assertNotEquals($resultFirst["credentials"]["user"], $resultSecond["credentials"]["user"]);
        $this->assertEquals($resultFirst["credentials"]["schema"], $resultSecond["credentials"]["schema"]);

        $this->client->dropCredentials($resultFirst["credentials"]["id"]);
        $this->client->dropCredentials($resultSecond["credentials"]["id"]);
    }

    /**
     *
     */
    public function testKeepSchema()
    {
        $write = $this->client->getCredentials("write");
        $read = $this->client->getCredentials("read");

        $connectionRead = $this->connect($read["credentials"]);
        $this->assertCount(1, $connectionRead->fetchAll("SELECT * FROM pg_namespace WHERE nspname = '{$read["credentials"]["schema"]}';"));
        $connectionRead->close();

        $connectionWrite = $this->connect($write["credentials"]);
        $this->assertCount(1, $connectionWrite->fetchAll("SELECT * FROM pg_namespace WHERE nspname = '{$write["credentials"]["schema"]}';"));
        $connectionWrite->close();

        $this->client->dropCredentials($write["credentials"]["id"]);

        $this->assertFalse($this->dbConnection($write["credentials"]));

        $connectionRead = $this->connect($read["credentials"]);
        $this->assertCount(1, $connectionRead->fetchAll("SELECT * FROM pg_namespace WHERE nspname = '{$read["credentials"]["schema"]}';"));
        $connectionRead->close();

        $this->client->dropCredentials($read["credentials"]["id"]);

        $this->assertFalse($this->dbConnection($read["credentials"]));
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
            'schema' => $credentials["schema"],
            'port' => 5439,
            "driver" => "pdo_pgsql"
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
