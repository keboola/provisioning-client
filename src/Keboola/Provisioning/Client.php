<?php
namespace Keboola\Provisioning;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Plugin\Backoff\BackoffPlugin;
use Guzzle\Plugin\Backoff\CurlBackoffStrategy;
use Guzzle\Plugin\Backoff\ExponentialBackoffStrategy;
use Guzzle\Plugin\Backoff\TruncatedBackoffStrategy;
use Keboola\Provisioning\CredentialsNotFoundException;
use Keboola\Syrup\ClientException;

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
	private $timeout = 900;

	/**
	 * @var \Guzzle\Http\Client
	 */
	private $client = null;
    /**
     * @var \Keboola\Syrup\Client
     */
    private $syrupClient = null;

	/**
	 * @var string
	 */
	private $provisioningStorageUrl = '';

	/**
	 * @var string
	 */
	private $provisioningStorageToken = '';

    /**
     * Client constructor.
     *
     * @param $backend
     * @param $token
     * @param $runId
     * @param string $url
     * @param string $queueUrl
     */
	public function __construct($backend, $token, $runId, $url = 'https://syrup.keboola.com/provisioning', $queueUrl = '')
	{
		$this->setBackend($backend);
		$this->setToken($token);
		$this->setRunId($runId);
		$client = new \Guzzle\Http\Client($url);
		$client->getConfig()->set('curl.options', array(
			CURLOPT_TIMEOUT => $this->timeout
		));

        $retryStrategy = new BackoffPlugin(
            new TruncatedBackoffStrategy(10,
                new ApiCallBackoffStrategy(ApiCallBackoffStrategy::getDefaultFailureCodes(),
                    new CurlBackoffStrategy(CurlBackoffStrategy::getDefaultFailureCodes(),
                        new ExponentialBackoffStrategy()
                    )
                )
            )
        );
        $client->addSubscriber($retryStrategy);

		$this->client = $client;
        $syrupUrl = substr($url, 0, strrpos($url, '/'));
        $syrupClientOptions = [
            'token' => $token,
            'runId' => $runId,
            'super' => 'provisioning',
            'url' => $syrupUrl
        ];
        if ($queueUrl) {
            $syrupClientOptions["queueUrl"] = $queueUrl;
        }
        $this->syrupClient = new \Keboola\Syrup\Client($syrupClientOptions);
	}

    /**
     * @param string $type
     * @return mixed
     */
    public function getCredentials($type = "transformations")
    {
        try {
            $response = $this->getCredentialsRequest($type);
        } catch (CredentialsNotFoundException $e) {
            $response = $this->createCredentialsRequest($type);
        }
        return $response;
    }

    /**
     * @param string $type
     * @return array
     * @throws Exception
     */
    public function getCredentialsAsync($type = "rstudio")
    {
        try {
            $created = $this->syrupClient->runAsyncAction("async/{$this->getBackend()}", "POST", ["body" => ["type" => $type]]);
            if ($created["status"] == 'error') {
                throw new Exception('Error getting credentials: ' . $created["result"]["message"]);
            }
            $response = $this->getCredentialsByIdRequest($created["result"]["credentials"]["id"]);
        } catch (ClientException $e) {
            throw new Exception('Error from Provisioning API: ' . $e->getMessage(), null, $e);
        }
        return $response;
    }

    /**
     * @param string $configId
     * @param string $configVersion
     * @param string $rowId
     * @return array
     * @throws Exception
     */
    public function getTransformationSandboxCredentialsAsync($configId, $configVersion, $rowId)
    {
        try {
            $created = $this->syrupClient->runAsyncAction(
                "async/{$this->getBackend()}/transformation",
                "POST",
                ["body" => [
                    "transformation" => [
                        'config_id' => $configId,
                        'config_version' => $configVersion,
                        'row_id' => $rowId,
                    ]
                ]]
            );
            if ($created["status"] == 'error') {
                throw new Exception('Error getting credentials: ' . $created["result"]["message"]);
            }
            $response = $this->getCredentialsByIdRequest($created["result"]["credentials"]["id"]);
        } catch (ClientException $e) {
            throw new Exception('Error from Provisioning API: ' . $e->getMessage(), null, $e);
        }
        return $response;
    }

    /**
     * @param string $type
     * @return bool|mixed
     * @throws CredentialsNotFoundException
     * @throws \Exception
     */
   	public function getExistingCredentials($type = "transformations")
   	{
        try {
            $response = $this->getCredentialsRequest($type);
        } catch (CredentialsNotFoundException $e) {
            return false;
        }
   		return $response;
   	}

	/**
	 * @param $id
	 * @return mixed
	 */
	public function getCredentialsById($id) {
		$response = $this->getCredentialsByIdRequest($id);
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
     * @return array|bool
     * @throws Exception
     */
   	public function dropCredentialsAsync($id) {
        try {
            $this->syrupClient->runAsyncAction("async/{$this->getBackend()}/{$id}", "DELETE");
        } catch (ClientException $e) {
            throw new Exception('Error from Provisioning API: ' . $e->getMessage(), null, $e);
        }
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
     * @return mixed
     */
    public function extendCredentials($id)
    {
        $response = $this->extendCredentialsRequest($id);
        return $response;
    }

    public function loadData($id, $input)
    {
        try {
            $response = $this->syrupClient->runAsyncAction(
                "async/docker/" . $id . "/input",
                "POST",
                ["body" => ["input" => $input]]
            );
        } catch (ClientException $e) {
            throw new Exception('Error from Provisioning API: ' . $e->getMessage(), null, $e);
        }
        return $response;
    }

    /**
	 * @param string $type
	 * @return mixed
	 */
	private function createCredentialsRequest($type = "transformations")
	{
		$body = array("type" => $type);
		$request = $this->client->post($this->getBackend(), $this->getHeaders(), json_encode($body));
		return $this->sendRequest($request);
	}

    /**
	 * @param string $type
	 * @return mixed
	 */
	private function getCredentialsRequest($type = "transformations")
	{
		$request = $this->client->get($this->getBackend() . "?type=" . $type, $this->getHeaders());
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
   	 * @param string $id
   	 * @return mixed
   	 */
   	private function extendCredentialsRequest($id)
   	{
   		$request = $this->client->post($this->getBackend() . "/" . $id . "/extend", $this->getHeaders());
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
            if ($e->getResponse()->getStatusCode() == 404) {
                throw new CredentialsNotFoundException($data["message"], null, $e);
            } else {
                throw new Exception('Error from Provisioning API: ' . $data["message"], null, $e);
            }
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
        $data = json_decode($jsonString, true, 512, JSON_BIGINT_AS_STRING);
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
