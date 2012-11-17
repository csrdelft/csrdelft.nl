/**
 * Init datepicker for all date fields
 *
 * @author Adrian Lang <lang@cosmocode.de>
 */
jQuery(function () {
    jQuery('input.datepicker').each(function(i){
        if(!this.id) this.id = 'datepicker' + i;
        calendar.set(this.id);
    });
});
