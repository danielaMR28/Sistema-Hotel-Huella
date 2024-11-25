<?php
session_start();
$_SESSION['enviar']=false;
$user_id = $_GET['id'];

// Conexión a la base de datos
$connect = mysqli_connect('db', 'php_docker', 'password', 'php_docker');

$error_message = '';

// Verifica si el usuario ha iniciado sesión
if ($user_id != null) {
    // Consulta para obtener los datos del usuario
    $query = "SELECT * FROM users WHERE id = '$user_id'";
    $result = mysqli_query($connect, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $user_name = $user['name'];
        $user_last_name = $user['last_name'];
    } else {
        $user_name = "Invitado";
    }
} else {
    $user_name = "Invitado"; // Valor por defecto si no hay sesión activa
}

// Lógica de inserción y redirección (Registro)
if (isset($_POST['submit'])) {
    // Obtencion de los valores del formulario
    $start_date = mysqli_real_escape_string($connect, $_POST['reservation_arrive_date']);
    $finish_date = mysqli_real_escape_string($connect, $_POST['reservation_departure_date']);
    $room_type = mysqli_real_escape_string($connect, $_POST['type']);

    // Crear objetos DateTime para calcular la diferencia
    $date_inicio = new DateTime($start_date);
    $date_fin = new DateTime($finish_date);

    // Calcular la diferencia de días
    $diferencia = $date_inicio->diff($date_fin);
    $dias = $diferencia->days;

    // Generar un identificador alfanumérico aleatorio de 12 caracteres
    $reservation_id = bin2hex(random_bytes(4)); // Genera un string aleatorio de 12 caracteres

    // Query para seleccionar habitaciones disponibles en base a las fechas y tipo de habitación
    $select_query = "
        SELECT * 
        FROM rooms 
        WHERE available='1' 
        AND type='$room_type'
        AND room_number NOT IN (
            SELECT id_room 
            FROM reservations 
            WHERE ('$start_date' < finish_date AND '$finish_date' > start_date)
        )
    ";
    $result = mysqli_query($connect, $select_query);

    if (mysqli_num_rows($result) > 0) {
        $room = mysqli_fetch_assoc($result);
        $id_room = $room['room_number'];
        $type = $room['type'];
        $price = $room['price'];

        // Pasar a la parte de facturacion
        header('Location:billing.php?user_id=' . $user_id . '&room_id=' . $id_room . '&type=' . $type . '&start_date=' . $start_date . '&finish_date=' . $finish_date . '&price=' . $price);
        exit();
    } else {
        $error_message = "No hay habitaciones disponibles para las fechas seleccionadas";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservaciones : Digital Stay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilos -->
    <link rel="stylesheet" href="./styles/reservations.css">
    <!-- Fuentes utilizadas -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</head>
    <body>
        <main>
            <!-- Encabezado de la pagina -->
            <header>
                <h1>Digital Stay</h1>
                <p class="header-introduction">Bienvenido,  <?php echo $user_name; ?> <?php echo $user_last_name; ?></p>
                <p class="header-sub-introduction">Estamos encantados de recibirte en nuestra página de reservaciones</p>
            </header>

            <!-- Contenedor principal -->
            <section id="main--container">
                <div class="title-section--container">
                    <h3>Hacer una Reservación</h3>
                </div>

                <form method="post">
                    <!-- Tipo de habitacion a seleccionar -->
                    <label for="type" class="form-label">Tipo de Habitación</label>
                    <select name="type" id="type" required class="form-input">
                        <option value="" disabled selected>Seleccione la habitación</option>
                        <option value="1">Sencilla</option>
                        <option value="2">Doble</option>
                    </select>

                    <!-- Fechas de entrada -->
                    <label for="reservation_arrive_date" class="form-label">Fecha de Llegada</label>
                    <input type="date" id="reservation_arrive_date" name="reservation_arrive_date" class="form-input" required>

                    <!-- Fecha de salida -->
                    <label for="reservation_departure_date" class="form-label">Fecha de Salida</label>
                    <input type="date" id="reservation_departure_date" name="reservation_departure_date" class="form-input" required>

                    <!-- Mostrar mensaje de error -->
                    <?php if (!empty($error_message)): ?>
                        <div class="error-message">
                            <span><?php echo $error_message; ?></span>
                        </div>
                    <?php endif; ?>
            
                    <!-- Boton para pasar a la pagina de facturacion -->
                    <button type="submit" name="submit" class="primary-button">Pasar a facturación</button>
                </form>
            </section>
        </main>
    </body>
</html>
