var table = $('#log').DataTable({
    "ajax": "/admin/logdata/data", 
    "ordering": false,
    "info":     false,
    "sDom": 't<"container-fluid text-center"<p>>',
    "responsive": true,
    "pagingType": "full_numbers",
    "pageLength": 20,
    "language": {
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

$.notifyDefaults({
     newest_on_top: true,
        type: 'success',
        offset: {
            x: 0,
            y: 55
        },
        placement: {
            from: "top",
            align: "right"
        },
        allow_dismiss: false,
        animate:{
            enter: "animated fadeInRight",
            exit: "animated fadeOutRight"
        },
        delay: 3000
});

jQuery(function(){
    table.draw();
    $("#table-content").fadeIn(500);

    $('#clearlog').click(function() {
        $.ajax({
            url: "/admin/logdata/clear",
            dataType: 'json',
            cache: false,
            beforeSend: function() {
                $('#log').fadeOut(500, function() {
                   table.clear().draw();
                });   
                $('#flushbutton').html('<i class="fa fa-cog fa-spin"></i> Eliminando log...');
            },
            success: function(data) {
                if(data.successful){
                    $.notify({
                        message: data.message
                    });
                    $("#log").fadeIn(500, function(){
                        table.ajax.reload();
                    });
                    $('#flushbutton').html('Vaciar log');
                }else{
                    $('#flushbutton').html('Error');
                    $("#log").fadeIn(500, function(){
                        table.ajax.reload();
                    });
                    $.notify({
                        message: data.message
                    },{
                        type: 'danger'
                    });
                }
            },
            error: function() {
                $.notify({
                    message: 'Ocurrió un error.'
                },{
                    type: 'danger'
                });
            }
        });
    });
});