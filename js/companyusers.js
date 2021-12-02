var table = $('#companyusers').DataTable({
    "ajax": { url: "../companydata/drivers", cache: true },
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
    },
    "columnDefs": [
        {
            "targets": [4],
            "visible": false
        }
    ]
});

jQuery(function(){
    table.draw();
    $("#filter-onsite").change( function() {
        if ($(this).is(':checked')) {
            table.columns(4).search( $(this).next('label').text() ).draw();
        }
        else{
            if($("#filter-onroad").is(':checked')){
                table.columns(4).search( $("#filter-onroad").next('label').text() ).draw();
            }else{
                table.columns(4).search("").draw();
            }
        }
    });
    $("#filter-onroad").change( function() {
        if ($(this).is(':checked')) {
            table.columns(4).search( $(this).next('label').text() ).draw();
        }
        else{
            if($("#filter-onsite").is(':checked')){
                table.columns(4).search( $("#filter-onsite").next('label').text() ).draw();
            }else{
                table.columns(4).search("").draw();
            }
        }
    });
    $('#filter-name').bind('keyup', function(e) {
        table.columns(1).search(this.value).draw();
    });
});