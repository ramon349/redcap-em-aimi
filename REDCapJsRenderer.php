<?php
namespace Stanford\AIMI;



/**
 * The purpose of this class is to assist in the secure rendering of an instrument for javascript usage
 * - New Survey Submission
 * - Edit existing survey / form
 */


class REDCapJsRenderer
{
    public function __construct($module) {
		// Other code to run when object is instantiated
        $this->module 	= $module;
	}

    /**
     * When a field is modified on the client, an ajax call will post the data here
     * The format of the save should be:
     * [
     *  "hash" => "asdfasdf",
     *  "fields" => [
     *      "field_name" => "value",
     *      "field_name" => "value2",
     *      ...
     *  ]
     * @param $data
     * @return mixed
     * @throws Exception
     */
    public function saveData($fields) {
        global $Proj;

        // $this->module->emDebug("save data", $fields);

        $_Proj      = array_key_exists("project_id",$Proj) ? json_decode(json_encode($Proj),true) : new \Project();
        $project_id = $_Proj["project_id"];
        $pk         = $_Proj["table_pk"];
        $event_id   = $_Proj["firstEventId"];
        $form_name  = $_Proj["firstForm"];

        $valid_fields   = self::getValidFields($project_id,$event_id,$form_name);
        $saveFields     = [];
        foreach ($fields as $field) {
            $field_name     = $field["name"];
            $field_values   = $field["value"];

            // Make sure the field is valid, check out
            if (!in_array($field_name, $valid_fields) && strpos($field_name,"__") === false) {
                $this->module->emDebug("Attempt to save invalid field $field_name", $field_values);
                continue;
            }

            //make sure date is Y-m-d
            if(strpos($field_name,"date") !== false){
                $field_values = Date("Y-m-d", strtotime($field_values));
            }

            $saveFields[$field_name] = $field_values;
        }

        $record = $this->getNextRecordId($project_id);
        $saveFields["record_id"] = $record;
        
        $saveData = array(
            $saveFields
        );
        $result = \REDCap::saveData($project_id,'json', json_encode($saveData), 'overwrite');
        $result["record_id"] = $record;

        return $result;
        // $hash = isset($data['hash']@$data['hash'];
    }


    /**
     * Utility to return array of valid metadata as an indexed array
     * [0] => record_id,
     * [1] => field_one, ...
     *
     * @param $project_id
     * @param $event_id
     * @param $form_name
     * @return array
     * @throws \Exception
     */
    public function getValidFields($project_id, $event_id, $form_name) {
        // Get the project object
        global $Proj;
        $_Proj          = array_key_exists("project_id",$Proj) ? json_decode(json_encode($Proj),true) : new \Project();
        
        // Let's assemble an array of valid fields for this form, starting with event_id:
        $valid_fields   = \REDCap::getValidFieldsByEvents($project_id, $event_id);

         // If we have a form, then let's also filter fields by form
        if (!empty($form_name)) {
            $form_fields    = \REDCap::getFieldNames($form_name);
            $valid_fields   = array_unique(array_merge($valid_fields, $form_fields));
        }

        return $valid_fields;
    }



    /**
     * Take in the hash and then determine what metadata should be returned
     * If there is an existing record, let's include that data as well
     * @param $hash
     * @return array|mixed
     * @throws Exception
     */
    public function getMetadata($hash) {
        global $Proj;

        $_Proj      = array_key_exists("project_id",$Proj) ? json_decode(json_encode($Proj),true) : new \Project();
        $project_id = $_Proj["project_id"];
        $project    = $_Proj["project"];
        $metadata   = $_Proj["metadata"];
        $pk         = $_Proj["table_pk"];
        $pk_label   = $_Proj["table_pk_label"];
        $event_id   = $_Proj["firstEventId"];
        $form_name          = $_Proj["firstForm"];
        $expected_fields    = $_Proj["numFields"];
        $form_fields        = array_keys($_Proj["forms"][$first_form]["fields"]);
        $meta_fields        = array_keys($metadata);

        // $this->module->emDebug("project",$_Proj);

        // Get the valid fields for this event/form combination
        $valid_fields       = $this->getValidFields($project_id, $event_id, $form_name);


        // Let's filter out only those fields that are in the valid_fields array above from all Metadata
        $valid_metadata = array_intersect_key($metadata, array_flip($valid_fields));

        // $module->emDebug(count($metadata_all), count($valid_fields), count($metadata));

        // If we have a record/instance, we need to include the current record's data with this metadata
        if (!empty($record)) {
            // TODO: Implement ability to handle instance numbers - punting for now!

            // Get Data
            $q = \REDCap::getData($project_id, 'array', $record, $valid_fields, $event_id);

            if (empty($q[$record][$event_id])) {
                $module->emError("Unable to find record $record, event $event_id in query results", $q);
            } else {

                // Lets add data to the valid metadata
                foreach ($q[$record][$event_id] as $field_name => $field_data) {
                    if (isset($valid_metadata[$field_name])) {
                        $valid_metadata[$field_name]['current_value'] = $field_data;
                    } else {
                        $module->emError("Data returned field $field_name which isn't part of the valid_metadata", $valid_metadata);
                    }
                }
            }
        }

        // $module->emDebug($valid_metadata);
        $result = $valid_metadata;
        return $result;
    }

    /**
     * Find the next available record_id in the RC project
     * @param $project_id
     * @return int
     */
    public function getNextRecordId($project_id){
        $params = array(
            'fields' => array("participant_id")
        );

        $result = \REDCap::getData($project_id);

        $next_available_id = 1;
        if(!empty($result)){
            $next_available_id = max(array_keys($result)) + 1;
        }

        return $next_available_id;
    }
}