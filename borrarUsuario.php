<?php

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

if($_SESSION['Rol'] != "Admin"){
    header("Location: login.php");
    exit;    
}

include 'funciones.php';

$error = false;
$config = include 'config.php';

try { 
    
    //recupera los datos del usuario
    $dsn = 'mysql:host='.$config['host'].';dbname='.$config['name'];
    $conn = new PDO($dsn, $config['user'], $config['pass']);

    $consultaSQL3 = "DELETE FROM `Usuario` WHERE Email = '" . $_GET['id'] ."'";
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
                    <?= $resultado['mensaje'] ?>
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
                    El usuario ha sido eliminado correctamente
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
