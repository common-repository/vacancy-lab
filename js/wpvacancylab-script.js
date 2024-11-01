function wpvacancylab_candidate_form_validate(form) {
    jQuery('#wpvacancylab_candidate_form tr').removeClass('wpvacancylab_err');
    var r = true;
    var e = '';
    
    if(!jQuery('#wpvacancylab_Title').val()){
        e+='<li>Title is a required field</li>';
        jQuery('.wpvacancylab_Title').addClass('wpvacancylab_err');
        r = false;
    }
    if(!jQuery('#wpvacancylab_FirstName').val()){
        e+='<li>First Name is a required field</li>';
        jQuery('.wpvacancylab_FirstName').addClass('wpvacancylab_err');
        r = false;
    }
    if(!jQuery('#wpvacancylab_LastName').val()){
        e+='<li>Last Name is a required field</li>';
        jQuery('.wpvacancylab_LastName').addClass('wpvacancylab_err');
        r = false;
    }
    if(!jQuery('#wpvacancylab_EMail').val()){
        e+='<li>E-Mail is a required field</li>';
        jQuery('.wpvacancylab_EMail').addClass('wpvacancylab_err');
        r = false;
    }
    if(!jQuery('#wpvacancylab_PositionSought').val()){
        e+='<li>Position Sought is a required field</li>';
        jQuery('.wpvacancylab_PositionSought').addClass('wpvacancylab_err');
        r = false;
    }
    
    //Check DOB
    if(jQuery('#wpvacancylab_DateofBirth').val() && !FormatUkDate(jQuery('#wpvacancylab_DateofBirth').val())){
        e+='<li>Date of Birth is invalid (dd/mm/yyyy)</li>';
        jQuery('.wpvacancylab_DateofBirth').addClass('wpvacancylab_err');
        r = false;
    }
    
    if (!form["declaration"].checked) { // If the box isn\'t checked
        e+='<li>Please agree to the Candidate Declaration to continue</li>';
        jQuery('.wpvacancylab_declaration').addClass('wpvacancylab_err');
        r = false;
    }
    
    if(r === false){
        jQuery('#wpvacancylab_msg').html('<span><ul>'+e+'</ul></span>');
    }
    return r;
}

function FormatUkDate(dateStr) { 
    dateStr = dateStr.split("/");
    var newDate = new Date(dateStr[2], dateStr[1] - 1, dateStr[0]);
    return newDate.getDate() == Number(dateStr[0]) && newDate.getMonth() == Number(dateStr[1]) - 1? newDate : null;
}