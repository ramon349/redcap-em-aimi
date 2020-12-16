<?php

namespace Stanford\AIMI;
/** @var \Stanford\AIMI\AIMI $module */

$repo_model_names = $module->fetchModelNames();
$previously_saved_names = $module->fetchSavedEntries();
$model_test_names = array(
    "model1",
    "model2",
    "model3"
);

$used_list_items = array("<option selected disabled>Please select a previously used model</option>");
$new_list_items = array("<option selected disabled>Add a new model from repository</option>");

if(isset($repo_model_names)) {
    foreach($repo_model_names as $options) //Listing all the Stanford Models from REPO
        array_push($new_list_items, "<option value='{$options['path']}'>{$options['path']}</option>");
}

foreach($model_test_names as $options)
    array_push($used_list_items, "<option value='{$options}'>{$options}</option>");
?>
    <form>
        <div class="grid-container">
            <div class="grid-x grid-padding-x">
                <div class="medium-12 cell">
                    <select >
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
                        <?php echo implode($new_list_items, " "); ?>
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
            <blockquote>
                These configuration options are filled in from the selection above, please confirm their
                validity once selecting an option
            </blockquote>
            <pre id="info" >
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
            <button id="submit" type="button" class="button">Save</button>
        </div>
    </form>
<!-- Foundation links -->

<!-- Compressed CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/foundation-sites@6.6.3/dist/css/foundation.min.css" integrity="sha256-ogmFxjqiTMnZhxCqVmcqTvjfe1Y/ec4WaRj/aQPvn+I=" crossorigin="anonymous">

<!-- Compressed JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/foundation-sites@6.6.3/dist/js/foundation.min.js" integrity="sha256-pRF3zifJRA9jXGv++b06qwtSqX1byFQOLjqa2PTEb2o=" crossorigin="anonymous"></script>
<script>
    let src ="<?php echo $module->getUrl('endpoints/ajaxHandler.php'); ?>"
</script>
<script src="<?php echo $module->getUrl('assets/scripts/config_model.js'); ?>"></script>
