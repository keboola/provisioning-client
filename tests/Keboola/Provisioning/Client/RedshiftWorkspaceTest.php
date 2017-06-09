<?php
/**
 *
 * User: Ondrej Hlavacek
 * Date: 10.7.2014
 *
 */
require_once ROOT_PATH . "/tests/Test/ProvisioningTestCase.php";

use Keboola\Provisioning\Client;

class Keboola_ProvisioningClient_RedshiftWorkspaceTest extends \ProvisioningTestCase
{

    public static function setUpBeforeClass()
    {
        // PRE cleanup
        \ProvisioningTestCase::cleanUp("redshift-workspace", "sandbox", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("redshift-workspace", "transformations", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("redshift-workspace", "luckyguess", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("redshift-workspace", "writer", PROVISIONING_API_TOKEN);
    }

    public static function tearDownAfterClass()
    {
        // PRE cleanup
        \ProvisioningTestCase::cleanUp("redshift-workspace", "sandbox", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("redshift-workspace", "transformations", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("redshift-workspace", "luckyguess", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("redshift-workspace", "writer", PROVISIONING_API_TOKEN);
    }

    public function setUp()
    {
        $this->client = new Client("redshift-workspace", PROVISIONING_API_TOKEN, "ProvisioningApiTest", PROVISIONING_API_URL);
    }

    public function tearDown() {
    }

    /**
     *
     */
    public function testCreateTransformationCredentials()
    {
        $result = $this->client->getCredentials();
        $this->assertArrayHasKey("id", $result["credentials"]);
        $this->assertArrayHasKey("hostname", $result["credentials"]);
        $this->assertArrayHasKey("db", $result["credentials"]);
        $this->assertArrayHasKey("password", $result["credentials"]);
        $this->assertArrayHasKey("user", $result["credentials"]);
        $this->assertArrayHasKey("schema", $result["credentials"]);
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("touch", $result);
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        $conn->close();
        $result2 = $this->client->getCredentials();
        $this->assertEquals($result["id"], $result2["id"]);
        $this->assertEquals($result["credentials"], $result2["credentials"]);
        $this->client->dropCredentials($result["id"]);
    }

    /**
     *
     */
    public function testCreateSandboxCredentials()
    {
        $result = $this->client->getCredentials("sandbox");
        $this->assertArrayHasKey("id", $result["credentials"]);
        $this->assertArrayHasKey("hostname", $result["credentials"]);
        $this->assertArrayHasKey("db", $result["credentials"]);
        $this->assertArrayHasKey("password", $result["credentials"]);
        $this->assertArrayHasKey("user", $result["credentials"]);
        $this->assertArrayHasKey("schema", $result["credentials"]);
        $this->assertArrayHasKey("touch", $result);
        $this->assertArrayHasKey("id", $result);
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        $conn->close();

        $result2 = $this->client->getCredentials("sandbox");
        $this->assertEquals($result["id"], $result2["id"]);
        $this->assertEquals($result["credentials"], $result2["credentials"]);

        $this->client->dropCredentials($result["id"]);

    }

    /**
     *
     */
    public function testCreateLuckyguessCredentials()
    {
        $result = $this->client->getCredentials("luckyguess");
        $this->assertArrayHasKey("id", $result["credentials"]);
        $this->assertArrayHasKey("hostname", $result["credentials"]);
        $this->assertArrayHasKey("db", $result["credentials"]);
        $this->assertArrayHasKey("password", $result["credentials"]);
        $this->assertArrayHasKey("user", $result["credentials"]);
        $this->assertArrayHasKey("schema", $result["credentials"]);
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("touch", $result);
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        $conn->close();

        $result2 = $this->client->getCredentials("luckyguess");
        $this->assertEquals($result["id"], $result2["id"]);
        $this->assertEquals($result["credentials"], $result2["credentials"]);

        $this->client->dropCredentials($result["id"]);
    }

    /**
     *
     */
    public function testCreateWriterCredentials()
    {
        $result = $this->client->getCredentials("writer");
        $this->assertArrayHasKey("id", $result["credentials"]);
        $this->assertArrayHasKey("hostname", $result["credentials"]);
        $this->assertArrayHasKey("db", $result["credentials"]);
        $this->assertArrayHasKey("password", $result["credentials"]);
        $this->assertArrayHasKey("user", $result["credentials"]);
        $this->assertArrayHasKey("schema", $result["credentials"]);
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("touch", $result);
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        $conn->close();

        $result2 = $this->client->getCredentials("writer");
        $this->assertEquals($result["id"], $result2["id"]);
        $this->assertEquals($result["credentials"], $result2["credentials"]);

        $this->client->dropCredentials($result["id"]);
    }

    /**
     *
     */
    public function testGetCredentials()
    {
        $result = $this->client->getCredentials();
        $id = $result["id"];
        $result = $this->client->getCredentialsById($id);
        $this->assertArrayHasKey("id", $result["credentials"]);
        $this->assertArrayHasKey("hostname", $result["credentials"]);
        $this->assertArrayHasKey("db", $result["credentials"]);
        $this->assertArrayHasKey("password", $result["credentials"]);
        $this->assertArrayHasKey("user", $result["credentials"]);
        $this->assertArrayHasKey("schema", $result["credentials"]);
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("touch", $result);
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        $conn->close();
        $this->client->dropCredentials($id);
    }

    /**
     *
     */
    public function testGetExistingCredentials()
    {
        $this->assertFalse($this->client->getExistingCredentials());
        $this->client->getCredentials();
        $result = $this->client->getExistingCredentials();
        $this->assertArrayHasKey("id", $result["credentials"]);
        $this->assertArrayHasKey("hostname", $result["credentials"]);
        $this->assertArrayHasKey("db", $result["credentials"]);
        $this->assertArrayHasKey("password", $result["credentials"]);
        $this->assertArrayHasKey("user", $result["credentials"]);
        $this->assertArrayHasKey("schema", $result["credentials"]);
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
        $result = $this->client->getCredentials();
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        $id = $result["id"];
        $this->assertTrue($this->client->killProcesses($id));
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
     * @expectedException PDOException
     */
    public function testDropCredentialsWithoutTerminating()
    {
        $result = $this->client->getCredentials();
        $conn = $this->connect($result["credentials"]);
        $this->assertTrue($this->dbQuery($conn));
        $id = $result["id"];
        $conn->close();
        $this->assertTrue($this->client->dropCredentials($id));
        $this->connect($result["credentials"]);
    }

    /**
     *
     */
    public function testDropCredentials()
    {
        $this->markTestSkipped(
          'Dropping credentials with killing not yet supported'
        );

        $result = $this->client->getCredentials();
        $conn = $this->connect($result["credentials"]);
        $this->assertTrue($this->dbQuery($conn));
        $id = $result["id"];
        $this->assertTrue($this->client->dropCredentials($id));
        $this->assertFalse($this->dbQuery($conn));
        $conn->close();
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
     * @expectedException  \Doctrine\DBAL\DBALException
     * @expectedExceptionMessageRegExp  /SQLSTATE[42501]: Insufficient privilege: 7 ERROR:  permission denied for relation svv_table_info/
     */
    public function testMetaQuery() {
        $result = $this->client->getCredentials();
        $conn = $this->connect($result["credentials"]);
        $conn->query("SELECT * FROM SVV_TABLE_INFO;");
    }


    /**
     *
     */
    public function testDropWorkspace()
    {
        $result = $this->client->getCredentials();
        $workspaceId = $result["credentials"]["workspaceId"];
        $storageApiClient = new \Keboola\StorageApi\Client(["token" => PROVISIONING_API_TOKEN]);
        $storageApiClient->apiDelete("storage/workspaces/{$workspaceId}");
        $result = $this->client->getCredentials();
        $this->assertNotEquals($result["credentials"]["workspaceId"], $workspaceId);
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
        }
        return true;
    }
}
