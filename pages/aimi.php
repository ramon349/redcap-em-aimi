<?php
namespace Stanford\AIMI;
/** @var \Stanford\AIMI\AIMI $module */

//Some Static URLS
$placeholder_image      = $module->getUrl("assets/images/placeholder.jpg");
$dedicated_upload_js    = $module->getUrl("assets/scripts/index_upload.js");
$url_configmodel        = $module->getUrl("pages/config_model.php");
$ajax_endpoint          = $module->getUrl("endpoints/ajaxHandler.php");

$UI_title               = !empty($module->getProjectSetting("run_model_page_title")) ? $module->getProjectSetting("run_model_page_title") : "XrayFusion";
$UI_subtitle            = !empty($module->getProjectSetting("run_model_page_subtitle")) ? $module->getProjectSetting("run_model_page_subtitle")  : "Powered by the <a href='https://arxiv.org/abs/1901.07031' target='_blank'>CheXpert</a> model. Diseases included are based on prevalence in reports and clinical relevance.";

//Active Model Meta Data
$selected_config        = !empty($module->getProjectSetting("config_uri")) ? $module->getProjectSetting("config_uri") : null;;
$active_alias           = !empty($module->getProjectSetting("active_alias")) ? $module->getProjectSetting("active_alias") : null;
$aliases                = !empty($module->getProjectSetting("aliases")) ? $module->getProjectSetting("aliases") : array();
$alias_names            = array_keys($aliases);

$current                = $aliases[$active_alias];
$current_info           = array_key_exists("info",$current) ? $current["info"] : array();
$current_config_uri     = array_key_exists("url",$current) ? $current["url"] : null;
$shard_paths            = array_key_exists("model_json",$current) ? $current["model_json"]["weightsManifest"][0]["paths"] : array();

//Should be including recap_config.js, if not exist or new model active, will need to create new
$selected_model         = array_key_exists("config_js", $current) ? $module->getUrl("endpoints/passthrough.php?em_setting=config_js", true, true) : null;

$css_sources = [
    "https://use.fontawesome.com/releases/v5.8.2/css/all.css",
    "https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap",
];

// Additional javascript sources
$js_sources    = [
    "https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@3.8.0/dist/tf.min.js",
    "https://unpkg.com/popper.js@1.12.6/dist/umd/popper.js",
    $module->getUrl("assets/scripts/templates.js", true, true),
    $module->getUrl('assets/scripts/redcapForm.js', true, true),
];

$metadata   = $module->getMetadata("bs");
$complete   = array_pop($metadata); //remove compeleted
$exclude    = array($complete["field_name"]);

$rcjs_renderer_config = [
    'exclude_fields'   => array($exclude)
   ,'readonly'         => array("record_id", "base64_image", "model_results", "model_config", "model_top_predictions","model_prediction_time")
   ,'metadata'         => $metadata
];



//Include Asset Files
foreach($css_sources as $css){
    echo '<link rel="stylesheet" href="'.$css.'" crossorigin="anonymous">';
}
foreach($js_sources as $js){
    echo '<script src="'.$js.'" crossorigin="anonymous"></script>';
}
?>
<script>
    var ajax_endpoint       = "<?=$ajax_endpoint?>";

    //CURRENT MODEL DETAILS
    var _alias              = "<?= $active_alias ?>";
    var _uri                = "<?= $current_config_uri ?>";
    var _info               = <?= json_encode($current_info) ?>;

    //ACTIVATE NEW SAVED MODELS
    function applyActiveConfig(alias, uri, info, loading){
        let payload = {
            'uri'   : uri,
            'info'  : info,
            'type'  : 'applyConfig',
            'alias' : alias
        };

        $.ajax({
            method: 'POST',
            url: ajax_endpoint,
            data: payload,
            dataType: 'json'
        }).done(function(redirect_url){
            //remove loading
            if(loading){
                loading.removeClass("loading");
            }
            setTimeout(function(){
                location.href = redirect_url;
            }, 2000);
        }).fail(function(err){
            console.log(err);
        });
    }
</script>
<style>

    #redcap_form{
        /* display:none; */
    }

    .select-xray {
        display:inline-block;
        margin:0 auto;
        font-size: 20px;
        max-width: 100%;
        max-height: 100%;
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

    .progress{
        height: 1rem;
        border-radius: .25rem;
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
        min-width:354px;
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

    span.element_label{
        font-weight:600;
    }

    .element_label em{
        display:block;
        font-size:85%;
    }

    blockquote.alert{
        border:0 !important;
    }
    blockquote span.element_label{
        font-weight:normal;
    }

    #saveRecord .fas{
        display:none;
    }
    #saveRecord.loading .fas{
        display:inline-block;
    }

    .activate_model i {
        display:none;
    }
    .activate_model.loading i {
        display:inline-block;
    }

    .activate_model span:before{
        content:"Activate";
    }
    .activate_model:disabled {
        background:#ccc;
        border:1px solid #999;
    }
    .activate_model:disabled span:before{
        content:"Active";
    }

    #model_select{
        position:absolute;
        top:15px;
        right:15px;
    }
</style>
<main>
    <div class="col-sm-11 my-3 row">
        <div class="col-sm-12 viewer_hd">
            <h1><?=$UI_title?><?=$temp_shard_folder?></h1>
            <p><?=$UI_subtitle?></p>
            <?php
            if(empty($alias_names)){
            ?>
                <div class="well alert-danger my-4">
                    <h4>No Models have been Configured.</h4>
                    <h5>Go to <a href='<?=$url_configmodel?>'>Config/Select Model</a> to configure and save at least one pre-trained model*.</h5>
                </div>
            <?php
            }elseif(!$active_alias){
            ?>
                <div class="well alert-danger my-4">
                    <h4>Please activate a pre-saved model alias.</h4>
                    <select class="model_select"  style="vertical-align:middle">
                        <?php

                            foreach($aliases as $alias => $meta){
                                echo "<option $selected data-url='".$meta["url"]."' data-info='".json_encode($meta["info"])."'>$alias</option>";
                            }
                        ?>
                    </select>
                    <button class="btn btn-xs btn-info activate_model"><i class="fas fa-spinner fa-pulse "></i> Activate Model</button>
                </div>
            <?php
                }else{
            ?>
                <div class="progress mb-2 model-progress-bar" style="">
                    <div class="progress-bar pl-3" style="text-align: left">Loading Model</div>
                </div>
                <div class="loading_saved_model"><i class="fas fa-spinner fa-pulse "></i> Loading Stored Model</div>
            <?php
                }
            ?>
        </div>
        <?php
            if($selected_model){
        ?>
            <div id="model_select">
                <select class="model_select"  style="vertical-align:middle">
                    <?php

                    foreach($aliases as $alias => $meta){
                        $selected = $alias == $active_alias ? "selected" : null;
                        echo "<option $selected data-url='".$meta["url"]."' data-info='".json_encode($meta["info"])."'>$alias</option>";
                    }
                    ?>
                </select>
                <button class="btn btn-xs btn-info activate_model" disabled><i class="fas fa-spinner fa-pulse "></i> <span></span> Model</button>
            </div>
            <div class="post-model" hidden>
                <div class="col-sm-12 row">
                    <div class="col-sm-4 main_image">
                        <input type='file' onchange="readURL(this);" id="select_file" hidden accept="image/*;capture=camera"/>
                        <img id="xray-image" src="<?=$placeholder_image?>" width="320" height="320" class="select-xray"/>
                        <img id="xray-image1" src="<?=$placeholder_image?>" width="320" height="320" style=" display:none;"/>
                        <div id="xray_canvas" style="display: none;" ></div>
                        <button class="btn btn-info select-xray"><i class="fas fa-x-ray"></i> Select X-ray Image</button>
                    </div>
                    <div class="col-sm-6 model_details">
                        <h5>ML Model Details:</h5>
                        <ul>
                        <li><b>Saved Model Alias:</b> <?=$active_alias ?? "N/A" ?></li>
                        <li><b>Model Path:</b> <span id="selected_config"><?=$selected_config?></span></li>
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

            <script type="text/javascript" src="<?=$selected_model?>"></script>
            <script type="text/javascript" src="<?=$dedicated_upload_js ?>"></script>

            <hr>

            <div id="redcap_container" class="col-sm-12"></div>
        <?php
            }
        ?>


    </div>
</main>
<script>
    $(document).ready(function(){
        //pass info required to download project's survey metadata and build html
        // this puts the form into the DOM so must happen before the RCTF work
        RCForm.init(<?=json_encode($rcjs_renderer_config);?>, $("#redcap_container"), '<?=$ajax_endpoint?>' );

        //form inits disabled
        $('.select-xray').click(function() {
            //form inits disabled
            RCForm.enableForm();
            RCForm.clearForm();
        });

        //ACTIAVTE DIFFERENT MODEL ON SELECT
        $(".activate_model").click(function(e){
            e.preventDefault();
            var _el = $(this);
            _el.addClass("loading");

            var _model = $(".model_select option:selected");
            var _uri    = _model.data("url");
            var _info   = _model.data("info");
            var _alias  = _model.text();

            applyActiveConfig(_alias, _uri, _info, _el);
        });

        //CHANGE BUTTON UI ON SELECT CHAGNE
        $(".model_select").change(function(e){
            $(".activate_model").attr("disabled", false);
        });
    });
</script>
