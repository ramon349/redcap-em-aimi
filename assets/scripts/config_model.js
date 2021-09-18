const AIMI = {
    clearFields: (field) => {
        $('#block').html('These configuration options are filled in from the selection above unless a custom configuration is selected, please confirm their validity once selecting an option');
        switch(field){
            case 'new_model': //clearing new model + version +
                $("#new_model").prop('selectedIndex',0);
                $("#version").prop('selectedIndex', 0);
                $("#version").find('option').not(':first').remove();
                $('#info').html("");
                $('#config_uri').val('');
                $('#path').val('');
                $('#alias').val('');

                $('#version').attr('disabled', true);
                $('#alias').attr('disabled', true); //disable fields of already saved configuration

                $('#apply').attr("disabled", true);
                $('#submit').attr('disabled', true);
                $('#delete').attr('disabled', false);
                break;
            case 'existing_model': //clearing existing model == new model selected
                $("#existing_model").prop('selectedIndex',0);
                $('#info').html("");
                $('#config_uri').val('');
                $('#path').val('');
                $('#alias').val('');

                $('#version').attr('disabled', false);
                $('#apply').attr("disabled", true);
                $('#alias').attr('disabled', false); //enable fields
                $('#submit').attr('disabled', false);
                $('#delete').attr('disabled', true);
                break;
            default:
                break;
        }
    },

    bind: () => {
        $('#new_model').on('change', function(){
            AIMI.clearFields('existing_model');
            let selected = $(this).find(":selected");
            if(selected.val() === 'custom_new')
                AIMI.generateCustom();
            else
                AIMI.fetchModelConfig(selected.val());
        });

        $('#existing_model').on('change', function(){
            AIMI.clearFields('new_model');
            let selected = $(this).find(":selected");
            AIMI.getExistingModelConfig(selected.val());
        });

        $('#submit').on('click', function(){
            AIMI.saveConfig();
        });

        $("#apply").on('click', function(){
            AIMI.applyConfig();
        });

        $('#delete').on('click', function(){
            AIMI.deleteConfig();
        });
    },

    generateCustom: () => {
        let example = {
            "name": "TITLE",
            "description": "DESCRIPTIVE_TEXT",
            "authors": [
                "AUTHOR_1",
            ],
            "institution": [
                "INSTITUTION_1"
            ],
            "released": "RELEASE_DATE"
        };

        $('#version').prop('selectedIndex', 0);
        $('#version').attr('disabled', true);
        $('#info').attr('contentEditable', true);
        $('#info').html(JSON.stringify(example, null, 2));
        $('#block').html('Please fill out the following options along with the path to the config.js');
        $('#config_uri').attr('disabled', false);
        $('#config_uri').val('https://github.com/example/model1/config.js')
        $('#path').val('CUSTOM');
    },

    triggerAlert: (msg, type) => {
        $("#alert p").text(msg);

        if($('#alert').hasClass('success')){
            $('#alert').removeClass('success');
        }

        if($('#alert').hasClass('alert')){
            $('#alert').removeClass('alert');
        }

        $('#alert').addClass(type);
        $('#alert').slideDown("medium");

        setTimeout(function(){
            $('#alert').slideUp("slow");
        }, 3000);
    },

    sendRequest: (payload, successCallback, failureCallback) => {
        $.ajax({
            data: payload,
            method: 'POST',
            url: src, //from php page, ajaxHandler endpoint
            dataType: 'json'
        })
            .done((res) => successCallback(res)) //remove column reference
            .fail((jqXHR, textStatus, errorThrown) => {
                console.log(textStatus, errorThrown);
                failureCallback();
            })
    },

    fetchVersions: (path) => {
        let payload = {
            'path' : path,
            'type': 'fetchVersions'
        };

        AIMI.sendRequest(payload,
            (res) => AIMI.enumerateVersion(res),
            () => AIMI.triggerAlert('Error fetching model versions, please contact administrator', 'alert')
        );
    },

    fetchModelConfig: (path) => {
        let payload = {
            'path' : path,
            'type': 'fetchModelConfig'
        };

        AIMI.sendRequest(payload,
            (res) => AIMI.populateConfig(res),
            () => AIMI.triggerAlert('Error fetching model configurations, please contact administrator', 'alert')
        );
    },

    getExistingModelConfig: (alias) => {
        let payload = {
            'alias': alias,
            'type' : 'getExistingModelConfig'
        };
        AIMI.sendRequest(payload,
            (res) => {
                $("#apply").attr("disabled",false);
                AIMI.populateExistingConfig(res,alias);
            },
            () => AIMI.triggerAlert('Error fetching existing model configurations, please contact administrator', 'alert')
        );
    },

    enumerateVersion: (versions) => {
        let html = `<option selected disabled>Select Version here</option>`;
        for(let key in versions){
            html += `<option value=${encodeURIComponent(versions[key]['path'])}>${versions[key]['name']}</option>`;
        }
        $('#version').html(html);
    },

    populateConfig: (response) => {
        if("config" in response){
            $('#config_uri').val(response['config']['html_url'] ? response['config']['html_url'] : 'ERROR: NOT FOUND');
            $('#path').val(response['config']['path'] ? response['config']['path'] : 'ERROR: NOT FOUND');
        }

        if("info" in response){
            $('#info').html(JSON.stringify(response['info'], null, 2));
        } else {
            $('#info').html('No redcap_config.json found in repository for this model');
        }
    },

    populateExistingConfig: (response, alias) => {
        $('#config_uri').val(response['url']);
        $('#path').val(response['path']);
        $('#info').html(JSON.stringify(response['info'], null, 2));
        $('#alias').val(alias);
    },

    checkValidity: () => {
        let check = [
            $('#info').text() !== '',
            $('#config_uri').val() !== '',
            $("#path").val() !== ''
        ];
        return !check.includes(false);
    },

    applyConfig: () => {
        const uri   = $('#config_uri').val();
        const alias = $('#alias').val();
        const info  = JSON.parse($('#info').text());

        //add loading spinner to button
        $("#submit").addClass("loading");

        if(uri){
            let payload = {
                'uri': uri,
                'info': info,
                'type' : 'applyConfig',
                'alias' : alias
            };
            AIMI.sendRequest(payload,
                (redirect_url) => {
                    //remove loading
                    $("#apply").removeClass("loading");
                    console.log("response", redirect_url);
                    AIMI.triggerAlert('Success: configuration applied , redirecting to Run Model in a few seconds', 'success');
                    setTimeout(function(){
                        location.href = redirect_url;
                    }, 5000);
                },
                () => AIMI.triggerAlert('Error applying config: please contact administrator', 'alert')
            );
        }
    },

    saveConfig: () => {
        let alias = $('#alias').val();
        if(AIMI.checkValidity() && alias) { //all options are valid, with alias
            let config = {};
            config['info'] = JSON.parse($('#info').text());
            config['url'] = $('#config_uri').val();
            config['path'] = $('#path').val();

            let payload = {
                'url' : src,
                'type': 'saveConfig',
                'alias': alias,
                'config': config
            };
            AIMI.sendRequest(payload,
                ()=> {
                    $("#apply").attr("disabled",false);

                    AIMI.applyConfig();

                    // AIMI.triggerAlert('Success : configuration saved, please refresh', 'success');
                    // setTimeout(function(){
                    //     location.href = location.href;
                    // }, 2000);
                },
                ()=> AIMI.triggerAlert('Error saving config: please contact administrator', 'alert')
            );
        } else {
            AIMI.triggerAlert('Some fields are empty, please fill them out before continuing', 'alert');
        }
    },

    deleteConfig: () => {
        let alias = $('#alias').val();
        let payload = {
            'url' : src,
            'type': 'deleteConfig',
            'alias': alias,
        };
        AIMI.sendRequest(payload,
            () => location.reload(),
            () => AIMI.triggerAlert('Error deleting config, please contact administrator')
        );

    },
};

$(function(){
    AIMI.bind();
});




