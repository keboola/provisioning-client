<?php

use Keboola\Provisioning\Client;
use PHPUnit\Framework\TestCase;

class ProvisioningTestCase extends TestCase
{
    /**
   	 * @var Client
   	 */
   	protected $client;

    public static function cleanUp($backend, $type, $token)
    {
        $client = new \Guzzle\Http\Client(PROVISIONING_API_URL);
        $client->getConfig()->set('curl.options', array(
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_0,
            CURLOPT_TIMEOUT => 300
        ));

        $headers = array(
            "X-StorageApi-Token" => $token,
            "X-KBC-RunId" => "ProvisioningApiTest (Cleanup)"
        );
        $request = $client->get($backend . "?type={$type}", $headers);
        $idToDelete = 0;
        try {
            $request->send();
            $data = json_decode($request->getResponse()->getBody(true), true);
            $idToDelete = $data["credentials"]["id"];
        } catch (\Guzzle\Http\Exception\ClientErrorResponseException $e) {
            if ($e->getResponse()->getStatusCode() != 404) {
                throw $e;
            }
        }
        if ($idToDelete) {
            $requestDelete = $client->delete($backend . "/" . $idToDelete, $headers);
            $requestDelete->send();
        }
    }

    public static function cleanUpAsync($backend, $type, $token)
    {
        $client = new \Guzzle\Http\Client(PROVISIONING_API_URL);
        $client->getConfig()->set('curl.options', array(
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_0,
            CURLOPT_TIMEOUT => 300
        ));

        $headers = array(
            "X-StorageApi-Token" => $token,
            "X-KBC-RunId" => "ProvisioningApiTest (Cleanup)"
        );
        $request = $client->get($backend . "?type={$type}", $headers);
        $idToDelete = 0;
        try {
            $request->send();
            $data = json_decode($request->getResponse()->getBody(true), true);
            $idToDelete = $data["credentials"]["id"];
        } catch (\Guzzle\Http\Exception\ClientErrorResponseException $e) {
            if ($e->getResponse()->getStatusCode() != 404) {
                throw $e;
            }
        }
        if ($idToDelete) {
            $syrupClient = new \Keboola\Syrup\Client([
                "token" => $token,
                "runId" => "ProvisioningApiTest (Cleanup)",
                "super" => "provisioning",
                "url" => substr(PROVISIONING_API_URL, 0, strrpos(PROVISIONING_API_URL, '/')),
                "queueUrl" => SYRUP_QUEUE_URL
            ]);
            $syrupClient->runAsyncAction("async/" . $backend . "/" . $idToDelete, "DELETE");
        }
    }
}
