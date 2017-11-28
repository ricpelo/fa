<?php session_start() ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Borrar película</title>
    </head>
    <body>
        <?php
        require 'auxiliar.php';

        if (!comprobarLogueado()) {
            return;
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?? false;
        try {
            $error = [];
            comprobarParametro($id, $error);
            $pdo = conectar();
            buscarPelicula($pdo, $id, $error);
            borrarPelicula($pdo, $id, $error);
            comprobarErrores($error);
            $_SESSION['mensaje'] = 'Película eliminada correctamente.';
            header('Location: index.php');
        } catch (Exception $e) {
            mostrarErrores($error);
        }
        ?>
    </body>
</html>
