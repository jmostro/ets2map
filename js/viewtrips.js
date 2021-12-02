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

        $('#confirm-delete').on('show.bs.modal', function(e) {
            $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            
            $('.debug-url').html('Delete URL: <strong>' + $(this).find('.btn-ok').attr('href') + '</strong>');
        });


$("a#delete").on('click', function(e) {
    e.preventDefault();
    $('#confirmation').modal({ backdrop: 'static', keyboard: false })
        .on('click', '#deleteconfirm', function (e) {
            $.ajax({
                type: "POST",
                url: "/tripsdata",
                data: {
                        "a": "delete",
                        "id": $("a#delete").attr('data-tripid')
                    },
                cache: false,
                dataType: 'json',
                success: function(data) {
                    if(data.successful === true){
                        $.notify({
                            message: data.message
                        },{
                            type: 'success'
                        });
                        $("a#delete").text("Viaje borrado");
                        $("a#delete").addClass('disabled');
                    } else {
                        $.notify({
                            message: data.message
                        },{
                            type: 'danger'
                        });
                    }
                },
                error: function(xhr, textStatus, thrownError) {
                    $.notify({
                        message: 'Hubo un error, intente nuevamente.'
                    },{
                        type: 'danger'
                    });
                }
            });
        });
});

$("a#deleteadmin").on('click', function(e) {
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: "/tripsdata",
        data: {
                "a": "deleteadmin",
                "id": $("a#deleteadmin").attr('data-tripid'),
                "status": $("a#deleteadmin").attr('data-eliminated')
            },
        cache: false,
        dataType: 'json',
        success: function(data) {
            if(data.successful === true){
                if(data.deleted === 1){
                    $.notify({
                        message: data.message
                    },{
                        type: 'success'
                    });
                    $("a#deleteadmin").text("Recuperar viaje borrado");
                    $('a#deleteadmin').attr('data-eliminated', 1);
                    $('a#inranking').addClass('disabled');
                }else if(data.deleted === 2){
                    $.notify({
                        message: data.message
                    },{
                        type: 'success'
                    });
                    $("a#deleteadmin").text("Borrar viaje");
                    $('a#deleteadmin').attr('data-eliminated', 0);
                    $('a#inranking').removeClass('disabled');
                }else{
                    $.notify({
                        message: data.message
                    },{
                        type: 'danger'
                    });
                }
            } else {
                $.notify({
                    message: data.message
                },{
                    type: 'danger'
                });
            }
        },
        error: function(xhr, textStatus, thrownError) {
            $.notify({
                message: 'Hubo un error, intente nuevamente.'
            },{
                type: 'danger'
            });
        }
    });
});

$("a#inranking").on('click', function(e) {
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: "/tripsdata",
        data: {
                "a": "inranking",
                "id": $("a#deleteadmin").attr('data-tripid'),
                "status": $("a#inranking").attr('data-showed')
            },
        cache: false,
        dataType: 'json',
        success: function(data) {
            if(data.successful === true){
                if(data.ranking === 1){
                    $.notify({
                        message: data.message
                    },{
                        type: 'success'
                    });
                    $("a#inranking").text("Eliminar del ranking");
                    $('a#inranking').attr('data-showed', 1);
                }else if(data.ranking === 2){
                    $.notify({
                        message: data.message
                    },{
                        type: 'success'
                    });
                    $("a#inranking").text("Mostrar en el ranking");
                    $('a#inranking').attr('data-showed', 0);
                }else{
                    $.notify({
                        message: data.message
                    },{
                        type: 'danger'
                    });
                }
            } else {
                $.notify({
                    message: data.message
                },{
                    type: 'danger'
                });
            }
        },
        error: function(xhr, textStatus, thrownError) {
            $.notify({
                message: 'Hubo un error, intente nuevamente.'
            },{
                type: 'danger'
            });
        }
    });
});