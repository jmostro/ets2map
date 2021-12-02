var table = $('#companytrips').DataTable({
    "ajax": { url: "../companydata/trips", cache: false},
    "ordering": false,
    "info":     false,
    "sDom": 't<"container-fluid"<"pull-left"l><"pull-right"p>>',
    "responsive": true,
    "pagingType": "full_numbers",
    "aLengthMenu": [[20, 40, 80, -1], [20, 40, 80, "Todos"]],
    "language": {
        "lengthMenu": "Mostrar _MENU_ registros por página",
        "emptyTable": "No se encontraron resultados",
        "zeroRecords": "No se encontraron resultados",
        "processing": "<i class='fa fa-spinner fa-pulse fa-3x fa-fw margin-bottom'></i>Procesando",
        "loadingRecords": "<i class='fa fa-spinner fa-pulse fa-3x fa-fw margin-bottom'></i>Cargando",
        "paginate": {
            "first":      "Primero",
            "last":       "Último",
            "next":       "Siguiente",
            "previous":   "Anterior"
        }
    }
});

jQuery(function(){
    table.draw();
    $('#simuladores').on('change', function() {
        table.columns(1).search( this.value ).draw();
    });
});