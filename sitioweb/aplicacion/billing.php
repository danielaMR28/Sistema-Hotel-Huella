<?php
// Recuperando los parametros de la peticion
$user_id = $_GET['user_id'];
$room_id = $_GET['room_id'];
$type = $_GET['type'];
$price = $_GET['price'];
$start_date = $_GET['start_date'];
$finish_date = $_GET['finish_date'];

// Precios de habitaciones
$single_price = 400;
$double_price = 600;
$final_price = 0;
$taxes = 0;

// Conexión a la base de datos
$connect = mysqli_connect('db', 'php_docker', 'password', 'php_docker');

// Verifica si el usuario ha iniciado sesión
if ($user_id != null) {
    // Consulta para obtener los datos del usuario
    $query = "SELECT * FROM users WHERE id = '$user_id'";
    $result = mysqli_query($connect, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $user_name = $user['name'];
        $user_last_name = $user['last_name'];
        $user_email = $user['email'];
        $user_phone = $user['phone'];
    } else {
        $user_name = "Invitado";
    }
} else {
    $user_name = "Invitado";
}

$diferenciaEnSegundos = strtotime($finish_date) - strtotime($start_date);
$diferenciaEnDias = $diferenciaEnSegundos / (60 * 60 * 24);

// Lógica de inserción y redirección (Registro)
if (isset($_POST['submit'])) {
    // Crear objetos DateTime para calcular la diferencia
    $date_inicio = new DateTime($start_date);
    $date_fin = new DateTime($finish_date);
    $taxes = $price * 16 / 100;
    $final_price = $final_price = $taxes + ($diferenciaEnDias * $price);

    // Generar un identificador alfanumérico aleatorio de 12 caracteres
    $reservation_id = bin2hex(random_bytes(4)); // Genera un string aleatorio de 12 caracteres

    if (mysqli_num_rows($result) > 0) {

        // Query para insertar la reservación con el ID único generado
        $insert_query = "INSERT INTO reservations (id, id_user, id_room, start_date, finish_date, price) 
                         VALUES ('$reservation_id', '$user_id', '$room_id', '$start_date', '$finish_date', '$final_price')";

        if (mysqli_query($connect, $insert_query)) {
            // Redireccionar a la página de confirmación con el ID de reservación
            header('Location: confirmation.php?reservation_id=' . $reservation_id . '&last_name=' . $user_last_name . '&price=' . $final_price);
            exit();
        } else {
            echo "<div class='alert alert-danger' role='alert'>Error al crear la reservación: " . mysqli_error($connect) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilos -->
    <link rel="stylesheet" href="./styles/billing.css">
    <!-- Fuentes utilizadas -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
</head>
<body>
    <main>
        <!-- Encabezado de la pagina -->
        <header>
            <h1>Facturación</h1>
        </header>

        <!-- Contenedor principal -->
        <section>
            <!-- Subcontenedor -->
            <div class="main--container">
                <h3>Datos de Pago</h3>
                <form method="POST" class="form--container">
                        <label for="name" class="form-label">Nombre en la tarjeta</label>
                        <input type="text" id="name" name="name" placeholder="Juan Pérez" class="form-input" required>
                    
                        <label for="card_number" class="form-label">Número de tarjeta</label>
                        <input type="text" id="card_number" name="card_number" class="form-input" maxlength=19 required>
                        
                        <div class="card-info--container">
                            <div class="card-data--container">
                                <label for="expiration" class="form-label">Fecha de expiración</label>
                                <input type="text" id="expiration" name="expiration" placeholder="MM/YY" class="form-input" required>
                            </div>
                            <div class="card-data--container">
                                <label for="cvc" class="form-label">CVC:</label>
                                <input type="number" id="cvc" name="cvc" class="form-input" maxlength="3" required>
                            </div>
                        </div>
                    <button type="submit" name="submit" class="primary-button">Confirmar reserva</button>
                </form>
            </div>

            <!-- Subcontenedor -->
            <div class="main--container">
                <h3>Resumen de la reservación</h3>

                <div class="summary-reservation-item">
                    <p>Fecha de Entrada:</p>
                    <p class="reservation-info"><?php echo date("d/m/Y", strtotime($start_date)) ?></p>
                </div>
                
                <div class="summary-reservation-item">
                    <p>Fecha de Salida:</p>
                    <p class="reservation-info"><?php echo date("d/m/Y", strtotime($finish_date)) ?></p>
                </div>

                <div class="summary-reservation-item">
                    <p>Duración de la estancia: </p>
                    <p class="reservation-info"><?php echo $diferenciaEnDias; ?> noche(s) </p>
                </div>

                <div class="summary-reservation-item">
                    <p>Tipo de habitación: </p>
                    <p class="reservation-info"><?php echo ($type == 1) ? "Sencilla" : "Doble"; ?> </p>
                </div>

                <hr>

                <h5>Desglose del precio</h5>
                <div class="summary-reservation-item">
                    <p>Tarifa por noche: </p>
                    <p class="reservation-info">$<?php echo ($type == 1) ? $single_price : $double_price; ?>.00</p>
                </div>

                <div class="summary-reservation-item">
                    <p>Subtotal (<?php echo $diferenciaEnDias; ?> noche(s)): </p>
                    <p class="reservation-info">$<?php echo ($type == 1) ?$final_price = $taxes + ($diferenciaEnDias * $single_price) : $final_price = $taxes + ($diferenciaEnDias * $double_price); ?>.00</p>
                </div>

                <div class="summary-reservation-item">
                    <p>Impuestos (16%):</p>
                    <p class="reservation-info">$<?php echo ($type == 1) ? $taxes = $single_price * 16 / 100 : $taxes = $double_price * 16 / 100;  ?>.00</p>
                </div>

                <hr>

                <div class="summary-reservation-item">
                    <p class="reservation-info final-price">Precio final</p>
                    <p class="reservation-info final-price">$<?php echo ($type == 1) ?$final_price = $taxes + ($diferenciaEnDias * $single_price) : $final_price = $taxes + ($diferenciaEnDias * $double_price); ?>.00</p>
                </div>
            </div>
        </section>

        <!-- Boton de regreso -->
        <div class="button-container">
            <a href="login.php" class="secondary-button">Volver al login</a>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const creditCardInput = document.getElementById('card_number');

        creditCardInput.addEventListener('input', function (e) {
            // Elimina cualquier carácter que no sea un dígito
            let value = this.value.replace(/\D/g, '');

            // Añade un espacio cada 4 dígitos
            value = value.replace(/(.{4})/g, '$1 ');

            // Establece el valor del input
            this.value = value.trim();

            // Previene que el cursor se mueva al final del campo
            const cursorPosition = this.value.length; 
            this.setSelectionRange(cursorPosition, cursorPosition);
        });

        document.getElementById('expiration').addEventListener('input', function(e) {
            // Eliminar cualquier carácter no numérico
            const value = e.target.value.replace(/\D/g, '');
            
            // Limitar la longitud a 4 caracteres (MMYY)
            const maxLength = 4;
            let formattedValue = '';
            
            // Formatear el valor como MM/YY
            if (value.length > maxLength) {
                value = value.substring(0, maxLength);
            }
            
            // Validar el mes
            if (value.length >= 2) {
                const month = value.substring(0, 2);
                if (parseInt(month) > 12) {
                    formattedValue = '12/';
                } else {
                    formattedValue = month + '/';
                }
            } else if (value.length === 1) {
                formattedValue = value + '/';
            } else {
                formattedValue = value;
            }

            // Añadir el año si está presente
            if (value.length > 2) {
                formattedValue += value.substring(2, 4);
            }

            // Actualizar el input con el valor formateado
            e.target.value = formattedValue;
        });

        const input = document.getElementById('cvc');

        input.addEventListener('input', function() {
            if (this.value.length > 3) {
                this.value = this.value.slice(0, 3);
            }
        });

    </script>
</body>
</html>
