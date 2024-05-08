<?php
session_start();
$config = include 'config.php';

$dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['name'];
$conn = new PDO($dsn, $config['user'], $config['pass']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    echo(password_hash('Benditera', PASSWORD_DEFAULT));

    $sql = "SELECT Email, Password, Rol FROM Usuario WHERE Email = '$username'";
    $sentencia2 = $conn->prepare($sql);
    $sentencia2->execute();

    $result = $sentencia2->fetchall();
    echo($result[0]["Email"]);
    
    if ($result[0]["Email"] != null) {
        // Verificar la contraseña encriptada
        if (password_verify($password, $result[0]['Password'])) {
            // Inicio de sesión exitoso
            $_SESSION['loggedin'] = true;
            $_SESSION['Email'] = $result[0]['Email'];
            $_SESSION['Rol'] = $result[0]['Rol'];
            if($result[0]["Rol"] == "Admin"){
                header("Location: index.php");
            }else{
                header("Location: consultarUsuario.php");
            }
        } else {
            // Contraseña incorrecta
            $_SESSION['Incorrecto'] = "incorrecto";
            header("Location: login.php");
        }
    } else {
        // Usuario no encontrado
        $_SESSION['Incorrecto'] = "incorrecto";
        header("Location: login.php");
    }
}
?>
