<?php
/**
 *
 * User: Ondřej Hlaváček
 * Date: 13.8.12
 * Time: 8:52
 *
 */

class ProvisioningTestCase extends \PHPUnit_Framework_TestCase
{
    /**
   	 * @var \Keboola\Provisioning\Client
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
}
