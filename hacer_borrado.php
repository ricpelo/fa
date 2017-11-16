<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Borrar película</title>
    </head>
    <body>
        <?php
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?? false;
        try {
            if ($id === false) {
                throw new Exception('Parámetro incorrecto');
            }
            $pdo = new PDO('pgsql:host=localhost;dbname=fa', 'fa', 'fa');
            $query = $pdo->prepare("SELECT COUNT(*)
                                      FROM peliculas
                                     WHERE id = :id");
            $query->execute([':id' => $id]);
            if ($query->fetchColumn() === 0) {
                throw new Exception('La película no existe');
            }
            $query = $pdo->prepare("DELETE FROM peliculas
                                          WHERE id = :id");
            $query->execute([':id' => $id]);
            if ($query->rowCount() !== 1) {
                throw new Exception('Ha ocurrido un error al eliminar la película');
            }
            ?>
            <h3>Película eliminada correctamente.</h3>
            <a href="index.php">Volver</a>
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
