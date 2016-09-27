<?php
/**
 *
 * User: Ondrej Hlavacek
 * Date: 10.7.2014
 *
 */
require_once ROOT_PATH . "/tests/Test/ProvisioningTestCase.php";

use Keboola\Provisioning\Client;

class Keboola_ProvisioningClient_SnowflakeTest extends \ProvisioningTestCase
{

    public static function setUpBeforeClass()
    {
        // PRE cleanup
        \ProvisioningTestCase::cleanUp("snowflake", "sandbox", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("snowflake", "transformations", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("snowflake", "luckyguess", PROVISIONING_API_TOKEN);
    }

    public static function tearDownAfterClass()
    {
        // PRE cleanup
        \ProvisioningTestCase::cleanUp("snowflake", "sandbox", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("snowflake", "transformations", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("snowflake", "luckyguess", PROVISIONING_API_TOKEN);
    }

    public function setUp()
    {
        $this->client = new Client("snowflake", PROVISIONING_API_TOKEN, "ProvisioningApiTest", PROVISIONING_API_URL);
    }

    /**
     *
     */
    public function testCreateTransformationCredentials()
    {
        $result = $this->client->getCredentials();
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("hostname", $result);
        $this->assertArrayHasKey("db", $result);
        $this->assertArrayHasKey("password", $result);
        $this->assertArrayHasKey("user", $result);
        $this->assertArrayHasKey("schema", $result);
        $this->assertArrayHasKey("warehouse", $result);
        $conn = $this->connect($result);
        $this->dbQuery($conn);
        odbc_close($conn);

        $result2 = $this->client->getCredentials();
        $this->assertEquals($result, $result2);

        $this->client->dropCredentials($result["id"]);

    }

    /**
     *
     */
    public function testCreateSandboxCredentials()
    {
        $result = $this->client->getCredentials("sandbox");
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("hostname", $result);
        $this->assertArrayHasKey("db", $result);
        $this->assertArrayHasKey("password", $result);
        $this->assertArrayHasKey("user", $result);
        $this->assertArrayHasKey("schema", $result);
        $this->assertArrayHasKey("warehouse", $result);
        $conn = $this->connect($result);
        $this->dbQuery($conn);
        odbc_close($conn);

        $result2 = $this->client->getCredentials("sandbox");
        $this->assertEquals($result, $result2);

        $this->client->dropCredentials($result["id"]);

    }

    /**
     *
     */
    public function testCreateLuckyguessCredentials()
    {
        $result = $this->client->getCredentials("luckyguess");
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("hostname", $result);
        $this->assertArrayHasKey("db", $result);
        $this->assertArrayHasKey("password", $result);
        $this->assertArrayHasKey("user", $result);
        $this->assertArrayHasKey("schema", $result);
        $this->assertArrayHasKey("warehouse", $result);
        $conn = $this->connect($result);
        $this->dbQuery($conn);
        odbc_close($conn);

        $result2 = $this->client->getCredentials("luckyguess");
        $this->assertEquals($result, $result2);

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
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("hostname", $result);
        $this->assertArrayHasKey("db", $result);
        $this->assertArrayHasKey("password", $result);
        $this->assertArrayHasKey("user", $result);
        $this->assertArrayHasKey("schema", $result);
        $this->assertArrayHasKey("warehouse", $result);
        $conn = $this->connect($result);
        $this->dbQuery($conn);
        odbc_close($conn);
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
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("hostname", $result);
        $this->assertArrayHasKey("db", $result);
        $this->assertArrayHasKey("password", $result);
        $this->assertArrayHasKey("user", $result);
        $this->assertArrayHasKey("schema", $result);
        $this->assertArrayHasKey("warehouse", $result);
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
        $this->markTestSkipped(
          'Killing Snowflake transactions not yet supported'
        );
        $result = $this->client->getCredentials();
        $conn = $this->connect($result);
        $this->dbQuery($conn);
        $id = $result["id"];
        $result = $this->client->killProcesses($id);
        $this->assertTrue($result);
        $this->assertFalse($this->dbQuery($conn));
        odbc_close($conn);
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
        $conn = $this->connect($result);
        $this->assertTrue($this->dbQuery($conn));
        $id = $result["id"];
        $result = $this->client->dropCredentials($id);
        $this->assertTrue($result);
        $this->assertFalse($this->dbQuery($conn));
        odbc_close($conn);

        $result = $this->client->getCredentials("sandbox");
        $id = $result["id"];
        $conn = $this->connect($result);
        $result = $this->client->dropCredentials($id);
        $this->assertTrue($result);
        $this->assertFalse($this->dbQuery($conn));
        odbc_close($conn);

        $result = $this->client->getCredentials("luckyguess");
        $id = $result["id"];
        $conn = $this->connect($result);
        $result = $this->client->dropCredentials($id);
        $this->assertTrue($result);
        $this->assertFalse($this->dbQuery($conn));
        odbc_close($conn);
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
    public function testDropWorkspace()
    {
        $result = $this->client->getCredentials();
        $workspaceId = $result["workspaceId"];
        $storageApiClient = new \Keboola\StorageApi\Client(["token" => PROVISIONING_API_TOKEN]);
        $storageApiClient->apiDelete("storage/workspaces/{$workspaceId}");
        $result = $this->client->getCredentials();
        $this->assertNotEquals($result["workspaceId"], $workspaceId);
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
