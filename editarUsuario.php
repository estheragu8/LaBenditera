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
    $dsn = 'mysql:host='.$config['host'].';dbname='.$config['name'];
    $conn = new PDO($dsn, $config['user'], $config['pass']);

    $mewSQL = "SELECT * FROM `Bono` WHERE Email = '" . $_GET['id'] . "'";
        $consulta2 = $conn->prepare($mewSQL);
        $consulta2->execute();

        $minutosDisponibles = $consulta2->fetchall();
        if( $minutosDisponibles[0]["MinutosDisponibles"] == null) {
            $minutosDisponibles = $minutosDisponibles[0]["MinutosBono"];
        }else{
            $minutosDisponibles = $minutosDisponibles[0]["MinutosDisponibles"];
        }

} catch(PDOException $error) {
    $error = $error->getMessage();
    echo($error);
}

$resultado = [
    'error' => false,
    'mensaje' => ''
];

if (!isset($_GET['id'])) {
    $resultado['error'] = true;
    $resultado['mensaje'] = "El usuario no existe";
}

if (isset($_POST['submit'])) { //actualización del usuario
    try {
        $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['name'];
        $conn = new PDO($dsn, $config['user'], $config['pass']);

        // codigo actualizacion
        $usuario2 = [
            "nombre" => $_POST['nombre'],
            "apellido" => $_POST['apellido'],
            "telefono" => $_POST['telefono'],
            "email" => $_POST['email'],
            "minutosBono" => $_POST['minutosBono']*60,
            "fechaNacimiento" => $_POST['fechaNacimiento'],
            "fechaCaducidad" => $_POST['fechaCaducidad']
        ];

        $querySQL = "UPDATE Usuario SET Nombre = :nombre, Apellido = :apellido, Telefono = :telefono, Email = :email, MinutosBono = :minutosBono, FechaNacimiento = :fechaNacimiento, FechaCaducidad = :fechaCaducidad WHERE Email = :email";
        $consulta = $conn->prepare($querySQL);
        if ($consulta->execute($usuario2) == false) {
            echo ("ha fallado el SQL");
        } 
    } catch (PDOException $error) {
        $resultado['error'] = true;
        $resultado['mensaje'] = $error->getMessage();
        echo ($error);
    }
}

try {
    $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['name'];
    $conn = new PDO($dsn, $config['user'], $config['pass']);

    $email = $_GET['id'];
    $querySQL = "SELECT * FROM Usuario WHERE Email ='" . $email . "'";

    $sentencia = $conn->prepare($querySQL);
    $sentencia->execute();

    $usuario = $sentencia->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        $resultado['error'] = true;
        $resultado['mensaje'] = 'No se ha encontrado el usuario';
    }

} catch (PDOException $error) {
    $resultado['error'] = true;
    $resultado['mensaje'] = $error->getMessage();
}
?>

<?php require "templates/header.php"; ?>

<?php
if ($resultado['error']) {
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
if (isset($_POST['submit']) && !$resultado['error']) {
    ?>
    <div class="container mt-2">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success" role="alert">
                    El usuario ha sido actualizado correctamente
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>

<?php
if (isset($usuario) && $usuario) {
    ?>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="mt-4">Editando el usuario <?= escapar($usuario['Nombre']) . ' ' . escapar($usuario['Apellido']) ?>
                </h2>
                <hr>
                <form method="post">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="text" name="email" id="email" value="<?= escapar($usuario['Email']) ?>"
                            class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" name="nombre" id="nombre" value="<?= escapar($usuario['Nombre']) ?>"
                            class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="apellidos">Apellidos</label>
                        <input type="text" name="apellido" id="apellido" value="<?= escapar($usuario['Apellido']) ?>"
                            class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="tel" name="telefono" id="telefono" value="<?= escapar($usuario['Telefono']) ?>"
                            class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="FechaNacimiento">Fecha de nacimiento</label>
                        <input type="date" name="fechaNacimiento" id="FechaNacimiento" class="form-control"
                            value="<?= escapar($usuario['FechaNacimiento']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="tipoBono">Tipo de bono</label>
                        <br>
                        <input type="radio" id="otro" name="minutosBono" value="<?= escapar($minutosDisponibles)  / 60 ?>" checked>
                        <label for="otro"><?= escapar($minutosDisponibles) / 60 ?> (Horas disponibles actualmente)</label><br>
                        <input type="radio" id="10" name="minutosBono" value="10">
                        <label for="10">10</label><br>
                        <input type="radio" id="20" name="minutosBono" value="20">
                        <label for="20">20</label><br>
                        <input type="radio" id="30" name="minutosBono" value="30">
                        <label for="30">30</label>
                        
                    </div>
                    <div class="form-group">
                        <label for="FechaCaducidad">Fecha de caducidad</label>
                        <input type="date" name="fechaCaducidad" id="FechaCaducidad" class="form-control"
                            value="<?= escapar($usuario['FechaCaducidad']) ?>">
                    </div>
                    <div class="form-group">
                        <input type="submit" name="submit" class="btn btn-primary" value="Actualizar">
                        <a class="btn btn-primary" href="index.php">Regresar al inicio</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}
?>
