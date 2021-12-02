<?php
/**
    INICIO DE CONTENIDO DE LA PAGINA
**/
function layoutInitContent(){
    global $msg_class;
?>          
    <!-- Page Content -->
    <div id="page-content-wrapper">
    <?php
    if (!isset($_SESSION['site_msg_t'])){ $_SESSION['site_msg_t'] = MSG_TYPE_NOMSG; }

    if ($_SESSION['site_msg_t'] > MSG_TYPE_NOMSG) { ?>
        <div id="alert-message" class="alert <?php echo $msg_class[$_SESSION['site_msg_t']]; ?>">
            <a href="#" class="close" data-dismiss="alert" aria-label="Cerrar">&times;</a>
        <?php echo $_SESSION['site_msg']; ?>
        </div>
        <?php
        $_SESSION['site_msg_t'] = MSG_TYPE_NOMSG;
        $_SESSION['site_msg'] = "";
    } ?>
    <?php
}

/**
    MAPA
**/
function layoutDrawMap(){
?>    
        <div id="onlinedrivers-window" class="info-window">
            <ul id="drivers-online"></ul>
        </div>
        <div id="truckinfo-window" class="info-window"></div>
        <div id="chatbox-window" class="chat-window">
            <div id="messages-div">
                <ul id="chat-messages"></ul>
            </div>
            <div class="form-group">
                <input id="chat_input" placeholder="Enviar mensaje" class="form-control input-sm" type="text">
            </div>
        </div>
    <div id='map'></div>
<?php
}

/**
    FIN CONTENIDO DE LA PAGINA
**/
function layoutEndContent(){    
?>
    </div>
    <!-- /#page-content-wrapper -->
<?php
}
?>