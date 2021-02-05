<?php
namespace Stanford\AIMI;
/** @var \Stanford\AIMI\AIMI $module */

$selected_model         = $module->getProjectSetting("config_uri");
$selected_alias         = $module->getProjectSetting("active_alias");

$placeholder_image      = $module->getUrl("assets/images/placeholder.jpg");
$dedicated_upload_js    = $module->getUrl("assets/scripts/index_upload.js");

$url_configjs           = $module->getUrl("temp_config/config.js");
$url_modeljson          = $module->getUrl("temp_config/model.json");
$url_reddcapconfigjs    = $module->getUrl("temp_config/redcap_config.js");

$url_configmodel        = $module->getUrl("pages/config_model.php");

$em_save_path           = __DIR__ . '/../temp_config/';
$file_redcapconfigjs    = $em_save_path ."redcap_config.js";
$file_configjs          = $em_save_path ."config.js";

$selected_model         = null;

$ajax_endpoint          = $module->getUrl("endpoints/ajaxHandler.php");
if(file_exists($file_configjs)){
    //If config.js exists in temp_config (means theres a new set of files) then save new redcap_model.js then delete temp_config
 
    // Modify Config File to hold full URL to  "model.json" in the model_path
    $temp_js                = file_get_contents($url_configjs);
    $configjs               = str_replace("model.json", $url_modeljson, $temp_js);
    file_put_contents($file_redcapconfigjs, $configjs);
}

if(file_exists($file_redcapconfigjs)){
    // load the new config.js
    $selected_model         = $url_reddcapconfigjs;
}
?>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@1.2.9/dist/tf.min.js"></script>
<style>
    .select-xray {
        display:inline-block;
        margin:0 auto;
        font-size: 20px;
    }

    #xray-image,
    #xray-image1,
    #xray_canvas {
        margin-top:20px; margin-bottom: 20px;
    }
    #xray-image,
    #xray-image1 {
        border:2px solid #000;
    }
    
    .select-xray:hover {
        background: #10707f;
        outline: none;
    }
    .stats_progress{
        background-image: linear-gradient(to right, green, white , red);
    }
    .stats_progress_bar{
        position: relative;
        display: -ms-flexbox;
        display: flex;
        -ms-flex-direction: column;
        flex-direction: column;
        -ms-flex-pack: center;
        justify-content: center;
        color: #000;
        font-size: 17px;
        text-align: center;
        white-space: nowrap;
        /*background-color: #007bff;*/
        transition: width .6s ease;
    }
    .stats_gress_text{
        display: none;
        position: relative;
        font-size: 15px;
        line-height: 1;
    }
    .Explain_btn{
        float: right;
        display: none;
        background-color: #87ceeb;
        border-radius: 8px;
        font-size: 12px;
        padding: 3px 5px 3px 5px;
        line-height: 0.75rem;
        /* padding: 3px 5px 3px 5px; */
    }

    .Explain_btn:hover {
        transform:scale(1.3,1.3);
        -webkit-transform:scale(1.3,1.3);
        -moz-transform:scale(1.3,1.3);
    }

    .prediction_hdr {
        margin:40px 0 20px;
        border-bottom:1px solid #999;
    }
    .prediction_hdr h4,
    .prediction_status,
    .prediction_stats{
        display:inline-block;
    }
    .prediction_status{
        margin-left:10px; 
    }
    .prediction_stats{
        float:right;
        margin-top:8px;
        color:#666;
    }

    .post-model{
        width:100%;
    }

    .main_image{
        text-align:center;
    }
    .model_details{
        padding-top:30px; 
    }
    .model_details ul{
        list-style:none;
        margin:0 0 30px;
        padding:0;
    }
    .viewer_hd{
        padding-bottom:5px;
        border-bottom:1px solid #999;
    }

    .loading_saved_model{
        display:none;
        font-size:150%;
        margin:10px 0;
    }   
    .model-progress-bar{
        font-size: medium; 
        height: 30px;
    }
    #xray_explain{
        font-size:120%;
        margin-bottom:8px;
    }
</style>
<main>
    <div class="col-sm-11 my-3 row">
        <div class="col-sm-12 viewer_hd">
            <h1>XrayFusion<?=$temp_shard_folder?></h1>
            <p>Powered by the <a href="https://arxiv.org/abs/1901.07031" target="_blank">CheXpert</a> model. Diseases included are based on prevalence in reports and clinical relevance. </p>
            <?php 
            if(!$selected_model){
            ?>
                <div class="well alert-danger my-4">
                    <h6>You must first configure/select a model and "apply to EM"*</h6>
                    <p>Go to <a href='<?=$url_configmodel?>'>Config/Select Model</a> to do so.</p>
                    <br>
                    <p><em>*Once a model is applied, it may take a few minutes to compile, if you have already selected a model try refreshing this page in a 3 minutes</em></p>
                </div>
            <?php
                }else{
            ?>
                <div class="progress mb-2 model-progress-bar" style="">
                    <div class="progress-bar pl-3" style="text-align: left">Loading Model</div>
                </div>
                <div class="loading_saved_model"><i class="fas fa-spinner fa-pulse"></i> Loading Stored Model</div>
            <?php
                }
            ?>
        </div>
        <?php 
            if($selected_model){
        ?>
            <div class="post-model" hidden>
                <div class="col-sm-12 row">
                    <div class="col-sm-4 main_image">
                        <input type='file' onchange="readURL(this);" id="select_file" hidden accept="image/*"/>
                        <img id="xray-image" src="<?=$placeholder_image?>" width="320" height="320" class="select-xray"/>
                        <img id="xray-image1" src="<?=$placeholder_image?>" width="320" height="320" style=" display:none;"/>
                        <div id="xray_canvas" style="display: none;" ></div>
                        <button class="btn btn-info select-xray"><i class="fas fa-x-ray"></i> Select X-ray Image</button>
                    </div>
                    <div class="col-sm-8 model_details">
                        <h5>ML Model Details:</h5>
                        <ul>
                        <li><b>Saved Model Alias:</b> <?=$selected_alias ?? "N/A" ?></li>
                        <li><b>Model Path:</b> <?=$selected_model?></li>
                        </ul>

                        <h5>Explanations:</h5>
                        <div id="xray_explain" style="display: none;">Predictive Regions for "progress_label" </div>
                        <div id="grad_download" style="display: none;"></div>
                    </div>
                </div>

                <div class="col-sm-12 prediction_hdr">
                    <h4>Prediction</h4>
                    <div class="prediction_status">
                        <div id="loading_indicator" hidden><i class="fas fa-spinner fa-pulse"></i> ANALYZING IMAGE ...</div>
                        <div id="loading_explain" hidden><i class="fas fa-spinner fa-pulse"></i> LOADING IMAGE HEATMAP ...</div>
                    </div>
                    <div class="prediction_stats">
                        <span id="memory" class="gpu-mem mr-3"></span>
                        <span class="prediction-time"></span>
                    </div>
                </div>

                
                <div class="prediction-results col-sm-12">
                    <div id="prediction-list" style="width: 100%; padding-right: 20px">
                        <div class="row">
                            <div class="col-3">
                            </div>
                            <div class="col-8" style="text-align: center;">
                                <span class="label" style="float: left;">Very Unlikely</span>
                                <span class="label">Neutral</span>
                                <span class="label" style="float: right;">Very Likely</span>
                            </div>
                        </div>
                        <div class="row prediction-template" hidden>
                            <div class="col-3" style="top: -5px;">
                                <span class="label"></span>
                            </div>
                            <div class="col-8">
                                <div class="progress stats_progress mb-2" style="">
                                    <!-- <div class="progress-bar" role="progressbar"></div> -->
                                    <i class="fas fa-ambulance stats_progress_bar" role="progressbar"></i><span class="stats_gress_text">0%</span>
                                </div>
                            </div>
                            <div class="col-1" style="right: 20px; bottom: 2px;">
                                <button class="Explain_btn" style="font-size: 12px;">Explain</button>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
            <script>
                var ajax_endpoint = "<?=$ajax_endpoint?>";
            </script>
            <script type="text/javascript" src="<?=$selected_model?>"></script>
            <script type="text/javascript" src="<?=$dedicated_upload_js ?>"></script>
        <?php
            }
        ?>
    </div>
</main>

