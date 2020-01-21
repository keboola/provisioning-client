<?php

require_once ROOT_PATH . "/tests/Test/ProvisioningTestCase.php";

use Keboola\Csv\CsvFile;
use Keboola\Provisioning\Client;
use Keboola\StorageApi\Client as SapiClient;
use Keboola\StorageApi\ClientException;
use Keboola\StorageApi\Options\ListFilesOptions;
use Keboola\Temp\Temp;

class Keboola_ProvisioningClient_DataLoaderApiTest extends \ProvisioningTestCase
{
    const TEST_FILE_TAG = "provisioning-client-tests";
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

    protected function removeTestFiles(): void
    {
        // remove uploaded test files
        // let sapi finish it's work https://github.com/keboola/connection/issues/1925
        sleep(2);
        $testFiles = $this->sapiClient->listFiles(
            (new ListFilesOptions())->setTags([self::TEST_FILE_TAG])
        );
        foreach ($testFiles as $testFile) {
            $this->sapiClient->deleteFile($testFile['id']);
        }
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

    public function testOutputData()
    {
        $result = $this->client->getCredentialsAsync("jupyter");

        $response = $this->client->unloadData($result['id'], [
            'tables' => [
                [
                    'source' => 'source.csv',
                    'destination' => 'in.c-sandbox.test'
                ]
            ]
        ]);
        // since there is no data in the sandbox output folder this should 404
        $this->assertEquals('error', $response['status']);
        $this->assertContains(
            'Loading data to storage failed: Client error:',
            $response['result']['message']
        );
        $this->assertContains(
            'resulted in a `404 Not Found`',
            $response['result']['message']
        );
    }

    public function testSaveFile()
    {
        $this->removeTestFiles();
        $result = $this->client->getCredentialsAsync("jupyter");
        $response = $this->client->saveFile($result['id'], [
                "source" => "notebook.ipynb",
                "tags" => [self::TEST_FILE_TAG, "test-tag"]
        ]);
        $this->assertEquals('success', $response['status']);

        $listOptions = new ListFilesOptions();
        $listOptions->setTags([self::TEST_FILE_TAG]);
        sleep(1);
        $files = $this->sapiClient->listFiles($listOptions);
        $this->assertCount(1, $files);
        $this->assertEquals('notebook.ipynb', $files[0]['name']);
        $this->assertEquals(
            [self::TEST_FILE_TAG, 'test-tag', 'jupyter_workspace'],
            $files[0]['tags']
        );
    }
}