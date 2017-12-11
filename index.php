<?php

require 'auxiliar.php';

cabecera('Listado de películas');

$titulo = trim(filter_input(INPUT_GET, 'titulo'));
?>
<div class="row">
    <div class="col-md-offset-2 col-md-8">
        <div class="panel panel-default">
            <div class="panel-heading">Buscar</div>
            <div class="panel-body">
                <form action="index.php" method="get" class="form-inline">
                    <div class="form-group">
                        <label for="titulo">Título</label>
                        <input id="titulo" class="form-control" type="text" name="titulo"
                               value="<?= h($titulo) ?>">
                    </div>
                    <input type="submit" class="btn btn-default" value="Buscar">
                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <?php
    $pdo = conectar();
    $clausulas = "FROM peliculas
                  JOIN generos ON genero_id = generos.id
                 WHERE lower(titulo) LIKE lower('%' || :titulo || '%')";
    $sent = $pdo->prepare("SELECT count(*)
                                  $clausulas");
    $sent->execute([':titulo' => $titulo]);
    $numFilas = $sent->fetchColumn();
    $numPags = ceil($numFilas / FPP);
    $pag = filter_input(INPUT_GET, 'pag', FILTER_VALIDATE_INT, [
        'options' => [
            'default' => 1,
            'min_range' => 1,
            'max_range' => $numPags,
        ],
    ]);
    $sent = $pdo->prepare("SELECT peliculas.id,
                                  titulo,
                                  anyo,
                                  left(sinopsis, 40) AS sinopsis,
                                  duracion,
                                  genero_id,
                                  genero
                                  $clausulas
                         ORDER BY id
                            LIMIT :limit
                           OFFSET :offset");
    $sent->execute([
        ':titulo' => $titulo,
        ':limit' => FPP,
        ':offset' => ($pag - 1) * FPP,
    ]);
    ?>
    <div class="col-md-offset-1 col-md-10">
        <table id="tabla" class="table table-striped">
            <thead>
                <th>Id</th>
                <th>Título</th>
                <th>Año</th>
                <th>Sinopsis</th>
                <th>Duración</th>
                <th>Género</th>
                <th colspan="2">Operaciones</th>
            </thead>
            <tbody>
                <?php foreach ($sent as $fila): ?>
                    <tr>
                        <td><?= h($fila['id']) ?></td>
                        <td><?= h($fila['titulo']) ?></td>
                        <td><?= h($fila['anyo']) ?></td>
                        <td><?= h($fila['sinopsis']) ?></td>
                        <td><?= h($fila['duracion']) ?></td>
                        <td><?= h($fila['genero']) ?></td>
                        <td>
                            <a class="btn btn-info btn-xs" href="modificar.php?id=<?= h($fila['id']) ?>">
                                Modificar
                            </a>
                        </td>
                        <td>
                            <a class="btn btn-danger btn-xs" href="borrar.php?id=<?= h($fila['id']) ?>">
                                Borrar
                            </a>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<?php paginador($pag, $numPags, $titulo) ?>
<div class="row">
    <div class="text-center">
        <a class="btn btn-default" href="insertar.php">Insertar una nueva película</a>
    </div>
</div>
<?php
pie();
?>
