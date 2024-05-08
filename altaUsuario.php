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

try { //recupera los datos del usuario
    $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['name'];
    $conn = new PDO($dsn, $config['user'], $config['pass']);

    //codigo que obtendrá la lista de usuarios
    $consultaSQL2 = "SELECT * FROM Tarjeta";

    $sentencia2 = $conn->prepare($consultaSQL2);
    $sentencia2->execute();

    $tarjeta = $sentencia2->fetchall();

} catch (PDOException $error) {
    $error = $error->getMessage();
}

if (isset($_POST['submit'])) {

    $resultado = [
        'error' => false,
        'mensaje' => 'El usuario ' . escapar($_POST['Nombre']) . ' ' . escapar($_POST['Apellido']) . ' ' . escapar($_POST['Email']) . ' ha sido agregado con éxito'
    ];

    $config = include 'config.php';

    try { //completar la conexión y la query para insertar los datos nuevos
        $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['name'];
        $conn = new PDO($dsn, $config['user'], $config['pass']);

        if ($conn->connect_error) {
            die("connection failed: " . $conn->connect_error);
        }

        if ($_POST['MinutosBono'] == 10) {
            $fechaCad = date('Y-m-d', strtotime('+6 month'));
        } else if ($_POST['MinutosBono'] == 10) {
            $fechaCad = date('Y-m-d', strtotime('+9 month'));
        } else {
            $fechaCad = date('Y-m-d', strtotime('+12 month'));
        }

        $usuario = [
            'Email' => $_POST['Email'],
            'Nombre' => $_POST['Nombre'],
            'Apellido' => $_POST['Apellido'],
            'FechaNacimiento' => $_POST['FechaNacimiento'],
            'Telefono' => $_POST['Telefono'],
            'IdTarjeta' => $tarjeta[0]['Id'],
            'MinustosBono' => $_POST['MinutosBono'] * 60,
            'FechaCaducidad' => $fechaCad
        ];

        $querySQL = "INSERT INTO Usuario (Email, Nombre, Apellido, FechaNacimiento, Telefono, IdTarjeta, MinutosBono, FechaCaducidad) ";
        $querySQL .= "values (:" . implode(", :", array_keys($usuario)) . ")";

        $sentencia = $conn->prepare($querySQL);
        if ($sentencia->execute($usuario) == false) {
            echo ("ha fallado el SQL");
        } else {
            header("Location: index.php");
        }

    } catch (PDOException $error) {
        $resultado['error'] = true;
        $resultado['mensaje'] = $error->getMessage();
    }
}
?>

<?php include "templates/header.php"; ?>

<?php
if (isset($resultado)) {
    ?>
    <div class="container mt-3">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-<?= $resultado['error'] ? 'danger' : 'success' ?>" role="alert">
                    <?= $resultado['mensaje'] ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mt-4">Registra nuevo ususario</h2>
            <form method="post">
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" name="Nombre" id="nombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="apellidos">Apellido</label>
                    <input type="text" name="Apellido" id="apellidos" class="form-control">
                </div>
                <div class="form-group">
                    <label for="idTarjeta">Tarjeta</label>
                    <input type="text" value="<?php echo escapar($tarjeta[0]['Id']); ?>" name="IdTarjeta" id="idTarjeta"
                        class="form-control" disabled>
                </div>
                <div class="form-group">
                    <label for="email">e-mail</label>
                    <input type="email" name="Email" id="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="FechaNacimiento">Fecha de nacimiento</label>
                    <input type="date" name="FechaNacimiento" id="FechaNacimiento" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="telefono">Telefono</label>
                    <input type="tel" name="Telefono" id="telefono" class="form-control">
                </div>
                <div class="form-group">
                    <label for="tipoBono">Tipo de bono</label><br>
                    <input type="radio" id="10" name="MinutosBono" value="10" required>
                    <label for="10">10</label><br>
                    <input type="radio" id="20" name="MinutosBono" value="20" required>
                    <label for="20">20</label><br>
                    <input type="radio" id="30" name="MinutosBono" value="30" required>
                    <label for="30">30</label><br>
                    <input onclick="" type="radio" id="otro" name="MinutosBono" value="0" required>
                    <input type="text" id="otroValue" for="otro" onchange="cambiarValor()"
                        placeholder="Otro..."></input><br>
                </div>
        </div>
        <div class="col-md-12 mt-3">
            <div class="form-group">
                <input type="submit" name="submit" class="btn btn-primary" value="Alta usuario">
                <a class="btn btn-primary" href="index.php">Regresar al inicio</a>
            </div>
            </form>
        </div>
    </div>
</div>

<script>
    function cambiarValor() {
        document.getElementById("otro").value = document.getElementById("otroValue").value;
    }
</script>

<?php include "templates/footer.php"; ?>
