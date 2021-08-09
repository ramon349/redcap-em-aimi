<?php


namespace Stanford\AIMI;

use GuzzleHttp\Exception\ClientException;

CONST SHA_ENDPOINT = 'https://api.github.com/repos/susom/redcap-aimi-models/git/trees/';


class Model
{
    private $sha;
    private $github_url;
    private $client;
    private $versions;
    private $path;

    public function __construct($client, $config)
    {
        $this->setClient($client);
        $this->setGithubUrl($config['url']);
        $this->setSha($config['sha']);
        $this->setPath($config['path']);
        $this->setVersions();
    }

    /**
     * @return mixed
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * @param mixed $versions
     */
    public function setVersions($versions = array())
    {
        if(empty($versions)) {
            $contents = $this->getClient()->createRequest('GET', SHA_ENDPOINT . $this->getSha()); //Returns all contents of subrepo
            $trees = array();
            foreach($contents['tree'] as $entry) {
                if($entry['type'] === 'tree')
                    array_push($trees, $entry);
            }
            $this->versions = $trees;
        } else {
            $this->versions = $versions;
        }

    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }


    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }


    /**
     * @return mixed
     */
    public function getSha()
    {
        return $this->sha;
    }

    /**
     * @param mixed $sha
     */
    public function setSha($sha)
    {
        $this->sha = $sha;
    }

    /**
     * @return mixed
     */
    public function getGithubUrl()
    {
        return $this->github_url;
    }


    /**
     * @param mixed $github_url
     */
    public function setGithubUrl($github_url)
    {
        $this->github_url = $github_url;
    }
}
