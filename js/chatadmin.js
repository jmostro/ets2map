var table = $('#chats').DataTable({
    "ajax": "/admin/chatdata/data", 
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

    $('#clearchatdeleted').click(function() {
        $.ajax({
            url: "/admin/chatdata/cleardeleted",
            dataType: 'json',
            cache: false,
            beforeSend: function() {
                $('#chats').fadeOut(500, function() {
                   table.clear().draw();
                });   
                $('#clearchatdeleted #flushbutton').html('<i class="fa fa-cog fa-spin"></i> Eliminando chats borrados...');
            },
            success: function(data) {
                if(data.successful){
                    $.notify({
                        message: data.message
                    });
                    $("#chats").fadeIn(500, function(){
                        table.ajax.reload();
                    });
                    $('#clearchatdeleted #flushbutton').html('Eliminar chats borrados');
                }else{
                    $("#chats").fadeIn(500, function(){
                        table.ajax.reload();
                    });
                    $.notify({
                        message: data.message
                    },{
                        type: 'danger'
                    });
                    $('#clearchatdeleted #flushbutton').html('Eliminar chats borrados');
                }
            },
            error: function() {
                $.notify({
                    message: 'Ocurrió un error al realizar la petición.'
                },{
                    type: 'danger'
                });
            }
        });
    });
    $('#clearchat').click(function() {
        $.ajax({
            url: "/admin/chatdata/clear",
            dataType: 'json',
            cache: false,
            beforeSend: function() {
                $('#chats').fadeOut(500, function() {
                   table.clear().draw();
                });   
                $('#clearchat #flushbutton').html('<i class="fa fa-cog fa-spin"></i> Eliminando chats...');
            },
            success: function(data) {
                if(data.successful){
                    $.notify({
                        message: data.message
                    });
                    $("#chats").fadeIn(500, function(){
                        table.ajax.reload();
                    });
                    $('#clearchat #flushbutton').html('Eliminar chats');
                }else{
                    $("#chats").fadeIn(500, function(){
                        table.ajax.reload();
                    });
                    $.notify({
                        message: data.message
                    },{
                        type: 'danger'
                    });
                    $('#clearchat #flushbutton').html('Eliminar chats');
                }
            },
            error: function() {
                $.notify({
                    message: 'Ocurrió un error al realizar la petición.'
                },{
                    type: 'danger'
                });
                $('#clearchat #flushbutton').html('Error');
            }
        });
    });
});