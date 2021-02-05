<?php

namespace Stanford\AIMI;
/** @var \Stanford\AIMI\AIMI $module */

$repo_model_names       = $module->fetchModelNames();
$previously_saved_names = $module->fetchSavedEntries();
$model_test_names       = $previously_saved_names ? array_keys($previously_saved_names) : array();
$used_list_items        = array("<option selected disabled>Please select a previously saved model</option>");
$new_list_items         = array("<option selected disabled>Add a new model from repository</option>");

$loading_gif            = $module->getUrl("assets/images/loading.gif");
if(isset($repo_model_names)) {
    foreach($repo_model_names as $options) //Listing all the Stanford Models from REPO
        array_push($new_list_items, "<option value='{$options['path']}'>{$options['path']}</option>");
}

foreach($model_test_names as $options)
    array_push($used_list_items, "<option value='{$options}'>{$options}</option>");
?>
<style>
#alert{
    position: fixed; 
    top: 0; 
    left: 0; 
    width: 100%;
}

#apply .fas{
    display:none;
}
#apply.loading .fas{
    display:inline-block;
}
</style>
<main>
    <div class="col-sm-11 my-3 row">
        <h3 class="px-3 mb-1">Configure or Select Pre-trained Models to use with this Module</h3>
        <p class="px-3 mb-3">In order to run a model, please  select from the drop down and "save configuration" with an alias, then "apply to em"</p>

        <div id='alert' class="callout" data-closable style="display: none">
            <button class="close-button" aria-label="Close alert" type="button" data-close>
                <span aria-hidden="true">&times;</span>
            </button>
            <p style="font-size:150%;"></p>
        </div>
        <form class="row ">
            <input  id="path" type="hidden" >
            <div class="col-sm-10">
                <div class="grid-x grid-padding-x">
                    <div class="col-sm-12 row">
                        <label class="col-sm-6">
                            Pre-Trained Models
                            <select id="new_model" >
                                <optgroup label="Respository Models">
                                    <?php echo implode($new_list_items, " "); ?>
                                </optgroup>
                                <optgroup label="Custom">
                                    <option value="custom_new">Choose a New Custom Configuration</option>
                                </optgroup>
                            </select>
                        </label>
                        <label class="col-sm-6">
                            Version
                            <select id="version">
                                <option selected disabled>Please select a model first</option>
                            </select>
                        </label>
                    </div>
                </div>
                
                <h5 class="text-center col-sm-5">OR</h5>

                <div class="grid-x grid-padding-x">
                    <div class="col-sm-6 row">
                        <label class="col-sm-12">
                            Saved Configurations
                            <select id="existing_model" >
                                <?php echo implode($used_list_items, " "); ?>
                            </select>
                        </label>
                    </div>
                </div>
            </div>
            
            <hr>
            
            <div class="col-sm-10">
                <hr> 

                <h4 class="px-3 mb-1">Configuration options</h4>
                <p class="px-3 mb-3">
                    These configuration options are filled in from the selection above unless a custom configuration is selected, please confirm their
                    validity once selecting an option, the following box contains the raw github URL for each shard file.
                </p>
                <pre id="info" class="mx-3 mb-3" contentEditable="false" style="height:130px; overflow: scroll;">
                </pre>


                <div class="grid-x grid-padding-x">
                    <div class="medium-8 cell">
                        <label>Config URI
                            <input disabled id="config_uri" type="text" >
                        </label>
                    </div>
                </div>
                <div class="grid-x grid-padding-x mb-3">
                    <div class="medium-8 cell">
                        <label>Configuration alias
                            <input id="alias" type="text" placeholder="Please provide a name for this entry">
                        </label>
                    </div>
                </div>

                <div class="col-sm-12 border-top pt-4">
                    <button id="submit" type="button" class="button success rounded mr-2">Save Configuration</button>
                    <button id="apply" type="button" class="button rounded" disabled>Apply to EM <i class="fas fa-spinner fa-pulse"></i></button>
                    <button id="delete" type="button" class="button rounded float-right alert" disabled>Delete</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- Foundation links -->

<!-- Compressed CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/foundation-sites@6.6.3/dist/css/foundation.min.css" integrity="sha256-ogmFxjqiTMnZhxCqVmcqTvjfe1Y/ec4WaRj/aQPvn+I=" crossorigin="anonymous">

<!-- Compressed JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/foundation-sites@6.6.3/dist/js/foundation.min.js" integrity="sha256-pRF3zifJRA9jXGv++b06qwtSqX1byFQOLjqa2PTEb2o=" crossorigin="anonymous"></script>
<script>
    let src ="<?php echo $module->getUrl('endpoints/ajaxHandler.php'); ?>"
</script>
<script src="<?php echo $module->getUrl('assets/scripts/config_model.js'); ?>"></script>
