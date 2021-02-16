var element_preceding_header = `
<div class="col-md py-3 mb-2 bg-light text-dark border rounded">
<h6></h6>
</div>`;

var form_menu_description = `<h4 class="mb-3 col-md bg-dark text-white py-3 px-3 rounded"></h4>`;


var template_text = ` 
    <div data-type="text"> 
        <div class="form-group bmd-form-group  col-md mb-5 field_name" >
            <label class="h6 bmd-label-floating" for=""> <span class="element_label"></span></label>
            <input type="text" class="form-control" name="" id="" placeholder="" value="" >
            <div class="invalid-feedback">Invalid Input</div>
        </div>
    </div>`;

var template_textarea = `
    <div data-type="textarea"> 
        <div class="form-group bmd-form-group col-md  mb-5 field_name" >
            <label class="h6 bmd-label-floating" for=""> <span class="element_label"></span></label>
            <textarea class="form-control" id="" name="" placeholder="" ></textarea>
            <div class="invalid-feedback">Invalid Input</div>
        </div>
    </div>`;

var template_select = `
<div data-type="select"> 
    <div class="form-group bmd-form-group col-md  mb-5 field_name" >
        <label class="h6 bmd-label-floating" for="">
             <span class="element_label"></span></label>

        <select class="form-control" id="" name="" >
        </select>
        <div class="invalid-feedback">Invalid Input</div>
    </div>
</div>`;

var template_radio = `
<div data-type="radio"> 
    <div class="form-group bmd-form-group col-md mb-5 field_name" >
        <p class="h6 mb-2 "> <span class="element_label"></span></p>

        <div class="choices"></div>
        <div class="invalid-feedback">Invalid Input</div>
    </div>
</div>`;

var template_checkbox = `
<div data-type="checkbox"> 
    <div class="form-group bmd-form-group  col-md mb-5 field_name" >
        <p class="h6 mb-3"> <span class="element_label"></span></p>

        <div class="choices"></div>
        <div class="invalid-feedback">Invalid Input</div>
    </div>
</div>`;

var template_descriptive = `
    <blockquote class="blockquote text-left my-5 mx-1 alert alert-info">
        <p><span class="element_label h5"></span></p>
    </blockquote>`;

var template_file = `
<div data-type="file"> 
    <div class="form-group  col-md mb-5 field_name" >
        <label class="h6 bmd-label-floating" for=""> <span class="element_label"></span></label>
        <form method="post" enctype="multipart/form-data"><input type="file" class="form-control-file" name="" id="" placeholder="" value="" ></form>
        <div class="invalid-feedback">Invalid Input</div>
    </div>
</div>`;

var template_datetime = `
<div data-type="datetime"> 
    <div class="form-group bmd-form-group  col-md mb-5 field_name" >
        <label class="h6 bmd-label-floating" for=""> <span class="element_label"></span></label>
        <input type="text" class="form-control datepicker picker__input" name="" id="" placeholder="" value="" >
        <div class="invalid-feedback">Invalid Input</div>
    </div>
</div>`;

// <i class="fas fa-spinner" ></i><i class="fas fa-times"></i><i class="far fa-check-circle"></i>