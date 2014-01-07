<?
namespace Keboola\Provisioning;
class Client
{

	private $_runId;
	private $_token;
	private $_apiUrl = 'https://syrup.keboola.com/provisioning';
	private $_backend = "mysql";
	private $_timeout = 300;
	private $_id;
	private $_credentials;

	private $_provisioningStorageUrl = '';
	private $_provisioningStorageToken = '';

	public function __construct($token, $runId)
	{
		$this->setToken($token);
		$this->setRunId($runId);
	}

	public function getCredentials()
	{
		if ($this->_credentials) {
			return $this->_credentials;
		}
		$response = $this->_createCredentials();
		$this->setCredentialsId($response["id"]);
		$credentialsResponse = $this->_getCredentials($this->_id);
		$this->_credentials = $credentialsResponse["credentials"];
		return $this->_credentials;
	}

	public function dropCredentials() {
		if (!$this->getCredentialsId()) {
			return false;
		}
		$result = $this->_dropCredentials($this->getCredentialsId());
		return true;
	}

	private function _createCredentials()
	{
		$command = 'curl -k -XPOST ';
		foreach ($this->_getHeaders() as $header) {
			$command .= " -H " . escapeshellarg($header);
		}
		$url = $this->_constructUrl();
		$command .= " " . escapeshellarg($url);
		return $this->_request($command);
	}

	private function _getCredentials($id)
	{
		$command = 'curl -k ';
		foreach ($this->_getHeaders() as $header) {
			$command .= " -H " . escapeshellarg($header);
		}
		$url = $this->_constructUrl($id);
		$command .= " " . escapeshellarg($url);
		return $this->_request($command);
	}

	private function _dropCredentials($id) {
		$command = 'curl -k -XDELETE ';
		foreach ($this->_getHeaders() as $header) {
			$command .= " -H " . escapeshellarg($header);
		}
		$url = $this->_constructUrl($id);
		$command .= " " . escapeshellarg($url);
		return $this->_request($command);
	}

	private function _request($command)
	{
		$process = new \Symfony\Component\Process\Process($command, null, null, null, $this->getTimeout());
		$process->run();
		if (!$process->isSuccessful()) {
		    throw new Exception("Provisioning API Error ({$process->getErrorOutput()}).", null, null, 'PROVISIONING_API_ERROR');
		}
		$response = $this->_parseResponse($process->getOutput());
		return $response;
	}


	public function getCredentialsId()
	{
		return $this->_id;
	}

	public function setCredentialsId($id)
	{
		$this->_id = $id;
		return $this;
	}

	private function _constructUrl($id="") {
		return $this->getApiUrl() . "/" . $this->getBackend() . "/" . $id;
	}

	private function _getHeaders() {
		$headers = array(
			"X-StorageApi-Token: {$this->getToken()}",
			"X-KBC-RunId: {$this->getRunId()}"
		);
		if ($this->getProvisioningStorageToken()) {
			$headers[] = "X-Provisioning-Token: " . $this->getProvisioningStorageToken();
		}
		if ($this->getProvisioningStorageUrl()) {
			$headers[] = "X-StorageApi-Url: ". $this->getProvisioningStorageUrl();
		}

		return $headers;
	}

	public function setTimeout($timeout)
	{
		$this->_timeout = $timeout;
		return $this;
	}

	public function getTimeout()
	{
		return $this->_timeout;
	}

	/**
	 *
	 * Converts JSON to object and detects errors
	 *
	 * @param $jsonString
	 * @throws Exception
	 * @return mixed
	 */
	private function _parseResponse($jsonString)
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

	public function setProvisioningStorageUrl($url) {
		$this->_provisioningStorageUrl = $url;
		return $this;
	}

	public function setProvisioningStorageToken($token) {
		$this->_provisioningStorageToken = $token;
		return $this;
	}

	public function getProvisioningStorageUrl() {
		return $this->_provisioningStorageUrl;
	}

	public function getProvisioningStorageToken() {
		return $this->_provisioningStorageToken;
	}

	public function setApiUrl($url)
	{
		$this->_apiUrl = $url;
		return $this;
	}

	public function getApiUrl()
	{
		return $this->_apiUrl;
	}

	public function setBackend($backend)
	{
		$this->_backend = $backend;
		return $this;
	}

	public function getBackend()
	{
		return $this->_backend;
	}

	public function setToken($token)
	{
		$this->_token = $token;
		return $this;
	}

	public function getToken()
	{
		return $this->_token;
	}

	public function setRunId($runId)
	{
		$this->_runId = $runId;
		return $this;
	}

	public function getRunId()
	{
		return $this->_runId;
	}


}