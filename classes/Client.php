<?php

namespace Stanford\AIMI;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

class Client extends \GuzzleHttp\Client
{
    private $em;

    public function __construct($em, array $config = ['Content-Type' => 'application/json'])
    {

        parent::__construct($config);

        $this->setEm($em);

    }

    public function createRequest($method, $uri = '', array $options = [])
    {
        try {
            $response = parent::request($method, $uri, $options);

            $code = $response->getStatusCode();

            if ($code == 200) {
                $content = $response->getBody()->getContents();
                if (is_array(json_decode($content, true))) {
                    return json_decode($content, true);;
                }
                return $content;
            } else {
                throw new \Exception("cant make request!");
            }
        } catch (ClientException $e) {
            $this->getEm()->emError($e->getMessage());
        } catch (\Exception $e) {
            $this->getEm()->emError($e->getMessage());
        } catch (GuzzleException $e) {
            $this->getEm()->emError($e->getMessage());
        }
    }

    /**
     * @return mixed
     */
    public function getEm()
    {
        return $this->em;
    }

    /**
     * @param mixed $em
     */
    public function setEm($em)
    {
        $this->em = $em;
    }


}
