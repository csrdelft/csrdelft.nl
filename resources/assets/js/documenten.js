import $ from 'jquery';

/**
 *    Documentenketzerjavascriptcode.
 */
$(function () {
    function readableFileSize(size) {
        let units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        let i = 0;
        while (size >= 1024) {
            size /= 1024;
            ++i;
        }
        size = size / 1;
        return size.toFixed(1) + ' ' + units[i];
    }

    //tabellen naar zebra converteren.
    $('#documenten').find('tr:odd').addClass('odd');

    //hippe sorteerbare tabel fixen.
    $('#documentencategorie').dataTable({
        oLanguage: {
            sZeroRecords: 'Geen documenten gevonden',
            sInfoEmtpy: 'Geen documenten gevonden',
            sSearch: 'Zoeken:',
        },
        iDisplayLength: 20,
        bInfo: false,
        bLengthChange: false,
        aaSorting: [[3, 'desc']],
        aoColumns: [
            {sType: 'html'}, // documentnaam
            //Bestandstgrootte naar B/KB omzetten.
            {
                fnRender: (oObj) => readableFileSize(oObj.aData[1]),
                bUseRendered: false,
            },
            null, //mime-type
            {sType: 'html'}, //moment toegevoegd
            null, //Eigenaar
        ],
    });
});
