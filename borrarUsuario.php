<?php
include 'funciones.php';

$error = false;
$config = include 'config.php';

try { 
    
    echo("HOLAAAAAAAAAAAAAA");
    //recupera los datos del usuario
    $dsn = 'mysql:host='.$config['host'].';dbname='.$config['name'];
    $conn = new PDO($dsn, $config['user'], $config['pass']);

    echo($_GET['id']);

    $consultaSQL3 = "DELETE FROM `Usuario` WHERE Email = '" . $_GET['id'] ."'";
    echo($consultaSQL3);
    $sentencia3 = $conn->prepare($consultaSQL3);
    $sentencia3->execute();

    header("Location: index.php");

} catch(PDOException $error) {
    $error = $error->getMessage();
    echo($error);
}
