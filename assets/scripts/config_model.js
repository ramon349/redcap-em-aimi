const fetchVersions = (path) => {
    let payload = {
        'path' : path,
        'type': 'fetchVersions'
    };
    $.ajax({
        data: payload,
        method: 'POST',
        url: src, //from php page, ajaxHandler endpoint
        dataType: 'json'
    })
        .done((res) => enumerateVersion(res)) //remove column reference
        .fail((jqXHR, textStatus, errorThrown) => console.log(errorThrown)) //provide notification
};

const fetchModelConfig = (path) => {
    let payload = {
        'path' : path,
        'type': 'fetchModelConfig'
    };
    $.ajax({
        data: payload,
        method: 'POST',
        url: src, //from php page, ajaxHandler endpoint
        dataType: 'json'
    })
        .done((res) => populateConfig(res)) //remove column reference
        .fail((jqXHR, textStatus, errorThrown) => console.log(errorThrown)) //provide notification

};

const getExistingModelConfig = (alias) => {
    let payload = {
        'alias': alias,
        'type' : 'getExistingModelConfig'
    };

    $.ajax({
        data: payload,
        method: 'POST',
        url: src, //from php page, ajaxHandler endpoint
        dataType: 'json'
    })
        .done((res) => populateExistingConfig(res, alias)) //remove column reference
        .fail((jqXHR, textStatus, errorThrown) => console.log(errorThrown)) //provide notification
};

const enumerateVersion = (versions) => {
    let html = `<option selected disabled>Select Version here</option>`;
    for(let key in versions){
        html += `<option value=${encodeURIComponent(versions[key]['path'])}>${versions[key]['name']}</option>`;
    }
    $('#version').html(html);
};

const populateConfig = (response) => {
    if("config" in response){
        $('#config_uri').val(response['config']['html_url'] ? response['config']['html_url'] : 'ERROR: NOT FOUND');
        $('#path').val(response['config']['path'] ? response['config']['path'] : 'ERROR: NOT FOUND');
    }

    if("info" in response){
        $('#info').html(JSON.stringify(response['info'], null, 2));
    } else {
        $('#info').html('No redcap_config.json found in repository for this model');
    }
};

const populateExistingConfig = (response, alias) => {
    $('#config_uri').val(response['url']);
    $('#path').val(response['path']);
    $('#info').html(JSON.stringify(response['info'], null, 2));
    $('#alias').val(alias);
};

const checkValidity = () => {
    let check = [
        $('#info').text() !== '',
        $('#config_uri').val() !== '',
        $("#path").val() !== ''
    ];
    return !check.includes(false);
};

const applyConfig = () => {
    let uri = $('#config_uri').val();
    if(uri){
        let payload = {
            'uri': uri,
            'type' : 'applyConfig'
        };
        sendRequest(payload,
            () => triggerAlert('Success: configuration applied', 'success'),
            () => triggerAlert('Error applying config: please contact administrator', 'alert')
        );
        // $.ajax({
        //     data: payload,
        //     method: 'POST',
        //     url: src, //from php page, ajaxHandler endpoint
        //     dataType: 'json'
        // })
        //     .done((res) => triggerAlert('Success: configuration applied', 'success')) //remove column reference
        //     .fail((jqXHR, textStatus, errorThrown) => {
        //         console.log(jqXHR)
        //         triggerAlert('Error applying config: please contact administrator', 'alert')
        //     }) //provide notification
    }
};

const saveConfig = () => {
    let alias = $('#alias').val();
    if(checkValidity() && alias) { //all options are valid, with alias
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
        sendRequest(payload,
            ()=>triggerAlert('Success : configuration saved', 'success'),
            ()=>triggerAlert('Error saving config: please contact administrator', 'alert')
        );
        // $.ajax({
        //     data: payload,
        //     method: 'POST',
        //     url: src, //from php page, ajaxHandler endpoint
        //     dataType: 'json'
        // })
        //     .done((res) => triggerAlert('Success: configuration saved', 'success')) //remove column reference
        //     .fail((jqXHR, textStatus, errorThrown) => {
        //         triggerAlert('Error saving config: please contact administrator', 'alert')
        //     }) //provide notification
    } else {
        triggerAlert('Some fields are empty, please fill them out before continuing', 'alert');
    }
};

deleteConfig = () => {
    let alias = $('#alias').val();
    console.log('deleting ', alias);
    let payload = {
        'url' : src,
        'type': 'deleteConfig',
        'alias': alias,
    };
    sendRequest(payload,
        () => location.reload(),
        () => triggerAlert('Error deleting config, please contact administrator')
    );

};

const sendRequest = (payload, successCallback, failureCallback) => {
    $.ajax({
        data: payload,
        method: 'POST',
        url: src, //from php page, ajaxHandler endpoint
        dataType: 'json'
    })
    .done((res) => successCallback()) //remove column reference
    .fail((jqXHR, textStatus, errorThrown) => {
        failureCallback()
    })
};


const triggerAlert = (msg, type) => {
    $("#alert p").text(msg);

    if($('#alert').hasClass('success'))
        $('#alert').removeClass('success');
    if($('#alert').hasClass('alert'))
        $('#alert').removeClass('alert');

    $('#alert').addClass(type);
    $('#alert').show();
};

const clearFields = (field) => {
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
            $('#alias').attr('disabled', false); //enable fields
            $('#submit').attr('disabled', false);
            $('#delete').attr('disabled', true);
            break;
        default:
            break;
    }
};

const generateCustom = () => {
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
    $('#version').attr('disabled', true);
    $('#info').attr('contentEditable', true);
    $('#info').html(JSON.stringify(example, null, 2));
    $('#block').html('Please fill out the following options along with the path to the config.js');
    $('#config_uri').attr('disabled', false);
    $('#config_uri').val('https://github.com/example/model1/config.js')
    $('#path').val('CUSTOM');
};

$(function(){
    $('#new_model').on('change', function(){
        clearFields('existing_model');
        let selected = $(this).find(":selected");
        if(selected.val() === 'custom_new')
            generateCustom();
        else
            fetchVersions(selected.val());
    });

    $('#version').on('change', function(){
        let selected = $(this).find(":selected");
        fetchModelConfig(selected.val());
    });

    $('#existing_model').on('change', function(){
        clearFields('new_model');
        let selected = $(this).find(":selected");
        getExistingModelConfig(selected.val());
    });

    $('#submit').on('click', function(){
       saveConfig();
    });

    $("#apply").on('click', function(){
       applyConfig();
    });

    $('#delete').on('click', function(){
       deleteConfig();
    });
});

