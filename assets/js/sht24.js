$(document).ready(function () {
    $('table.sht24-trainingslist').DataTable({
        language: {
            decimal: ',',
            thousands: '.',
            url: sht24_object.plugin_directory + '/DataTables/i18n/de_de.lang',
            infoEmpty: '',
            emptyTable: 'Derzeit stehen keine Trainings in der gewünschten Ansicht zur Verfügung. Bitte schauen Sie später noch einmal vorbei.',
            paginate: {
                first: '&laquo;',
                previous: '&lt; Zurück',
                next: 'Weiter &gt;',
                last: '&raquo;'
            },
        },
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        lengthMenu: [],
        info: true,
        searching: false,
        bLengthChange: false,
        pageLength: 5,
        pagingType: 'full',
        autoWidth: true,
        order: [[sht24_object.defaultOrderColumnNumber, 'asc']]
    });
});
