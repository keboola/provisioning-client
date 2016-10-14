<?php
/**
 *
 * User: Ondrej Hlavacek
 * Date: 10.7.2014
 *
 */
require_once ROOT_PATH . "/tests/Test/ProvisioningTestCase.php";

use Keboola\Provisioning\Client;

class Keboola_ProvisioningClient_RSTudioTest extends \ProvisioningTestCase
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
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("hostname", $result);
        $this->assertArrayHasKey("password", $result);
        $this->assertArrayHasKey("user", $result);
        $this->assertArrayHasKey("port", $result);

        // test connection
        $this->assertTrue($this->connect($result));

        // reuse credentials
        $result2 = $this->client->getCredentialsAsync("rstudio");
        $this->assertEquals($result, $result2);
    }

    /**
     *
     */
    public function testDropRStudioCredentials()
    {
        $result = $this->client->getCredentialsAsync("rstudio");
        $this->assertTrue($this->connect($result));

        $this->client->dropCredentialsAsync($result["id"]);
        // test connection
        $this->assertFalse($this->connect($result));
    }

    public function connect($credentials)
    {
        $client = new \Guzzle\Http\Client("http://" . $credentials["hostname"] . ":" . $credentials["port"]);
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
            throw $e;
        }
        return false;
    }
}
