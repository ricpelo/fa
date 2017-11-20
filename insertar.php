<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Insertar una nueva película</title>
    </head>
    <body>
        <?php
        $titulo = trim(filter_input(INPUT_POST, 'titulo')) ?? '';
        $anyo = trim(filter_input(INPUT_POST, 'anyo')) ?? '';
        $sinopsis = trim(filter_input(INPUT_POST, 'sinopsis')) ?? '';
        $duracion = trim(filter_input(INPUT_POST, 'duracion')) ?? '';
        $genero_id = trim(filter_input(INPUT_POST, 'genero_id')) ?? '';
        if (!empty($_POST)):
            try {
                $error = [];
                comprobarTitulo($titulo, $error);
                comprobarAnyo($anyo, $error);
                comprobarDuracion($duracion, $error);
                comprobarGenero($genero_id, $error);
                comprobarErrores($error);
            } catch (Exception $e) {
                mostrarErrores($e);
            }
        endif;
        ?>
        <form action="insertar.php" method="post">
            <label for="titulo">Título*:</label>
            <input id="titulo" type="text" name="titulo"
                value="<?= htmlspecialchars($titulo) ?>"><br>
            <label for="anyo">Año:</label>
            <input id="anyo" type="text" name="anyo"
                value="<?= htmlspecialchars($anyo) ?>"><br>
            <label for="sinopsis">Sinopsis:</label>
            <textarea
                id="sinopsis"
                name="sinopsis"
                rows="8"
                cols="70"><?= htmlspecialchars($sinopsis) ?></textarea><br>
            <label for="duracion">Duración:</label>
            <input id="duracion" type="text" name="duracion"
                value="<?= htmlspecialchars($duracion) ?>"><br>
            <label for="genero_id">Género*:</label>
            <input id="genero_id" type="text" name="genero_id"
                value="<?= htmlspecialchars($genero_id) ?>"><br>
            <input type="submit" value="Insertar">
            <input type="submit" value="Cancelar"
                formaction="index.php" formmethod="get">
        </form>
    </body>
</html>
