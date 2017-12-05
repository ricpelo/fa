<?php
require 'auxiliar.php';

cabecera('Modificar una película');

if (!comprobarLogueado()) {
    return;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? false;
try {
    $error = [];
    comprobarParametro($id, $error);
    $pdo = conectar();
    $pelicula = buscarPelicula($pdo, $id, $error);
    comprobarErrores($error);
    if (!empty($_POST)):
        $pelicula = obtenerParametro('pelicula', PELICULA_DEFECTO);
        try {
            $error = [];
            comprobarTitulo($pelicula['titulo'], $error);
            comprobarAnyo($pelicula['anyo'], $error);
            comprobarDuracion($pelicula['duracion'], $error);
            comprobarGenero($pdo, $pelicula['genero_id'], $error);
            comprobarErrores($error);
            modificar($pdo, $id, $pelicula);
            $_SESSION['mensaje'] = 'La película se ha modificado correctamente.';
            header('Location: index.php');
            return;
        } catch (Exception $e) {
            mostrarErrores($error);
        }
    endif;
    formulario($pelicula, $id, $pdo);
} catch (Exception $e) {
    mostrarErrores($error);
}

pie();
