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
    $consultaSQL = "SELECT * FROM Usuario";

    $sentencia = $conn->prepare($consultaSQL);
    $sentencia->execute();

    $usuarios = $sentencia->fetchall();


} catch (PDOException $error) {
    $error = $error->getMessage();
}
?>
<?php
if (isset($_GET['musica'])) {
    echo exec("(sleep 1 ; play -q https://playerservices.streamtheworld.com/api/livestream-redirect/CADENADIAL.mp3; ) & ls;");
}
?>

<?php include "templates/header.php"; ?>

<?php
if ($error) {
    ?>
    <div class="container mt-2">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger" role="alert">
                    <?= $error ?>
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
            <div class="callout-warning">
                <strong>¡Recuerda!</strong> Pasa la tarjeta por el lector antes de añadir un nuevo usuario.
            </div>
            <a href="altaUsuario.php" class="btn btn-outline-danger mt-4">Nuevo usuario</a>
            <a href="phpmyadmin" target="_blank" class="btn btn-outline-primary mt-4">phpmyadmin</a>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mt-3">Usuarios registrados</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Acciones</th>
                        <th>Tarjeta</th>
                        <th hidden>e-mail</th>
                        
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($usuarios && $sentencia->rowcount() > 0) {
                        foreach ($usuarios as $fila) {
                            ?>
                            <tr>
                                <td><?php echo escapar($fila['Nombre']); ?></td>
                                <td><?php echo escapar($fila['Apellido']); ?></td>
                                <td>
                                    <div class="dropdown-center">
                                        <button class="btn btn-secondary dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            Acciones
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a href="<?= 'addNumero.php?id=' . escapar($fila['Email']) ?>"
                                                    class="dropdown-item">Añadir
                                                    Tarjeta ➕</a></li>
                                            <li><a id="<?= escapar($fila['Email']) ?>"
                                                    class="dropdown-item"
                                                    data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                    onclick="setEmail()">Borrar 🗑️</a></li>
                                            <li><a href="<?= 'editarUsuario.php?id=' . escapar($fila['Email']) ?>"
                                                    class="dropdown-item">Editar ✏️</a>
                                            </li>
                                            <li><a href="<?= 'consultarUsuario.php?id=' . escapar($fila['Email']) ?>"
                                                    class="dropdown-item">Consultar 📝</a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                                <td><?php echo escapar($fila['IdTarjeta']); ?></td>
                                <td hidden><?php echo escapar($fila['Email']); ?></td>
                            </tr>
                            <?php
                        }
                    }
                    ?>

                    <!-- Modal -->
                    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">Borrar</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="close"></button>
                                </div>
                                <div class="modal-body">
                                    ¿Estás seguro de eliminar el usuario?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-banger" data-bs-dismiss="modal">No</button>
                                    <button type="button" class="btn btn-success" onclick="borrar()">Sí</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    var email = "";

    function setEmail() {
        email = (event.srcElement.id);
    }
    function borrar(e) {
        console.log(event);
        var web = "borrarUsuario.php?id=" + email;
        console.log(web);
        location.href = web;
    }
</script>
