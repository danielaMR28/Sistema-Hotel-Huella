<?php
session_start();
$reservation_id = $_GET['reservation_id'];
$reservation_last_name = $_GET['last_name'];
//$reservation_price = $_GET['price'];

// Conexión a la base de datos
$connect = mysqli_connect('db', 'php_docker', 'password', 'php_docker');

// Verificar si se ha recibido el ID de la reservación
if (isset($_GET['reservation_id'])) {
    // Consulta para obtener detalles de la reservación
    $query = "SELECT r.*, u.name as user_name, u.last_name as user_last_name, ro.type as room_type
              FROM reservations r
              JOIN users u ON r.id_user = u.id
              JOIN rooms ro ON r.id_room = ro.room_number
              WHERE r.id = '$reservation_id' AND u.last_name = '$reservation_last_name'";
    $result = mysqli_query($connect, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $reservation = mysqli_fetch_assoc($result);
    } else {
        header('Location: notFound.php');
        exit();
    }
} else {
    echo "<div class='alert alert-danger' role='alert'>Error: ID de reservación no proporcionado.</div>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Reservación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilos -->
    <link rel="stylesheet" href="./styles/confirmation.css">
    <!-- Fuentes utilizadas -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
</head>
<body>
    <main>
        <section id="confirmation--container">
            <h1>¡Reserva confirmada!</h1>

            <div class="reservation-info--container">
                <div class="thanks-message">
                    <p class="message1">Gracias por su reserva</p>
                    <p class="message2">Su estancia está confirmada</p>
                </div>

                <hr>
            </div>

            <div class="reservation-info--container reservation-flex">
                <div>
                    <p class="reservation-info--name">ID de Reservación</p>
                    <p class="reservation-info"><?php echo $reservation['id']; ?></p>

                    <p class="reservation-info--name">Tipo de Habitación</p>
                    <p class="reservation-info"><?php echo ($reservation['room_type'] == 1) ? "Sencilla" : "Doble"; ?></p>

                    <p class="reservation-info--name">Nombre del Huésped</p>
                    <p class="reservation-info"><?php echo $reservation['user_name'] . ' ' . $reservation['user_last_name']; ?></p>
                </div>
                <div>
                    <p class="reservation-info--name">Fecha de Llegada</p>
                    <p class="reservation-info"><?php echo date("d/m/Y", strtotime($reservation['start_date'])) ?></p>

                    <p class="reservation-info--name">Fecha de Salida</p>
                    <p class="reservation-info"><?php echo date("d/m/Y", strtotime($reservation['finish_date'])) ?></p>

                    <p class="reservation-info--name">Precio Final</p>
                    <p class="reservation-info">$<?php echo $reservation['price'] ?>.00</p>
                </div>
            </div>

            <div class="button-container">
                <hr>
                <a href="login.php" class="primary-button">Volver al login</a>
            </div>
        </section>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
