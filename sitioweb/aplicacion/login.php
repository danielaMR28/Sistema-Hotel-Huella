<?php
session_start();
// Parametro para evitar el envio automatico del formulario
$_SESSION['enviar'] = false;
$_SESSION['comprobar'] = false;

// Conexión a la base de datos
$connect = mysqli_connect('db', 'php_docker', 'password', 'php_docker');

$error_message = '';

// Lógica de inserción y redirección (Registro)
if (isset($_POST['enviar'])) {
    // Obtencion de los valores del formulario
    $user_name = mysqli_real_escape_string($connect, $_POST['name']);
    $user_last_name = mysqli_real_escape_string($connect, $_POST['last_name']);
    $user_phone = mysqli_real_escape_string($connect, $_POST['phone']);
    $user_email = mysqli_real_escape_string($connect, $_POST['email']);
    $user_password = mysqli_real_escape_string($connect, $_POST['password']);

    // Cifrar la contraseña antes de almacenarla
    $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);

    // Query para insertar usuarios
    $insert_query = "INSERT INTO users (name, last_name, email, password, phone) VALUES ('$user_name', '$user_last_name', '$user_email', '$hashed_password', '$user_phone')";

    if (mysqli_query($connect, $insert_query)) {
        // Tomar el id registrado recientemente
        $user_id = mysqli_insert_id($connect);

        // Redireccionar después de la inserción exitosa
        header('Location: reservations.php?id='.$user_id);
        exit();
    } else {
        echo "<div class='alert alert-danger' role='alert'>Error al registrarse: " . mysqli_error($connect) . "</div>";
    }
}

// Verificación del login (Inicio de sesión)
if (isset($_POST['comprobar'])) {
    // Obtención de los valores del formulario
    $username = mysqli_real_escape_string($connect, $_POST['email']);
    $password = mysqli_real_escape_string($connect, $_POST['password']);

    // Validaciones para diferentes usuarios
    if($username == 'admin' && $password == 'admin') {
        header('Location: admin.php');
        exit();
    } else if($username == 'limpieza' && $password == 'limpieza') {
        header('Location: limpieza.php');
        exit();
    } else if($username == 'gerencia' && $password == 'gerencia') {
        header('Location: reports.php');
        exit();
    } else {
        // Query para validar las credenciales del usuario
        $select_query = "SELECT * FROM users WHERE email='$username'";
        $result = mysqli_query($connect, $select_query);
    
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            $password_from_db = $user['password']; // Aquí estará el hash de la contraseña
            $user_id = $user['id'];
    
            // Verificar la contraseña usando password_verify
            if (password_verify($password, $password_from_db)) {
                // Redireccionar si las credenciales son correctas
                header('Location: reservations.php?id='.$user_id);
                exit();
            } else {
                // Mensaje de error si la contraseña es incorrecta
                $error_message = "Contraseña incorrecta. Por favor inténtelo de nuevo.";
            }
        }
    }
}

// Lógica de inserción y redirección (Registro)
if (isset($_POST['search'])) {
    // Obtención de los valores del formulario
    $reservation_id = mysqli_real_escape_string($connect, $_POST['reservation_id']);
    $reservation_last_name = mysqli_real_escape_string($connect, $_POST['reservation_last_name']);

    header('Location: confirmation.php?reservation_id=' . $reservation_id . '&last_name=' . $reservation_last_name);
    exit(); 
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Stay: Bienvenido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Iconos -->
    <script src="https://kit.fontawesome.com/a6992b7fd0.js" crossorigin="anonymous"></script>
    <!-- Estilos -->
    <link rel="stylesheet" href="./styles/main.css">
    <link rel="stylesheet" href="./styles/login.css">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <!-- Contenedor principal -->
    <main>
        <!-- Encabezado del contenedor -->
        <header>
            <i class="fa-solid fa-bed blue-icon"></i>
            <p>Bienvenido a nuestro hotel</p>
            <p>Inicia sesión para acceder a tu reserva</p>
        </header>

        <section>
            <!-- Formulario de inicio de sesion -->
            <form method="POST">
                <div class="input--container">
                    <label for="email">Correo Electrónico</label>
                    <input type="text" name="email" placeholder="tu@email.com">
                    <i class="fa-regular fa-envelope input-icon email-icon"></i>
                </div>
                <div class="input--container">
                    <label for="password">Contraseña</label>
                    <input type="password" name="password">
                    <i class="fa-solid fa-lock input-icon lock-icon"></i>
                </div>

                <!-- Mostrar mensaje de error -->
                <?php if (!empty($error_message)): ?>
                    <div class="error-message">
                        <span><?php echo $error_message; ?></span>
                    </div>
                <?php endif; ?>

                <button class="primary-button" name="comprobar"><i class="fa-solid fa-right-to-bracket"></i>Iniciar Sesión</button>
            </form>

            <!-- Botones secundarios -->
            <div class="buttons--container">
                <div class="button-single--container">
                    <i class="fa-solid fa-user-plus button-icon user-icon"></i>
                    <button type="button" class="secondary-button" onclick="showModal()">Registrarse</button>
                </div>
                <div class="button-single--container">
                    <i class="fa-solid fa-magnifying-glass button-icon magnifying-icon"></i>
                    <button type="button" class="secondary-button" onclick="showSearchModal()">Buscar Reservación</button>
                </div>
            </div>
        </section>
    </main>

    <!-- Formulario oculto para seleccionar rol -->
    <form id="roleForm" action="index.php" method="post" style="display: none;">
        <input type="hidden" name="role" id="roleInput">
    </form>

    <!-- Modal para registro de huespedes -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Registrarse - Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre:</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Apellido:</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Teléfono:</label>
                            <input type="text" class="form-control" id="phone_number" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico:</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password1" class="form-label">Contraseña:</label>
                            <input type="password" class="form-control" id="password1" name="password" required>
                        </div>
                        <button type="submit" name="enviar" id="submit" class="primary-button">Registrarse</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para buscar reservacion -->
    <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="searchModalLabel">Buscar Reservación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit-id">
                        <div class="mb-3">
                            <label for="reservation_id" class="form-label">Identificador de la Reservación:</label>
                            <input type="text" id="reservation_id" name="reservation_id" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="reservation_last_name" class="form-label">Apellido de la Reservación:</label>
                            <input type="text" id="reservation_last_name" name="reservation_last_name" class="form-control" required>
                        </div>
                        <button type="submit" name="search" class="primary-button">Buscar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showModal() {
            var modal = new bootstrap.Modal(document.getElementById('loginModal'));
            modal.show();
        }

        function showSearchModal() {
                var modal = new bootstrap.Modal(document.getElementById('searchModal'));
                modal.show();
            }
    </script>
</body>
</html>
