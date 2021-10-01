<?php
namespace Stanford\AIMI;

use ExternalModules\ExternalModules;
use Sabre\DAV\Exception;

require_once "emLoggerTrait.php";
require_once "classes/Client.php";
require_once "classes/Model.php";
require_once "REDCapJsRenderer.php";

CONST MODEL_REPO_ENDPOINT = 'https://api.github.com/repos/susom/redcap-aimi-models/contents';

class AIMI extends \ExternalModules\AbstractExternalModule {
    use emLoggerTrait;

    /** @var Client $client */
    private $client;
    private $model_repo_endpoint;
    private $run_model_title;
    private $run_model_subtitle;
    private $required_project_fields;

    public function __construct() {
		parent::__construct();
        $this->RCJS 	= new \Stanford\AIMI\REDCapJsRenderer($this);
        $this->model_repo_endpoint = MODEL_REPO_ENDPOINT;

        $this->required_project_fields = array("record_id","model_config","model_results","model_top_predictions","model_prediction_time","partner_ground_truth");
    }

    //Pass through to REDCapJsRenderer Class
    public function getMetadata($something){
        return $this->RCJS->getMetadata($something);
    }
    public function saveData($data){
        return $this->RCJS->saveData($data);
    }
    public function getValidFields($project_id, $event_id, $form_name){
        return $this->RCJS->getValidFields($project_id, $event_id, $form_name);
    }

    public function getDefaultRepo(){
        return $this->model_repo_endpoint;
    }

    public function getProjectRequiredFields(){
        return $this->required_project_fields;
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
            $rate_limit     = $this->getClient()->createRequest("GET", 'https://api.github.com/rate_limit');
            $github_entries = $this->getClient()->createRequest("GET", $this->model_repo_endpoint);
            $models = array();

            //Generate new model for each subtree to record versions
            if(!empty($github_entries)) {
                foreach($github_entries as $entry) {
                    if($entry['type'] === 'dir') {
                        $path               = $entry["path"];
                        $model_versions     = $this->fetchVersions($path);
                        $entry["versions"]  = $model_versions;
                        array_push($models, $entry);
                    }
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
            $contents = $this->getClient()->createRequest("GET", $this->model_repo_endpoint . '/' . rawurlencode($path));
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
            $contents = $this->getClient()->createRequest("GET", $this->model_repo_endpoint . '/' . $path);
            $payload = array();
            $payload['info'] = array();

            if(!empty($contents)) {
                foreach($contents as $entry) {
                    if ($entry['name'] === 'config.js') {
                        $payload['config'] = $entry;
                    } elseif ($entry['name'] === 'redcap_config.json') {
                        $raw = file_get_contents($entry['download_url']);
                        $payload['info'] = array_merge($payload['info'], json_decode($raw, true));
                    } elseif (strpos($entry['name'],'.bin')) {
                        $payload['info']['shards'][] = $entry['download_url'];
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

            $active_alias = $this->getProjectSetting("active_alias");
            if($active_alias == $alias){
                //deleted model was the active one so remove from active
                //and delete the redcap_config.js
                $result = $this->setProjectSetting('active_alias', null);

                $this->emDebug("active alias/model deleted");
            }
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
            $alias      = urldecode($alias); //store key as decoded alias
            $existing   = $this->getProjectSetting('aliases');

            if(!isset($existing)) {
                $existing = array();
            }

            if(!array_key_exists($alias,$existing)){
                $build          = array_merge($existing, array($alias=>$config));
            }else{
                $build          = $existing;
                $build[$alias]  = $config;
            }

            $build[$alias]["shard_edocs"]   = array(); // array that specifies edoc IDs for already saved models
            $build[$alias]["model_json"]    = null;
            $build[$alias]["config_js"]     = null;

            $result = $this->setProjectSetting('aliases', $build); //Will overwrite existing aliases:

            echo $result;

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
     * @param $edoc_id
     * @throws \Exception
     */
    public function getTempShardPath($edoc_id)
    {
        $doc_temp_path = \Files::copyEdocToTemp($edoc_id, false, true); //save to temp folder for later reference
        if($doc_temp_path) {
            $this->emLog("Saved shard file temp for alias : " .
                ', doc_id : ' . $edoc_id .  ', tempPath : ' . $doc_temp_path
            );
            return $doc_temp_path;

        } else {
            throw new \Exception("No temp path returned for doc id: " . $edoc_id);
        }
    }


    /**
     * @param $uri github url to config.js of model
     * @return http_response_code 400 / 200
     */

    public function applyConfig($uri, $info, $active_alias)
    {
        $existing_aliases = $this->getProjectSetting('aliases');
        $temp_shard_paths = array();
        try{
            if(empty($existing_aliases[$active_alias])) {
                throw new \Exception("Active alias : " . $active_alias . " Not present in project config. ");
            }

            if(!empty($existing_aliases[$active_alias]["shard_edocs"])) { //shard edocs should be present, and we have already downloaded them
                foreach($existing_aliases[$active_alias]["shard_edocs"] as $edoc_id) {
                    $doc_temp_path = $this->getTempShardPath($edoc_id);
                    array_push($temp_shard_paths, $doc_temp_path);
                }
            } else { //first time applying config, we have to download the file
                $convert_to_raw = str_replace("https://github.com", "https://raw.githubusercontent.com", $uri);
                $raw_uri        = str_replace("blob/", "", $convert_to_raw);
                $raw_model      = str_replace("config.js", "model.json", $raw_uri);

                $model_files    = $info['shards'];
                //ADD config.js and model.json to the batch of files for download
                array_push($model_files, $raw_uri, $raw_model);

                $shard_edocids  = array();
                $model_json     = array();
                $config_js      = null;
                foreach($model_files as $ind => $shard) { //upload all shards to edocs
                    $shard_binary_or_js = file_get_contents($shard);
                    if(isset($shard_binary_or_js)) {
                        $temp       = explode("/",$shard);
                        $name       = array_pop($temp);

                        if($name !== "model.json" && $name !== "config.js"){
                            //need to first download the file to redcap temp
                            $temp_path = APP_PATH_TEMP . "AIMI_" . $name;
                            file_put_contents($temp_path, $shard_binary_or_js);
                            $this->emDebug("temp_path", $temp_path);

                            //then we can use File::upload to upload the temp file to edoc
                            $file       = array(
                                'name'=>basename($name),
                                'type'=>'application/octet-stream',
                                'size'=>filesize($temp_path),
                                'tmp_name'=>$temp_path);
                            $edoc_id    = \Files::uploadFile($file);

                            if($edoc_id){
                                $doc_temp_path = $this->getTempShardPath($edoc_id);
                                array_push($shard_edocids, $edoc_id);
                                array_push($temp_shard_paths, $doc_temp_path);
                            }
                        }

                        if($name == "model.json"){
                            $model_json     = $shard_binary_or_js;
                        }

                        if($name == "config.js"){
                            $config_js      = $shard_binary_or_js;
                        }
                    }
                }

                $url_modeljson      = $this->getUrl("endpoints/passthrough.php?em_setting=model_json", true, true);
                $config_js          = str_replace("model.json", $url_modeljson, $config_js);

                $existing_aliases[$active_alias]["config_js"]   = $config_js;
                $existing_aliases[$active_alias]["model_json"]  = json_decode($model_json, true);
                $existing_aliases[$active_alias]["shard_edocs"] = $shard_edocids;

                $result = $this->setProjectSetting('aliases', $existing_aliases);
            }
        } catch (\Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
            $this->emError($e->getMessage());
        }

//        $this->emDebug("temp_shard_paths ", $temp_shard_paths);

        try{
            if(isset($uri) && isset($info)) {
                //need to wait to do this because temp files will be deleted periodically
                $passthrough_shard_urls = array();
                foreach($temp_shard_paths as $temp_path){

                    $shard_url          = $this->getUrl("endpoints/passthrough.php?shard=$temp_path", true, true);
                    $shard_name_only    = str_replace(APP_PATH_TEMP, "", $shard_url);
                    array_push($passthrough_shard_urls , $shard_name_only);
                }

                $model_json                                     = $existing_aliases[$active_alias]["model_json"];
                $model_json["weightsManifest"][0]["paths"]      = $passthrough_shard_urls;
                $existing_aliases[$active_alias]["model_json"]  = $model_json;
                $result = $this->setProjectSetting('aliases', $existing_aliases); //Will overwrite existing aliases:

                $result = $this->setProjectSetting('active_alias', $active_alias);
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

    public function clearTempFiles(){
        //clear all from "temp_config" except for "redcap_config.js"
        $model_temp_path    = __DIR__ . '/temp_config/*';
        $save_file          = __DIR__ . '/temp_config/redcap_config.js';
        $files_to_keep      = array( $save_file );

        $dirList = glob($model_temp_path);
        foreach ($dirList as $file) {
            if (!in_array($file, $files_to_keep)) {
                if (is_dir($file)) {
                    $this->emDebug("is dir remove $file");
                    rmdir($file);
                } else {
                    $this->emDebug("is file remove $file");
                    unlink($file);
                }
            }
        }
        return;
    }

	// Sync raw data to Master Project , If Institution has agreement in place
	public function syncMaster(){
		$processed = array();
		return $processed;
	}
	// Project Cron

	public function projectCron(){
        return;
		$projects = $this->framework->getProjectsWithModuleEnabled();

		//manually enter array of cron pages to run
		$urls = array(
		    $this->getUrl('cron/syncMaster.php', true)
        ); //has to be page

		foreach($projects as $index => $project_id){
			foreach($urls as $url){
				$thisUrl 	= $url . "&pid=$project_id"; //project specific
				$client 	= new Client();
				$response 	= $client->createRequest('GET', $thisUrl, array(\GuzzleHttp\RequestOptions::SYNCHRONOUS => true));
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
