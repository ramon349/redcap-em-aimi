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
        $versions = (isset($options["versions"]) and count($options["versions"])) ? $options["versions"] : array("name" => "version_1.0", "path" => $options['path']);
        foreach($versions as $version){
            array_push($new_list_items, "<option value='{$version['path']}'>".str_replace("_"," ",$options['path']) ." - ".  str_replace("_"," ", $version["name"]) ."</option>");
        }
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

#submit .fas{
    display:none;
}
#submit.loading .fas{
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
    <div class="col-sm-12 my-3 row">
        <div id='alert' class="callout" data-closable style="display: none">
            <button class="close-button" aria-label="Close alert" type="button" data-close>
                <span aria-hidden="true">&times;</span>
            </button>
            <p style="font-size:150%;"></p>
        </div>
        <form class="row ">
            <input  id="path" type="hidden" >
            <div class="col-sm-12">
                <h3 class="px-3 mb-1"><span class='ordball'>1</span> Select a Pre-trained Model to use with this Module</h3>

                <div class="grid-x grid-padding-x">
                    <div class="col-sm-12 row">
                        <label class="offset-sm-1 col-sm-5">
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
                    </div>
                </div>

                <div class="grid-x grid-padding-x">
                    <div class="col-sm-12 row">
                        <p class="offset-sm-1 col-sm-10 mb-3">
                            The model details are shown below, please confirm their validity once selecting an option
                        </p>

                        <div class="offset-sm-1 col-sm-10 mb-3" >
                            <pre id="info" contentEditable="false" style="height:auto; min-height:130px; overflow: scroll;"></pre>
                        </div>


                        <div class="offset-sm-1 col-sm-10 mb-3">
                            <label>Config URI
                                <input disabled id="config_uri" type="text" >
                            </label>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-sm-12">
                <hr>
                <h3 class="px-3 mb-1"><span class='ordball'>2</span> Save Configuration as an Alias</h3>

                <div class="grid-x grid-padding-x">
                    <div class="col-sm-12 row">
                        <p class="offset-sm-1 col-sm-10 mb-3">
                            Please provide an alias for this model.  As we implement future models/versions this will help differentiate
                        </p>

                        <div class="offset-sm-1 col-sm-10 mb-3">
                            <label>
                                <input id="alias" type="text" placeholder="Alias/shortcut for this model configuration">
                            </label>
                        </div>

                    </div>
                </div>
            </div>


            <div class="col-sm-12">
                <hr>

                <div class="grid-x grid-padding-x">
                    <div class="col-sm-12 row">
                        <p class="offset-sm-1 col-sm-10 mb-3">
                            *Once a configuration is saved.  Go to <a href="<?= $module->getUrl("pages/aimi.php"); ?>">Run Model</a>.
                        </p>

                        <div class="offset-sm-1 col-sm-10 mb-3">
                            <button id="submit" type="button" class="button success rounded mr-2">Save Configuration & Make Active <i class="fas fa-spinner fa-pulse"></i></button>
<!--                            <button id="delete" type="button" class="button rounded float-right alert" disabled>Delete</button>-->
                        </div>

                    </div>
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
