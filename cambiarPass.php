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

?>
<?php include "templates/header.php"; ?>
  
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
            <h4>Cambiar Contraseña</h4>
          </div>
          <div class="card-body">
            <form id="passwordForm" action="login.php" method="POST">
              <div class="form-group">
                <label for="newPassword">Nueva Contraseña</label>
                <input type="password" class="form-control" id="newPassword" name="newPassword" required>
              </div>
              <div class="form-group">
                <label for="confirmPassword">Confirmar Nueva Contraseña</label>
                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                <small id="passwordHelpBlock" class="form-text text-danger"></small>
              </div>
              <button type="submit" class="btn btn-primary btn-block bg-warning">Cambiar Contraseña</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Bootstrap JS (optional) -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <!-- JavaScript para comprobar si las contraseñas coinciden -->
  <script>
    document.getElementById("passwordForm").addEventListener("submit", function(event) {
      var newPassword = document.getElementById("newPassword").value;
      var confirmPassword = document.getElementById("confirmPassword").value;
      if (newPassword !== confirmPassword) {
        event.preventDefault();
        document.getElementById("passwordHelpBlock").textContent = "Las contraseñas no coinciden.";
      } else {
        document.getElementById("passwordHelpBlock").textContent = "";
      }
    });
  </script>
</body>
</html>
