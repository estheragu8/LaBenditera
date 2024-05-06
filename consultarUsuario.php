<?php
include 'funciones.php';

$config = include 'config.php';

$resultado = [
    'error' => false,
    'mensaje' => ''
];

if (!isset($_GET['id'])) {
    $resultado['error'] = true;
    $resultado['mensaje'] = "El usuario no existe";
}

try { //recupera el bono del usuario
    $dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['name'];
    $conn = new PDO($dsn, $config['user'], $config['pass']);

    $id = $_GET['id'];
    $querySQL = "SELECT * FROM Bono WHERE Email ='" . $id . "'";
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

    $id = $_GET['id'];
    $querySQL = "SELECT * FROM Visita WHERE UsuarioEmail ='" . $id . "'";

    $sentencia = $conn->prepare($querySQL);
    $sentencia->execute();

    $sesiones = $sentencia->fetchall(PDO::FETCH_ASSOC);

    if (!$sesiones) {
        $resultado['error'] = true;
        $resultado['mensaje'] = 'No se ha encontrado marcajes';
    }

} catch (PDOException $error) {
    $resultado['error'] = true;
    $resultado['mensaje'] = $error->getMessage();
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

if (isset($_POST['exportarCSV'])) { //exporta el fichero CSV
    if (isset($sesiones)) {
        $ficheroExcel = $usuario['apellidos'] . ' ' . $usuario['nombre'] . '_' . date("Ymd") . '.csv';

        //indicamos que vamosa tratar con un fichero csv
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=" . $ficheroExcel);

        //formamos la tabla que se guardará en el csv
        echo "apellidos;nombre;bono;entrada;salida;consumo\n";

        //recorremos los datos y los mostramos en csv
        foreach ($sesiones as $fila) {
            $entrada = new DateTime(escapar($fila['Entrada']));
            $salida = new DateTime(escapar($fila['Salida']));
            $salida = new DateTime(escapar($fila['Minutos']));
        }
    } else {
        $resultado['error'] = true;
        $resultado['mensaje'] = "No hay datos a exportar";
    }
    //para que se cree el excel, hay que añadir la sentencia exit
    exit;
}

?>

<?php include 'templates/header.php' ?>

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
if (isset($sesiones) && isset($usuario)) {
    ?>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="mt-3">Histórico de
                    <?= escapar($usuario['nombre']) . ' ' . escapar($usuario['apellidos']) . ' Bono - ' . escapar($usuario['tipoBono']) ?>
                </h2>
                <form method="post">
                    <button type="submit" name="exportarCSV"><img src="images/excel.png" width="50" heigth="50"> </button>
                    <a class="btn btn-primary" href="index.php">Regresar al inicio</a>
                </form>
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
                            <td><?php echo escapar($bono[0]['MinutosBono'])/60;?></td>
                            <td><?php echo escapar($bono[0]['MinutosGastados'])/60; ?></td>
                            <td><?php echo escapar($bono[0]['MinutosDisponibles'])/60; ?></td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}
?>
