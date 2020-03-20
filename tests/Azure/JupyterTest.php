<?php
namespace Keboola\Provisioning\Tests\Azure;

require_once ROOT_PATH . "/tests/Test/ProvisioningTestCase.php";

use Keboola\Provisioning\Client;
use Keboola\StorageApi\Options\Components\Configuration;
use Keboola\StorageApi\Options\Components\ConfigurationRow;

class JupyterTest extends \ProvisioningTestCase
{

    public static function setUpBeforeClass(): void
    {
        // PRE cleanup
        \ProvisioningTestCase::cleanUpAsync("kubernetes", "jupyter", PROVISIONING_API_TOKEN);
    }

    public static function tearDownAfterClass(): void
    {
        // PRE cleanup
        \ProvisioningTestCase::cleanUpAsync("kubernetes", "jupyter", PROVISIONING_API_TOKEN);
    }

    public function setUp(): void
    {
        $this->client = new Client("kubernetes", PROVISIONING_API_TOKEN, "ProvisioningApiTest", PROVISIONING_API_URL, SYRUP_QUEUE_URL);
    }

    /**
     *
     */
    public function testCreateJupyterCredentials()
    {
        $result = $this->client->getCredentialsAsync("jupyter");
        $this->assertArrayHasKey("id", $result["credentials"]);
        $this->assertArrayHasKey("hostname", $result["credentials"]);
        $this->assertArrayHasKey("password", $result["credentials"]);
        $this->assertArrayHasKey("user", $result["credentials"]);
        $this->assertArrayHasKey("port", $result["credentials"]);
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("touch", $result);

        // test connection
        $this->assertTrue($this->connect($result["credentials"]));

        // reuse credentials
        $result2 = $this->client->getCredentialsAsync("jupyter");
        $this->assertEquals($result["credentials"], $result2["credentials"]);
        $this->assertEquals($result["id"], $result2["id"]);
        $this->assertEquals($result["touch"], $result2["touch"]);
    }

    /**
     *
     */
    public function testDropJupyterCredentials()
    {
        $result = $this->client->getCredentialsAsync("jupyter");
        $this->assertTrue($this->connect($result["credentials"]));

        $this->client->dropCredentialsAsync($result["id"]);
        // test connection
        $this->assertFalse($this->connect($result["credentials"]));
    }

    /**
     *
     */
    public function testExtendCredentials()
    {
        $result = $this->client->getCredentialsAsync("jupyter");
        $result2 = $this->client->extendCredentials($result["id"]);
        $this->assertArrayHasKey("id", $result2);
        $this->assertArrayHasKey("touch", $result2);
        $this->assertArrayNotHasKey("credentials", $result2);
        $this->assertGreaterThan($result["touch"], $result2["touch"]);
        $this->client->dropCredentialsAsync($result["id"]);
    }

    public function testCreatePythonTransformationCredentials()
    {
        $client = new \Keboola\StorageApi\Client([
            'url' => STORAGE_API_URL,
            'token' => PROVISIONING_API_TOKEN
        ]);
        $components = new \Keboola\StorageApi\Components($client);
        $config = new Configuration();
        $config->setName('test-config');
        $config->setComponentId('transformation');
        $configData = $components->addConfiguration($config);
        $config->setConfigurationId($configData['id']);
        $row = new ConfigurationRow($config);
        $row->setConfiguration([
            "backend" => "kubernetes",
            "description" => "Test configuration",
            "type" => "python",
            "packages" => [],
            "tags" => [],
            "queries" => [
                "this is some script\ncode on multiple lines"
            ]
        ]);
        $rowData = $components->addConfigurationRow($row);

        $result = $this->client->getTransformationSandboxCredentialsAsync(
            $configData['id'],
            $configData['version'] + 1,
            $rowData['id']
        );
        $components->deleteConfiguration('transformation', $configData['id']);
        self::assertArrayHasKey("id", $result["credentials"]);
        self::assertArrayHasKey("hostname", $result["credentials"]);
        self::assertArrayHasKey("password", $result["credentials"]);
        self::assertArrayHasKey("user", $result["credentials"]);
        self::assertArrayHasKey("port", $result["credentials"]);
        self::assertArrayHasKey("id", $result);
        self::assertArrayHasKey("touch", $result);

        // test connection
        self::assertTrue($this->connect($result["credentials"]));
    }

    public function connect($credentials)
    {
        $client = new \Guzzle\Http\Client($credentials["url"]);
        $client->getConfig()->set('curl.options', array(
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_0,
            CURLOPT_TIMEOUT => 5
        ));
        $request = $client->get();
        try {
            $request->send();
            $body = $request->getResponse()->getBody(true);
            if (strpos($body, "Jupyter") > 0) {
                return true;
            }
        } catch (\Guzzle\Http\Exception\CurlException $e) {
            if (strpos($e->getMessage(), "Failed to connect") !== false) {
                return false;
            }
            if (strpos($e->getMessage(), "Operation timed out") !== false) {
                return false;
            }
            throw $e;
        }
        return false;
    }
}
