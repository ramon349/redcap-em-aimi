<?php

namespace Stanford\AIMI;
/** @var \Stanford\AIMI\AIMI $module */

//Active Model Meta Data
$active_alias           = !empty($module->getProjectSetting("active_alias")) ? $module->getProjectSetting("active_alias") : null;
$aliases                = !empty($module->getProjectSetting("aliases")) ? $module->getProjectSetting("aliases") : array();
$current                = isset($aliases[$active_alias]) ? $aliases[$active_alias] : null;

$repo_model_names       = $module->fetchModelNames();
$new_list_items         = array("<option  disabled>Add a new model from repository</option>");

$active_model_name      = !empty($active_alias) ? $active_alias : "No Active Model Selected";
$model_repo             = !empty($module->getProjectSetting("model_repo_endpoint")) ? $module->getProjectSetting("model_repo_endpoint") : $module->getDefaultRepo();

$loading_gif            = $module->getUrl("assets/images/loading.gif");
if(isset($repo_model_names)) {
    foreach($repo_model_names as $options) //Listing all the Stanford Models from REPO
        $model_name = str_replace("_"," ",$options["name"]);
        $versions   = (isset($options["versions"]) and count($options["versions"])) ? $options["versions"] : array("name" => "version_1.0", "path" => $options['path']);
        foreach($versions as $version){
            $ver_num        = str_replace("_"," ",$version["name"]);
            $model_name_ver = $model_name ." - ". $ver_num;
            $selected       = $active_alias == $model_name_ver ? "selected" : null;
            array_push($new_list_items, "<option $selected data-modelnamever='$model_name_ver'  value='{$version['path']}'>". $model_name_ver ."</option>");
        }
}

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

/* Style the button that is used to open and close the collapsible content */
.collapsible {
    background-color: #999;
    color: #fff;
    cursor: pointer;
    border-radius: 3px !important;
    margin:0 0 0 20px;
    padding: 5px 15px;
    width: 100%;
    border: none;
    text-align: left;
    outline: none;
    font-size: 15px;
}

/* Add a background color to the button if it is clicked on (add the .active class with JS), and when you move the mouse over it (hover) */
.active, .collapsible:hover {
    background-color: #bbb;
    border:none;
    outline:none;
}

label.collapsible.active:after {
    content: "\2212";
}
label.collapsible:after {
    content: '\002B';
    color: white;
    font-weight: bold;
    float: right;
    margin-left: 5px;
}

/* Style the collapsible content. Note: hidden by default */
.showhide_content {
    padding: 18px;
    margin-left:20px;
    border-radius:0 0 3px 3px;
    display: none;
    overflow: hidden;
    width:100%;
    background-color: #f1f1f1;
}
.showhide_content.active{
    display:block;
}

.model_details li {
    list-style:none;
    overflow:hidden;
}
.model_details li span{
    width:calc(100% - 100px);
    float:right;
}
.model_details li b {
    display:inline-block;
    min-width:100px;
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
            <input  id="alias" type="hidden" value="<?=$active_alias?>">
            <div class="col-sm-12">
                <h3 class="px-3 mb-1">Select a Pre-trained Model to use with this External Module</h3>
                <p class="px-3 mb-3">
                    The REDCap AIMI EM can use any available pre-trained machine learning model from a dedicated model repository (ie; github.com).<br>
                    The default model repository can be set to a custom repository in the EM's settings.
                </p>
                <p class="px-3 mb-3">
                    To choose a model, select one from the "Pre-Trained Models" dropdown below and click "Save Configuration & Make Active".  Only one model can be made active at a time for the project.
                </p>
                <p class="px-3 mb-3">
                    <b>Current Model Repository</b>: <?=$model_repo?><br>
                    <b>Current Active Model</b>: <?=$active_model_name?>
                </p>

                <div class="grid-x grid-padding-x well">
                    <div class="col-sm-12 row">
                        <label class="col-sm-5">
                            <b class="h5">Pre-Trained Models</b>
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

                    <div class="col-sm-12 row">
                        <ul class=" col-sm-10 mb-3 model_details">
                            <p>The model details are shown below, please confirm their validity once selecting an option:</p>
                            <li><b>Name:</b> <span id="model_name"></span></li>
                            <li><b>Description:</b> <span id="model_description"></span></li>
                            <li><b>Release Date:</b> <span id="model_release"></span></li>
                            <li><b>Authors:</b> <span id="model_authors"></span></li>
                        </ul>


                        <label for="showhide_content" class="collapsible">See Raw DATA</label>
                        <div class="mb-3 showhide_content" >
                            <pre class="mb-3" id="info" contentEditable="false" style="height:auto; min-height:130px; overflow: scroll;"></pre>

                            <label>Config URI
                                <input disabled id="config_uri" type="text" >
                            </label>
                        </div>

                    </div>
                </div>
            </div>

<!--            <div class="col-sm-12">-->
<!--                <hr>-->
<!--                <h3 class="px-3 mb-1"><span class='ordball'>2</span> Save Configuration as an Alias</h3>-->
<!---->
<!--                <div class="grid-x grid-padding-x">-->
<!--                    <div class="col-sm-12 row">-->
<!--                        <p class="offset-sm-1 col-sm-10 mb-3">-->
<!--                            Please provide an alias for this model.  As we implement future models/versions this will help differentiate-->
<!--                        </p>-->
<!---->
<!--                        <div class="offset-sm-1 col-sm-10 mb-3">-->
<!--                            <label>-->
<!--                                <input id="alias" type="text" placeholder="Alias/shortcut for this model configuration">-->
<!--                            </label>-->
<!--                        </div>-->
<!---->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->


            <div class="col-sm-12 mt-1">
                <div class="grid-x grid-padding-x">
                    <div class="col-sm-12 row">
                        <div class="col-sm-10">
                            <button id="submit" type="button" class="button success rounded mr-2">Save Configuration & Make Active <i class="fas fa-spinner fa-pulse"></i></button>
                        </div>
                        <p class="col-sm-10 mb-3">
                            *Once a configuration is saved.  Go to <a href="<?= $module->getUrl("pages/aimi.php"); ?>">Run Model</a>.
                        </p>
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
