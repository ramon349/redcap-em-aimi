<?php
namespace Stanford\AIMI;

require_once "emLoggerTrait.php";
require_once "classes/Client.php";

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
        $this->setClient(new Client($this));
        //Check repo link here
        $response = $this->getClient()->request("GET", "https://api.github.com/repos/facebook/react/contents/");
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
