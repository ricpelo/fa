<?php

/**
 * Crea una conexión a la base de datos y la devuelve.
 * @return PDO          La instancia de la clase PDO que representa la conexión
 * @throws PDOException Si se produce algún error que impide la conexión
 */
function conectar(): PDO
{
    try {
        return new PDO('pgsql:host=localhost;dbname=fa', 'fa', 'fa');
    } catch (PDOException $e) {
        ?>
        <h1>Error catastrófico de base de datos: no se puede continuar</h1>
        <?php
        throw $e;
    }
}

/**
 * Busca una película a partir de su ID.
 * @param  PDO       $pdo   La conexión a la base de datos
 * @param  int       $id    El ID de la película
 * @param  array     $error El array de errores
 * @return array            La fila que contiene los datos de la película
 * @throws Exception        Si la película no existe
 */
function buscarPelicula(PDO $pdo, int $id, array &$error): array
{
    $sent = $pdo->prepare('SELECT *
                             FROM peliculas
                            WHERE id = :id');
    $sent->execute([':id' => $id]);
    $fila = $sent->fetch();
    if (empty($fila)) {
        $error[] = 'La película no existe';
        throw new Exception;
    }
    return $fila;
}

/**
 * Borra una película a partir de su ID.
 * @param  PDO   $pdo   La conexión a la base de datos
 * @param  int   $id    El ID de la película
 * @param  array $error Los mensajes de error
 */
function borrarPelicula(PDO $pdo, int $id, array &$error): void
{
    $sent = $pdo->prepare("DELETE FROM peliculas
                                 WHERE id = :id");
    $sent->execute([':id' => $id]);
    if ($sent->rowCount() !== 1) {
        $error[] = 'Ha ocurrido un error al eliminar la película';
    }
}

/**
 * Comprueba si un parámetro es correcto.
 *
 * Un parámetro se considera correcto si ha superado los filtros de validación
 * de filter_input(). Si el parámetro no existe, entendemos que su valor
 * también es false, con lo cual sólo tenemos que comprobar si el valor no
 * es false.
 * @param  mixed     $param El parámetro a comprobar
 * @param  array     $error El array de errores
 * @throws Exception        Si el parámetro no es correcto
 */
function comprobarParametro($param, array &$error): void
{
    if ($param === false) {
        $error[] = 'Parámetro incorrecto';
        throw new Exception;
    }
}

/**
 * Muestra un enlace a la página principal (index.php) con el texto 'Volver'.
 */
function volver(): void
{
    ?>
    <a href="index.php">Volver</a>
    <?php
}

/**
 * Escapa una cadena correctamente.
 * @param  string $cadena La cadena a escapar
 * @return string         La cadena escapada
 */
function h(?string $cadena): string
{
    return htmlspecialchars($cadena, ENT_QUOTES | ENT_SUBSTITUTE);
}

/**
 * Muestra en pantalla los mensajes de error capturados
 * hasta el momento.
 * @param array $error Los mensajes capturados
 */
function mostrarErrores(array $error): void
{
    foreach ($error as $v) {
        ?>
        <div class="row">
            <div class="alert alert-danger alert-dismissible" role="alert">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <?= h($v) ?>
            </div>
        </div>
        <?php
    }
}

function comprobarTitulo(string $titulo, array &$error): void
{
    if ($titulo === '') {
        $error[] = "El título es obligatorio";
        return;
    }
    if (mb_strlen($titulo) > 255) {
        $error[] = "El título es demasiado largo";
    }
}

function comprobarAnyo(string $anyo, array &$error): void
{
    if ($anyo === '') {
        return;
    }
    $filtro = filter_var($anyo, FILTER_VALIDATE_INT, [
        'options' => [
            'min_range' => 0,
            'max_range' => 9999,
        ],
    ]);
    if ($filtro === false) {
        $error[] = 'No es un año válido';
    }
}

function comprobarDuracion(string $duracion, array &$error): void
{
    if ($duracion === '') {
        return;
    }
    $filtro = filter_var($duracion, FILTER_VALIDATE_INT, [
        'options' => [
            'min_range' => 0,
            'max_range' => 32767,
        ],
    ]);
    if ($filtro === false) {
        $error[] = 'No es una duración válida';
    }
}

function comprobarGenero(PDO $pdo, $genero_id, array &$error): void
{
    if ($genero_id === '') {
        $error[] = 'El género es obligatorio';
        return;
    }
    $filtro = filter_var($genero_id, FILTER_VALIDATE_INT);
    if ($filtro === false) {
        $error[] = 'El género debe ser un número entero';
        return;
    }
    $sent = $pdo->prepare('SELECT COUNT(*)
                             FROM generos
                            WHERE id = :genero_id');
    $sent->execute([':genero_id' => $genero_id]);
    if ($sent->fetchColumn() === 0) {
        $error[] = 'El género no existe';
    }
}

function comprobarErrores(array $error): void
{
    if (!empty($error)) {
        throw new Exception;
    }
}

function insertar(PDO $pdo, array $valores): void
{
    $cols = array_keys($valores);
    $vals = array_fill(0, count($valores), '?');
    $sql = 'INSERT INTO peliculas (' . implode(', ', $cols) . ')'
                        . 'VALUES (' . implode(', ', $vals) . ')';
    $sent = $pdo->prepare($sql);
    $sent->execute(array_values($valores));
}

function comp($valor)
{
    return $valor !== '';
}

function modificar(PDO $pdo, int $id, array $valores): void
{
    $sets = [];
    foreach ($valores as $k => $v) {
        $sets[] = $v === '' ? "$k = NULL" : "$k = ?";
    }
    $set = implode(', ', $sets);
    $sql = "UPDATE peliculas
               SET $set
             WHERE id = ?";
    $exec = array_values(array_filter($valores, 'comp'));
    $exec[] = $id;
    $sent = $pdo->prepare($sql);
    $sent->execute($exec);
}

function formulario(array $datos, ?int $id): void
{
    if ($id === null) {
        $destino = 'insertar.php';
        $boton = 'Insertar';
    } else {
        $destino = "modificar.php?id=$id";
        $boton = 'Modificar';
    }
    extract($datos);
    ?>
    <form action="<?= $destino ?>" method="post">
        <label for="titulo">Título*:</label>
        <input id="titulo" type="text" name="titulo"
            value="<?= h($titulo) ?>"><br>
        <label for="anyo">Año:</label>
        <input id="anyo" type="text" name="anyo"
            value="<?= h($anyo) ?>"><br>
        <label for="sinopsis">Sinopsis:</label>
        <textarea
            id="sinopsis"
            name="sinopsis"
            rows="8"
            cols="70"><?= h($sinopsis) ?></textarea><br>
        <label for="duracion">Duración:</label>
        <input id="duracion" type="text" name="duracion"
            value="<?= h($duracion) ?>"><br>
        <label for="genero_id">Género*:</label>
        <input id="genero_id" type="text" name="genero_id"
            value="<?= h($genero_id) ?>"><br>
        <input type="submit" value="<?= $boton ?>">
        <a href="index.php">Cancelar</a>
    </form>
    <?php
}

function recogerParametros()
{
    global $titulo, $anyo, $sinopsis, $duracion, $genero_id;

    $titulo    = trim(filter_input(INPUT_POST, 'titulo'));
    $anyo      = trim(filter_input(INPUT_POST, 'anyo'));
    $sinopsis  = trim(filter_input(INPUT_POST, 'sinopsis'));
    $duracion  = trim(filter_input(INPUT_POST, 'duracion'));
    $genero_id = trim(filter_input(INPUT_POST, 'genero_id'));
}

function comprobarUsuario(string $usuario, array &$error): void
{
    if ($usuario === '') {
        $error[] = 'El usuario es obligatorio';
        return;
    }
    if (mb_strlen($usuario) > 255) {
        $error[] = 'El usuario es demasiado largo';
    }
    if (mb_strpos($usuario, ' ') !== false) {
        $error[] = 'El usuario no puede contener espacios';
    }
}

function comprobarPassword(string $password, array &$error): void
{
    if ($password === '') {
        $error[] = 'La contraseña es obligatoria';
    }
}

function buscarUsuario(
    string $usuario,
    string $password,
    array &$error
): array
{
    $pdo = conectar();
    $sent = $pdo->prepare('SELECT *
                             FROM usuarios
                            WHERE usuario = :usuario');
    $sent->execute([':usuario' => $usuario]);
    $fila = $sent->fetch();
    if (empty($fila)) {
        $error[] = 'El usuario no existe';
        throw new Exception;
    }
    if (!password_verify($password, $fila['password'])) {
        $error[] = 'La contraseña no coincide';
        throw new Exception;
    }
    return $fila;
}

function comprobarLogueado(): bool
{
    if (!isset($_SESSION['usuario'])) {
        $_SESSION['mensaje'] = 'Usuario no identificado';
        header('Location: index.php');
        return false;
    }

    return true;
}
