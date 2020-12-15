<?php
namespace Stanford\AIMI;

use Sabre\DAV\Exception;

require_once "emLoggerTrait.php";
require_once "classes/Client.php";
require_once "classes/Model.php";

CONST MODEL_REPO_ENDPOINT = 'https://api.github.com/repos/susom/redcap-aimi-models/git/trees/main';

class AIMI extends \ExternalModules\AbstractExternalModule {

    use emLoggerTrait;

    /** @var Client $client */
    private $client;

    public function __construct() {
		parent::__construct();

		// Other code to run when object is instantiated
	}

    public function fetchModelConfigs()
    {
        try {
            $this->setClient(new Client($this));

            //Check repo link here
            //TODO RATE LIMITED TO 60 PER HOUR MIGHT HAVE TO ADUST
            $github_entries = $this->getClient()->request("GET", MODEL_REPO_ENDPOINT);
            $models = array();

            if(!empty($github_entries['tree'])) {
                foreach($github_entries['tree'] as $entry) {
                    if($entry['type'] === 'tree') //Only take dir titles
                        array_push($models, new Model($this->getClient(), $entry));
                }
                return $models;
            }

            throw new \Exception('Error: Request to pull model files returned null');

        } catch (\Exception $e) {
            $this->emError($e->getMessage());
        }
    }

    public function fetchRedcapConfigs($tree_url)
    {
        try {
//            $this->getClient()->request("GET", )
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
