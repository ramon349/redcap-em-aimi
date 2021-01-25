<?php
namespace Stanford\AIMI;

use ExternalModules\ExternalModules;
use Sabre\DAV\Exception;

require_once "emLoggerTrait.php";
require_once "classes/Client.php";
require_once "classes/Model.php";

//CONST MODEL_REPO_ENDPOINT = 'https://api.github.com/repos/susom/redcap-aimi-models/git/trees/main';
CONST MODEL_REPO_ENDPOINT = 'https://api.github.com/repos/susom/redcap-aimi-models/contents';

class AIMI extends \ExternalModules\AbstractExternalModule {

    use emLoggerTrait;

    /** @var Client $client */
    private $client;

    public function __construct() {
		parent::__construct();

		// Other code to run when object is instantiated
	}

    /**
     * Fetches all models of type dir
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function fetchModelNames()
    {
        try {
            $this->setClient(new Client($this));

            //TODO RATE LIMITED TO 60 PER HOUR MIGHT HAVE TO ADUST
            //Grab all root repository contents, non recursively
            $rate_limit = $this->getClient()->request("GET", 'https://api.github.com/rate_limit');
            $github_entries = $this->getClient()->request("GET", MODEL_REPO_ENDPOINT);
            $models = array();

            //Generate new model for each subtree to record versions
            if(!empty($github_entries)) {
                foreach($github_entries as $entry) {
                    if($entry['type'] === 'dir') //Only take dir titles
                        array_push($models, $entry);
                }
                return $models;
            }

            throw new \Exception('Error: Request to pull model files returned null');

        } catch (\Exception $e) {
            $this->emError($e->getMessage());
        }
    }

    /**
     * Fetches the version count (dirs) given a github path
     * @param $path
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function fetchVersions($path)
    {
        try {
            //Fetch all contents from repo tree
            $this->setClient(new Client($this));
            $contents = $this->getClient()->request("GET", MODEL_REPO_ENDPOINT . '/' . rawurlencode($path));
            $versions = array();
            foreach($contents as $entry) {
                if($entry['type'] === 'dir')
                    array_push($versions, $entry);
            }

            return $versions;

        } catch (\Exception $e) {
            $this->emError($e->getMessage());
        }
        exit;
    }

    /**
     * Fetches the text information populating the config.js given a github path
     * @param $path
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function fetchModelConfig($path)
    {
        try {
            $this->setClient(new Client($this));
            $contents = $this->getClient()->request("GET", MODEL_REPO_ENDPOINT . '/' . $path);
            $payload = array();

            if(!empty($contents)) {
                foreach($contents as $entry) {
                    if ($entry['name'] === 'config.js')
                        $payload['config'] = $entry;
                    elseif ($entry['name'] === 'redcap_config.json') {
                        $raw = file_get_contents($entry['download_url']);
                        $payload['info'] = json_decode($raw, true);
                    }
                }
                return $payload;
            }
            throw new \Exception("Error: no config.js found for URI: {$contents['html_url']}");

        } catch (\Exception $e) {
            $this->emError($e->getMessage());
        }
    }

    /**
     * Fetches the saved model configs
     * @return array
     */
    public function fetchSavedEntries()
    {
        $existing = $this->getProjectSetting('aliases');
        return $existing;
    }

    public function removeConfig($alias)
    {
        $alias = urldecode($alias); //find decoded key
        $existing = $this->getProjectSetting('aliases');
        if (array_key_exists($alias,$existing)) {
            unset($existing[$alias]);
            $result = $this->setProjectSetting('aliases', $existing);
        } else {
            $this->emError('Error removing alias, alias not found');
        }
        return $existing;
    }

    /**
     * @param $alias
     * @param $config
     * @return bool
     */
    public function saveConfig($alias, $config)
    {
        try{
            $alias = urldecode($alias); //store key as decoded alias
            $existing = $this->getProjectSetting('aliases');
            if (array_key_exists($alias,$existing))
                throw new \Exception("Error: alias: $alias already exists, skipping");

            $build = array_merge($existing, array($alias=>$config));
            $result = $this->setProjectSetting('aliases', $build); //Will overwrite existing aliases:
            http_response_code(200);//return 200 on success

        } catch (\Exception $e) {
            $this->emError($e->getMessage());
            http_response_code(400);
        }
    }

    /**
     * @param $alias key to search for
     * @return mixed
     */
    public function getExistingModuleConfig($alias)
    {
        try {
            $existing = $this->fetchSavedEntries();
            return $existing[urldecode($alias)];
        } catch(\Exception $e) {
            $this->emError($e->getMessage());
            http_response_code(400);
        }
    }

    /**
     * @param $uri github url to config.js of model
     * @return http_response_code 400 / 200
     */
    public function applyConfig($uri)
    {   
        /*
        this https://github.com/susom/redcap-aimi-models/blob/main/Stanford%20Imaging%201/version_1.0/config.js
        to this https://raw.githubusercontent.com/susom/redcap-aimi-models/main/Stanford%20Imaging%201/version_1.0/config.js
        */


        //THIS WILL NEED TO STORE TO redcap/temp AND THEN CACHED IN THE TF LIBRARY , THEN DELETED

        $uri = str_replace("https://github.com", "https://raw.githubusercontent.com", $uri);
        $uri = str_replace("blob/", "", $uri);
        $this->emDebug("wwheres my log", $uri);

        try{
            if(isset($uri)) {
                $result = $this->setProjectSetting('config_uri', $uri);
                http_response_code(200);//return 200 on success
            } else {
                throw new \Exception('URI not passed');
            }
        } catch(\Exception $e) {
            $this->emError($e->getMessage());
            http_response_code(400);
        }
    }

	// Sync raw data to Master Project , If Institution has agreement in place
	public function syncMaster(){
		$processed = array();
		return $processed;
	}
	// Project Cron

	public function projectCron(){
		$projects = $this->framework->getProjectsWithModuleEnabled();

		//manually enter array of cron pages to run
		$urls = array(
		    $this->getUrl('cron/syncMaster.php', true)
        ); //has to be page

		foreach($projects as $index => $project_id){
			foreach($urls as $url){
				$thisUrl 	= $url . "&pid=$project_id"; //project specific
				$client 	= new Client();
				$response 	= $client->request('GET', $thisUrl, array(\GuzzleHttp\RequestOptions::SYNCHRONOUS => true));
				$this->emDebug("running cron for $url on project $project_id");
			}
		}
    }
    
    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }
}
