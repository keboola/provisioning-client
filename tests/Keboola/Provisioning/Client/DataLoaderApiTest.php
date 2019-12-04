<?php

require_once ROOT_PATH . "/tests/Test/ProvisioningTestCase.php";

use Keboola\Csv\CsvFile;
use Keboola\Provisioning\Client;
use Keboola\StorageApi\Client as SapiClient;
use Keboola\StorageApi\ClientException;
use Keboola\Temp\Temp;

class Keboola_ProvisioningClient_DataLoaderApiTest extends \ProvisioningTestCase
{
    /** @var SapiClient */
    private $sapiClient;

    public static function setUpBeforeClass(): void
    {
        // PRE cleanup
        \ProvisioningTestCase::cleanUpAsync("docker", "jupyter", PROVISIONING_API_TOKEN);
    }

    public static function tearDownAfterClass(): void
    {
        // PRE cleanup
        \ProvisioningTestCase::cleanUpAsync("docker", "jupyter", PROVISIONING_API_TOKEN);
    }

    public function setUp(): void
    {
        $this->client = new Client("docker", PROVISIONING_API_TOKEN, "ProvisioningApiTest", PROVISIONING_API_URL, SYRUP_QUEUE_URL);
        $this->sapiClient = new SapiClient([
            'url' => STORAGE_API_URL,
            'token' => PROVISIONING_API_TOKEN
        ]);
        try {
            $this->sapiClient->dropBucket('in.c-sandbox', ['force' => true]);
        } catch (ClientException $e) {
            if ($e->getCode() != 404) {
                throw $e;
            }
        }
        $this->sapiClient->createBucket('sandbox', 'in');
        $temp = new Temp();
        $tmpDir = $temp->getTmpFolder();
        $csv = new CsvFile($tmpDir . "/upload.csv");
        $csv->writeRow(["Id", "Name"]);
        for ($i = 0; $i < 100; $i++) {
            $csv->writeRow([$i, "test"]);
        }
        $this->sapiClient->createTable('in.c-sandbox', 'test', $csv);
        unset($csv);
    }

    public function testInputData()
    {
        $result = $this->client->getCredentialsAsync("jupyter");

        $response = $this->client->loadData($result['id'], [
            'tables' => [
                [
                    'source' => 'in.c-sandbox.test',
                    'destination' => 'source.csv'
                ]
            ]
        ]);

        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('component', $response);
        $this->assertArrayHasKey('command', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('input', $response['command']);
        $this->assertEquals('success', $response['status']);
    }
}
