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
 * @param  PDO       $pdo La conexión a la base de datos
 * @param  int       $id  El ID de la película
 * @return array          La fila que contiene los datos de la película
 * @throws Exception      Si la película no existe
 */
function buscarPelicula(PDO $pdo, int $id): array
{
    $sent = $pdo->prepare('SELECT *
                             FROM peliculas
                            WHERE id = :id');
    $sent->execute([':id' => $id]);
    $fila = $sent->fetch();
    if (empty($fila)) {
        throw new Exception('La película no existe');
    }
    return $fila;
}

/**
 * Borra una película a partir de su ID.
 * @param  PDO       $pdo La conexión a la base de datos
 * @param  int       $id  El ID de la película
 * @throws Exception      Si ha habido algún problema al borrar la película
 */
function borrarPelicula(PDO $pdo, int $id): void
{
    $sent = $pdo->prepare("DELETE FROM peliculas
                                 WHERE id = :id");
    $sent->execute([':id' => $id]);
    if ($sent->rowCount() !== 1) {
        throw new Exception('Ha ocurrido un error al eliminar la película');
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
 * @throws Exception        Si el parámetro no es correcto
 */
function comprobarParametro($param): void
{
    if ($param === false) {
        throw new Exception('Parámetro incorrecto');
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
 * Muestra en pantalla el mensaje asociado a la excepción capturada.
 * @param Exception $e La excepción capturada
 */
function mostrarErrores(Exception $e): void
{
    ?>
    <h3>Error: <?= $e->getMessage() ?></h3>
    <?php
    volver();
}
