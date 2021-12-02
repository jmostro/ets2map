<?php
/**
    FORMULARIO DE INICIO DE SESION
**/
function layoutLoginForm(){
?>
    <div class="container">
        <div class="col-md-6 col-md-offset-3">
            <div class="form-background">
                <div class="text-center">
                    <legend>Acceder</legend>
                </div>
                <div class="col-md-12">
                    <form id="login-form" data-action="<?php echo SITE_URL; ?>/doLogin" role="form">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                <input id="drivername" name="drivername" placeholder="Usuario" class="form-control" required data-minlength="<?php echo MIN_USERNAME_LENGTH; ?>" type="text">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                <input id="password" name="password" placeholder="Contrase&ntilde;a" class="form-control" required data-minlength="<?php echo MIN_PASSWORD_LENGTH; ?>" type="password">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group-md" style="text-align:right;">
                                <div class="input-append">
                                  <span>No cerrar sesi&oacute;n&nbsp;</span>
                                  <span class="add-on">
                                    <input id="rememberme" name="rememberme" value=1 type="checkbox">
                                  </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <button type="submit" id="loginbutton" name="loginbutton" class="btn btn-md btn-primary">Entrar</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-12">
                    <form id='login-steam' method="POST" action="<?php echo SITE_URL; ?>/login?steamLogin" role="form">
                        <div class="form-group">
                            <div class="input-group">
                                <p>Tambi&eacute;n pod&eacute;s ingresar con tu usuario de Steam. Si no eres conductor de la empresa, pod&eacute;s usar el bot&oacute;n para postularte como recluta.</p>
                                <input style="margin: 0px auto;display:block;" text-align="center" type="image" src="img/steam_large.png">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="sponsor">
        <a href="http://www.digitalwebs.com.ar" target="_blank"><img src="img/pbDigitalWebsSM.png"></a>
    </div>
<?php
    showModalTerms();
}
/**
    FORMULARIO DE REGISTRO DE POSTULANTE
**/
function layoutRecruitForm($steam_id=""){
?>
    <div class="container">
        <div class="col-md-6 col-md-offset-3">
            <div class="form-background">
                <div class="text-center">
                    <legend>Registrarse como recluta</legend>
                </div>
                <div class="col-md-12">
                    <form id="register" data-action="<?php echo SITE_URL; ?>/doRegister" role="form">
                        <div class="form-group">
                            <label for="inputName" class="control-label">Nombre y Apellido</label>
                            <input id="driverfullname" name="driverfullname" placeholder="Nombre y Apellido" class="form-control" required type="text" maxlength="100">
                        </div>
                        <div class="form-group">
                            <label for="inputFb" class="control-label">Facebook</label>
                            <input id="driverfb" name="driverfb" placeholder="Link al perfil de facebook" class="form-control" type="url" maxlength="75">
                        </div>
                        <div class="form-group">
                            <label for="inputEmail" class="control-label">E-mail</label>
                            <input id="driveremail" name="driveremail" placeholder="E-mail" class="form-control" type="email" maxlength="100" data-error="Ese email no es v&aacute;lido">
                            <div class="help-block with-errors"></div>
                        </div>
                        <div class="form-group">
                            <label for="inputName" class="control-label">Nombre de usuario</label>
                            <input id="drivername" name="drivername" placeholder="Nombre de usuario" class="form-control" required data-minlength="<?php echo MIN_USERNAME_LENGTH; ?>" type="text" maxlength="30" data-error="M&iacute;nimo <?php echo MIN_USERNAME_LENGTH; ?> caracteres">
                            <div class="help-block with-errors"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="inputPassword" data-toggle="validator" class="inputPassword-label">Contrase&ntilde;a</label>
                                <input id="inputPassword" name="inputPassword" placeholder="Contrase&ntilde;a" class="form-control" required data-minlength="<?php echo MIN_PASSWORD_LENGTH; ?>" type="password" maxlength="32" data-error="M&iacute;nimo <?php echo MIN_USERNAME_LENGTH; ?> caracteres" >
                                <div class="help-block with-errors"></div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="inputPasswordConfirm" class="control-label">Repetir contrase&ntilde;a</label>
                                <input id="inputPasswordConfirm" name="inputPasswordConfirm" placeholder="Repetir contrase&ntilde;a" class="form-control" required type="password" maxlength="32" data-match="#inputPassword" data-match-error="Las contrase&ntildes;as no coinciden!">
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="control-label">Fecha de nacimiento</label>
                                <div class="input-group date" id="datetimepicker">
                                    <input type="text" class="form-control" readonly name="driverage"/>
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="inputCountry" class="control-label">Pa&iacute;s</label>
                                <select class="form-control" name="drivercountry" required>
                                    <option value="Argentina">Argentina</option>
                                    <option value="Brasil">Brasil</option>
                                    <option value="Canadá">Canadá</option>
                                    <option value="Chile">Chile</option>
                                    <option value="Colombia">Colombia</option>
                                    <option value="Costa Rica">Costa Rica</option>
                                    <option value="Cuba">Cuba</option>
                                    <option value="Dominica">Dominica</option>
                                    <option value="Ecuador">Ecuador</option>
                                    <option value="El Salvador">El Salvador</option>
                                    <option value="España">España</option>
                                    <option value="Estados Unidos">Estados Unidos</option>
                                    <option value="Francia">Francia</option>
                                    <option value="Guatemala">Guatemala</option>
                                    <option value="México">México</option>
                                    <option value="Nicaragua">Nicaragua</option>
                                    <option value="Panamá">Panamá</option>
                                    <option value="Paraguay">Paraguay</option>
                                    <option value="Perú">Perú</option>
                                    <option value="Portugal">Portugal</option>
                                    <option value="Puerto Rico">Puerto Rico</option>
                                    <option value="República Dominicana">República Dominicana</option>
                                    <option value="Uruguay">Uruguay</option>
                                    <option value="Venezuela">Venezuela</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputVTC" class="control-label">Perteneces a otra empresa virtual? (Si o No)</label>
                            <input id="drivervtc" name="drivervtc" placeholder="Si la respuesta es Si, ind&iacute;canos cual. Sino, responde No." class="form-control" required type="text" maxlength="20">
                        </div>
                        <div class="form-group">
                            <label for="inputWhy" class="control-label">Cu&eacute;ntanos en breves palabras por qu&eacute; quieres ser parte de L-LATAM </label>
                            <textarea id="driverwhy" name="driverwhy" class="form-control" style="resize:vertical;" required maxlength="250"></textarea>
                        </div>
                        <input type="text" hidden="true" id="driversteamid" name="driversteamid" value="<?php echo $steam_id; ?>">
                        <button type="submit" id="registerbutton" name="registerbutton" class="btn btn-info btn-block">Registrarse</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="sponsor"><a href="http://www.digitalwebs.com.ar" target="_blank"><img src="img/pbDigitalWebsSM.png"></a></div>
<?php
    showModalTerms(true);
}

function showModalTerms($recluta = false){
    ?>
    <div class="modal fade" id="modal-agree" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" >
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">Aviso para todos los conductores</div>
                <div class="modal-body">
                    <p>Compa&ntilde;eros, les comunicamos que por decision de la direcci&oacute;n de la empresa y con el fin de evitar competencias malsanas e injustas, asi como tambi&eacute;n en pos de favorecer el compa&ntilde;erismo compartimos desde un principio, hemos decidido que:</p>
                    <ul>
                        <li>
                            <p>El ranking de viajes que se encuentra en <a href="http://map.logisticalatinoamericana.com/ranking/view">http://map.logisticalatinoamericana.com/ranking/view</a> solo contendr&aacute; estad&iacute;sticas del mes en curso. <b>Por lo que al principio de cada mes, todos los conductores estaran en cero en los rankings.</b></p>
                        </li>
                        <li>                    
                            <p>A los conductores que pudiendo jugar en compa&ntilde;&iacute;a de otros miembros de la empresa decidan jugar solos para poder mejorar sus estad&iacute;sticas y demuestren ser reincidentes en este comportamiento, <b>se les advierte el staff de la empresa no está a favor de este modo de juego.</b> Es por esto que apelamos a vuestro sentido com&uacute;n para que sigamos siendo una empresa de compa&ntilde;eros y no de competidores. </p>                        
                        </li>
                        <li>
                            <p>Los viajes que tengan valores inadecuados ya sea por el uso de mods, o por el abuso de las caracter&iacute;sticas del sistema, ser&aacute;n eliminados por el staff de Log&iacute;stica Latinoamericana sin previo aviso</p><p>En caso de creer que un viaje no debe ser borrado, comunicarlo al staff con pruebas fehacientes y n&uacute;mero de ID de viaje (visible en la barra de direcciones)</p>
                        </li>
                        <li>
                            <p>La reiteraci&oacute;n y/o alevos&iacute;a de los comportamientos mencionados arriba puede ser motivo de deshabilitar los registros de viaje del conductor</p>
                        </li>
                    </ul>
                    <p>Esperamos que comprendan el motivo de esta decisión y nos hagan llegar por v&iacute;a privada cualquier inquietud que tengan al respecto.</p>
                </div>
                <div class="modal-footer">
                    <a id="acepto-terminos" href="javascript:void(0)" data-action="<?php echo SITE_URL; ?>/agree"><button class="btn btn-success">Acepto</button></a>
                    <?php if(!$recluta) echo '<a data-dismiss="modal"><button class="btn btn-danger">No acepto</button></a>'; ?>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>