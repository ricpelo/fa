<?php session_start() ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Insertar una nueva película</title>
    </head>
    <body>
        <?php
        require 'auxiliar.php';

        if (!comprobarLogueado()) {
            return;
        }

        $titulo    = trim(filter_input(INPUT_POST, 'titulo'));
        $anyo      = trim(filter_input(INPUT_POST, 'anyo'));
        $sinopsis  = trim(filter_input(INPUT_POST, 'sinopsis'));
        $duracion  = trim(filter_input(INPUT_POST, 'duracion'));
        $genero_id = trim(filter_input(INPUT_POST, 'genero_id'));
        // recogerParametros();
        $error = [];
        if (!empty($_POST)):
            try {
                comprobarTitulo($titulo, $error);
                comprobarAnyo($anyo, $error);
                comprobarDuracion($duracion, $error);
                $pdo = conectar();
                comprobarGenero($pdo, $genero_id, $error);
                comprobarErrores($error);
                $valores = array_filter(compact(
                    'titulo',
                    'anyo',
                    'sinopsis',
                    'duracion',
                    'genero_id'
                ), 'comp');
                insertar($pdo, $valores);
                $_SESSION['mensaje'] = 'La película se ha insertado correctamente.';
                header('Location: index.php');
                return;
            } catch (Exception $e) {
                mostrarErrores($error);
            }
        endif;
        formulario(compact(
            'titulo',
            'anyo',
            'sinopsis',
            'duracion',
            'genero_id'
        ), null);
        ?>
    </body>
</html>
