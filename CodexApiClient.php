<?php
require 'vendor/autoload.php';

use GuzzleHttp\Exception\RequestException;


/**
* Codex_API
*/
class CodexApiClient extends GuzzleHttp\Client
{
	/**
	 * Create a new Codex API Client
	 *
	 * You can create the credentials needed through the API Manager: https://CLIENT.codex.link/apimanager
	 * 	
	 * @param string $codex_client the Codex Client: https://CLIENT.codex.link
	 * @param string $email        Email used in the client definition in the API manager
	 * @param string $password     Password used in the client definition in the API manager
	 */
	function __construct($codex_client,$email,$password)
	{
		parent::__construct();


		$this->codex_client = $codex_client;
		
		$this->base_url = "https://{$this->codex_client}.codex.link/api/";
	
		if($codex_client ==="localhost") {
			$this->base_url = "https://localhost/gitlab/codex-core/api/";
		}

		$this->authentication = false;
		if(!$this->authenticate($email,$password)) {

			throw new Exception("Could not authenticate; did you use the right credentials created in the API Manager? 
				Go here: https://{$codex_client}.codex.link/apimanager", 403);
		}
		
	}

	/**
	 * Exchange an Authentication token and store it in the `authentication` object.
	 * @param  string $email    Given email
	 * @param  string $password Given password
	 * @return bool           Returns status of authentication
	 */
	protected function authenticate($email,$password)
	{

		$params = [	'email'=>$email,'password'=>$password];

		$auth_response = $this->_post('authenticate',$params);

		if(isset($auth_response->status) && $auth_response->status===true) {
			
			$this->authentication = $auth_response;
			return true;
		} else {
			$this->authentication = false;			
			return false;
		}
	}

	/**
	 * Abstract GET request without authentication
	 * @param  string $endpoint The endpoint to request
	 * @param  array  $params   Any parameters given
	 * @return mixed           The response body
	 */
	protected function _get($endpoint,$params=[])
	{
		return $this->_request("GET",$endpoint,$params,false);
	}

	/**
	 * Abstract POST request without authentication
	 * @param  string $endpoint The endpoint to request
	 * @param  array  $params   Any parameters given in form body
	 * @return mixed           The response body
	 */
	protected function _post($endpoint,$params=[])
	{
		return $this->_request("POST",$endpoint,$params,false);
	}

	/**
	 * Abstract GET request WITH authentication
	 * @param  string $endpoint The endpoint to request
	 * @param  array  $params   Any parameters given
	 * @return mixed           The response body
	 */
	protected function _auth_get($endpoint="",$params=[])
	{	
		return $this->_request("GET",$endpoint,$params,true);
	}

	/**
	 * Abstract POST request with authentication
	 * @param  string $endpoint The endpoint to request
	 * @param  array  $params   Any parameters given
	 * @return mixed           The response body
	 */
	protected function _auth_post($endpoint="",$params=[])
	{
		return $this->_request("POST",$endpoint,$params,true);
	} 

	/**
	 * Abstracted request method
	 * @param  string  $method        Method to use (choose from GET, PUT, PATCH etc.). Use uppercase
	 * @param  string  $endpoint      Endpoint to request
	 * @param  mixed  $params        An array of objects to give as parameters
	 * @param  boolean $requires_auth Do you need to check authentication credentials?
	 * @return mixed                 The response body
	 */
	protected function _request($method,$endpoint,$params,$requires_auth=true)
	{
		if($requires_auth===true) {

			if($this->authentication===false) {
				throw new Exception("Trying to request an secured endpoint without succesful authentication", 1);
			}
			
		}
		
		if(!in_array($method, ["GET","DELETE","OPTIONS","PUT","POST","PATCH","HEAD"]))
		{
			throw new Exception("invalid request method given", 503);	
		}
		$url = $this->base_url.$endpoint;
		
		if($requires_auth) {
			$headers = ['Authorization' => 'Bearer '.$this->authentication->token];
			
		} else {
			$headers = [];
		}

		try {
			$res = $this->request($method, $url, ['form_params'=>$params,'headers' => $headers]);
		} catch (RequestException $e) {
			if ($e->hasResponse()) {
				return json_decode($e->getResponse()->getBody());
			}
		}

		$status = $res->getStatusCode();

		if($status !==200) {

			throw new Exception("API Call error",$status);
		}

		// @todo: make this nicer
		$response = $res->getBody();
		
		return json_decode($response);

	}


	/** 
	 * Get all items from the mambo module
	 * @return Array list of mambo items
	 */
	public function mambo()
	{
		return $this->_auth_get("mambo");
	}

	/** 
	 * Get all items from the mambo module
	 * @return Array list of mambo items
	 */
	public function members()
	{


		return $this->_auth_get("members");
	}

	/** 
	 * Get the API:Index endpoint (status of API)
	 * @return Stdclass API Object
	 */
	public function index()
	{
		return $this->_get("index");
	}

	/** 
	 * Get the API:Index endpoint (status of API), only when authenticated
	 * @return Stdclass API Object
	 */
	public function index2()
	{
		return $this->_auth_get("index2");
	}
}
