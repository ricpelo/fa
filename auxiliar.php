<?php

const PELICULA_DEFECTO = [
    'titulo' => '',
    'anyo' => '',
    'sinopsis' => '',
    'duracion' => '',
    'genero_id' => '',
];

define('FPP', 4);

function obtenerParametro(string $parametro, array $defecto): array
{
    $ret = filter_input(
        INPUT_POST,
        $parametro,
        FILTER_DEFAULT,
        FILTER_REQUIRE_ARRAY
    ) ?? [];
    $ret = array_map('trim', $ret);
    $ret = array_merge($defecto, $ret);
    $ret = array_intersect_key($ret, $defecto);
    return $ret;
}

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

function formulario(array $datos, ?int $id, PDO $pdo): void
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
    <div class="row">
        <div class="col-md-offset-2 col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading"><?= $boton ?> una película</div>
                <div class="panel-body">
                    <form action="<?= $destino ?>" method="post">
                        <div class="form-group">
                            <label for="titulo">Título*</label>
                            <input id="titulo" class="form-control"
                                type="text" name="pelicula[titulo]"
                                value="<?= h($titulo) ?>">
                        </div>
                        <div class="form-group">
                            <label for="anyo">Año:</label>
                            <input id="anyo" class="form-control"
                                type="text" name="pelicula[anyo]"
                                value="<?= h($anyo) ?>">
                        </div>
                        <div class="form-group">
                            <label for="sinopsis">Sinopsis:</label>
                            <textarea
                                id="sinopsis"
                                class="form-control"
                                name="pelicula[sinopsis]"
                                rows="8"
                                cols="70"><?= h($sinopsis) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="duracion">Duración:</label>
                            <input id="duracion" class="form-control"
                                type="text" name="pelicula[duracion]"
                                value="<?= h($duracion) ?>">
                        </div>
                        <div class="form-group">
                            <label for="genero_id">Género*</label>
                            <select class="form-control" name="pelicula[genero_id]">
                                <?php generoOptions($genero_id, $pdo) ?>
                            </select>
                        </div>
                        <input type="submit" class="btn btn-success" value="<?= $boton ?>">
                        <a href="index.php" class="btn btn-default">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function generoOptions($genero_id, $pdo)
{
    $sent = $pdo->query('SELECT id, genero FROM generos');
    $res = '';
    foreach ($sent as $fila):
        ?>
        <option <?= selected($fila['id'], $genero_id) ?>
            value="<?= $fila['id'] ?>">
            <?= $fila['genero'] ?>
        </option>
        <?php
    endforeach;
}

function selected($x, $y)
{
    return $x == $y ? 'selected' : '';
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

function cabecera($title = '')
{
    session_start();
    ?>
    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
            <style type="text/css">
                .container {
                    margin-top: 24px;
                }
                #buscar {
                    margin-bottom: 12px;
                }
            </style>
            <title><?= $title ?></title>
        </head>
        <body>
            <div class="container">
                <div class="row">
                    <div class="pull-right">
                        <?php if (isset($_SESSION['usuario'])): ?>
                            <?= $_SESSION['usuario']['nombre'] ?>
                            <a class="btn btn-info" href="usuarios/cambiar-password.php">Cambiar contraseña</a>
                            <a class="btn btn-info" href="usuarios/logout.php">Logout</a>
                        <?php else: ?>
                            <a class="btn btn-info" href="usuarios/login.php">Login</a>
                        <?php endif ?>
                    </div>
                </div>
                <div class="row"><hr></div>
                <?php if (isset($_SESSION['mensaje'])): ?>
                    <div class="row">
                        <div class="alert alert-success alert-dismissible" role="alert">
                          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                          <?= $_SESSION['mensaje'] ?>
                        </div>
                    </div>
                    <?php unset($_SESSION['mensaje']) ?>
                <?php endif ?>
    <?php
}

function pie()
{
    ?>
            </div>
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        </body>
    </html>
    <?php
}

function comprobarPasswordConfirm(string $passwordConfirm, string $password, array &$error): void
{
    if ($passwordConfirm === '') {
        $error[] = 'La confirmación de contraseña no puede ser vacía.';
        return;
    }
    if ($passwordConfirm !== $password) {
        $error[] = 'Las contraseñas no coinciden.';
    }
}

function cambiarPassword($password)
{
    $pdo = conectar();
    $sent = $pdo->prepare('UPDATE usuarios
                              SET password = :password
                            WHERE id = :id');
    $sent->execute([
        ':password' => password_hash($password, PASSWORD_DEFAULT),
        ':id' => $_SESSION['usuario']['id'],
    ]);
}

function formularioRecordarPassword()
{
    ?>
    <div class="row">
        <div class="col-md-offset-4 col-md-4">
            <form method="post">
                <div class="form-group">
                    <label for="password">Contraseña*</label>
                    <input id="password" class="form-control"
                        type="password" name="usuario[password]">
                </div>
                <div class="form-group">
                    <label for="passwordConfirm">Confirmar contraseña*</label>
                    <input id="passwordConfirm" class="form-control"
                        type="password" name="usuario[passwordConfirm]">
                </div>
                <input type="submit" class="btn btn-success" value="Cambiar">
                <a class="btn btn-default" href="../index.php">Volver</a>
            </form>
        </div>
    </div>
    <?php
}

function formularioConfirmarBorrado($id, $titulo)
{
    ?>
    <div class="row">
        <div class="col-md-offset-3 col-md-6">
            <div class="panel panel-danger">
                <div class="panel-heading">
                    ¿Borrar la película <?= $titulo ?>?
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
}

function paginador($pag, $numPags, $titulo)
{
    ?>
    <div class="row">
        <div class="text-center">
            <ul class="pagination">
                <?php if ($pag > 1):
                    $p = $pag - 1;
                    $url = "index.php?pag=$p&titulo=$titulo";
                    ?>
                    <li>
                        <a href="<?= $url ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="disabled">
                        <span aria-hidden="true">&laquo;</span>
                    </li>
                <?php endif ?>
                <?php
                for ($p = 1; $p <= $numPags; $p++):
                    $url = "index.php?pag=$p&titulo=$titulo";
                    ?>
                    <li <?= $pag == $p ? 'class="active"' : '' ?> >
                        <a href="<?= $url ?>"><?= $p ?></a>
                    </li>
                    <?php
                endfor;
                ?>
                <?php if ($pag < $numPags):
                    $p = $pag + 1;
                    $url = "index.php?pag=$p&titulo=$titulo";
                    ?>
                    <li>
                        <a href="<?= $url ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="disabled">
                        <span aria-hidden="true">&raquo;</span>
                    </li>
                <?php endif ?>
            </ul>
        </div>
    </div>
    <?php
}
