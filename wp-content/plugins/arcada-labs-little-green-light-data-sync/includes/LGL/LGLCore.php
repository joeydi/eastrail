<?php

namespace ArcadaLabs\LGL;

use ArcadaLabs\Constants\Names;

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
            'Origin: ' . get_site_url(),
            'Accept: application/json'
        ];
    }

    /**
     * Handle a Request error
     * @param \Exception $exception
     * @return false
     */
    protected function handleError(\Exception $exception)
    {
        if ($exception instanceof \Exception) {
            if ($exception->getMessage() !== null) {
                return $exception->getMessage();
            }
        }
        return false;
    }

    /**
     * Makes the Request to LGL and sets the result on the response property
     * @param $path
     * @param $method
     */
    private function sendRequest($path, $method)
    {
        $url = $this->makeUrl($path);

        $ch = curl_init($url);

        // Set the request method
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Set the request headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization'=>'Authorization: Bearer '.$this->api_key,
            'Accept'=>'application/json'
        ]);

        try {
            // Execute the request
            $response = curl_exec($ch);

            if ($response === false) {
                throw new \Exception(curl_error($ch));
            }

            // Handle the response
            $this->response = $response;
        } catch (\Exception $e) {
            $this->handleError($e);
        } finally {
            // Close the cURL session
            curl_close($ch);
        }
    }

    /**
     * Makes a GET request to LGL
     * @param $path
     * @param array $params
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
            $response = $this->response;

            if (is_string($response)) {
                // Handle the response as a string (cURL version)
                $decodedResponse = json_decode($response);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $response = $decodedResponse;
                }
            }

            return $response;
        } else {
            error_log('No response available. Did you send the request using sendRequest()?');
        }
    }

    public function license($license)
    {
        $url = $this->home_url . 'activate';
        $data = array('license' => $license);
        $this->options['json'] = $data;
        $this->setHomeHeaders();

        $ch = curl_init($url);

        // Set the request method
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Set the request body
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        // Set the request headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->options['headers']);

        try {
            // Execute the request
            $response = curl_exec($ch);

            if ($response === false) {
                throw new \Exception(curl_error($ch));
            }

            // Handle the response
            $this->response = $response;
            return $url;
        } catch (\Exception $e) {
            $response = $this->handleError($e);
            $this->response = $response;
            return $url;
        } finally {
            // Close the cURL session
            curl_close($ch);
        }
    }

    public function removeLicense()
    {
        $url = $this->home_url . 'deactivate';
        $data = array(
            'license' => get_option(Names::LICENSES['license']),
            'pair' => get_option(Names::LICENSES['pair']),
        );
        $this->options['json'] = $data;
        $this->setHomeHeaders();

        $ch = curl_init($url);

        // Set the request method
        curl_setopt($ch, CURLOPT_POST, true);

        // Set the request body
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Set the request headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->options['headers']);

        try {
            // Execute the request
            $response = curl_exec($ch);

            if ($response === false) {
                throw new \Exception(curl_error($ch));
            }

            // Handle the response
            $this->response = $response;
            delete_option(Names::LICENSES['license']);
            delete_option(Names::LICENSES['pair']);
        } catch (\Exception $e) {
            $response = $this->handleError($e);
            $this->response = $response;
        } finally {
            // Close the cURL session
            curl_close($ch);
        }
    }

    public function licenseActive($partial = false)
    {
        if (($license = get_option(Names::LICENSES['license'])) && ($pair = get_option(Names::LICENSES['pair']))) {
            if ($last_check = get_option(Names::DATA_CHECKS['license'])) {
                if ($last_check === date('Y-m-d')) {
                    return array(
                        Names::TIERS['GF_LICENSE'] => get_option(Names::ACCESS_LEVELS['GF_LICENSE']),
                        Names::TIERS['WC_LICENSE'] => get_option(Names::ACCESS_LEVELS['WC_LICENSE']),
                    );
                } else {
                    update_option(Names::DATA_CHECKS['license'], date('Y-m-d'));
                }
            } else {
                add_option(Names::DATA_CHECKS['license'], date('Y-m-d'));
            }

            $url = $this->home_url . 'validate';
            $data = array('license' => $license, 'pair' => $pair);
            $this->options['json'] = $data;
            $this->setHomeHeaders();

            $ch = curl_init($url);

            // Set the request method
            curl_setopt($ch, CURLOPT_POST, true);

            // Set the request body
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            // Set the request headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->options['headers']);

            try {
                // Execute the request
                $response = curl_exec($ch);

                if ($response === false) {
                    throw new \Exception(curl_error($ch));
                }

                // Handle the response
                $this->response = $response;
                $licenses = $this->getResponse();

                if (isset($licenses->message)) {
                    return array(
                        Names::TIERS['GF_LICENSE'] => false,
                        Names::TIERS['WC_LICENSE'] => false,
                    );
                }

                if (isset($licenses->access)) {
                    update_option(Names::ACCESS_LEVELS['GF_LICENSE'], $licenses->access->GF_LICENSE);
                    update_option(Names::ACCESS_LEVELS['WC_LICENSE'], $licenses->access->WC_LICENSE);

                    return array(
                        Names::TIERS['GF_LICENSE'] => $licenses->access->GF_LICENSE,
                        Names::TIERS['WC_LICENSE'] => $licenses->access->WC_LICENSE,
                    );
                }
            } catch (\Exception $e) {
                $this->handleError($e);
            } finally {
                // Close the cURL session
                curl_close($ch);
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

        $ch = curl_init($url);

        // Set the request method
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Set the request body
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        // Set the request headers
        $this->setHeaders();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->options['headers']);

        try {
            // Execute the request
            $result = curl_exec($ch);

            if ($result === false) {
                throw new \Exception(curl_error($ch));
            }

            // Handle the response
            if ($result) {
                $response = json_decode($result);

                if ($response) {
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $response = $result;
                    }

                    return $response;
                }
            }
        } catch (\Exception $e) {
            $response = $this->handleError($e);
        } finally {
            // Close the cURL session
            curl_close($ch);
        }

        $this->response = $response;
        return $data;
    }
}
