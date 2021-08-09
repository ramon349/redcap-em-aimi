// This is the matching js file for the model display page

/*
project_id: "39"
field_name: "participant_id"
field_phi: null
form_name: "survey"
form_menu_description: "Example Survey"
field_order: "1"
field_units: null
element_preceding_header: null
element_type: "text"
element_label: "Participant ID"
element_enum: null
element_note: null
element_validation_type: null
element_validation_min: null
element_validation_max: null
element_validation_checktype: "soft_typed"
branching_logic: null
field_req: "0"
edoc_id: null
edoc_display_img: "0"
custom_alignment: null
stop_actions: null
question_num: null
grid_name: null
grid_rank: "0"
misc: null
video_url: null
video_display_inline: "0"
*/

function REDCapField(metadata, parentForm) {
    this['metadata']    = metadata;
    this['parentForm']  = parentForm;
}

REDCapField.prototype.insertRow = function(container) {
    let jq_row              = $( this.getRow() );
    this.jq_input           = jq_row.find(":input");
    this.og_parent_classes  = this.jq_input.closest(".bmd-form-group").attr("class"); //will do some class manipulation, so lets remember og state.

    if(!this.metadata["readonly"]) {
        this.bindChange(jq_row);
    }

    jq_row.appendTo(container);
};

REDCapField.prototype.getRow = function() {
    //templates are injected (hidden) into the HTML and reused throughout , referenced to by #id

    var template_suffix = this.getType();
    if(template_suffix == "yesno" || template_suffix == "truefalse"){
        template_suffix = "radio";
    }

    var template_var    = window["template_" + template_suffix];
    var field_template  = $(template_var);
    
    /* radio/checkboxes/selects require extra steps to extract options which are "\\n" delimited strings and need massaging
        ... current values absurdly can be an {}, [] or String */
    var enum_result = [];
    if(this.metadata.element_enum !== null) {
        var temp = this.metadata.element_enum.split("\\n");
        for (var i in temp) {
            i           = parseInt(i);  //the # index is a "string"?  make it an int.
            var txt     = temp[i];
            var comma   = txt.indexOf(",");
            var label   = txt.substr(0, comma);
            var value   = txt.substr(comma+1);

            var checked_selected = "";
            var curval           = this.metadata.current_value;
            var hot              = false;

            if(Array.isArray(curval) || typeof(curval) == "object"){
                //if [] or {} using the current index value will have be non null if active
                hot = Array.isArray(curval) ? parseInt(curval[i]) : parseInt(curval[i+1]);
            }else {
                //if string, then we have to match against label value per iteration
                if (label == parseInt(curval)) {
                    hot = true;
                }
            }

            if(hot){
                checked_selected = template_suffix == "select" ? "selected" : "checked";
            }

            enum_result.push({"index": i, "key" : label.trim(), "val" : value.trim(), "selected" : checked_selected});
        }
    }
    //push a new property for multi option inputs
    this.metadata["element_enum_array"] = enum_result;
    
    //following two must be in this order because using prepend, common to most template types
    if(this.metadata.hasOwnProperty("form_menu_description") && this.metadata["form_menu_description"] !== null){
        var formmenudescription = $(form_menu_description);
        formmenudescription.html(this.metadata["form_menu_description"]);
        field_template.prepend(formmenudescription);
    }

    if(this.metadata.hasOwnProperty("element_preceding_header") && this.metadata["element_preceding_header"] !== null){
        var elementprecedingheader = $(element_preceding_header);
        elementprecedingheader.find("h6").html(this.metadata["element_preceding_header"]);
        field_template.prepend(elementprecedingheader);
    }

    if(this.metadata.hasOwnProperty("element_label") && this.metadata["element_label"] != ""){
        field_template.find(".element_label").html(this.metadata["element_label"]);
        // console.log(this.metadata["element_label"]);
    }
    
    field_template.find(".field_name").data("field_name", this.metadata["field_name"]);
    if(template_suffix == "radio" || template_suffix == "checkbox"){
        //Was i to do something here?
    }else{
        field_template.find(".field_name label").attr("for", this.metadata["field_name"]);
        field_template.find(".field_name :input").attr("name", this.metadata["field_name"]);
    }
    
    if(this.metadata.hasOwnProperty("current_value") && this.metadata["current_value"] != ""){
        field_template.find(":input").val(this.metadata["current_value"]);
    }

    if(this.metadata["element_enum_array"].length > 0){
        //select, radio, checkbox
        for(var i in this.metadata["element_enum_array"]){
            var choice = this.metadata["element_enum_array"][i];
            if(template_suffix == "select"){
                var opti = $("<option>").attr("value",choice["key"]).text(choice["val"]);
                if(choice["selected"]){
                    opti.prop("selected",true);
                }
                field_template.find("select").append(opti);
            }else{
                var forname = this.metadata["field_name"] + "__" + i;

                var div = $("<div>").addClass("custom-control");
                var inp = $("<input>").addClass("custom-control-input").addClass("form-control").attr("id",forname).attr("name",this.metadata["field_name"]).attr("type",template_suffix).val(choice["key"]);
                var lab = $("<label>").addClass("custom-control-label").attr("for",forname).text(choice["val"]);

                if(choice["selected"]){
                    inp.prop("checked",true);
                }

                div.append(inp);
                div.append(lab);

                field_template.find(".choices").append(div);
            }
        }
    }

    /*ONLY WAY TO TELL IF ITS A DATE FIELD IS if it has a element_validation_type of "date(time)" */
    if (this.getType() == "text"
        && this.metadata.hasOwnProperty("element_validation_type")
        && this.metadata["element_validation_type"]
        && this.metadata["element_validation_type"].indexOf("date") > -1){

        // template_suffix = "datetime";

        //store this to bind datepicker later
        // this.parentForm["addpickdates"].push(this.getName());
//     <div class="form-group bmd-form-group  col-md mb-5" data-field_name="{{field_name}}">
//         <label class="h6 bmd-label-floating" for="{{ field_name }}"><i class="fas fa-spinner" ></i><i class="fas fa-times"></i><i class="far fa-check-circle"></i> {{ element_label }}</label>
//         <input type="text" class="form-control datepicker picker__input" name="{{ field_name }}" id="{{ field_name }}" placeholder="" value="{{ current_value }}" {{readonly}}>
//         <div class="invalid-feedback">Invalid Input</div>
//     </div>
    }

    if (this.getType() == "text"){
        var placeholder = this.metadata["element_note"];
        field_template.find("input").prop("placeholder",placeholder);
    }

    // Special attention for file type
    if (this.getType() == "file"){
        //file type
    }

    if(this.metadata["readonly"]){
        field_template.find(":input").prop("readonly",true);
    }

    
    if(field_template.length){
        return field_template;
    }else{
        console.log("The field type [",template_suffix,"] is not supported yet");
        return false;
    }
};

REDCapField.prototype.bindChange = function(element){
    var _this = this;

    // THE .change() EVENT IN MD LIBRARY HAS AN INFINITE TRIGGER BUG.
    // TODO If unable to use .change() , then need to check save dirty status
    return;
    
    this.jq_input.blur(function(e){
        _this.saved = false;
        _this.save();
        e.preventDefault();
    });
}

REDCapField.prototype.save = function(){
    // console.log("field.save() " + this.getName() );
    this.updateStatus("queued");
    this.parentForm.saveField(this);
}

REDCapField.prototype.getValue = function(){
    var field_type =  this.getType()
    if(field_type == "checkbox" || field_type == "radio") {
        var check_values = [];
        this.jq_input.each(function (){
            if($(this).prop("checked")){
                check_values.push({ "val" : $(this).val() , "checked" :  1});
            }else{
                //damn, ok only want to get all inputs if checkbox. not regular radio
                if(field_type == "checkbox"){
                    check_values.push({ "val" : $(this).val() , "checked" :  0});
                }
            }
        });

        var val = field_type == "checkbox" ? check_values : check_values[0]["val"];
    }else{
        var val = this.jq_input.val();
    }
    return val;
}

REDCapField.prototype.getName = function(){
    return this.metadata.field_name;
}

REDCapField.prototype.getType = function(){
    return this.metadata.element_type;
}

REDCapField.prototype.updateStatus = function(status){
    var filled = this.jq_input.closest(".bmd-form-group").hasClass("is-filled");
    this.jq_input.closest(".bmd-form-group").removeClass().addClass(this.og_parent_classes).addClass(status);
    if(filled){
        this.jq_input.closest(".bmd-form-group").addClass("is-filled");
    }
}

REDCapField.prototype.clearField = function(){
    if(this.jq_input){
        this.jq_input.closest(".bmd-form-group").removeClass().addClass(this.og_parent_classes);
    }
}


RCForm = {
    //not really a requirement to preset these vars
    config      : {},
    new_hash    : "",
    record_hash : "",
    hash_flag   : false,    //this is to keep 2 simultaneous ajax calls from createing multiple new record hashes

    record_id   : "",
    metadata    : {},
    fields      : {},
    queue       : $.Deferred().resolve(),
    addpickdates : [],

    init: function(config, container, actionurl) {
        this.config         = config;
        this.exclude_fields = config.exclude_fields;
        this.readonly       = config.readonly;
        this.metadata       = config["metadata"];
        this.actionurl      = actionurl;

        // Build the field objects and append to container
        this.createForm(container);
        // this.bindDatePicker();
    },

    bindDatePicker : function(){
        //for date inputs (need to have date validation in REDCap), bind pickadate Functionality
        for(var i in this.addpickdates){
            var input_id = "#"+this.addpickdates[i];
            $(input_id).pickadate();
        }
    },

    // todo:  HANDLE ERRORS
    getMetadata: function(form_name) {
        $.ajax({
            context: RCForm,
            method: 'POST',
            data: {
                "action": "getMetadata",
                "hash"  : this.new_hash
            },
            dataType: 'json'
        }).done(function(data) {
            this.metadata = data;
        });
    },

    createForm : function(container){
        var newForm = $("<form>").addClass("container").addClass("mt-5").attr("id","redcap_form").attr("method","POST");
        var action  = $("<input>").prop("type","hidden").prop("name","action").val("saveRecord");
        newForm.append(action);
        container.append(newForm);
        
        this.buildFields(newForm);

        this.jq     = newForm;
        this.addFormButtons();

        //start form disabled until image selected
        this.disableForm();
    },

    addFormButtons: function(){
        var _this = this;

        var reset_button = $("<button type='reset' class='btn btn-raised btn-danger mr-3'>Reset Form</button>");
        var save_button  = $("<button type='button' class='btn btn-raised btn-primary' id='saveRecord'>Save Form <i class='fas fa-spinner fa-pulse'></i></button>");

        var btn_group   = $("<div class='btn-group'></div>");
        btn_group.append(reset_button);
        btn_group.append(save_button);

        reset_button.click(function(e){
            _this.refreshForm();
            e.preventDefault();
        });

        save_button.click(function(){
            var fields = [];
            for(var i in _this.fields){
                let field       = _this.fields[i];
                let field_name  = field.getName();
                let field_value = field.getValue();

                if(field_value == null || field_value == ""){
                    continue;
                }

                if(field.metadata["element_type"] =="checkbox"){
                    for (var c in field_value){
                        if(field_value[c]["checked"]){
                            var kv = {};
                            var chkval  = field_value[c]["val"];
                            kv.name     = field_name + "___" + chkval;
                            kv.value    = 1; 
                            fields.push(kv);
                        }
                    }
                }else{
                    var kv = {"name": field_name, "value" : field_value};
                    fields.push(kv);
                }
                
            }
            var data = {"type" : "saveRecord", "fields" : fields};

            //do some spinny shit on the save button
            $("#saveRecord").addClass("loading");
            var action_url = $("#redcap_form").attr("action");
            $.ajax({
                url : _this.actionurl,
                method: 'POST',
                data: data,
                dataType: 'json'
            }).done(function (result) {
                //remove the spinny shit 
                $("#saveRecord").removeClass("loading");

                let record_id = result["record_id"];
                console.log("record id", record_id);
                $("input[name='record_id']").val(record_id);
                //CLEAR FORM
                // _this.clearForm();

                console.log("save complete", result);
            }).fail(function (e) {
                $("#saveRecord").removeClass("loading");
                console.log("save failed", e);
            });
        });

        this.jq.append(btn_group);
    },

    buildFields: function(container){
        // log("Building Fields From Metadata ... ")
        for (const field_name in this.metadata) {
            if (this.exclude_fields.indexOf(field_name) > -1){
               continue;
            }

            this.fields[field_name] = new REDCapField(this.metadata[field_name], this);
            var readonly            = this.readonly.indexOf(field_name) > -1 ? "readonly" : "";
            this.fields[field_name]["metadata"]["readonly"] = readonly;

            this.fields[field_name].insertRow(container);
        }
    },

    saveField: function(field){
        var _this = this;
        // console.log("saveField started for " + field.metadata.field_name);

        //Checking if record_hash exists or is being fetched
        if(this.record_hash == "") {
            // get record_hash for the first time
            if (!this.hash_flag) {
                this.hash_flag  = true; // get only once per record;
                this.queue      = this.queue.then(this.getRecordHash.bind(this));
            }
            // console.log("quueing field" + field.metadata.field_name);
            // this.queue = this.queue.then(this.save.bind(field));
            this.queue = this.queue.then(this.saveField.bind(this,field));



        } else {
            console.log("alright, field saving  " + field.getName());

            var input_field     = field.getName();
            var input_value     = field.getValue();
            var field_type      = field.getType();

            if(field_type == "file"){
                var js_input = field.jq_input[0];
                var files    = js_input.files;
                var file     = files[0];

                if(files && file){
                    this.ajaxlikeFormUpload(field);
                }
            }else {
                var data = {
                    "action": "saveField",
                    "hash": _this.record_hash,
                    "input_field": input_field,
                    "input_value": input_value,
                    "field_type": field_type,
                    "date_field" : false
                };

                if(this.addpickdates.indexOf(input_field) > -1){
                    data["date_field"] = true;
                }

                $.ajax({
                    method: 'POST',
                    data: data,
                    dataType: 'json'
                }).done(function (result) {
                    console.log("saved " + field.metadata.field_name + " with result", result);
                    field.updateStatus("done");
                }).fail(function () {
                    console.log("save failed on field ", field);
                });
            }
        }
    },

    ajaxlikeFormUpload : function(field){
        // create temp hidden iframe for submitting from/to;
        if($('iframe[name=iframeTarget]').length < 1){
            var iframe = document.createElement('iframe');
            $(iframe).css('display','none');
            $(iframe).attr('src','#');
            $(iframe).attr('name','iframeTarget');
            $('body').append(iframe);
        }

        var input_field     = field.getName();
        var input_value     = field.getValue();
        var field_type      = field.getType();
        var el       = field.jq_input;
        var js_input = field.jq_input[0];
        var files    = js_input.files;
        var file     = files[0];

        el.parent().attr("target","iframeTarget");
        el.parent().append($("<input type='hidden'>").attr("name","action").val("saveField"));
        el.parent().append($("<input type='hidden'>").attr("name","hash").val(this.record_hash));
        el.parent().append($("<input type='hidden'>").attr("name","field_type").val(field_type));
        el.parent().append($("<input type='hidden'>").attr("name","input_field").val(input_field));
        el.parent().trigger("submit");
    },

    refreshForm: function(){
        // make form ready for new image

        // console.log("reset form + disable form");
        this.clearForm();
        // this.disableForm();
    },

    clearForm: function(){
        //reset form , js functionatlity
        this.jq.trigger("reset");

        //reset hash or else will try to save on existing record
        this.record_hash = false;

        //clear UI indicators
        for(var i in this.fields){
            this.fields[i].clearField();
        }
    },

    disableForm: function(){
        //set disabled until triggered
        this.jq.find(":input").prop("disabled", true);
    },

    enableForm: function(){
        //remove disabled
        this.jq.find(":input").prop("disabled", false);
    }

};