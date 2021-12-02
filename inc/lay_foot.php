<?php
/** 
    FIN CONTENEDOR PRINCIPAL
**/
function layoutEndWrapper(){    
?>   
    </div>
    <!-- /#wrapper -->
<?php
}

/**
    AGREGAR SCRIPTS AL PIE DE PAGINA
**/
function layoutAddDefaultScript($filesToLoad = array()){

?>
<!-- Bootstrap Core JavaScript -->
<script src="<?php echo SITE_URL; ?>/js/bootstrap.min.js"></script>   
<!-- Helper para actualizar menus -->
<script src="<?php echo SITE_URL; ?>/js/menu.js"></script>
<!-- Notify -->
<script src="<?php echo SITE_URL; ?>/js/bootstrap-notify.min.js"></script>
<?php
    if(!empty($filesToLoad))
        foreach ($filesToLoad as $file)
            echo "<script src='".SITE_URL."/js/$file'></script>\n";
?>
<script>
$('#convoystatus').click(function() {
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
   $.ajax({
        type: "POST",
        url: "<?php echo SITE_URL; ?>" + "/admin/convoy/" + this.getAttribute('data-userid'),
        dataType: 'json',
        cache: false,
        success: function(data) {
            if(!data.error){
                if(data.status === 0){
                    $.notify({
                        message: 'Se desactivó el modo convoy.'
                    });
                    $("#convoystatus").text('Activar modo convoy');
                }else{
                    $.notify({
                        message: 'Modo convoy activado.'
                    });
                    $("#convoystatus").text('Desactivar modo convoy');
                }
            }else{
                $.notify({
                    message: 'Hubo un error. No se pudo modificar el estado.'
                },{
                    type: 'danger',
                });
            }
        },
        error: function() { 
            $.notify({
                message: 'Hubo un error. No se pudo modificar el estado.'
            },{
                type: 'danger',
            });
        }
    });
});

jQuery(function(){
    $("#alert-message").fadeTo(3000, 500).slideUp(500, function(){
           $("#salert-message").alert('close');
    });
});
</script>
<?php
}

/**
    FIN DEL CUERPO
**/
function layoutEndBody(){
?>
    </body>
</html>
<?php
}
?>