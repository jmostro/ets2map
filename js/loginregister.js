$.backstretch(["../img/ets2_1.jpg", "../img/ats_1.jpg"], {duration: 3000, fade: 750});

$.notifyDefaults({
     newest_on_top: true,
        offset: {
            x: 0,
            y: 55
        },
        placement: {
            from: "top",
            align: "center"
        },
        allow_dismiss: false,
        animate:{
            enter: "animated fadeInDown",
            exit: "animated fadeOutUp"
        },
        delay: 3000
});

$('#login-form').validator().on('submit', function (e) {
    if (e.isDefaultPrevented()) {
        $.notify({
            message: 'Datos incorrectos.'
        },{
            type: 'danger'
        });
    } else {
        // everything looks good!
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: this.getAttribute('data-action'),
            cache: false,
            data: $('form#login-form').serialize(),
            dataType: 'json',
            success: function(data) {
                if(data.success === false){
                    $.notify({
                        message: data.error
                    },{
                        type: 'danger'
                    });
                } else {
                    if(!data.showagreed)
                        window.location.replace("/");
                    else
                        $('#modal-agree').modal('toggle');
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
        return false;
    }
});

$('#register').validator().on('submit', function (e) {
    if (e.isDefaultPrevented()) {
        $.notify({
            message: 'Datos incorrectos.'
        },{
            type: 'danger'
        });
    } else {
        // everything looks good!
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: this.getAttribute('data-action'),
            cache: false,
            data: $('form#register').serialize(),
            dataType: 'json',
            success: function(data) {
                if(data.success === false){
                    $.each(data.error, function(index, value) {
                        if (value.length !== 0)
                        {
                            $.notify({
                                message: value
                            },{
                                type: 'danger'
                            });
                        }
                    });
                } else {
                    if(!data.showagreed)
                        window.location.replace("/");
                    else
                        $('#modal-agree').modal({
                            backdrop: 'static',
                            keyboard: false
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
        return false;
    }
});

$('#acepto-terminos').click(function() {
   	$.ajax({
        type: "POST",
        url: this.getAttribute('data-action'),
        dataType: 'json',
        cache: false,
        success: function(data) {
            if(data.status === true)
                window.location.replace("/");
            else
                $.notify({
                    message: 'Hubo un error, intente nuevamente.'
                },{
                    type: 'danger'
                });
        },
        error: function() { 
            $.notify({
                message: 'Hubo un error al momento de aceptar los términos.'
            },{
                type: 'danger'
            });
        }
    });
});