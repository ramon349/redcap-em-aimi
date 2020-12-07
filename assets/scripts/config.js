// Keys for config dictionary:
// all_labels -> List of labels the model predicts for. In CheXpert's case, there are 14 diseases.
// labels -> The labels we wish to show predictions for (can be the same as all_labels)
// labels_to_show -> Must be the same as the 'labels' list, used for UI logic
// model_path -> The path of the tensorflow.js model, which will usually contain a 'model.json' and weight files (shards)
// RGB_COLORMAP -> The colormap scheme used for gradCAM, to overlay a heatmap on the original image

// These keys will depend on the user's pre-processing function. They may or may not need to be present:
// IMG_SIZE -> The size of the image as per pre-processing requirements of the model
// CROP_SIZE -> The crop size of the image as per pre-processing requirements of the model

// GUI_WAITTIME -> The wait time used for sleep calls within the platform to keep the UI smooth during certain operationss

// Model calibration / scaling: Once a prediction is made, the user can choose to apply model calibration/scaling. The
// Stanford module does so by applying a piecewise linear function based on operating points generated from the CheXpert 
// holdout set. For each label, we maximize the difference between true positive and false positive rate. to get an op. point.
// Here are the config keys that correspond to the scaling/calibration process. 
// op_points -> List of operating points of the model. Used for post-processing model calibration.
// MAX_VAL -> Maximum probability value that any prediction can reach. 
// SCALE_MIN -> Threshold above which a processed output is scaled by a multiplier. 
// SCALE_UPPER -> A multiplier to scale a processed prediction value. 

config_data = {

    "all_labels":[
        "No Finding", 
        "Enlarged Cardiomediastinum",
        "Cardiomegaly", 
        "Lung Lesion", 
        "Airspace Opacity",
        "Edema",
        "Consolidation", 
        "Pneumonia", 
        "Atelectasis", 
        "Pneumothorax", 
        "Pleural Effusion",
        "Pleural Other", 
        "Fracture",
        "Support Devices"
    ],
    "labels": [
        "No Finding", 
        "Cardiomegaly", 
        "Lung Lesion", 
        "Edema", 
        "Consolidation",
        "Pneumothorax", 
        "Pleural Effusion"
    ],
    
    "labels_to_show": [
        "No Finding", 
        "Cardiomegaly", 
        "Lung Lesion", 
        "Edema", 
        "Consolidation",
        "Pneumothorax", 
        "Pleural Effusion"
    ],

    "model_path": "https://www.irvinszeto.com/stanford/aimi/models/KerasModel-TFJSWithLayers/model.json",

    "RGB_COLORMAP": [0.0416, 0, 0, 1, 0.05189484, 0, 0, 1, 0.06218969, 0, 0, 1, 0.07248453, 0, 0, 1], 

    "IMG_SIZE": 320,

    "CROP_SIZE": 320, 

    "GUI_WAITTIME": 60, 

    "op_points" : [
        0.19211840629577637,
        0.11442524194717407,
        0.3262208104133606,
        0.3407338261604309,
        0.2148394137620926,
        0.6378878355026245,
        0.5620131492614746
    ],

    "MAX_VAL": 0.9, 

    "SCALE_MIN": 0.7,

    "SCALE_UPPER": 1.2
}