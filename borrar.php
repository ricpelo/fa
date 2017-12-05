<?php
require 'auxiliar.php';

cabecera('Borrar película');

if (!comprobarLogueado()) {
    return;
}

try {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? false;
    $error = [];
    comprobarParametro($id, $error);
    $pdo = conectar();
    $fila = buscarPelicula($pdo, $id, $error);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        borrarPelicula($pdo, $id, $error);
        comprobarErrores($error);
        $_SESSION['mensaje'] = 'Película eliminada correctamente.';
        header('Location: index.php');
        return;
    }
    formularioConfirmarBorrado($id, $fila['titulo']);
} catch (Exception $e) {
    $_SESSION['mensaje'] = $error[0];
    header('Location: index.php');
    return;
}

pie();
