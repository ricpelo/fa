<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Confirmación de borrado</title>
    </head>
    <body>
        <?php
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? false;
        try {
            if ($id === false) {
                throw new Exception('Parámetro incorrecto');
            }
            $pdo = new PDO('pgsql:host=localhost;dbname=fa', 'fa', 'fa');
            $query = $pdo->query("SELECT *
                                    FROM peliculas
                                   WHERE id = $id");
            $fila = $query->fetch();
            if (empty($fila)) {
                throw new Exception('La película no existe');
            }
            ?>
            <h3>
                ¿Seguro que desea borrar la película <?= $fila['titulo'] ?>?
            </h3>
            <form action="hacer_borrado.php" method="post">
                <input type="hidden" name="id" value="<?= $id ?>" />
                <input type="submit" value="Sí" />
                <input type="submit" value="No"
                       formaction="index.php" formmethod="get" />
            </form>
            <?php
        } catch (Exception $e) {
            ?>
            <h3>Error: <?= $e->getMessage() ?></h3>
            <a href="index.php">Volver</a>
            <?php
        }
        ?>
    </body>
</html>
