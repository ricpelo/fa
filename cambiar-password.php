<?php

require './auxiliar.php';

cabecera('Cambiar contraseña');

if (!comprobarLogueado()) {
    return;
}
$error = [];
if (!empty($_POST)):
    try {
        $usuario = obtenerParametro('usuario', []);
        comprobarPassword($usuario['password'], $error);
        comprobarPasswordConfirm($usuario['passwordConfirm'], $usuario['password'], $error);
        comprobarErrores($error);
        cambiarPassword($usuario['password']);
        $_SESSION['mensaje'] = 'Contraseña cambiada correctamente.';
        header('Location: index.php');
        return;
    } catch (Exception $e) {
        mostrarErrores($error);
    }
endif;

formularioRecordarPassword();

pie();
