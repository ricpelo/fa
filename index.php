<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <style type="text/css">
            #buscar {
                margin-bottom: 12px;
            }
            #tabla {
                margin: auto;
            }
        </style>
        <title>Listado de películas</title>
    </head>
    <body>
        <?php
        $titulo = trim(filter_input(INPUT_GET, 'titulo')) ?? '';
        ?>
        <div id="buscar">
            <form action="index.php" method="get">
                <fieldset>
                    <legend>Buscar</legend>
                    <label for="titulo">Título:</label>
                    <input id="titulo" type="text" name="titulo"
                           value="<?= $titulo ?>" />
                    <input type="submit" value="Buscar" />
                </fieldset>
            </form>
        </div>
        <?php
        require 'auxiliar.php';

        $pdo = conectar();
        $sent = $pdo->prepare("SELECT *
                                 FROM peliculas
                                WHERE lower(titulo) LIKE lower('%' || :titulo || '%')");
        $sent->execute([':titulo' => $titulo]);
        ?>
        <div>
            <table border="1" id="tabla">
                <thead>
                    <th>Título</th>
                    <th>Año</th>
                    <th>Sinopsis</th>
                    <th>Duración</th>
                    <th>Género</th>
                    <th>Operaciones</th>
                </thead>
                <tbody>
                    <?php foreach ($sent as $fila): ?>
                        <tr>
                            <td><?= $fila['titulo'] ?></td>
                            <td><?= $fila['anyo'] ?></td>
                            <td><?= $fila['sinopsis'] ?></td>
                            <td><?= $fila['duracion'] ?></td>
                            <td><?= $fila['genero_id'] ?></td>
                            <td>
                                <a href="borrar.php?id=<?= $fila['id'] ?>"
                                    Borrar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <a href="insertar.php">Insertar una nueva película</a>
    </body>
</html>
