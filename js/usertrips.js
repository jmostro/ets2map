var table = $('#usertrips').DataTable({
    "ajax": {
            "url": "../../tripsdata",
            "type": "POST",
            "data": {
                    "a":"view",
                    "id":$('#usertrips').attr('data-id')
                }
        },
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
        "processing": "<div align='center'><i class='fa fa-spinner fa-pulse fa-3x fa-fw margin-bottom'></i>Procesando</div>",
        "loadingRecords": "<div align='center'><i class='fa fa-spinner fa-pulse fa-3x fa-fw margin-bottom'></i>Cargando</div>",
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