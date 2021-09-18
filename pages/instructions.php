<?php

namespace Stanford\AIMI;
/** @var \Stanford\AIMI\AIMI $module */

$metadata                   = $module->getMetadata("bs");
$fields_in_project          = array_keys($metadata);

//CHECK TO SEE IF PROJECT EVEN HAS THE REQUIRE FIELDS BEFORE TRYING TO LOAD
$project_required_fields    = $module->getProjectRequiredFields();
$intersect                  = array_intersect($fields_in_project,$project_required_fields);
$has_required_fields        = count($project_required_fields) == count($intersect);
$required_fields_zip        = $module->getUrl("assets/REDCap_AIMI_Required_Fields_Instrument.zip");
?>
<style>
    img.instructions {
        max-width:60%;
        box-shadow:0 0 5px 3px #bbb;
        margin:10px 0;
    }
</style>
<div style='margin:20px 0; max-width:98%'>
    <h4>Stanford AIMI EM Instructions</h4>

    <?php
        if(!$has_required_fields){
    ?>
        <div id="missing_fields">
            <hr>
            <p class="alert alert-danger h5">This project is missing one or more of the following required fields needed to standardize recorded observation data. You may still interact and experiment with the active model.  However to allow for saving data, Please download and install the <a class="h5" href="<?=$required_fields_zip?>">REDCap_AIMI_Required_Fields_Instrument.zip</a>.</p>
            <ul>
                <?php
                foreach($project_required_fields as $field){
                    echo "<li>$field</li>";
                }
                ?>
            </ul>
            <hr>
        </div>
    <?php
        }
    ?>

    <p>The REDCap AIMI EM allows this project to use Stanford ML-models in a generalized UI to make predictions in the client browser via the Tensorflow.js Library. </p>


    <h4>Getting Started : Selecting & Activating a Model</h4>

    <p>The first step is to choose a Pre-Trained Model Configuration from the <b>"Select & Activate Model"</b> page.</p>
    <img class="instructions" src="<?=$module->getUrl("assets/images/instructions_select_model.png",true,true)?>"/>
    <br><br>
    <p>Next save the model to the project with an alias or short name, then click <b>"Save Configuration & Make Active"</b>.</p>
    <img class="instructions" src="<?=$module->getUrl("assets/images/instructions_alias.png",true,true)?>"/>
    <p>After a few moments, the model will be active and ready to use.  The page will then redirect to the <b>"Run Active Model"</b> page.</p>
    <br><br>
    <hr>
    <br>

    <h4>Getting Started : Running the Model</h4>
    <p>Download a set of test images to get familiarized with the process <br><a target="_blank" href="<?=$module->getUrl("assets/images/test_images.zip",true,true);?>">Test Images.zip (right-click and "Save Link As")</a></p>

    <p>When first arriving to the <b>"Run Active Model"</b> page.  The model will need to "warm up" for a few seconds.</p>
    <img class="instructions" src="<?=$module->getUrl("assets/images/instructions_loading.png",true,true)?>"/>

    <br><br>
    <p>Once loaded, simply click on "Select X-ray Image" and browse to an image file stored locally.</p>
    <img class="instructions" src="<?=$module->getUrl("assets/images/instructions_select_img.png",true,true)?>"/>

    <br><br>
    <p>The model will start processing the image and after a few moments will give a classification prediction (based on preset labels that come with the model)</p>
    <img class="instructions" src="<?=$module->getUrl("assets/images/instructions_explain.png",true,true)?>"/>

    <br><br>
    <p>Clicking on <b>"explain"</b> will generate a heatmap overlay on top of the original image demonstrating why the model predicted that class.</p>
    <img class="instructions" src="<?=$module->getUrl("assets/images/instructions_explain_heatmap.png",true,true)?>"/>

    <br><br>
    <p>Immedietely below the prediction graphics there are some disabled fields that will automatically fill with required data.  <br>The first field availabe for input is named <b>"Ground Truth"</b>.   The user can verify or counter the predictions given by the model by simply inputting  a comma delimited string of 1s and 0s to signify true or false for each label in order.</p>

    <p>eg;  there are 5 labels , if in reality only <b>"Pleural Effusion"</b> is present, then the user would input <b>0,0,0,0,1</b></p>
    <img class="instructions" src="<?=$module->getUrl("assets/images/instructions_ground_truth.png",true,true)?>"/>

    <br><br>
    <p>Once complete, click <b>"Save Form"</b> to store the record and/or send the data (predictions and ground truth only) back to Stanford*</p>


    <br><br>
    <hr>
    <br>

    <h4>Stanford Data Share Agreement*</h4>
    <p>If an agreement was made to share prediction data with Stanford. A small amount of configuration will be required.
     Stanford's AIMI team will have sent a unique <b>[Unique Stanford Partner Token]</b> and an <b>[API endpoint for Stanford Project]</b>.</p>
    <p>Simply click <b>"External Modules"</b> in the left hand navigation under <b>"Applications"</b></p>
    <img class="instructions" src="<?=$module->getUrl("assets/images/instructions_find_em_link.png",true,true)?>"/>

    <br><br>
    <p>Find the <b>"Project EM : A.I. Based Medical Image Diagnosis - v9.9.9"</b> Module and click <b>"Configure"</b></p>
    <img class="instructions" src="<?=$module->getUrl("assets/images/instructions_find_em.png",true,true)?>"/>

    <br><br>
    <p>Input the two pieces of data and click <b>"Save"</b></p>
    <img class="instructions"  src="<?=$module->getUrl("assets/images/instructions_configure_em.png",true,true)?>"/>



</div>
