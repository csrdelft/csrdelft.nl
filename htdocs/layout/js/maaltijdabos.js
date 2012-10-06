/*
 * maaltijdabobeheerder
 */

jQuery(document).ready(function(){

    //wijzigingen van checkboxes op abobeheerpagina verwerken
    observeCheckboxesAbos();

    //tabellen naar zebra converteren.
    jQuery("#abolijst tr:odd").addClass('odd');

    //hippe sorteerbare tabel fixen.
    var oTable = jQuery("#abolijst").dataTable({
        "oLanguage": {
            "sZeroRecords": "Geen leden gevonden",
            "sInfoEmtpy": "Geen leden gevonden",
            "sSearch": "Zoeken:",
            oPaginate:{
                "sFirst": "Eerste",
                "sPrevious": "Vorige",
                "sNext": "Volgende",
                "sLast": "Laatste"}
        },
        "iDisplayLength": 30,
        "bInfo": false,
        "bLengthChange": false,
        "aaSorting": [[1, 'asc']],
        "sPaginationType": "full_numbers",
        "aoColumns": [
            {'iDataSort': 7},	// naam
            {'sType': 'string'}, 				// waarschuwing
            {'sType': 'html'},	// jaar
            {'sSortDataType': 'dom-checkbox' }, // donderdagabo
            {'sType': 'html'},	// verticale
            {'sSortDataType': 'dom-checkbox' }, // verticaleabo
            {'sSortDataType': 'dom-checkbox' }, // vrouwenabo
            {'sType': 'html'}	// achternaam(verborgen)
        ],
        "aoColumnDefs": [
            { "bVisible": false, "aTargets": [ 7] }
        ]
    });
    /* Add event listeners to the two range filtering inputs */
    $('#filterwaarschuwingen').click( function() { oTable.fnDraw(); } );
});
/* Create an array with the values of all the checkboxes in a column */
$.fn.dataTableExt.afnSortData['dom-checkbox'] = function  ( oSettings, iColumn )
{
    var aData = [];
    $( 'td:eq('+iColumn+') input', oSettings.oApi._fnGetTrNodes(oSettings) ).each( function () {
        aData.push( this.checked==true ? "1" : "0" );
    } );
    return aData;
};
$.fn.dataTableExt.afnFiltering.push(
    function( oSettings, aData, iDataIndex ) {
        if(jQuery('#filterwaarschuwingen').is(':checked')){
            if(aData[1].length >6){ //de standaardwaarde &nbsp; is 6 tekens
                return true;
            }else{
                return false;
            }
        }else{
            return true;
        }
    }
);
function observeCheckboxesAbos(){
    jQuery(".abovinkje").change(function(){
        var ID =jQuery(this).attr('id');
        var ids=ID.split('-');
        var uid=ids[0];
        var abo=ids[1];
        var actie;
        jQuery(this).children('input').each(function(index){
            if(jQuery(this).is(':checked')){
                actie = 'add';
            }else{
                actie = 'delete';
            }
        });
        var data = 'uid='+uid+'&'+'abo='+abo;

        jQuery.ajax({
            type: "POST",
            url: '/actueel/maaltijden/abonnementenbeheer/abo/'+actie,
            data: data,
            cache: false,
            success: function(result){
                if(result=='Abonnementwijziging gelukt'){
                    jQuery("#"+ID).attr("style", "background-color: green !important").attr('title', result);
                }else{
                    jQuery("#"+ID).attr("style", "background-color: red !important").attr('title', result);
                }
            }
        });
    });
}
