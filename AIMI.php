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
     *
     */
    public function fetchSavedEntries()
    {
        $existing = $this->getProjectSetting(); //TODO How to get all project settings after saving?
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
//            $existing = $this->getProjectSetting($alias);
            $result = $this->setProjectSetting($alias, $config); //Will overwrite existing aliases:
            return true;

        } catch (\Exception $e) {
            $this->emError($e->getMessage());
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
