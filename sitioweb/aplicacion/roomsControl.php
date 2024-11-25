<?php
session_start();

// Precios de habitaciones
$single_room = 400;
$double_room = 600;

// Conexión a la base de datos
$connect = mysqli_connect('db', 'php_docker', 'password', 'php_docker');

// Registro de habitaciones
if (isset($_POST['createRoom'])) {
    // Obtención de los datos del formulario
    $room_type = mysqli_real_escape_string($connect, $_POST['type']);

    // Se establece el precio dependiendo de la habitación
    $price = $room_type == 1 ? $single_room : $double_room;

    if ($room_type == 0) {
        echo "<script>alert('Seleccione el tipo de habitación.');</script>";
    } else {
        // Query para insertar habitaciones
        $insert_query = "INSERT INTO rooms (type, price) VALUES ('$room_type', '$price')";

        if (mysqli_query($connect, $insert_query)) {
            // Obtener el ID de la habitación recién creada
            $room_number = mysqli_insert_id($connect); // Asumiendo que room_number es el ID auto-incremental

            // Query para insertar en la tabla cleaning
            $cleaning_status = 1; // Asumiendo que 1 significa "En limpieza"
            $insert_cleaning_query = "INSERT INTO cleaning (room_number, cleaning_status) VALUES ('$room_number', '$cleaning_status')";

            if (mysqli_query($connect, $insert_cleaning_query)) {
                // Redireccionar después de la inserción exitosa
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "<div class='alert alert-danger' role='alert'>Error al crear el registro de limpieza: " . mysqli_error($connect) . "</div>";
            }
        } else {
            echo "<div class='alert alert-danger' role='alert'>Error al crear la habitación: " . mysqli_error($connect) . "</div>";
        }
    }
}

// Edición de habitaciones
if (isset($_POST['editRoom'])) {
    // Obtencion de los datos del formulario
    $id = intval($_POST['room_number']);
    $type = mysqli_real_escape_string($connect, $_POST['type']);
    $available = mysqli_real_escape_string($connect, $_POST['available']);
    $price = $type == 1 ? $single_room : $double_room;

    // Query para actualizar habitaciones
    $update_query = "UPDATE rooms SET type = '$type', available = '$available', price = '$price' WHERE room_number = $id";
    mysqli_query($connect, $update_query);
}

// Eliminación de habitaciones
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Primero, eliminar las reservas asociadas
    $delete_reservations_query = "DELETE FROM reservations WHERE id_room = $id";
    mysqli_query($connect, $delete_reservations_query);

    // Luego, eliminar la habitación
    $delete_query = "DELETE FROM rooms WHERE room_number = $id";
    mysqli_query($connect, $delete_query);
        
}

// Logica de paginacion para mostrar las habitaciones
// Número de habitaciones por página
$rooms_per_page = 10;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$offset = ($page - 1) * $rooms_per_page;

// Query para contar el número total de habitaciones
$total_query = "SELECT COUNT(*) as total FROM rooms";
$total_result = mysqli_query($connect, $total_query);
$total_rooms = mysqli_fetch_assoc($total_result)['total'];

$total_pages = ceil($total_rooms / $rooms_per_page);

// Query para obtener las habitaciones para la página actual
$query = "SELECT * FROM rooms LIMIT $rooms_per_page OFFSET $offset";
$response = mysqli_query($connect, $query);
?>

<!-- HTML de la pagina -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Habitaciones</title>
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
                <i class="fa-solid fa-bed blue-icon"></i>
                <p>Control de Habitaciones</p>
            </div>
            <button data-bs-toggle="modal" data-bs-target="#createModal" data-id="' . $row['room_number'] . '" data-name="'. $row['room_number'] . '" data-name="' . $row['type'] . '" data-details="' . $row['available'] . '">
                <i class="fa-solid fa-plus"></i>
                <p>Agregar Habitación</p>
            </button>
        </section>

        <section class="table--container">
                <?php
                    // Si hay habitaciones se muestra la tabla completa
                    if (mysqli_num_rows($response) > 0) {
                        echo '<table class="room-table">';
                        echo '<thead><tr><th>Número de Habitación</th><th>Tipo</th><th>Precio</th><th>Disponible</th><th>Acciones</th></tr></thead>';
                        echo '<tbody>';
                        while ($row = mysqli_fetch_assoc($response)) {
                            echo '<tr>';
                            echo '<td>' . $row['room_number'] . '</td>'; 
                            echo '<td>' . ($row['type'] == 1 ? 'Sencilla' : 'Doble') . '</td>';
                            echo '<td> $' . $row['price'] . '</td>';
                            echo '<td> ' . ($row['available'] == 1 ? 'Disponible' : 'Ocupada') . '</td>';
                            echo '<td>';
                            echo '<button class="edit-button" data-bs-toggle="modal" data-bs-target="#editModal" data-id="' . $row['room_number'] . '" data-name="'. $row['room_number'] . '" data-name="' . $row['type'] . '" data-details="' . $row['available'] . '"><i class="fa-solid fa-pen-to-square"></i></button>';
                            echo ' <a href="?delete=' . $row['room_number'] . '" class="delete-button" onclick="return confirm(\'¿Estás seguro de que deseas eliminar esta habitación?\')"><i class="fa-solid fa-trash"></i></a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table>';
                    } else {
                        // Si no hay habitaciones se muestra la alerta
                        echo "<div class='alert' role='alert'><p>No se encontraron habitaciones</p><p class='alert--sub-heading'>Intente cambiar los filtros o agregar una habitación</p></div>";
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

    <!-- Modal para editar habitación -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title title-modal" id="editModalLabel">Crear Habitación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form method="POST">
                    <div>
                        <input type="hidden" name="room_number" id="edit-id">
                        <div class="input--container">
                            <label for="edit-room_type" class="form-label">Tipo de habitación:</label>
                            <select name="type" id="edit-room_type" required class="form-control">
                                <option value="1">Sencilla</option>
                                <option value="2">Doble</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="createRoom" class="primary-button">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para editar habitación -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title title-modal" id="editModalLabel">Editar Habitación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form method="POST">
                    <div>
                        <input type="hidden" name="room_number" id="edit-id">
                        <div class="input--container">
                            <label for="edit-room_type" class="form-label">Tipo de habitación:</label>
                            <select name="type" id="edit-room_type" required class="form-control">
                                <option value="1">Sencilla</option>
                                <option value="2">Doble</option>
                            </select>
                        </div>
                        <div class="input--container">
                            <label for="edit-available" class="form-label">Estado:</label>
                            <select name="available" id="edit-available" required class="form-control">
                                <option value="1">Disponible</option>
                                <option value="0">Ocupada</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="editRoom" class="primary-button">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        

        var editModal = new bootstrap.Modal(document.getElementById('editModal'));

        document.getElementById('editModal').addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var type = button.getAttribute('data-name');
            var available = button.getAttribute('data-details');

            document.getElementById('edit-id').value = id;
            document.getElementById('edit-room_type').value = type;
            document.getElementById('edit-available').value = available;
        });
    </script>
</body>
</html>
