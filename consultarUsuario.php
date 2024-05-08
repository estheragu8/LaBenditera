<?php

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['Rol'] == "Admin") {
    $email = $_GET['id'];
} else {
    $email = $_SESSION["Email"];
}

include 'funciones.php';

$config = include 'config.php';

$resultado = [
    'error' => false,
    'mensaje' => '',
    'actualizado' => false,
];

if (!$email) {
    $resultado['error'] = true;
    $resultado['mensaje'] = "El usuario no existe";
}

try { //recupera el bono del usuario
    $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['name'];
    $conn = new PDO($dsn, $config['user'], $config['pass']);

    $querySQL = "SELECT * FROM Bono WHERE Email ='" . $email . "'";
    $sentencia = $conn->prepare($querySQL);
    $sentencia->execute();

    $bono = $sentencia->fetchall(PDO::FETCH_ASSOC);
    if (!$bono) {
        $resultado['error'] = true;
        $resultado['mensaje'] = 'No se ha encontrado el bono';
    }

} catch (PDOException $error) {
    $resultado['error'] = true;
    $resultado['mensaje'] = $error->getMessage();
}


if (isset($_POST['MinutosBono'])) { //Renovamos el bono del usuario, eliminando los registros de acceso anteriores y actualizando sus minutos.
    $minutos = $bono[0]['MinutosDisponibles'];
    if ($minutos == null) {
        $minutos = $bono[0]['MinutosBono'];
    }
    $minutos = intval($_POST['MinutosBono'] * 60) + intval($minutos);

    try {
        $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['name'];
        $conn = new PDO($dsn, $config['user'], $config['pass']);

        $querySQL0 = "INSERT INTO `AntiguosAccesos`(`UsuarioEmail`, `MarcaTiempo`, `Tipo`) SELECT * FROM Acceso WHERE UsuarioEmail ='" . $email . "'";

        $sentencia0 = $conn->prepare($querySQL0);
        $sentencia0->execute();

        $querySQL1 = "DELETE FROM Acceso WHERE UsuarioEmail ='" . $email . "'";

        $sentencia1 = $conn->prepare($querySQL1);
        $sentencia1->execute();

        $querySQL = "UPDATE Usuario SET MinutosBono = '" . $minutos . "' WHERE Email ='" . $email . "'";

        $sentencia = $conn->prepare($querySQL);
        $sentencia->execute();
        $resultado['actualizado'] = true;

    } catch (PDOException $error) {
        $resultado['error'] = true;
        $resultado['mensaje'] = $error->getMessage();
    }

}

try { //recupera el bono del usuario
    $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['name'];
    $conn = new PDO($dsn, $config['user'], $config['pass']);

    $querySQL = "SELECT * FROM Bono WHERE Email ='" . $email . "'";
    $sentencia = $conn->prepare($querySQL);
    $sentencia->execute();

    $bono = $sentencia->fetchall(PDO::FETCH_ASSOC);
    if (!$bono) {
        $resultado['error'] = true;
        $resultado['mensaje'] = 'No se ha encontrado el bono';
    }

} catch (PDOException $error) {
    $resultado['error'] = true;
    $resultado['mensaje'] = $error->getMessage();
}

try { //recupera las sesiones del usuario
    $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['name'];
    $conn = new PDO($dsn, $config['user'], $config['pass']);

    $querySQL = "SELECT * FROM Visita WHERE UsuarioEmail ='" . $email . "'";

    $sentencia = $conn->prepare($querySQL);
    $sentencia->execute();

    $sesiones = $sentencia->fetchall(PDO::FETCH_ASSOC);

    if (!$sesiones) {
        $resultado['error'] = true;
        $resultado['mensaje'] = 'No se ha encontrado visitas.';
    }

} catch (PDOException $error) {
    $resultado['error'] = true;
    $resultado['mensaje'] = $error->getMessage();
}

try {
    $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['name'];
    $conn = new PDO($dsn, $config['user'], $config['pass']);

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

<?php include 'templates/header.php' ?>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
    crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
    integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
    crossorigin="anonymous"></script>
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
if ($resultado['actualizado']) {
    ?>
    <div class="container mt-2">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success" role="alert">
                    ¡Bono actualizado!
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>

<?php
if (isset($sesiones) && isset($usuario)) {
    ?>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="mt-3">Histórico de
                    <?= escapar($usuario['nombre']) . ' ' . escapar($usuario['apellidos']) . ' Bono - ' . escapar($usuario['tipoBono']) ?>
                </h2>
            </div>
            <div class="col-md-12">
                <?php
                if ($_SESSION['Rol'] == "Admin") {
                    echo '<form method="post">
                        <div class="accordion bg-warning-subtle" id="accordionExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed bg-warning-subtle" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#flush-collapseOne" aria-expanded="false"
                                        aria-controls="flush-collapseOne">
                                        Renovar Bono
                                    </button>
                                </h2>
                                <div id="flush-collapseOne" class="accordion-collapse collapse"
                                    data-bs-parent="#accordionFlushExample">
                                    <div class="accordion-body">
                                        <div class="form-group row align-items-center">
                                            <div class="col-md-1"> <input type="radio" id="10" name="MinutosBono" value="10"
                                                    required>
                                                <label for="10">10h</label>
                                            </div>
                                            <div class="col-md-1"><input type="radio" id="20" name="MinutosBono" value="20"
                                                    required>
                                                <label for="20">20h</label>
                                            </div>
                                            <div class="col-md-1"><input type="radio" id="30" name="MinutosBono" value="30"
                                                    required>
                                                <label for="30">30h</label>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="submit" class="btn btn-warning" name="renovar">Renovar
                                                    bono</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>';
                }
                ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Apellidos</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Tiempo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($sesiones && $sentencia->rowcount() > 0) {
                            foreach ($sesiones as $fila) {
                                ?>
                                <tr>
                                    <td><?php echo escapar($usuario['Nombre']); ?></td>
                                    <td><?php echo escapar($usuario['Apellido']); ?></td>
                                    <td><?php echo escapar($fila['Entrada']); ?></td>
                                    <td><?php echo escapar($fila['Salida']); ?></td>
                                    <td><?php echo escapar($fila['Minutos']); ?></td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Horas totales</th>
                            <th>Horas gastadas</th>
                            <th>Horas disponibles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo escapar($bono[0]['MinutosBono']) / 60; ?></td>
                            <td><?php echo escapar($bono[0]['MinutosGastados']) / 60; ?></td>
                            <td><?php echo escapar($bono[0]['MinutosDisponibles']) / 60; ?></td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}
?>
