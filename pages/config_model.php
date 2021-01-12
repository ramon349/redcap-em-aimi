<?php

namespace Stanford\AIMI;
/** @var \Stanford\AIMI\AIMI $module */

$repo_model_names = $module->fetchModelNames();

$previously_saved_names = $module->fetchSavedEntries();
$model_test_names = $previously_saved_names ? array_keys($previously_saved_names) : array();

$used_list_items = array("<option selected disabled>Please select a previously saved model</option>");
$new_list_items = array("<option selected disabled>Add a new model from repository</option>");

if(isset($repo_model_names)) {
    foreach($repo_model_names as $options) //Listing all the Stanford Models from REPO
        array_push($new_list_items, "<option value='{$options['path']}'>{$options['path']}</option>");
}

foreach($model_test_names as $options)
    array_push($used_list_items, "<option value='{$options}'>{$options}</option>");
?>
<div class="card" style="margin-right: 20px">
    <form>
        <div class="grid-container" style="margin-top: 20px">
            <div id = 'alert' class="callout" data-closable style="display: none">
                <button class="close-button" aria-label="Close alert" type="button" data-close>
                    <span aria-hidden="true">&times;</span>
                </button>
                <p></p>
            </div>
<!--            <div id = 'alert' class="hidden alert" role="alert"></div>-->
            <div class="grid-x grid-padding-x">
                <div class="medium-12 cell">
                    <select id="existing_model" >
                        <?php echo implode($used_list_items, " "); ?>
                    </select>
                </div>
            </div>
            <br>
            <div class="grid-x">
                <div class="cell medium-offset-6"><h3>OR</h3></div>
            </div>
            <br>
            <div class="grid-x grid-padding-x">
                <div class="medium-12 cell">
                    <select id="new_model" >
                        <optgroup label="Respository Models">
                            <?php echo implode($new_list_items, " "); ?>
                        </optgroup>
                        <optgroup label="Custom">
                            <option value="custom_new">New Custom Configuration</option>
                        </optgroup>
                    </select>
                </div>
            </div>
        </div>
        <div class="grid-container">
            <div class="grid-x grid-padding-x">
                <div class="medium-8 cell">
                    <label>Version
                        <select id="version">
                            <option selected disabled>Please select a model</option>
                        </select>
                    </label>
                </div>
            </div>
            <hr>
            <h4>Configuration options</h4>
            <blockquote id="block">
                These configuration options are filled in from the selection above unless a custom configuration is selected, please confirm their
                validity once selecting an option
            </blockquote>
            <pre id="info" contentEditable="false">
            </pre>
            <br>
            <div class="grid-x grid-padding-x">
                <div class="medium-8 cell">
                    <label>Config URI
                        <input disabled id="config_uri" type="text" >
                    </label>
                </div>
            </div>
            <br>
            <div class="grid-x grid-padding-x">
                <div class="medium-8 cell">
                    <label>Path reference
                        <input disabled id="path" type="text" >
                    </label>
                </div>
            </div>
            <br>
            <div class="grid-x grid-padding-x">
                <div class="medium-8 cell">
                    <label>Configuration alias
                        <input id="alias" type="text" placeholder="Please provide a name for this entry">
                    </label>
                </div>
            </div>
            <button id="submit" type="button" class="button success">Save Configuration</button>
            <button id="apply" type="button" class="button">Apply to EM</button>
            <button id="delete" type="button" class="button float-right alert" disabled>Delete</button>
        </div>
    </form>
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
