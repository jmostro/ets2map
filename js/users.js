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

$('#user-form-admin').validator().on('submit', function (e) {
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
            data: $('form#user-form-admin').serialize(),
            dataType: 'json',
            success: function(data) {
                if(data.success === false){
                    $.each(data.error, function(index, value) {
                        if (value.length != 0)
                        {
                            $.notify({
                                message: value
                            },{
                                type: 'danger'
                            });
                        }
                    });
                } else {
                    $.notify({
                        message: data.error
                    },{
                        type: 'success'
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

$('#user-form-profile').validator().on('submit', function (e) {
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
            data: $('form#user-form-profile').serialize(),
            dataType: 'json',
            success: function(data) {
                if(data.success === false){
                    $.each(data.error, function(index, value) {
                        if (value.length != 0)
                        {
                            $.notify({
                                message: value
                            },{
                                type: 'danger'
                            });
                        }
                    });
                } else {
                    $.notify({
                        message: data.error,
                    },{
                        type: 'success'
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

$('#user-form-option').validator().on('submit', function (e) {
    if (e.isDefaultPrevented()) {
        $.notify({
            message: 'Datos incorrectos.'
        },{
            type: 'danger',
        });
    } else {
        // everything looks good!
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: this.getAttribute('data-action'),
            cache: false,
            data: $('form#user-form-option').serialize(),
            dataType: 'json',
            success: function(data) {
                if(data.success === false){
                    $.each(data.error, function(index, value) {
                        if (value.length != 0)
                        {
                            $.notify({
                                message: value
                            },{
                                type: 'danger'
                            });
                        }
                    });
                } else {
                    $.notify({
                        message: data.error,
                    },{
                        type: 'success'
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

$(function() {
    $('#themeselected').on('change', function() {
        switch(this.value) {
            case "1":
                $('.theme').attr('href',document.mybaseurl+"/css/bs-sandstone.css");
                break;
            case "2":
                $('.theme').attr('href',document.mybaseurl+"/css/bs-cyborg.css");
                break;
            case "3":
                $('.theme').attr('href',document.mybaseurl+"/css/bs-slate.css");
                break;
            case "4":
                $('.theme').attr('href',document.mybaseurl+"/css/bs-united.css");
                break;
            case "5":
                $('.theme').attr('href',document.mybaseurl+"/css/bs-darkly.css");
                break;
            case "6":
                $('.theme').attr('href',document.mybaseurl+"/css/bs-cosmo.css");
                break;
        }
        $.notify({
            icon: 'fa fa-info-circle',
            message: 'Mostrando previsualización del tema, recordá guardar para mantener el tema del sitio.'
        },{
            type: 'info',
            allow_dismiss: true
        });
        return false;
    });
});