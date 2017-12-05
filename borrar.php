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
    ?>
    <div class="row">
        <div class="col-md-offset-3 col-md-6">
            <div class="panel panel-danger">
                <div class="panel-heading">
                    ¿Borrar la película <?= $fila['titulo'] ?>?
                </div>
                <div class="panel-body">
                    <form action="borrar.php?id=<?= $id ?>" method="post">
                        <input class="btn btn-success" type="submit" value="Sí">
                        <a class="btn btn-default" href="index.php">No</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php
} catch (Exception $e) {
    $_SESSION['mensaje'] = $error[0];
    header('Location: index.php');
    return;
}

pie();
