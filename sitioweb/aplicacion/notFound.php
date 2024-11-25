<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservación no encontrada</title>
    <!-- Estilos -->
    <link rel="stylesheet" href="./styles/notFound.css">
    <!-- Fuentes utilizadas -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
</head>
<body>
    <main id="main--container">
        <img src="./error.png" alt="Error">
        <h1>Reservación no encontrada</h1>
        <p>Lo sentimos, no pudimos encontrar una reservación con los datos proporcionados. Por favor, verifique su información e intente de nuevo.</p>

        <form action="" method="POST">
            <div class="input--container">
                <label for="reservation_id" class="form-label">Identificador de la Reservación:</label>
                <input type="text" id="reservation_id" name="reservation_id" class="form-input" required>
            </div>
            <div class="input--container">
                <label for="reservation_last_name" class="form-label">Apellido de la Reservación:</label>
                <input type="text" id="reservation_last_name" name="reservation_last_name" class="form-input" required>
            </div>
            <button type="submit" name="search" class="primary-button">Buscar de nuevo</button>
        </form>

        <p>¿No tiene una reservación?</p>
        <a href="login.php" class="secondary-button">Crear nueva reservación</a>
    </main>
</body>
</html>