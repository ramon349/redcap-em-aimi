# REDCap AIMI - A.I. enabled Medical Imaging Diagnosis initiative
Stanford ML-models as downloadable, plug-and-play modules for all REDCap users 

## Module Purpose
This module was designed to allow all REDCap users access to pre-trained Stanford Machine Learning models to run against images uploaded from the client.  A model repository was set up to service this module. 

The main features of this module:
* Choose from a drop down of pre-trained ML models
* The ability to edit parts of the model and save the configuration as an alias to the EM settings.
* Each image uploaded and evaluated will be stored as a record in the project.
* The ability to add or augment each record with custom observations or questions via REDcap instrument
* The ability to "explain" a model prediction with a heatmap overlay (GradCam)

## Module Endpoints
This module has no API endpoints.

## Module Crons
A cron task that runs once per day has been configured in this module.  

The following scripts will be triggered from this Cron task:
* __syncMaster__  : For installations where a DUA is in place, and a token avaialable.  Push data from this system back to the "master" system set in the model meta config.
