jQuery(document).ready(function() {

    // Start with disabled submit button
    jQuery(".addnewpage :submit").prop("disabled", true);
    // Then enable it when a title is entered
    jQuery(".addnewpage input[name='title']").keyup(function(){
        var $submit = jQuery(this).parent("form").find(":submit");
        if (jQuery(this).val().length > 0) {
            $submit.removeAttr("disabled");
        } else {
            // For when the user deletes the text
            $submit.attr("disabled", "disabled");
        }
    }).keyup();

    // Change the form's page-ID field on submit
    jQuery(".addnewpage form").submit(function(e) {

        // Build the new page ID and save in hidden form field
        var ns = jQuery(this).find("[name='np_cat']");
        var title = jQuery(this).find("input[name='title']");
        var id = ns.val()+":"+title.val();
        jQuery(this).find("input[name='id']").val(id);

        // Clean up the form vars, just to make the resultant URL a bit nicer
        ns.prop("disabled", true);
        title.prop("disabled", true);

        return true;
    });

});
