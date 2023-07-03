<?php

namespace ArcadaLabs\LGL;

use ArcadaLabs\Constants\Names;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Client;
use Exception;

class LGLCore
{
    private $api_key;
    private $base_url = 'https://api.littlegreenlight.com/api/v1/';
    private $home_url = 'https://arcadalabs.com/wp-json/arcadalabs-license-key/v1/';
    private $options;
    private $response;

    /**
     * Constructor
     * Set the key and URL for the API.
     *
     */
    function __construct()
    {
        $this->api_key = get_option('arcada_labs_lgl_sync_settings_field_lgl_api_key');
    }

    /**
     * Creates the full path for a request to lgl api
     * @param $path
     * @return string
     */
    private function makeUrl($path)
    {
        return $this->base_url . $path;
    }

    /**
     * Set Request headers
     */
    private function setHeaders()
    {
        $this->options['headers'] = [
            'Authorization'=>'Bearer '.$this->api_key,
            'Accept'=>'application/json'
        ];
    }

    /**
     * Set Request headers
     */
    private function setHomeHeaders()
    {
        $this->options['headers'] = [
            'Origin'=>get_site_url()
        ];
    }

    /**
     * Handle a Request error
     * @param Exception $exception
     * @return false|\Psr\Http\Message\ResponseInterface|null
     */
    protected function handleError(Exception $exception)
    {
        if ($exception instanceof ClientException) {
            if ($exception->hasResponse()) {
                return $exception->getResponse();
            }
        }
        return false;
    }

    /**
     * Makes the Request to LGL and sets the result on the response property
     * @param $path
     * @param $method
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function sendRequest($path, $method)
    {
        $url = $this->makeUrl($path);
        $this->setHeaders();

        $client = new Client();

        try {
            $response = $client->request($method, $url, $this->options);
        } catch (ClientException | ServerException $e) {
            $response = $this->handleError($e);
        }
        $this->response = $response;
    }

    /**
     * Makes a GET request to LGL
     * @param $path
     * @param array $params
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get($path, $params = array())
    {
        $this->options['query'] = $params;
        $this->sendRequest($path, 'get');
    }

    /**
     * Makes a POST request to LGL
     * @param $path
     * @param array $params
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post($path, $params = array())
    {
        $this->options['json'] = $params;
        $this->sendRequest($path, 'post');
    }

    /**
     * Makes a PUT request to LGL
     * @param $path
     * @param array $params
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function put($path, $params = array())
    {
        $this->options['json'] = $params;
        $this->sendRequest($path, 'put');
    }

    /**
     * Gets the response from the Request
     * @return mixed|string|void
     */
    public function getResponse()
    {
        if ($this->response) {
            $response = json_decode((string) $this->response->getBody());
            if (json_last_error() !== 0) {
                $response = (string) $this->response->getBody();
            }
            return $response;
        } else {
            error_log('No response available. Did you send the request using sendRequest()?');
        }
    }

    public function license($license)
    {
        $client = new Client();
        $data = array('license'=>$license);
        $this->options['json'] = $data;
        $this->setHomeHeaders();

        try {
            $response = $client->request('post', $this->home_url . 'activate', $this->options);
        } catch (ClientException | ServerException $e) {
            $response = $this->handleError($e);
        }
        $this->response = $response;
        return $this->home_url . 'activate';
    }

    public function removeLicense()
    {
        $client = new Client();
        $data = array(
            'license'=>get_option(Names::LICENSES['license']),
            'pair'=>get_option(Names::LICENSES['pair']),
        );
        $this->options['json'] = $data;
        $this->setHomeHeaders();

        try {
            $response = $client->request('post', $this->home_url . 'deactivate', $this->options);
            delete_option(Names::LICENSES['license']);
            delete_option(Names::LICENSES['pair']);
        } catch (ClientException | ServerException $e) {
            $response = $this->handleError($e);
        }
        $this->response = $response;
    }

    public function licenseActive($partial = false)
    {
        if (($license = get_option(Names::LICENSES['license'])) && ($pair = get_option(Names::LICENSES['pair']))) {
	        if ( $last_check = get_option( Names::DATA_CHECKS[ 'license' ] ) ) {
		        if ( $last_check === date( "Y-m-d" ) ) {
			        return array(
				        Names::TIERS['GF_LICENSE'] => get_option(Names::ACCESS_LEVELS['GF_LICENSE']),
				        Names::TIERS['WC_LICENSE'] => get_option(Names::ACCESS_LEVELS['WC_LICENSE']),
			        );
		        } else {
			        update_option( Names::DATA_CHECKS[ 'license' ], date( 'Y-m-d' ) );
		        }
	        } else {
		        add_option( Names::DATA_CHECKS[ 'license' ], date( 'Y-m-d' ) );
	        }

            $client = new Client();
            $data = array('license'=>$license, 'pair'=>$pair);
            $this->options['json'] = $data;
            $this->setHomeHeaders();

            try {
                $response = $client->request('post', $this->home_url . 'validate', $this->options);
            } catch (ClientException | ServerException $e) {
                $this->handleError($e);
                return array(
                    Names::TIERS['GF_LICENSE'] => get_option(Names::ACCESS_LEVELS['GF_LICENSE']),
                    Names::TIERS['WC_LICENSE'] => get_option(Names::ACCESS_LEVELS['WC_LICENSE']),
                );
            }
            $this->response = $response;
            $licenses = $this->getResponse();

            if ($licenses->message ?? false) {
                return array(
                    Names::TIERS['GF_LICENSE'] => false,
                    Names::TIERS['WC_LICENSE'] => false,
                );
            }

            if ($licenses->access ?? false) {
				update_option(Names::ACCESS_LEVELS['GF_LICENSE'], $licenses->access->GF_LICENSE);
				update_option(Names::ACCESS_LEVELS['WC_LICENSE'], $licenses->access->WC_LICENSE);

                return array(
                    Names::TIERS['GF_LICENSE'] => $licenses->access->GF_LICENSE,
                    Names::TIERS['WC_LICENSE'] => $licenses->access->WC_LICENSE,
                );
            }

        }

        return array(
            Names::TIERS['GF_LICENSE'] => false,
            Names::TIERS['WC_LICENSE'] => false,
        );
    }

    public function hookCall($data)
    {
        $url = get_option('arcada_labs_lgl_webhook_url');

        $client = new Client();
        $this->options['json'] = $data;

        try {
            $result = $client->request('post', $url, $this->options);
			if ($result) {
				$response = json_decode( (string) $result->getBody() );

				if ($response) {
					if ( json_last_error() !== 0 ) {
						$response = (string) $this->response->getBody();
					}

					return $response;
				}
			}
        } catch (ClientException | ServerException $e) {
            $response = $this->handleError($e);
        }
        $this->response = $response;
        return $data;
    }
}
