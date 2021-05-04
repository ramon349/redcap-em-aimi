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
    <p></p>

    <h6>Getting Started : Running the Model</h6>
    <p></p>

    <br><br>
    <hr>
    <br><br>

    <h4>Stanford Data Share Agreement</h4>
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
