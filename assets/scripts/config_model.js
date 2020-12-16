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

const enumerateVersion = (versions) => {
    let html = `<option selected disabled>Select Version here</option>`;
    for(let key in versions){
        html += `<option value=${encodeURIComponent(versions[key]['path'])}>${versions[key]['name']}</option>`;
    }
    $('#version').html(html);
}

const populateConfig = (response) => {
    if("config" in response){
        $('#config_uri').val(response['config']['html_url'] ? response['config']['html_url'] : 'ERROR: NOT FOUND');
        $('#path').val(response['config']['path'] ? response['config']['path'] : 'ERROR: NOT FOUND');
    }

    if("info" in response){
        $('#info').html(JSON.stringify(response['info'], null, 2));
    }

}

const saveConfig = (alias) => {
    let config = JSON.parse($('#info').text());
    config['url'] = $('#config_uri').val();
    config['path'] = $('#path').val();

    if(alias){
        let payload = {
            'url' : src,
            'type': 'saveConfig',
            'alias': alias,
            'config': config
        };
        console.log(payload);
        $.ajax({
            data: payload,
            method: 'POST',
            url: src, //from php page, ajaxHandler endpoint
            dataType: 'json'
        })
            .done((res) => console.log(res)) //remove column reference
            .fail((jqXHR, textStatus, errorThrown) => console.log(errorThrown)) //provide notification
    }

}

$(function(){
    $('#new_model').on('change', function(){
        let selected = $(this).find(":selected");
        fetchVersions(selected.val());
    });
    $('#version').on('change', function(){
        let selected = $(this).find(":selected");
        fetchModelConfig(selected.val());
    });
    $('#submit').on('click', function(){
       let alias = $('#alias').val();
       saveConfig(alias);
    });
});

