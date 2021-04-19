<?php

namespace Stanford\AIMI;
/** @var \Stanford\AIMI\AIMI $module */

$repo_model_names       = $module->fetchModelNames();
$previously_saved_names = $module->fetchSavedEntries();
$model_test_names       = $previously_saved_names ? array_keys($previously_saved_names) : array();
$used_list_items        = array("<option selected disabled>Please select a previously saved model</option>");
$new_list_items         = array("<option selected disabled>Add a new model from repository</option>");

$loading_gif            = $module->getUrl("assets/images/loading.gif");
if(isset($repo_model_names)) {
    foreach($repo_model_names as $options) //Listing all the Stanford Models from REPO
        array_push($new_list_items, "<option value='{$options['path']}'>{$options['path']}</option>");
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

#apply .fas{
    display:none;
}
#apply.loading .fas{
    display:inline-block;
}
</style>
<main>
    <div class="col-sm-11 my-3 row">
        <h2 class="px-3 mb-1">Stanford AIMI Module Instructions</h2>
        <p class="px-3 mb-3">In order to run this module.  </p>

        <h3 class="px-3 mb-1">How to Use</h3>
        <p>Select a model from drop down in config/select</p>
        <p>Save a model (to cache it locally)</p>
        <p>Apply Model to EM</p>
        <p>Run Model</p>

        <a href="<?=$module->getUrl('assets/images/test_images.zip', true, true)?>">Test Images [.zip]</a>

        <h3 class="px-3 mb-1">Mobile API</h3>

        <h3 class="px-3 mb-1">Local Docker Installation</h3>
        First get the zip from 
        https://github.com/123andy/redcap-docker-compose

        If you already have a local REDCap installation
        Navigate to your earlier installation and type ‘docker-compose down -v’.  
        This will shut down your earlier REDCap and remove its volumes.

        Follow the steps in the redcap-docker-compose Readme.md to do all the installation.  

        If you already have a local REDCap installation
        docker-compose build
        docker-compose up -d --force-recreate

        Final steps for redcap-docker after start up

        Visit your local redcap installation at [localhost]

        Control Center-> Add Users (Table-based Only)
        Create 2 users “[Yourname]” and “tester”
        Administrator Privileges > Set “[Yourname]” as administrator
        Security & Authentication > set Authentication Method to Table-based
        Check [localhost]/mailhog/ to set passwords for both accounts, logging out after each
        Administrator Privileges > remove Joe User as administrator by unchecking all the boxes
        General Configuration > set “Redcap base URL” appropriately (ie. http://localhost/)
        OPTIONAL:
        I like to have my modules from git in another em folder, so /modules is for repo-based ones and /modules-local is for git-based modules I'm working on.
        Modules/services configuration -> add additional em folders, such as “modules-local/”. NB: when configuring this property, ignore the hint to use full paths. In Docker you need to use the relative path, e.g. modules-pub/ or modules-local/
        Create a directory “modules-local” in your redcap/www/ dir. Where new external modules can be copied into.
        9. Clone the external module under the modules-local folder. 
        Note that the folder name needs to have suffix _v9.9.9, so you have to type it out, e.g.: 
        git clone https://github.com/susom/redcap-em-mrn-lookup.git redcap-em-mrn-lookup_v9.9.9
        10. Add a new external module folder to redcap under External Modules: Navigate to Control Center, click on ‘External Modules’ under ‘Technical / Developer Tools’, and import the newly cloned EM. Until you take this step your EM won’t be discoverable within a project
        11. Create a test project from XML file in the external module
        12. Enable the module on the external module: within your project, navigate to “External Modules” under the “Applications” grouper on the left nav
        13. Set the model URL
        14. composer install inside the module folder


        Em_logger configuration
        In the move from gitlab to github, em_logger was renamed at the project level from em_logger to redcap-em-logger.  However, the module derives its identity from the folder name, so you have to rename it when you pull it from github as follows:

        github  clone  https://github.com/susom/redcap-em-logger  em_logger_v9.9.9

        You do need to install and configure it in the System Control Panel , but you do not then need to install it in your project.

        Also you really do want to install the “optional” settings in your config.json, then configure it locally to turn on debug logging by going to the External Modules section in your project and checking the checkbox that now shows up after you made those edits to your EM’s config.json

        Make sure to check the option for "TSV (tab separated values)" to ensure that the logs will write to your specified log directory.   This is found in the EM's global config in the control center as seen in the screenshot below
        Make sure to check


        Legacy Plugins

        Plugins are all part of a single GitHub repo: https://github.com/susom/redcap-plugins

        This repo requires review, so authors need to contact a colleague when you create a pull request and bug them to review, approve, and merge into master

        Once the changes have been merged into master, use the “merge dev->master->prod” button on Redcap Build Server bookmark to release to production (https://redcap-builder.med.stanford.edu/admin/ )
    </div>
</div>
<!-- Foundation links -->

<!-- Compressed CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/foundation-sites@6.6.3/dist/css/foundation.min.css" integrity="sha256-ogmFxjqiTMnZhxCqVmcqTvjfe1Y/ec4WaRj/aQPvn+I=" crossorigin="anonymous">

<!-- Compressed JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/foundation-sites@6.6.3/dist/js/foundation.min.js" integrity="sha256-pRF3zifJRA9jXGv++b06qwtSqX1byFQOLjqa2PTEb2o=" crossorigin="anonymous"></script>
