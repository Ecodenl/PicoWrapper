<?php

namespace Ecodenl\PicoWrapper;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\ClientInterface;

/**
 * Client.
 *
 * @author Patrick van Kouteren <p.vankouteren@wedesignit.nl>
 */
class PicoClient
{

    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * @var bool
     */
    protected $success;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var array
     */
    protected $response;

    /**
     * @var array
     */
    protected $results = [];

    /**
     * @var array - valid calls
     */
    public static $validCalls = [
        'bag_adres_pchnr',
        'bag_adres_adrid',
        'cbs_buurt_brtcode',
        'nbh_kvb_pc6',
    ];

    /**
     * Initiate the class.
     *
     * @param  ClientInterface  $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->setClient($client);
    }

    /**
     * Set client
     *
     * @param  ClientInterface  $client
     *
     * @return $this
     */
    public function setClient(GuzzleClientInterface $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * return GuzzleClient.
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Check if the last request was successful.
     * @return bool
     */
    public function isSuccessful()
    {
        return (bool)$this->getSuccess();
    }

    /**
     * return the status from the last call.
     * @return bool
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * return the status message from the last call.
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * return the actual response array from the last call.
     * @return array
     */
    public function getResponse()
    {
        return isset($this->response['response']) ? $this->response['response'] : $this->response;
    }

    /**
     * return the results array from the GetSearchResults call.
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * magic method to invoke the correct API call
     * if the passed name is within the valid callbacks.
     *
     * @param  string  $name
     * @param  array  $arguments
     *
     * @return array
     */
    public function __call($name, $arguments)
    {
        if (in_array($name, self::$validCalls)) {
            $arguments = empty($arguments) ? $arguments : array_shift(
                $arguments
            );

            return $this->doRequest($name, $arguments);
        }
    }

    /**
     * set "success" and message of the api call.
     *
     * @param  bool  $success
     * @param  string  $message
     *
     * @return void
     */
    protected function setStatus($success, $message)
    {
        $this->success = $success;
        $this->message = $message;
    }

    /**
     * Perform the actual request to the Pico api endpoint.
     *
     * @param  string  $name
     * @param  array  $params
     *
     * @return array
     */
    protected function doRequest($call, array $params)
    {
        // Run the call
        $response = $this->getClient()->get(
            '',
            array_merge_recursive(['query' => ['request' => $call]], $params)
        );

        $this->response = $response->getBody()->getContents();


        // Parse response
        return $this->parseResponse($this->response);
    }

    /**
     * Parse the reponse into a formatted array
     * also set the status code and status message.
     *
     * @param  string  $response
     *
     * @return array
     *
     * @throws PicoException
     */
    protected function parseResponse($response)
    {
        // Init
        $this->response = json_decode($response, true);

        if ( ! $this->response['succes']) {
            throw new PicoException("Failed to parse response");
        }

        // Check if we have an error
        $this->setStatus($this->response['succes'], $this->response['message']);

        // If request was succesful then parse the result
        if ($this->isSuccessful()) {
            if (isset($this->response['result']) && count(
                    $this->response['result']
                )) {
                foreach ($this->response['result'] as $result) {
                    $this->results[] = $result;
                }
            }
        }

        return ! empty($this->results) ? $this->results : $this->response;
    }
}