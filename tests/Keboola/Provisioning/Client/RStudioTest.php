<?php
/**
 *
 * User: Ondrej Hlavacek
 * Date: 10.7.2014
 *
 */
require_once ROOT_PATH . "/tests/Test/ProvisioningTestCase.php";

use Keboola\Provisioning\Client;

class Keboola_ProvisioningClient_RStudioTest extends \ProvisioningTestCase
{

    public static function setUpBeforeClass()
    {
        // PRE cleanup
        \ProvisioningTestCase::cleanUpAsync("docker", "rstudio", PROVISIONING_API_TOKEN);
    }

    public static function tearDownAfterClass()
    {
        // PRE cleanup
        \ProvisioningTestCase::cleanUpAsync("docker", "rstudio", PROVISIONING_API_TOKEN);
    }

    public function setUp()
    {
        $this->client = new Client("docker", PROVISIONING_API_TOKEN, "ProvisioningApiTest", PROVISIONING_API_URL, SYRUP_QUEUE_URL);
    }

    /**
     *
     */
    public function testCreateRStudioCredentials()
    {
        $result = $this->client->getCredentialsAsync("rstudio");
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
        $result2 = $this->client->getCredentialsAsync("rstudio");
        $this->assertEquals($result["credentials"], $result2["credentials"]);
        $this->assertEquals($result["id"], $result2["id"]);
        $this->assertEquals($result["touch"], $result2["touch"]);
    }

    /**
     *
     */
    public function testDropRStudioCredentials()
    {
        $result = $this->client->getCredentialsAsync("rstudio");
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
        $result = $this->client->getCredentialsAsync("rstudio");
        $result2 = $this->client->extendCredentials($result["id"]);
        $this->assertArrayHasKey("id", $result2);
        $this->assertArrayHasKey("touch", $result2);
        $this->assertArrayNotHasKey("credentials", $result2);
        $this->assertGreaterThan($result["touch"], $result2["touch"]);
        $this->client->dropCredentialsAsync($result["id"]);
    }

    public function connect($credentials)
    {
        $client = new \Guzzle\Http\Client("https://" . $credentials["hostname"] . ":" . $credentials["port"]);
        $client->getConfig()->set('curl.options', array(
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_0,
            CURLOPT_TIMEOUT => 5
        ));
        $request = $client->get();
        try {
            $request->send();
            $body = $request->getResponse()->getBody(true);
            if (strpos($body, "RStudio") > 0) {
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
