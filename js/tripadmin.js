var table = $('#tripsadmin').DataTable({
    "ajax": "/admin/tripsdata", 
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
    },
    "columnDefs": [
        {
            "targets": [ 6 ],
            "visible": false
        },
        {
            "targets": [ 7 ],
            "visible": false
        }
    ]
});

table.draw();
$("#filter_delivered").change( function() {
    if ($(this).is(':checked')) {
        table.columns(6).search( $(this).next('label').text() ).draw();
    }
    else{
        if ($("#filter_deleted").is(':checked')) {
            table.columns(6).search( $("#filter_deleted").next('label').text() ).draw();
        }else{
            table.columns(6).search("").draw();
        }
    }
});
$("#filter_deleted").change( function() {
    if ($(this).is(':checked')) {
        table.columns(6).search( $(this).next('label').text() ).draw();
    }
    else{
        if ($("#filter_delivered").is(':checked')) {
            table.columns(6).search( $("#filter_delivered").next('label').text() ).draw();
        }else{
            table.columns(6).search("").draw();
        }
    }
});
$('#user-input').bind('keyup', function(e) {
    table.columns(0).search(this.value).draw();
});
$('#simuladores').on('change', function() {
    table.columns(7).search( this.value ).draw();
});