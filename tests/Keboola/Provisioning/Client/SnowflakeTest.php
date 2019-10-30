<?php

require_once ROOT_PATH . "/tests/Test/ProvisioningTestCase.php";

use Keboola\Provisioning\Client;
use Keboola\Provisioning\CredentialsNotFoundException;

class Keboola_ProvisioningClient_SnowflakeTest extends \ProvisioningTestCase
{

    public static function setUpBeforeClass(): void
    {
        // PRE cleanup
        \ProvisioningTestCase::cleanUp("snowflake", "sandbox", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("snowflake", "transformations", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("snowflake", "luckyguess", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("snowflake", "writer", PROVISIONING_API_TOKEN);
    }

    public static function tearDownAfterClass(): void
    {
        // PRE cleanup
        \ProvisioningTestCase::cleanUp("snowflake", "sandbox", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("snowflake", "transformations", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("snowflake", "luckyguess", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("snowflake", "writer", PROVISIONING_API_TOKEN);
    }

    public function setUp(): void
    {
        $this->client = new Client("snowflake", PROVISIONING_API_TOKEN, "ProvisioningApiTest", PROVISIONING_API_URL);
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
        odbc_close($conn);

        $result2 = $this->client->getCredentials();
        $this->assertEquals($result["credentials"], $result2["credentials"]);
        $this->assertEquals($result["id"], $result2["id"]);
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
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("touch", $result);
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        odbc_close($conn);

        $result2 = $this->client->getCredentials("sandbox");
        $this->assertEquals($result["credentials"], $result2["credentials"]);
        $this->assertEquals($result["id"], $result2["id"]);
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
        odbc_close($conn);

        $result2 = $this->client->getCredentials("luckyguess");
        $this->assertEquals($result["credentials"], $result2["credentials"]);
        $this->assertEquals($result["id"], $result2["id"]);
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
        odbc_close($conn);

        $result2 = $this->client->getCredentials("writer");
        $this->assertEquals($result["credentials"], $result2["credentials"]);
        $this->assertEquals($result["id"], $result2["id"]);
        $this->assertEquals($result["touch"], $result2["touch"]);

        $this->client->dropCredentials($result["id"]);
    }

    public function testCreateWriterCredentialsTimeout()
    {
        $result = $this->client->getCredentials("writer");
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);

        try {
            odbc_exec($conn, "CALL SYSTEM\$WAIT(70);");
            $this->fail("Query didn't time out.");
        } catch(\PHPUnit_Framework_Error_Warning $e) {
            if (strpos($e->getMessage(), 'SFExecuteQueryTimeout') !== false) {
                $this->assertContains("SFExecuteQueryTimeout", $e->getMessage());
            } else {
                $this->assertContains("timeout", $e->getMessage());
            }
        } finally {
            odbc_close($conn);
            $this->client->dropCredentials($result["id"]);
        }
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
        odbc_close($conn);
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
        $this->assertArrayHasKey("warehouse", $result["credentials"]);
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
        $this->markTestSkipped(
          'Killing Snowflake transactions not yet supported'
        );
        $result = $this->client->getCredentials();
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        $id = $result["id"];
        $this->assertTrue($this->client->killProcesses($id));
        $this->assertFalse($this->dbQuery($conn));
        odbc_close($conn);
        $this->client->dropCredentials($id);
    }

    public function testKillProcessesException()
    {
        $this->expectException(CredentialsNotFoundException::class);
        $this->expectExceptionMessage('Credentials not found.');
        $this->client->killProcesses("123456");
    }

    public function testDropCredentials()
    {
        $result = $this->client->getCredentials();
        $conn = $this->connect($result["credentials"]);
        $this->assertTrue($this->dbQuery($conn));
        $id = $result["id"];
        $this->assertTrue($this->client->dropCredentials($id));
        $this->assertFalse($this->dbQuery($conn));
        odbc_close($conn);

        $result = $this->client->getCredentials("sandbox");
        $id = $result["id"];
        $conn = $this->connect($result["credentials"]);
        $this->assertTrue($this->client->dropCredentials($id));
        $this->assertFalse($this->dbQuery($conn));
        odbc_close($conn);

        $result = $this->client->getCredentials("luckyguess");
        $id = $result["id"];
        $conn = $this->connect($result["credentials"]);
        $this->assertTrue($this->client->dropCredentials($id));
        $this->assertFalse($this->dbQuery($conn));
        odbc_close($conn);
    }

    public function testDropCredentialsException()
    {
        $this->expectException(CredentialsNotFoundException::class);
        $this->expectExceptionMessage('Credentials not found.');
        $this->client->dropCredentials("123456");
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
     * @return resource
     * @throws \Keboola\Provisioning\Exception
     */
    public function connect($credentials)
    {
        $dsn = "Driver=SnowflakeDSIIDriver;Server=" . $credentials["hostname"];
        $dsn .= ";Port=443";
        $dsn .= ";Schema=\"" . $credentials["schema"] . "\"";
        $dsn .= ";Database=\"" . $credentials["db"] . "\"";
        $dsn .= ";Warehouse=\"" . $credentials["warehouse"] . "\"";
	    $dsn .= ";Tracing=0";
	    $dsn .= ";Query_Timeout=30";
	    $dsn .= ";Login_Timeout=30";

        try {
            $connection = odbc_connect($dsn, $credentials["user"], $credentials["password"]);
        } catch (\Exception $e) {
            throw new \Keboola\Provisioning\Exception("Initializing Snowflake connection failed: " . $e->getMessage(), "SNOWFLAKE_INIT_FAILED", $e);
        }
        return $connection;
    }

    /**
     * @param $connection
     * @return bool
     */
    public function dbQuery($connection)
    {
        try {
            odbc_exec($connection, "SELECT 1;");
        } catch(\Exception $e) {
            return false;
        }
        return true;
    }
}
