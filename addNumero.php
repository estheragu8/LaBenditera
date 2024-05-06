<?php
include 'funciones.php';

$error = null;
$config = include 'config.php';

try { //recupera los datos del usuario
    $dsn = 'mysql:host='.$config['host'].';dbname='.$config['name'];
    $conn = new PDO($dsn, $config['user'], $config['pass']);

    //codigo que obtendrá la lista de usuarios
    $consultaSQL2 = "SELECT * FROM Tarjeta";

    $sentencia2 = $conn->prepare($consultaSQL2);
    $sentencia2->execute();

    $tarjeta = $sentencia2->fetchall();

    $consultaSQL3 = "UPDATE `Usuario` SET `IdTarjeta`='" . $tarjeta[0]['Id'] . "' WHERE Email = '" . $_GET['id'] . "'";
    $sentencia3 = $conn->prepare($consultaSQL3);
    $sentencia3->execute();

} catch(PDOException $error) {
    $error = $error->getMessage();
}

?>

<?php require "templates/header.php"; ?>

<?php
if ($error) {
    ?>
    <div class="container mt-2">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger" role="alert">
                    Recuerde pasar la tarjeta por el lector antes de clicar en el botón de añadir tarjeta. La tarjeta que está intentando añadir (<?php echo($tarjeta[0]['Id']); ?>) ya está asignada a otro usuario.
                </div>
            </div>
        </div>
    </div>
<?php
}
?>

<?php
if (!$error) {
    ?>
    <div class="container mt-2">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success" role="alert">
                    La tarjeta ha sido añadida correctamente.
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>

<?php
    ?>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <hr>
                <form method="post">
                    <div class="form-group">
                        <input type="submit" name="submit" class="btn btn-primary" value="Actualizar">
                        <a class="btn btn-primary" href="index.php">Regresar al inicio</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
?>
