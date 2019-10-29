<?php

require_once ROOT_PATH . "/tests/Test/ProvisioningTestCase.php";

use Keboola\Provisioning\Client;

class Keboola_ProvisioningClient_DataLoaderApiTest extends \ProvisioningTestCase
{
    public static function setUpBeforeClass()
    {
        // PRE cleanup
        \ProvisioningTestCase::cleanUpAsync("docker", "jupyter", PROVISIONING_API_TOKEN);
    }

    public static function tearDownAfterClass()
    {
        // PRE cleanup
        \ProvisioningTestCase::cleanUpAsync("docker", "jupyter", PROVISIONING_API_TOKEN);
    }

    public function setUp()
    {
        $this->client = new Client("docker", PROVISIONING_API_TOKEN, "ProvisioningApiTest", PROVISIONING_API_URL, SYRUP_QUEUE_URL);
    }

    public function testInputData()
    {
        $result = $this->client->getCredentialsAsync("jupyter");

        $response = $this->client->loadData($result['id'], 'input: {
            tables: [
                {
                    source: "in.c-sandbox.test",
                    destination: "source.csv"
                }
            ]
        }');

        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('component', $response);
        $this->assertArrayHasKey('command', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('input', $response['command']);
        $this->assertEquals('success', $response['status']);
    }

    public function testInvalidInputData()
    {
        $result = $this->client->getCredentialsAsync("jupyter");
        try {
            $response = $this->client->loadData($result['id'], 'input: {
                tables: [
                    [
                        source: "in.c-sandbox.test",
                        destination => "source.csv"
                    ]
                ]
            }');
            $this->fail('incorrect message body should fail');
        } catch (\Exception $e) {
            echo $e->getMessage() . " error code " . $e->getCode();
        }
    }
}