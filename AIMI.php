<?php
namespace Stanford\AIMI;

require_once "emLoggerTrait.php";

class AIMI extends \ExternalModules\AbstractExternalModule {

    use emLoggerTrait;

    public function __construct() {
		parent::__construct();
		// Other code to run when object is instantiated
	}

	


	// Sync raw data to Master Project , If Institution has agreement in place
	public function syncMaster(){
		$processed = array();

		return $processed;
	}

	// Project Cron
	public function projectCron(){
		$projects 	= $this->framework->getProjectsWithModuleEnabled();

		//manually enter array of cron pages to run
		$urls 		= array(
						$this->getUrl('cron/syncMaster.php', true)
					); //has to be page
		
		foreach($projects as $index => $project_id){
			foreach($urls as $url){
				$thisUrl 	= $url . "&pid=$project_id"; //project specific
				$client 	= new \GuzzleHttp\Client();
				$response 	= $client->request('GET', $thisUrl, array(\GuzzleHttp\RequestOptions::SYNCHRONOUS => true));
				$this->emDebug("running cron for $url on project $project_id");
			}
			
		}
	}	
}
