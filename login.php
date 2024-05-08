<?php
session_start();

if (isset($_SESSION['loggedin']) || $_SESSION['loggedin'] == true) {
    if ($_SESSION['Rol'] != "Admin") {
        header("Location: consultarUsuario.php");
        exit;
    } else {
        header("Location: index.php");
        exit;
    }
}
?>
<?php include "templates/header2.php"; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
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
                            <button type="submit" class="btn btn-primary mt-2">Iniciar sesión</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
