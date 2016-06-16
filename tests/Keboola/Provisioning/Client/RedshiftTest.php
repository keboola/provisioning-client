<?php
/**
 *
 * User: Ondrej Hlavacek
 * Date: 10.7.2014
 *
 */
require_once ROOT_PATH . "/tests/Test/ProvisioningTestCase.php";

use Keboola\Provisioning\Client;

class Keboola_ProvisioningClient_RedshiftTest extends \ProvisioningTestCase
{

    public static function setUpBeforeClass()
    {
        // PRE cleanup
        \ProvisioningTestCase::cleanUp("redshift", "sandbox", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("redshift", "transformations", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("redshift", "luckyguess", PROVISIONING_API_TOKEN);
    }

    public static function tearDownAfterClass()
    {
        // PRE cleanup
        \ProvisioningTestCase::cleanUp("redshift", "sandbox", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("redshift", "transformations", PROVISIONING_API_TOKEN);
        \ProvisioningTestCase::cleanUp("redshift", "luckyguess", PROVISIONING_API_TOKEN);
    }

    public function setUp()
    {
        $this->client = new Client("redshift", PROVISIONING_API_TOKEN, "ProvisioningApiTest", PROVISIONING_API_URL);
        $sapiClient = new \Keboola\StorageApi\Client(array("token" => PROVISIONING_API_TOKEN));
        if ($sapiClient->bucketExists("in.c-redshift")) {
            foreach($sapiClient->listTables("in.c-redshift") as $table) {
                $sapiClient->dropTable($table["id"]);
            }
            $sapiClient->dropBucket("in.c-redshift");
        }
        if ($sapiClient->bucketExists("out.c-redshift")) {
            foreach($sapiClient->listTables("out.c-redshift") as $table) {
                $sapiClient->dropTable($table["id"]);
            }
            $sapiClient->dropBucket("out.c-redshift");
        }
    }

    public function tearDown() {
        $sapiClient = new \Keboola\StorageApi\Client(array("token" => PROVISIONING_API_TOKEN));
        if ($sapiClient->bucketExists("in.c-redshift")) {
            foreach($sapiClient->listTables("in.c-redshift") as $table) {
                $sapiClient->dropTable($table["id"]);
            }
            $sapiClient->dropBucket("in.c-redshift");
        }
        if ($sapiClient->bucketExists("out.c-redshift")) {
            foreach($sapiClient->listTables("out.c-redshift") as $table) {
                $sapiClient->dropTable($table["id"]);
            }
            $sapiClient->dropBucket("out.c-redshift");
        }
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
        $conn = $this->connect($result);
        $this->dbQuery($conn);
        $conn->close();

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
        $conn = $this->connect($result);
        $this->dbQuery($conn);
        $conn->close();

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
        $conn = $this->connect($result);
        $this->dbQuery($conn);
        $conn->close();

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
        $conn = $this->connect($result);
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
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("hostname", $result);
        $this->assertArrayHasKey("db", $result);
        $this->assertArrayHasKey("password", $result);
        $this->assertArrayHasKey("user", $result);
        $this->assertArrayHasKey("schema", $result);
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
        $conn = $this->connect($result);
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
        $result = $this->client->getCredentials();
        $conn = $this->connect($result);
        $this->assertTrue($this->dbQuery($conn));
        $id = $result["id"];
        $result = $this->client->dropCredentials($id);
        $this->assertTrue($result);
        $this->assertFalse($this->dbQuery($conn));
        $conn->close();

        $result = $this->client->getCredentials("sandbox");
        $id = $result["id"];
        $conn = $this->connect($result);
        $result = $this->client->dropCredentials($id);
        $this->assertTrue($result);
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
     *
     */
    public function testBucketPermissions()
    {
        $sapiClient = new \Keboola\StorageApi\Client(array("token" => PROVISIONING_API_TOKEN));
        $tokenInfo = $sapiClient->verifyToken();
        if (in_array('transformation-force-staging', $tokenInfo["owner"]["features"])) {
            $this->markTestSkipped("transformation-force-staging feature present");
            return;
        }
        $csv = new \Keboola\Csv\CsvFile(ROOT_PATH . "/tests/data/table.csv");

        $sapiClient->createBucket("redshift", \Keboola\StorageApi\Client::STAGE_IN, "provisioning test", "redshift");
        $sapiClient->createBucket("redshift", \Keboola\StorageApi\Client::STAGE_OUT, "provisioning test", "redshift");
        $sapiClient->createTable("in.c-redshift", "test", $csv);
        $sapiClient->createTable("out.c-redshift", "test", $csv);

        $result = $this->client->getCredentials();
        $conn = $this->connect($result);
        $this->dbQuery($conn);

        $data = $conn->fetchAll("SELECT * FROM \"in.c-redshift\".\"test\" ORDER BY \"id\" ASC;");
        $this->assertEquals("1", $data[0]["id"]);
        $this->assertEquals("test1", $data[0]["name"]);
        $this->assertEquals("2", $data[1]["id"]);
        $this->assertEquals("test2", $data[1]["name"]);

        $data = $conn->fetchAll("SELECT * FROM \"out.c-redshift\".\"test\" ORDER BY \"id\" ASC;");
        $this->assertEquals("1", $data[0]["id"]);
        $this->assertEquals("test1", $data[0]["name"]);
        $this->assertEquals("2", $data[1]["id"]);
        $this->assertEquals("test2", $data[1]["name"]);

        $conn->close();
    }


    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage SQLSTATE[42501]: Insufficient privilege: 7 ERROR:  permission denied for schema in.c-redshift
     */
    public function testBucketPermissionsTransformationForceStagingFeature()
    {
        $sapiClient = new \Keboola\StorageApi\Client(array("token" => PROVISIONING_API_TOKEN));
        $tokenInfo = $sapiClient->verifyToken();
        if (!in_array('transformation-force-staging', $tokenInfo["owner"]["features"])) {
            $this->markTestSkipped("transformation-force-staging feature not present");
            return;
        }
        $csv = new \Keboola\Csv\CsvFile(ROOT_PATH . "/tests/data/table.csv");

        $sapiClient->createBucket("redshift", \Keboola\StorageApi\Client::STAGE_IN, "provisioning test", "redshift");
        $sapiClient->createBucket("redshift", \Keboola\StorageApi\Client::STAGE_OUT, "provisioning test", "redshift");
        $sapiClient->createTable("in.c-redshift", "test", $csv);
        $sapiClient->createTable("out.c-redshift", "test", $csv);

        $result = $this->client->getCredentials();
        $conn = $this->connect($result);
        $this->dbQuery($conn);

        $conn->fetchAll("SELECT * FROM \"in.c-redshift\".\"test\" ORDER BY \"id\" ASC;");
    }


    public function testMetaQueryTransformation() {
        $sapiClient = new \Keboola\StorageApi\Client(array("token" => PROVISIONING_API_TOKEN));
        $csv = new \Keboola\Csv\CsvFile(ROOT_PATH . "/tests/data/table.csv");

        $sapiClient->createBucket("redshift", \Keboola\StorageApi\Client::STAGE_IN, "provisioning test", "redshift");
        $sapiClient->createBucket("redshift", \Keboola\StorageApi\Client::STAGE_OUT, "provisioning test", "redshift");
        $sapiClient->createTable("in.c-redshift", "test", $csv);
        $sapiClient->createTable("out.c-redshift", "test", $csv);

        $result = $this->client->getCredentials();
        $conn = $this->connect($result);
        $result = $conn->fetchAll("SELECT * FROM SVV_TABLE_INFO;");
        $this->assertGreaterThan(0, count($result));
        $conn->close();
    }

    /**
     * @expectedException  \Doctrine\DBAL\DBALException
     * @expectedExceptionMessageRegExp  /SQLSTATE[42501]: Insufficient privilege: 7 ERROR:  permission denied for relation svv_table_info/
     */
    public function testMetaQuerySandbox() {
        $result = $this->client->getCredentials("sandbox");
        $conn = $this->connect($result);
        $conn->query("SELECT * FROM SVV_TABLE_INFO;");
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
