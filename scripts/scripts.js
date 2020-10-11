$(document).ready(function(){
    $('html').on('click', function(){
        $('#schedule_form input').parents('.fieldset').removeClass('focused');
        $('#schedule_form textarea').parents('.fieldset').removeClass('focused');
        $('#schedule_form textarea:focus').parents('.fieldset').addClass('focused');
        $('#schedule_form input:focus').parents('.fieldset').addClass('focused');
    });

}); 