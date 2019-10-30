<?php

require_once ROOT_PATH . "/tests/Test/ProvisioningTestCase.php";

use Keboola\Provisioning\Client;
use Keboola\Provisioning\CredentialsNotFoundException;
use Doctrine\DBAL\DBALException;

class Keboola_ProvisioningClient_RedshiftWorkspaceTest extends \ProvisioningTestCase
{

    public static function setUpBeforeClass(): void
    {
        // PRE cleanup
        \ProvisioningTestCase::cleanUp("redshift-workspace", "sandbox", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("redshift-workspace", "transformations", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("redshift-workspace", "luckyguess", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("redshift-workspace", "writer", PROVISIONING_API_TOKEN);
    }

    public static function tearDownAfterClass(): void
    {
        // PRE cleanup
        \ProvisioningTestCase::cleanUp("redshift-workspace", "sandbox", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("redshift-workspace", "transformations", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("redshift-workspace", "luckyguess", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("redshift-workspace", "writer", PROVISIONING_API_TOKEN);
    }

    public function setUp(): void
    {
        $this->client = new Client("redshift-workspace", PROVISIONING_API_TOKEN, "ProvisioningApiTest", PROVISIONING_API_URL);
    }

    public function tearDown(): void
    {

    }

    public function testCreateTransformationCredentials()
    {
        $result = $this->client->getCredentials();
        $this->assertArrayHasKey("id", $result["credentials"]);
        $this->assertArrayHasKey("hostname", $result["credentials"]);
        $this->assertArrayHasKey("db", $result["credentials"]);
        $this->assertArrayHasKey("password", $result["credentials"]);
        $this->assertArrayHasKey("user", $result["credentials"]);
        $this->assertArrayHasKey("schema", $result["credentials"]);
        $this->assertArrayHasKey("workspaceId", $result["credentials"]);
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("touch", $result);

        $this->assertIsString($result["credentials"]["workspaceId"]);

        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        $conn->close();
        $result2 = $this->client->getCredentials();
        $this->assertEquals($result["id"], $result2["id"]);
        $this->assertEquals($result["credentials"], $result2["credentials"]);
        $this->assertEquals($result["touch"], $result2["touch"]);
        $this->client->dropCredentials($result["id"]);
    }

    public function testCreateSandboxCredentials()
    {
        $result = $this->client->getCredentials("sandbox");
        $this->assertArrayHasKey("id", $result["credentials"]);
        $this->assertArrayHasKey("hostname", $result["credentials"]);
        $this->assertArrayHasKey("db", $result["credentials"]);
        $this->assertArrayHasKey("password", $result["credentials"]);
        $this->assertArrayHasKey("user", $result["credentials"]);
        $this->assertArrayHasKey("schema", $result["credentials"]);
        $this->assertArrayHasKey("workspaceId", $result["credentials"]);
        $this->assertArrayHasKey("touch", $result);
        $this->assertArrayHasKey("id", $result);
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        $conn->close();

        $result2 = $this->client->getCredentials("sandbox");
        $this->assertEquals($result["id"], $result2["id"]);
        $this->assertEquals($result["credentials"], $result2["credentials"]);
        $this->assertEquals($result["touch"], $result2["touch"]);

        $this->client->dropCredentials($result["id"]);

    }

    public function testCreateLuckyguessCredentials()
    {
        $result = $this->client->getCredentials("luckyguess");
        $this->assertArrayHasKey("id", $result["credentials"]);
        $this->assertArrayHasKey("hostname", $result["credentials"]);
        $this->assertArrayHasKey("db", $result["credentials"]);
        $this->assertArrayHasKey("password", $result["credentials"]);
        $this->assertArrayHasKey("user", $result["credentials"]);
        $this->assertArrayHasKey("schema", $result["credentials"]);
        $this->assertArrayHasKey("workspaceId", $result["credentials"]);
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("touch", $result);
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        $conn->close();

        $result2 = $this->client->getCredentials("luckyguess");
        $this->assertEquals($result["id"], $result2["id"]);
        $this->assertEquals($result["credentials"], $result2["credentials"]);
        $this->assertEquals($result["touch"], $result2["touch"]);

        $this->client->dropCredentials($result["id"]);
    }

    public function testCreateWriterCredentials()
    {
        $result = $this->client->getCredentials("writer");
        $this->assertArrayHasKey("id", $result["credentials"]);
        $this->assertArrayHasKey("hostname", $result["credentials"]);
        $this->assertArrayHasKey("db", $result["credentials"]);
        $this->assertArrayHasKey("password", $result["credentials"]);
        $this->assertArrayHasKey("user", $result["credentials"]);
        $this->assertArrayHasKey("schema", $result["credentials"]);
        $this->assertArrayHasKey("workspaceId", $result["credentials"]);
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("touch", $result);
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        $conn->close();

        $result2 = $this->client->getCredentials("writer");
        $this->assertEquals($result["id"], $result2["id"]);
        $this->assertEquals($result["credentials"], $result2["credentials"]);
        $this->assertEquals($result["touch"], $result2["touch"]);

        $this->client->dropCredentials($result["id"]);
    }

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
        $this->assertArrayHasKey("workspaceId", $result["credentials"]);
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("touch", $result);
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        $conn->close();
        $this->client->dropCredentials($id);
    }

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
        $this->assertArrayHasKey("workspaceId", $result["credentials"]);
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("touch", $result);
        $this->client->dropCredentials($result["id"]);
    }

    public function testGetCredentialsException()
    {
        $this->expectException(CredentialsNotFoundException::class);
        $this->expectExceptionMessage('Credentials not found.');
        $this->client->getCredentialsById("123456");
    }

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

    public function testKillProcessesException()
    {
        $this->expectException(CredentialsNotFoundException::class);
        $this->expectExceptionMessage('Credentials not found.');
        $this->client->killProcesses("123456");
    }

    public function testDropCredentialsWithoutTerminating()
    {
        $this->expectException(PDOException::class);
        $result = $this->client->getCredentials();
        $conn = $this->connect($result["credentials"]);
        $this->assertTrue($this->dbQuery($conn));
        $id = $result["id"];
        $conn->close();
        $this->assertTrue($this->client->dropCredentials($id));
        $this->connect($result["credentials"]);
    }

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

    public function testDropCredentialsException()
    {
        $this->expectException(CredentialsNotFoundException::class);
        $this->expectExceptionMessage('Credentials not found.');
        $this->client->dropCredentials("123456");
    }

    public function testMetaQuery() {
        $this->expectException(DBALException::class);
        $this->expectExceptionMessageMatches('/permission denied for relation svv_table_info/');
        $result = $this->client->getCredentials();
        $conn = $this->connect($result["credentials"]);
        $conn->query("SELECT * FROM SVV_TABLE_INFO;");
    }

    public function testDropWorkspace()
    {
        $result = $this->client->getCredentials();
        $workspaceId = $result["credentials"]["workspaceId"];
        $storageApiClient = new \Keboola\StorageApi\Client([
            'url' => STORAGE_API_URL,
            'token' => PROVISIONING_API_TOKEN
        ]);
        $storageApiClient->apiDelete("storage/workspaces/{$workspaceId}");
        $result = $this->client->getCredentials();
        $this->assertNotEquals($result["credentials"]["workspaceId"], $workspaceId);
    }

    public function testExtendCredentials()
    {
        $result = $this->client->getCredentials();
        try {
            $this->client->extendCredentials($result["id"]);
        } catch (\Keboola\Provisioning\Exception $e) {
            $this->assertStringContainsString('Cannot extend workspace credentials', $e->getMessage());
        }
        $this->client->dropCredentials($result["id"]);
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
