<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración</title>
    <!-- Iconos -->
    <script src="https://kit.fontawesome.com/a6992b7fd0.js" crossorigin="anonymous"></script>
    <!-- Estilos -->
    <link rel="stylesheet" href="../styles/main.css">
    <link rel="stylesheet" href="../styles/admin.css">
</head>
<body>
    <section>
        <section class="menu">
            <header>
                <p>Digital Stay - Hotel Admin</p>
            </header>

            <section>
                <div class="menu-item--container">
                    <i class="fa-solid fa-bed"></i>
                    <a href="roomsControl.php">Habitaciones</a>
                </div>
                <div class="menu-item--container">
                    <i class="fa-regular fa-calendar"></i>
                    <a href="reservationsControl.php">Reservaciones</a>
                </div>
                <div class="menu-item--container">
                    <i class="fa-regular fa-user"></i>
                    <a href="clientsControl.php">Clientes</a>
                </div>
            </section>
        </section>
    </section>
    
    <section class="top--container">
        <p>Panel de Administración</p>
    </section>

    <section class="links--container">
        <section class="link-item--container">
            <div>
                <i class="fa-solid fa-bed"></i>
                <p>Habitaciones</p>
            </div>
            <p class="section-description">Gestionar habitaciones y su estado</p>
            <p class="section-sub-description">Acceder a todas las funciones de habitaciones.</p>
            <a href="roomsControl.php" class="link-item">Ir a Habitaciones</a>
        </section>
        <section class="link-item--container">
            <div>
                <i class="fa-regular fa-calendar"></i>
                <p>Reservaciones</p>
            </div>
            <p class="section-description">Ver y administrar reservaciones</p>
            <p class="section-sub-description">Acceder a todas las funciones de reservaciones.</p>
            <a href="reservationsControl.php" class="link-item">Ir a Reservaciones</a>
        </section>
        <section class="link-item--container">
            <div>
                <i class="fa-regular fa-user"></i>
                <p>Clientes</p>
            </div>
            <p class="section-description">Información y gestión de clientes</p>
            <p class="section-sub-description">Acceder a todas las funciones de clientes.</p>
            <a href="clientsControl.php" class="link-item">Ir a Clientes</a>
        </section>
        
    </section>
</body>
</html>