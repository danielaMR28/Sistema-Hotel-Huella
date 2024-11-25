<?php
session_start();

// Conexión a la base de datos
$connect = mysqli_connect('db', 'php_docker', 'password', 'php_docker');

// Edición de reservaciones
if (isset($_POST['editReservation'])) {
    $id = mysqli_real_escape_string($connect, $_POST['reservation_id']);
    $start_date = mysqli_real_escape_string($connect, $_POST['arrive-date']);
    $finish_date = mysqli_real_escape_string($connect, $_POST['departure-date']);

    // Query para actualizar reservaciones
    $update_query = "UPDATE `reservations` SET `start_date`='$start_date', `finish_date`='$finish_date' WHERE id='$id'";
    if($id == null) {
        echo("Variable nula");
    }
    mysqli_query($connect, $update_query);
}

// Eliminación de habitaciones
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete_query = "DELETE FROM reservations WHERE id = '$id'";
    mysqli_query($connect, $delete_query);
}

// Número de habitaciones por página
$rooms_per_page = 10;

// Página actual (si no se envía, asume que es la primera página)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calcular el desplazamiento (OFFSET) para la consulta
$offset = ($page - 1) * $rooms_per_page;

// Consulta para contar el número total de habitaciones
$total_query = "SELECT COUNT(*) as total FROM rooms";
$total_result = mysqli_query($connect, $total_query);
$total_rooms = mysqli_fetch_assoc($total_result)['total'];

// Calcular el número total de páginas
$total_pages = ceil($total_rooms / $rooms_per_page);

// Consulta para obtener las habitaciones para la página actual
$query = "SELECT r.id AS r_id, r.*, u.name AS name, u.last_name AS last_name, ro.room_number, ro.type AS room_type, ro.available
          FROM reservations r
          JOIN users u ON r.id_user = u.id
          JOIN rooms ro ON r.id_room = ro.room_number
          LIMIT $rooms_per_page OFFSET $offset";
$response = mysqli_query($connect, $query);
?>

<!-- HTML de la pagina -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Reservaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Iconos -->
    <script src="https://kit.fontawesome.com/a6992b7fd0.js" crossorigin="anonymous"></script>
    <!-- Estilos -->
    <link rel="stylesheet" href="../styles/main.css">
    <link rel="stylesheet" href="./styles/roomsControl.css">
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

    <main>
        <section>
            <div>
                <i class="fa-regular fa-calendar blue-icon"></i>
                <p>Control de Reservaciones</p>
            </div>
        </section>

        <section class="table--container">
            <?php
                if (mysqli_num_rows($response) > 0) {
                    echo '<table class="room-table">';
                    echo '<thead><tr><th>Identificador de la reservación</th><th>Nombre del Huesped</th><th>Fecha de Llegada</th><th>Fecha de Salida</th><th>Habitación</th><th>Precio</th><th>Acciones</th></tr></thead>';
                    echo '<tbody>';
                    while ($row = mysqli_fetch_assoc($response)) {
                        echo '<tr>';
                        echo '<td>' . $row['r_id'] . '</td>';
                        echo '<td>' . $row['name'] . ' ' . $row['last_name'] . '</td>';
                        echo '<td>' . $row['start_date'] . '</td>';
                        echo '<td>' . $row['finish_date'] . '</td>';
                        echo '<td>' . $row['room_number'] . '</td>';
                        echo '<td>$' . $row['price'] . '.00</td>';
                        echo '<td>';
                        echo '<button class="edit-button" data-bs-toggle="modal" data-bs-target="#editModal" 
                            data-id="' . $row['r_id'] . '" 
                            data-arrive-date="' . $row['start_date'] . '" 
                            data-departure-date="' . $row['finish_date'] . '">
                            <i class="fa-solid fa-pen-to-square"></i>
                            </button>';

                        echo ' <a href="?delete=' . $row['r_id'] . '" class="delete-button" onclick="return confirm(\'¿Estás seguro de que deseas eliminar esta reservación?\')"><i class="fa-solid fa-trash"></i></a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                } else {
                    echo "<div class='alert' role='alert'><p>No se encontraron reservaciones</p><p class='alert--sub-heading'>Intente cambiar los filtros o agregar una reservación</p></div>";
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
        </section>
    </main>


    <!-- Modal para editar reservaciones -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title title-modal" id="editModalLabel">Editar Reservación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form method="POST">
                    <input type="hidden" name="reservation_id" id="edit-id">
                    <div class="input--container">
                        <label for="arrive-date" class="form-label">Fecha de llegada:</label>
                        <input type="date" name="arrive-date" id="arrive-date" required class="form-control"/>
                    </div>
                    <div class="input--container">
                        <label for="departure-date" class="form-label">Fecha de Salida:</label>
                        <input type="date" name="departure-date" id="departure-date" required class="form-control"/>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="editReservation" class="primary-button">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="button-container">
        <a class="primary-link" href="admin.php">Volver a la página principal</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var roomModal = new bootstrap.Modal(document.getElementById('roomModal'));

        function showModal() {
            roomModal.show();
        }

        var editModal = new bootstrap.Modal(document.getElementById('editModal'));

        document.getElementById('editModal').addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget; // Botón que disparó el modal
            var id = button.getAttribute('data-id');
            var arriveDate = button.getAttribute('data-arrive-date');
            var departureDate = button.getAttribute('data-departure-date');

            // Validar que los datos existan antes de asignarlos
            if (id) document.getElementById('edit-id').value = id;
            if (arriveDate) document.getElementById('arrive-date').value = arriveDate;
            if (departureDate) document.getElementById('departure-date').value = departureDate;
        });


    </script>
</body>
</html>
