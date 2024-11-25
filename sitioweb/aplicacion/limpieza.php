<?php
// Conexión a la base de datos
$conn = mysqli_connect('db', 'php_docker', 'password', 'php_docker');

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Manejar la actualización del estado de limpieza
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['clean'])) {
    $id_room = intval($_POST['id_room']);
    $cleaning_status = intval($_POST['cleaning_status']); // Convertir a entero

    // Actualizar el estado de limpieza
    $sql_update_cleaning = "UPDATE cleaning SET cleaning_status = ? WHERE room_number = ?";
    $stmt = $conn->prepare($sql_update_cleaning);
    $stmt->bind_param("ii", $cleaning_status, $id_room);
    $stmt->execute();
    
    $stmt->close();
}

// Lógica de paginación para mostrar las habitaciones
$rooms_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $rooms_per_page;

// Query para contar el número total de habitaciones
$total_query = "SELECT COUNT(*) as total FROM rooms";
$total_result = mysqli_query($conn, $total_query);
$total_rooms = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_rooms / $rooms_per_page);

// Query para obtener las habitaciones para la página actual
$query = "SELECT * FROM cleaning LIMIT $rooms_per_page OFFSET $offset";
$result_rooms = mysqli_query($conn, $query);
?>

<!-- HTML de la página -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Limpieza</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a6992b7fd0.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../styles/main.css">
    <link rel="stylesheet" href="./styles/cleaning.css">
</head>
<body>
    <section>
        <section class="menu">
            <header>
                <p>Digital Stay - Hotel Limpieza</p>
            </header>
        </section>
    </section>
    <main>
        <section>
            <div>
                <i class="fa-solid fa-broom blue-icon"></i>
                <p>Control de Habitaciones</p>
            </div>
        </section>

        <section class="table--container">
            <?php
            // Si hay habitaciones se muestra la tabla completa
            if (mysqli_num_rows($result_rooms) > 0) {
                echo '<table class="table room-table">';
                echo '<thead><tr><th>Número de habitación</th><th>Estatus</th></tr></thead>';
                echo '<tbody>';
                while ($row = mysqli_fetch_assoc($result_rooms)) {
                    echo '<tr>';
                    echo '<td>' . $row['room_number'] . '</td>';  
                    echo '<td>';
                    echo '<form method="POST" style="display:inline;">';
                    echo '<input type="hidden" name="id_room" value="' . $row['room_number'] . '">';
                    echo '<select name="cleaning_status">';
                    echo '<option value="0" ' . ($row['cleaning_status'] == 0 ? 'selected' : '') . '>Limpia</option>';
                    echo '<option value="1" ' . ($row['cleaning_status'] == 1 ? 'selected' : '') . '>En Limpieza</option>';
                    echo '<option value="2" ' . ($row['cleaning_status'] == 2 ? 'selected' : '') . '>Sucio</option>';
                    echo '</select>';
                    echo '<button type="submit" name="clean" class="clean-button">Actualizar Estado</button>';
                    echo '</form>';
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo "<div class='alert alert-warning' role='alert'><p>No se encontraron habitaciones</p><p class='alert--sub-heading'>Intente cambiar los filtros o agregar una habitación</p></div>";
            }

            // Mostrar paginación
            echo '<nav aria-label="Page navigation">';
            echo '<ul class="pagination">';

            // Botón "Anterior"
            if ($page > 1) {
                echo '<li class="page-item"><a class="page-link" href="?page=' . ($page - 1) . '">Anterior</a></li>';
            }

            // Botones numéricos de las páginas
            for ($i = 1; $i <= $total_pages; $i++) {
                echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '">';
                echo '<a class="page-link" href="?page=' . $i . '">' . $i . '</a>';
                echo '</li>';
            }

            // Botón "Siguiente"
            if ($page < $total_pages) {
                echo '<li class="page-item"><a class="page-link" href="?page=' . ($page + 1) . '">Siguiente</a></li>';
            }

            echo '</ul>';
            echo '</nav>';
            ?>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>