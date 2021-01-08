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

        $.ajax({
            data: payload,
            method: 'POST',
            url: src, //from php page, ajaxHandler endpoint
            dataType: 'json'
        })
            .done((res) => triggerAlert('Success: configuration applied', 'success')) //remove column reference
            .fail((jqXHR, textStatus, errorThrown) => {
                console.log(jqXHR)
                triggerAlert('Error applying config: please contact administrator', 'alert')
            }) //provide notification
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
        $.ajax({
            data: payload,
            method: 'POST',
            url: src, //from php page, ajaxHandler endpoint
            dataType: 'json'
        })
            .done((res) => triggerAlert('Success: configuration saved', 'success')) //remove column reference
            .fail((jqXHR, textStatus, errorThrown) => {
                triggerAlert('Error saving config: please contact administrator', 'alert')
            }) //provide notification
    } else {
        triggerAlert('Some fields are empty, please fill them out before continuing', 'alert');
    }
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
    switch(field){
        case 'new_model': //clearing new model + version +
            $("#new_model").prop('selectedIndex',0);
            $("#version").prop('selectedIndex', 0);
            $("#version").find('option').not(':first').remove();
            $('#info').html("");
            $('#config_uri').val('');
            $('#path').val('');
            $('#alias').val('');

            $('#alias').attr('disabled', true); //disable fields of already saved configuration
            $('#submit').attr('disabled', true);
            break;
        case 'existing_model': //clearing existing model == new model selected
            $("#existing_model").prop('selectedIndex',0);
            $('#info').html("");
            $('#config_uri').val('');
            $('#path').val('');
            $('#alias').val('');

            $('#alias').attr('disabled', false); //enable fields
            $('#submit').attr('disabled', false);
            break;
        default:
            break;
    }
};

$(function(){
    $('#new_model').on('change', function(){
        clearFields('existing_model');
        let selected = $(this).find(":selected");
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
});

