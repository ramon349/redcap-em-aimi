<?php

namespace Stanford\AIMI;
/** @var \Stanford\AIMI\AIMI $module */


?>
<style>
    img.instructions {
        max-width:40%;
        box-shadow:0 0 5px 3px #bbb;
    }
</style>
<div style='margin:20px 0; max-width:98%'>
    <h4>Stanford AIMI EM Instructions</h4>
    <p>The REDCap AIMI EM allows this project to use Stanford ML-models in a generalized UI to make predictions in the client browser via the Tensorflow.js Library. </p>
    
    <p>To get started here is a set of test images to get familiar with the process <br><a target="_blank" href="<?=$module->getUrl("assets/images/test_images.zip",true,true);?>">Test Images.zip (right-click and "Save Link As")</a></p>

    <h6>Getting Started : Configuring the Model</h6>
    
    <p>The first step is to choose a Stanford Model Configuration from <b>"Config/Select Model"</b>.<br>  This can be a new selection or chosen from an already saved configuration.</p>
    <img class="instructions" src="<?=$module->getUrl("assets/images/instructions_new_model.png",true,true)?>"/>
    <br><br>
    <img class="instructions" src="<?=$module->getUrl("assets/images/instructions_saved_model.png",true,true)?>"/>
    
    <br><br>
    <p>If choosing a new a new model, it must first be saved with an alias</p>
    <img class="instructions" src="<?=$module->getUrl("assets/images/instructions_save_config.png",true,true)?>"/>
    
    <br><br>
    <p>Once the new model config is saved with an Alias (or if choosing an already saved Model Alias), Click <b>"Apply to Em"</b></p>
    <img class="instructions" src="<?=$module->getUrl("assets/images/instructions_apply_config.png",true,true)?>"/>

    <br><br>
    <p>After a few moments when the weight files are downloaded and compiled, a success message will appear from the top of the screen.  The model is ready.  Go to <b>"Run Model"</b></p>
    <img class="instructions" src="<?=$module->getUrl("assets/images/instructions_success.png",true,true)?>"/>

    <br><br>
    <hr>
    <br><br>

    <h6>Getting Started : Running the Model</h6>
    <p>When first arriving to the <b>"Run Model"</b> page.  The model will need to "warm up" for a few seconds.</p>
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
    <br><br>

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
