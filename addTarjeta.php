<?php
include 'funciones.php';

$error = false;
$config = include 'config.php';

try { //recupera los datos del usuario
    $dsn = 'mysql:host='.$config['host'].';dbname='.$config['name'];
    $conn = new PDO($dsn, $config['user'], $config['pass']);

    //codigo que obtendrá la lista de usuarios
    $consultaSQL2 = "SELECT * FROM Tarjeta";

    $sentencia2 = $conn->prepare($consultaSQL2);
    $sentencia2->execute();

    $tarjeta = $sentencia2->fetchall();

    echo($tarjeta[0]['Id']);

    $consultaSQL3 = "UPDATE `Usuario` SET `IdTarjeta`='" . $tarjeta[0]['Id'] . "' WHERE Email = '" . $_GET['id'] . "'";
    echo($consultaSQL3);
    $sentencia3 = $conn->prepare($consultaSQL3);
    $sentencia3->execute();

    header("Location: index.php");

} catch(PDOException $error) {
    $error = $error->getMessage();
    echo($error);
}

?>

<?php require "templates/footer.php";?>
