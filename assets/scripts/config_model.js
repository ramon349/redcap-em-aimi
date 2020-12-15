
const enumerateVersion = (versions) => {
    let html = `<option selected disabled>Select Version here</option>`;
    for(let key in versions){
        html += `<option value=${versions[key]['url']}>${versions[key]['path']}</option>`;
    }
    $('#version').html(html);
}

$(function(){
    console.log(data);
    $('.new_model').on('change', function(){
        let selected = $(this).find(":selected");
        if(selected.text() in data)
            enumerateVersion(data[selected.text()]);
    });
});


