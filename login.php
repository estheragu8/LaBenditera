<?php
session_start();

include "templates/header2.php";
if (isset($_POST["newPassword"])) {
    $email = $_SESSION["Email"];
    include 'funciones.php';

    $error = false;
    $config = include 'config.php';

    try { //recupera los datos del usuario
        $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['name'];
        $conn = new PDO($dsn, $config['user'], $config['pass']);

        //codigo que obtendrá la lista de usuarios
        $consultaSQL2 = "UPDATE `Usuario` SET `Password` = '".password_hash($_POST["newPassword"], PASSWORD_DEFAULT)."' WHERE `Usuario`.`Email` = '".$email."';";

        $sentencia2 = $conn->prepare($consultaSQL2);
        $sentencia2->execute();

        $tarjeta = $sentencia2->fetchall();

    } catch (PDOException $error) {
        $error = $error->getMessage();
    }
}

if($_SESSION['Incorrecto'] == "incorrecto"){
    echo '<div class="toast align-items-center text-bg-danger border-0 fade show" role="alert" aria-live="assertive" aria-atomic="true" fade show>
    <div class="d-flex">
      <div class="toast-body">
        Email o contraseña incorrectos.
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>';
}

$_SESSION = array();
session_destroy();
?>

<style>
    body {
        background-color: #f8f9fa;
    }

    .card {
        border: 0;
        border-radius: 15px;
        box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background-color: #007bff;
        color: #fff;
        border-radius: 15px 15px 0 0;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .btn-primary {
        background-color: #007bff;
        border: none;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }
</style>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center bg-warning">
                    Iniciar sesión
                </div>
                <div class="card-body">
                    <form action="procesar_login.php" method="POST">
                        <div class="form-group">
                            <label for="username">Nombre de usuario:</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Contraseña:</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary mt-2 bg-warning">Iniciar sesión</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>

</html>
