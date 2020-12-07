// Import model configurations from config.json
var MODEL_CONFIGS = config_data;
console.log(MODEL_CONFIGS)
console.log(MODEL_CONFIGS.RGB_COLORMAP)

// Get diseases that we wish to display. Here we have chosen 7 out of the model's original 14
// outputs, based on clinical relevance and prevalence in reports. 
let diseases_needed_index = new Array();
for (var i = 0; i < MODEL_CONFIGS.labels.length; i++) {
    disease_index = MODEL_CONFIGS.all_labels.indexOf(MODEL_CONFIGS.labels[i]);
    diseases_needed_index.push(disease_index);
}
console.log(diseases_needed_index);

// Declare any custom network layers needed here (as classes). When converting the original Stanford CheXpert 
// model, we required a Lambda layer that does not yet exist in tensorflow.js by default. 
class Lambda extends tf.layers.Layer {
    constructor() {
    super({})
    }

    static get className() {
    return 'Lambda';
    }

    computeOutputShape(inputShape) {
    return [inputShape[0]];
    }

    call(inputs, kwargs) {
    this.invokeCallHook(inputs, kwargs);
    return inputs[0]
    }
}
// Don't forget to register your custom layers (if any) using this line of code 
tf.serialization.SerializationMap.register(Lambda);

// Initialize model that will be loaded upon page load
let model;

// Loads the model, and warms it up with test input. User can configure this load function 
// according to the input specifications of the model. 
async function loadModel() {
    console.log(`# of tensors on OPEN: ${tf.memory().numTensors}` );

    $('.model-progress-bar').show();
    function progress_model_load(p){
        $(".model-progress-bar .progress-bar").css("width",`${Math.round(p * 100)}%`);
        $(".model-progress-bar .stats_progress_bar").css("left",`${Math.round(p * 100)}%`);
        $(".model-progress-bar .progress-bar").text(`Loading Model (${Math.round(p * 100)}%) ...`);
    }
    
    console.log(`# of tensors just before LOAD: ${tf.memory().numTensors}` );

    // Load the model using tensorflow.js API call to load a layers model. This could be a 
    // loadLayersModel() or loadGraphModel() depending on which model the users imports. 
    // More here: https://js.tensorflow.org/api/latest/
    model = await tf.loadLayersModel(MODEL_CONFIGS.model_path, {'onProgress':progress_model_load});
    console.log(model);
    console.log(`# of tensors just after LOAD: ${tf.memory().numTensors}` );
    
    $(".model-progress-bar .progress").text(`Warming Up ...`);

    await sleep(MODEL_CONFIGS.GUI_WAITTIME);

    // Warmup the model before using real data.
    const zero_tensor = tf.zeros([1,3,MODEL_CONFIGS.IMG_SIZE,MODEL_CONFIGS.IMG_SIZE])
    const warmupResult = await model.predict(zero_tensor, );
    tf.dispose(warmupResult);
    tf.dispose(zero_tensor);
    console.log(`# of tensors after warmup: ${tf.memory().numTensors}` );
}

function sleep(ms) {
	return new Promise(resolve => setTimeout(resolve, ms));
}

// Include pre-processing function here. We preprocess the image as per the requirements of the CheXpert 
// model specifications. The model config file accordingly declares image sizing and cropping variables. 
// For the RedCAP module, users can modify this function to suit their pre-processing specifications. 
function preprocessImage(image) {
    return tf.tidy(() => {
        let tensor = tf.browser.fromPixels(image, numChannels=3).toFloat();
        let resized = tf.image.resizeBilinear(tensor, [MODEL_CONFIGS.IMG_SIZE, MODEL_CONFIGS.IMG_SIZE], alignCorners=true);

        let meanImageNetRGB = {red: 0.485, green: 0.456, blue: 0.406};
        let stdImageNetRGB = {red: 0.229, green: 0.224, blue: 0.225};
        let indices = [tf.tensor1d([0], "int32"),tf.tensor1d([1], "int32"),tf.tensor1d([2], "int32")];

        cropImage = (img) => {
            const size = MODEL_CONFIGS.CROP_SIZE;
            const beginHeight = parseInt((img.shape[1]-size+1)*0.5);
            const beginWidth = parseInt((img.shape[0]-size+1)*0.5);
            return img.slice([beginWidth , beginHeight, 0], [size, size, 3]);
        }

        let crop = cropImage(resized);
        let toTensor = crop.div(tf.scalar(255.0));

        let centeredRGB = {
            red: tf.gather(toTensor, indices[0], 2)
                .sub(tf.scalar(meanImageNetRGB.red))
                        .reshape([102400]),
            green: tf.gather(toTensor, indices[1], 2)
                .sub(tf.scalar(meanImageNetRGB.green))
                        .reshape([102400]),
            blue: tf.gather(toTensor, indices[2], 2)
                .sub(tf.scalar(meanImageNetRGB.blue))
                        .reshape([102400])
        };

        let norm = tf.stack([centeredRGB.red.div(tf.scalar(stdImageNetRGB.red)).reshape([1,320,320]), 
        centeredRGB.green.div(tf.scalar(stdImageNetRGB.green)).reshape([1,320,320]), 
        centeredRGB.blue.div(tf.scalar(stdImageNetRGB.blue)).reshape([1,320,320])], 1);

        let proc_image = tf.tensor4d(Array.from(norm.dataSync()),[1,3,320,320]);

        // Return final pre-processed image tensor
        console.log(`# of tensors after pre-processing: ${tf.memory().numTensors}` );
        return proc_image;
    })
}

// Variable that will store gradCAM image that displays predictive regions
let explained_image;

// Variables that measure performance and run-time of the model, populated upon prediction
let num_predictions = 0;
let prediction_rolling_mean = 0;
let tensor = 0;

// This function gets the prediction of an uploaded image, essentially running inference 
// on the model and then passing it via a sigmoid to generate probabilities. 
async function get_preds(webcam_elemnt, model){

    tensor = preprocessImage(webcam_elemnt);
    
    // Generate performance metrics and run prediction
    var t0 = performance.now();
    let prediction = model.predict(tensor);
    tf.dispose(tensor);
    let data_all = tf.sigmoid(prediction).dataSync();
    let data_needed = diseases_needed_index.map(i => data_all[i]);
    let data = new Array();

    // Here, we apply a piecewise linear function to calibrate/scale model output, as adapted from  
    // the Chester Chest X-ray system: https://arxiv.org/pdf/1901.11210.pdf
    for (let i = 0; i < data_needed.length; i++) {
        if (data_needed[i]<MODEL_CONFIGS.op_points[i]){
            scaled_value = data_needed[i]/(MODEL_CONFIGS.op_points[i]*2);
        }else{
            scaled_value = 1-((1-data_needed[i])/((1-(MODEL_CONFIGS.op_points[i]))*2));
            if (scaled_value>MODEL_CONFIGS.SCALE_MIN & MODEL_CONFIGS.SCALE_UPPER){
                scaled_value = Math.min(MODEL_CONFIGS.MAX_VAL, scaled_value*MODEL_CONFIGS.SCALE_UPPER);
            }
        }
        data.push(scaled_value)
        console.log(MODEL_CONFIGS.labels[i] + ",pred:" + data_needed[i] + "," + "OP_POINT:" + MODEL_CONFIGS.op_points[i] + "->normalized:" + scaled_value);
    }

    tf.dispose(prediction);

    for (let i=0; i<data.length; i++){
        console.log(`Probability for ${MODEL_CONFIGS.labels[i]}: ${data[i]}`);
    }

    await sleep(MODEL_CONFIGS.GUI_WAITTIME)

    var t1 = performance.now();
    var new_mean = (num_predictions * prediction_rolling_mean + t1-t0)/(num_predictions + 1);
    num_predictions += 1;
    prediction_rolling_mean = new_mean;
    $(".prediction-time").text(`Average Prediction Time: ${Math.round(prediction_rolling_mean)} ms`)
    
    $("#memory").text(`GPU Memory: ${Math.round(tf.memory()["numBytesInGPU"]/1024/1024)} MB`);

    console.log(`# of tensors before Prediction: ${tf.memory().numTensors}` );
    
    for (let i=0; i<data.length; i++){
        if (MODEL_CONFIGS.labels_to_show.indexOf(MODEL_CONFIGS.labels[i]) != -1) {
            bar = prediction_elements[i];
            bar_txt = prediction_elements_txt[i];
            bar_btn = explain_btns[i];
            bar.css("left", `${data[i] * 100}%`);
            bar_txt.css("left", `${data[i] * 100}%`);
            bar_txt.html(`${Math.round(data[i] * 100)}%`);
            if(Math.round(data[i] * 100) > 24){
                bar_btn.show();
                bar_btn.click(function(){
                    
                    $("#loading_explain").removeAttr("hidden");
                    console.log("Analyzing...")
                    var act_btn = $(this);
                    
                    // Timeout function since getGrads is async
                    setTimeout(function () {
                    getGrads($('#xray-image1')[0], i);
                    $("#xray_explain").show();
                    $("#xray_explain").html('Predictive Regions for "'+act_btn.parent().parent().find('span.label').html()+'"');
                    $("#grad_download").show();
                    $('#grad_download').html('<a id="download_anchor" download="image_explained.jpg" href="www.google.come">Download Explained Image</a>');
            
                }, 30);
                    
                })
            }
            
        }
    }
    
    tf.dispose(data);

    console.log(`# of tensors after Prediction: ${tf.memory().numTensors}` );

    $("#loading_indicator").attr("hidden", true);
    $("#loading_explain").attr("hidden", true);
    $("#xray_explain").hide();
    $("#xray_explain").html("");

    $("#grad_download").hide();
    $("#grad_download").html("");
}

// This function gets the gradients from the gradCAM method and then overlays the heatmap 
// on the original image. It then displays the heatmap along with a link to download it. 
async function getGrads(image, index) {

    grads = await gradCAM(image, index);

    heatMap = tf.tidy(() => {
        // Expand dimensions since colorMap function expects rank 4 tensor
        grads_expanded = grads.expandDims(-1).expandDims();
        heatMap = applyColorMap(grads_expanded);
        x = tf.browser.fromPixels(image, numChannels=3).toFloat().reshape([1, MODEL_CONFIGS.IMG_SIZE, MODEL_CONFIGS.IMG_SIZE, 3]);

        // Overlay heatmap on original image and add to canvas with overlay facor = 1.1
        heatMap = heatMap.mul(1.1).add(x.div(255));
        return heatMap.div(heatMap.max()).reshape([MODEL_CONFIGS.IMG_SIZE, MODEL_CONFIGS.IMG_SIZE, 3]);
    });

    var canvas = document.createElement("canvas");
    await tf.browser.toPixels(heatMap, canvas);

    $("#loading_indicator").attr("hidden", true);
    $("#loading_explain").attr("hidden", true);

    $("#xray_canvas").show();
    $("#xray-image").hide();
    $("#xray_canvas").html("");
    $("#xray_canvas").append(canvas);

    explained_image = canvas.toDataURL("image/jpeg");
    $('#download_anchor').attr('href', explained_image);
    $('#download_anchor').attr('download', MODEL_CONFIGS.labels[index] + "_explained.jpg");

    tf.dispose(grads);
    tf.dispose(heatMap);
    console.log(tf.memory());
}


// This function generates gradCAM (visual explanations of the model's predictions). Here we use the 
// tf.grad() method of TensorFlow.js which has the advantage that it can work on any model loaded 
// into the platform. On the other hand, the method has a slower runtime. 
async function gradCAM(image, index) {

    return layer = tf.tidy(() => {
        chestgrad = tf.grad(x => model.predict(x).reshape([-1]).gather(index))
        const batched = preprocessImage(image);
        const grad = chestgrad(batched);
        const layer = grad.mean(0).abs().max(0)
        return layer.div(layer.max())
    });
}

// Function that applies a color map to render the heatmap overlay on the uploaded image. Adapted from 
// the tensorflow.js implementation (https://github.com/tensorflow/tfjs-examples/blob/master/visualize-convnet/utils.js)
function applyColorMap(x) {
    tf.util.assert(
        x.rank === 4, `Expected rank-4 tensor input, got rank ${x.rank}`);
    tf.util.assert(
        x.shape[0] === 1,
        `Expected exactly one example, but got ${x.shape[0]} examples`);
    tf.util.assert(
        x.shape[3] === 1,
        `Expected exactly one channel, but got ${x.shape[3]} channels`);

    return tf.tidy(() => {
        // Get normalized x.
        const EPSILON = 1e-5;
        const xRange = x.max().sub(x.min());
        const xNorm = x.sub(x.min()).div(xRange.add(EPSILON));
        const xNormData = xNorm.dataSync();

        const h = x.shape[1];
        const w = x.shape[2];
        const buffer = tf.buffer([1, h, w, 3]);

        const colorMapSize = MODEL_CONFIGS.RGB_COLORMAP.length / 3;
        for (let i = 0; i < h; ++i) {
        for (let j = 0; j < w; ++j) {
            const pixelValue = xNormData[i * w + j];
            const row = Math.floor(pixelValue * colorMapSize);
            buffer.set(MODEL_CONFIGS.RGB_COLORMAP[3 * row], 0, i, j, 0);
            buffer.set(MODEL_CONFIGS.RGB_COLORMAP[3 * row + 1], 0, i, j, 1);
            buffer.set(MODEL_CONFIGS.RGB_COLORMAP[3 * row + 2], 0, i, j, 2);
        }
        }
        return buffer.toTensor();
    });
}

// Utility function to read in data from an uploaded image
function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        $("#xray-image").show();
        reader.onload = function (e) {
            $("#xray_canvas").hide();
            $("#xray-image").show();
            $("#xray_explain").hide();
            $("#xray_explain").html("");

            $("#grad_download").hide();
            $("#grad_download").html("");

            $('#xray-image')
                .attr('src', e.target.result);
            $('#xray-image1').attr('src', e.target.result);
            $('#xray-image1').hide();
        };
    
        reader.readAsDataURL(input.files[0]);
    }
}

let prediction_elements = [];
let prediction_elements_txt = [];
let explain_btns = [];

// Driver function to run the app 
async function app() {
    // Load the model using the loading function
    await loadModel();

    let pred_template = $(".prediction-template").clone();
    pred_template.removeAttr("hidden");
    pred_template.removeClass("prediction-template");
    
    for (let i = 0; i < MODEL_CONFIGS.labels.length; i ++){
      let e = pred_template.clone();
      let label = MODEL_CONFIGS.labels[i];
      e.find('.label').text(label);
      $("#prediction-list").append(e);
      prediction_elements.push($(e.find(".stats_progress_bar")));
      prediction_elements_txt.push($(e.find(".stats_gress_text")));

      explain_btns.push($(e.find(".Explain_btn")));
    }
    
    for (let i = 0; i < MODEL_CONFIGS.labels.length; i ++) {
        if (MODEL_CONFIGS.labels_to_show.indexOf(MODEL_CONFIGS.labels[i]) == -1) {
            prediction_elements[i].parent().parent().parent().hide()
        }
    }
    
  $('.model-progress-bar').hide()
  $('.post-model').removeAttr("hidden")    
  
  $('#select_file').change(function () {
      console.log("Analyzing...")
      $("#loading_indicator").removeAttr("hidden");
      
        for (let i=0; i<MODEL_CONFIGS.labels_to_show.length; i++){
            let bar = prediction_elements[i];
            let bar_txt = prediction_elements_txt[i];
            let bar_btn = explain_btns[i];
            bar.css("left", `0%`);
            bar_txt.css("left", `0%`);
            bar_txt.html('0%');
            bar_btn.hide();
        }
      setTimeout(function () {
        get_preds($("#xray-image")[0], model);    
      }, 100)
      
  })  

  $('.select-xray').click(function () {
      $("#select_file").click();
  })

  $('.stats_progress_bar').hover(function() {
    $(this).parent().find(".stats_gress_text").show();
    
  }, function() {
    $(this).parent().find(".stats_gress_text").hide();
    
  })
  
}


app();