<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Listado de películas</title>
    </head>
    <body>
        <?php
        require 'auxiliar.php';

        $pdo = conectar();
        $query = $pdo->query('SELECT * FROM peliculas');
        ?>
        <table border="1">
            <thead>
                <th>Id</th>
                <th>Título</th>
                <th>Año</th>
                <th>Sinopsis</th>
                <th>Duración</th>
                <th>Género</th>
                <th>Operaciones</th>
            </thead>
            <tbody>
                <?php foreach ($query as $fila): ?>
                    <tr>
                        <td><?= $fila['id'] ?></td>
                        <td><?= $fila['titulo'] ?></td>
                        <td><?= $fila['anyo'] ?></td>
                        <td><?= $fila['sinopsis'] ?></td>
                        <td><?= $fila['duracion'] ?></td>
                        <td><?= $fila['genero_id'] ?></td>
                        <td>
                            <a href="borrar.php?id=<?= $fila['id'] ?>">
                                Borrar
                            </a>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </body>
</html>
