<?php
require 'auxiliar.php';
cabecera('Insertar una película');

if (!comprobarLogueado()) {
    return;
}

$pelicula = obtenerParametro('pelicula', PELICULA_DEFECTO);
$error = [];
$pdo = conectar();
if (!empty($_POST)):
    try {
        comprobarTitulo($pelicula['titulo'], $error);
        comprobarAnyo($pelicula['anyo'], $error);
        comprobarDuracion($pelicula['duracion'], $error);
        comprobarGenero($pdo, $pelicula['genero_id'], $error);
        comprobarErrores($error);
        $valores = array_filter($pelicula, 'comp');
        insertar($pdo, $valores);
        $_SESSION['mensaje'] = 'La película se ha insertado correctamente.';
        header('Location: index.php');
        return;
    } catch (Exception $e) {
        mostrarErrores($error);
    }
endif;
formulario($pelicula, null, $pdo);
pie();
