<?php

namespace Stanford\AIMI;
/** @var \Stanford\AIMI\AIMI $module */

//Active Model Meta Data
$active_alias           = !empty($module->getProjectSetting("active_alias"))    ? $module->getProjectSetting("active_alias") : null;

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

.alphaball,
.ordball{
    display: inline-block;
    border-radius: 200px;
    width: 44px;
    height: 44px;
    text-align: center;
    line-height:135%;
    background:orange;
    color:#fff;
}

.alphaball{
    background:blue;
    color:#fff;
    width:34px; height:34px;
    line-height:260%;

}
</style>
<main>
    <?php if($active_alias) { ?>
    <div class="alert alert-info col-sm-9 my-3" style="border:initial !important">
        Current Active Model Alias: <b><?=$active_alias ?></b>
    </div>
    <?php } ?>
    <div class="col-sm-11 my-3 row">
        <h3 class="px-3 mb-1"><span class='ordball'>1</span> Configure or Select Pre-trained Models to use with this Module</h3>


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
                    <p class="col-sm-10 offset-sm-2 mb-4">In order to run a model, please select from the "Pre-Trained Models" (A) or "Previously Saved Configurations" (B) drop downs</p>
                    <div class="col-sm-12 row">
                        <div class="col-sm-1 offset-sm-1"><span class="alphaball">A</span></div>
                        <label class="col-sm-5">
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
                        <label class="col-sm-5">
                            Version
                            <select id="version">
                                <option selected disabled>Please select a model first</option>
                            </select>
                        </label>
                    </div>
                </div>

                <h5 class="text-center col-sm-5">OR</h5>

                <div class="grid-x grid-padding-x">
                    <div class="col-sm-1 offset-sm-1"><span class="alphaball">B</span></div>
                    <div class="col-sm-5 row">
                        <label class="col-sm-12">
                        Previously Saved Configurations
                            <select id="existing_model" >
                                <?php echo implode($used_list_items, " "); ?>
                            </select>
                        </label>
                    </div>
                </div>

                <div class="grid-x grid-padding-x">
                    <div class="col-sm-12 row">
                        <p class="offset-sm-2 col-sm-10 mb-3">
                            The model details are shown below, please confirm their validity once selecting an option
                        </p>

                        <div class="offset-sm-2 col-sm-10 mb-3" >
                            <pre id="info" contentEditable="false" style="height:130px; overflow: scroll;"></pre>
                        </div>


                        <div class="offset-sm-2 col-sm-10 mb-3">
                            <label>Config URI
                                <input disabled id="config_uri" type="text" >
                            </label>
                        </div>

                    </div>
                </div>
            </div>

            <hr>

            <div class="col-sm-10">
                <hr>

                <h3 class="px-3 mb-1"><span class='ordball'>2</span> Save Configuration as an Alias</h3>
                <p class="px-3 mb-3">If selecting a new "Pre-Trained Model", It must first be saved with an alias.  Please input a shortcut or alias and click "Save Configuration".</p>
                <div class="grid-x grid-padding-x mb-3">
                    <div class="medium-8 cell">
                        <label>
                            <input id="alias" type="text" placeholder="Please provide an alias/shortcut for this configuration">
                        </label>
                    </div>
                </div>
                <div class="col-sm-12 border-top pt-4 mt-4">
                    <p class="mb-3">*Once a configuration is saved.  Go to <a href="<?= $module->getUrl("pages/aimi.php"); ?>">Run Model</a> and chose a model alias from the dropdown.</p>

                    <button id="submit" type="button" class="button success rounded mr-2">Save Configuration</button>
<!--                    <button id="apply" type="button" class="button rounded" disabled>* Activate Selected Model Configuration <i class="fas fa-spinner fa-pulse"></i></button>-->
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
