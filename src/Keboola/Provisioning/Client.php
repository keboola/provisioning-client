<?php
namespace Keboola\Provisioning;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Message\RequestInterface;

/**
 * Class Client
 *
 * usage:
 *
 * $client = new \Keboola\Provisioning\Client("mysql", "token", "runid");
 * $credentials = $client->getCredentials("transformations");
 *
 * @package Keboola\Provisioning
 */
class Client
{

	/**
	 * @var string
	 */
	private $runId;

	/**
	 * @var string
	 */
	private $token;

	/**
	 * @var string
	 */
	private $backend = "mysql";

	/**
	 * @var int
	 */
	private $timeout = 300;

	/**
	 * @var \Guzzle\Http\Client $client
	 */
	private $client = null;

	/**
	 * @var string
	 */
	private $provisioningStorageUrl = '';

	/**
	 * @var string
	 */
	private $provisioningStorageToken = '';

	/**
     * Constructor.
     * 
	 * @param string $backend Backend type, currently 'mysql' or 'redshift' is accepted.
	 * @param string $token Storage API token.
	 * @param string $runId Storage API run Id.
	 * @param string $url
	 */
	public function __construct($backend, $token, $runId, $url='https://syrup.keboola.com/provisioning')
	{
		$this->setBackend($backend);
		$this->setToken($token);
		$this->setRunId($runId);
		$client = new \Guzzle\Http\Client($url);
		$client->getConfig()->set('curl.options', array(
			CURLOPT_SSLVERSION => 3,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_0,
			CURLOPT_TIMEOUT => $this->timeout
		));

		$this->client = $client;
	}

	/**
	 * @param string $type
	 * @return mixed
	 */
	public function getCredentials($type="transformations")
	{
		$response = $this->createCredentialsRequest($type);
		unset($response["status"]);
		return $response;
	}

	/**
	 * @param $id
	 * @return mixed
	 */
	public function getCredentialsById($id) {
		$response = $this->getCredentialsByIdRequest($id);
		unset($response["status"]);
		return $response;
	}

	/**
	 * @param $id
	 * @return bool
	 */
	public function dropCredentials($id) {
		$this->dropCredentialsRequest($id);
		return true;
	}

	/**
	 * @param $id
	 * @return bool
	 */
	public function killProcesses($id) {
		$this->killProcessesRequest($id);
		return true;
	}

    /**
     * @param $id
     * @param $tokenId
     * @return bool
     */
    public function shareCredentials($id, $tokenId) {
        $this->shareCredentialsRequest($id, $tokenId);
        return true;
    }

    /**
	 * @param string $type
	 * @return mixed
	 */
	private function createCredentialsRequest($type="transformations")
	{
		$body = array("type" => $type);
		$request = $this->client->post($this->getBackend(), $this->getHeaders(), json_encode($body));
		return $this->sendRequest($request);
	}

	/**
	 * @param string $id
	 * @return mixed
	 */
	private function getCredentialsByIdRequest($id)
	{
		$request = $this->client->get($this->getBackend() . "/" . $id, $this->getHeaders());
		return $this->sendRequest($request);
	}

	/**
	 * @param string $id
	 * @return mixed
	 */
	private function killProcessesRequest($id)
	{
		$request = $this->client->post($this->getBackend() . "/" . $id . "/kill", $this->getHeaders());
		return $this->sendRequest($request);
	}

	/**
	 * @param string $id
	 * @return mixed
	 */
	private function dropCredentialsRequest($id)
	{
		$request = $this->client->delete($this->getBackend() . "/" . $id, $this->getHeaders());
		return $this->sendRequest($request);
	}

    /**
     * @param $id
     * @param $tokenId
     * @return mixed
     */
    private function shareCredentialsRequest($id, $tokenId)
    {
        $request = $this->client->post($this->getBackend() . "/" . $id . "/share/" . $tokenId, $this->getHeaders());
        return $this->sendRequest($request);
    }

	/**
	 * @param $request RequestInterface
	 * @return mixed
	 * @throws Exception
	 */
	private function sendRequest(RequestInterface $request) {
		try {
			$request->send();
		} catch (ClientErrorResponseException $e) {
			$data = json_decode($request->getResponse()->getBody(true), true);
			throw new Exception('Error from Provisioning API: ' . $data["message"], null, $e);
		} catch (BadResponseException $e) {
			throw new Exception('Error receiving response from Provisioning API', null, $e);
		}
		$result = $this->parseResponse($request->getResponse()->getBody(true));
		return $result;
	}

	/**
	 * @return array
	 */
	private function getHeaders() {
		$headers = array(
			"X-StorageApi-Token" => "{$this->getToken()}",
			"X-KBC-RunId" => "{$this->getRunId()}"
		);
		if ($this->getProvisioningStorageToken()) {
			$headers["X-Provisioning-Token"] = $this->getProvisioningStorageToken();
		}
		if ($this->getProvisioningStorageUrl()) {
			$headers["X-StorageApi-Url"] = $this->getProvisioningStorageUrl();
		}

		return $headers;
	}

	/**
	 *
	 * Converts JSON to object and detects errors
	 *
	 * @param $jsonString
	 * @throws Exception
	 * @return mixed
	 */
	private function parseResponse($jsonString)
	{
		// Detect JSON string
		$data = json_decode($jsonString, true);
		if ($data === null) {
			throw new Exception("Provisioning API response empty or invalid JSON ($jsonString)", null, null, "PROVISIONING_API_INVALID_RESPONSE", array("response" => $jsonString));
		}
		if (is_string($data)) {
			return $data;
		}
		if(isset($data["status"]) && $data["status"] != 'ok' && $data["status"] != "MAINTENANCE") {
			$message = "Provisioning API error: " . $data["message"] . " ({$data["code"]})";
			$code = "PROVISIONING_API_ERROR_" . $data["code"];
			throw new Exception($message, null, null, $code, $data);

		}
		if(isset($data["status"]) && $data["status"] == "maintenance") {
			throw new Exception($data["reason"], null, null, "MAINTENANCE", $data);
		}
		return $data;
	}

	/**
	 * @param $backend
	 * @return $this
	 */
	private function setBackend($backend)
	{
		$this->backend = $backend;
		return $this;
	}

	/**
	 * @return string
	 */
	private function getBackend()
	{
		return $this->backend;
	}

	/**
	 * @param $url
	 * @return $this
	 */
	public function setProvisioningStorageUrl($url) {
		$this->provisioningStorageUrl = $url;
		return $this;
	}

	/**
	 * @param $token
	 * @return $this
	 */
	public function setProvisioningStorageToken($token) {
		$this->provisioningStorageToken = $token;
		return $this;
	}

	/**
	 * @return string
	 */
	private function getProvisioningStorageUrl() {
		return $this->provisioningStorageUrl;
	}

	/**
	 * @return string
	 */
	private function getProvisioningStorageToken() {
		return $this->provisioningStorageToken;
	}

	/**
	 * @param $token
	 * @return $this
	 */
	private function setToken($token)
	{
		$this->token = $token;
		return $this;
	}

	/**
	 * @return mixed
	 */
	private function getToken()
	{
		return $this->token;
	}

	/**
	 * @param $runId
	 * @return $this
	 */
	private function setRunId($runId)
	{
		$this->runId = $runId;
		return $this;
	}

	/**
	 * @return mixed
	 */
	private function getRunId()
	{
		return $this->runId;
	}


}