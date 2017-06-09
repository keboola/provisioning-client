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
        if ($sapiClient->bucketExists("in.c-UPPERCASE")) {
            foreach($sapiClient->listTables("in.c-UPPERCASE") as $table) {
                $sapiClient->dropTable($table["id"]);
            }
            $sapiClient->dropBucket("in.c-UPPERCASE");
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

        $result2 = $this->client->getCredentials();
        $this->assertEquals($result["credentials"], $result2["credentials"]);
        $this->assertEquals($result["id"], $result2["id"]);
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
        $this->assertEquals($result["credentials"], $result2["credentials"]);
        $this->assertEquals($result["id"], $result2["id"]);
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
        $this->assertArrayHasKey("touch", $result);
        $this->assertArrayHasKey("id", $result);
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        $conn->close();

        $result2 = $this->client->getCredentials("luckyguess");
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
        $this->assertArrayHasKey("touch", $result);
        $this->assertArrayHasKey("id", $result);
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
        $this->assertArrayHasKey("touch", $result);
        $this->assertArrayHasKey("id", $result);
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
     *
     */
    public function testDropCredentials()
    {
        $result = $this->client->getCredentials();
        $conn = $this->connect($result["credentials"]);
        $this->assertTrue($this->dbQuery($conn));
        $id = $result["id"];
        $this->assertTrue($this->client->dropCredentials($id));
        $this->assertFalse($this->dbQuery($conn));
        $conn->close();

        $result = $this->client->getCredentials("sandbox");
        $id = $result["id"];
        $conn = $this->connect($result["credentials"]);
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
     *
     */
    public function testBucketPermissions()
    {
        $sapiClient = new \Keboola\StorageApi\Client(array("token" => PROVISIONING_API_TOKEN));
        $csv = new \Keboola\Csv\CsvFile(ROOT_PATH . "/tests/data/table.csv");
        $sapiClient->createBucket("redshift", \Keboola\StorageApi\Client::STAGE_IN, "provisioning test", "redshift");
        $sapiClient->createBucket("redshift", \Keboola\StorageApi\Client::STAGE_OUT, "provisioning test", "redshift");
        $sapiClient->createBucket("UPPERCASE", \Keboola\StorageApi\Client::STAGE_IN, "provisioning test", "redshift");
        $sapiClient->createTable("in.c-redshift", "test", $csv);
        $sapiClient->createTable("in.c-redshift", "UPPERCASE", $csv);
        $sapiClient->createTable("out.c-redshift", "test", $csv);
        $sapiClient->createTable("out.c-redshift", "UPPERCASE", $csv);
        $sapiClient->createTable("in.c-UPPERCASE", "test", $csv);
        $sapiClient->createTable("in.c-UPPERCASE", "UPPERCASE", $csv);
        $result = $this->client->getCredentials();
        $conn = $this->connect($result["credentials"]);
        $this->dbQuery($conn);
        $this->assertCount(2, $conn->fetchAll("SELECT * FROM \"in.c-redshift\".\"test\";"));
        $this->assertCount(2, $conn->fetchAll("SELECT * FROM \"in.c-redshift\".\"uppercase\""));
        $this->assertCount(2, $conn->fetchAll("SELECT * FROM \"out.c-redshift\".\"test\""));
        $this->assertCount(2, $conn->fetchAll("SELECT * FROM \"out.c-redshift\".\"uppercase\""));
        $this->assertCount(2, $conn->fetchAll("SELECT * FROM \"in.c-uppercase\".\"test\""));
        $this->assertCount(2, $conn->fetchAll("SELECT * FROM \"in.c-uppercase\".\"uppercase\""));
        $conn->close();
    }


    public function testMetaQueryTransformation() {
        $sapiClient = new \Keboola\StorageApi\Client(array("token" => PROVISIONING_API_TOKEN));
        $csv = new \Keboola\Csv\CsvFile(ROOT_PATH . "/tests/data/table.csv");

        $sapiClient->createBucket("redshift", \Keboola\StorageApi\Client::STAGE_IN, "provisioning test", "redshift");
        $sapiClient->createBucket("redshift", \Keboola\StorageApi\Client::STAGE_OUT, "provisioning test", "redshift");
        $sapiClient->createTable("in.c-redshift", "test", $csv);
        $sapiClient->createTable("out.c-redshift", "test", $csv);

        $result = $this->client->getCredentials();
        $conn = $this->connect($result["credentials"]);
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
        $conn = $this->connect($result["credentials"]);
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
