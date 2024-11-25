<?php
session_start();

// Precios de habitaciones
$single_room = 400;
$double_room = 600;

// Conexión a la base de datos
$connect = mysqli_connect('db', 'php_docker', 'password', 'php_docker');

// Edición de habitaciones
if (isset($_POST['editClient'])) {
    // Obtencion de los datos del formulario
    $id = intval($_POST['room_number']);
    $name = mysqli_real_escape_string($connect, $_POST['name']);
    $last_name = mysqli_real_escape_string($connect, $_POST['last_name']);
    $email = mysqli_real_escape_string($connect, $_POST['email']);
    $phone = mysqli_real_escape_string($connect, $_POST['phone']);

    // Query para actualizar habitaciones
    $update_query = "UPDATE users SET name = '$name', last_name = '$last_name', email = '$email', phone = '$phone' WHERE id = $id";
    mysqli_query($connect, $update_query);
}

// Eliminación de habitaciones
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Query para borrar habitaciones
    $delete_query = "DELETE FROM users WHERE id = $id";
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
$query = "SELECT * FROM users LIMIT $rooms_per_page OFFSET $offset";
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
            <i class="fa-regular fa-user blue-icon"></i>
                <p>Control de Clientes</p>
            </div>
        </section>

        <section class="table--container">
                <?php
                    // Si hay habitaciones se muestra la tabla completa
                    if (mysqli_num_rows($response) > 0) {
                        echo '<table class="room-table">';
                        echo '<thead><tr><th>ID del cliente</th><th>Nombre</th><th>Apellido</th><th>Email</th><th>Teléfono</th><th>Acciones</th></tr></thead>';
                        echo '<tbody>';
                        while ($row = mysqli_fetch_assoc($response)) {
                            echo '<tr>';
                            echo '<td>' . $row['id'] . '</td>'; 
                            echo '<td>' . ($row['name']) . '</td>';
                            echo '<td> ' . $row['last_name'] . '</td>';
                            echo '<td> ' . ($row['email']) . '</td>';
                            echo '<td> ' . ($row['phone']) . '</td>';
                            echo '<td>';
                            echo '<button class="edit-button" data-bs-toggle="modal" data-bs-target="#editModal" data-id="' . $row['id'] . '" data-name="'. $row['name'] . '" data-name="' . $row['last_name'] . '" data-details="' . $row['email'] . '"><i class="fa-solid fa-pen-to-square"></i></button>';
                            echo ' <a href="?delete=' . $row['id'] . '" class="delete-button" onclick="return confirm(\'¿Estás seguro de que deseas eliminar este cliente?\')"><i class="fa-solid fa-trash"></i></a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table>';
                    } else {
                        // Si no hay habitaciones se muestra la alerta
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
            </div>
        </section>
    </main>

    <!-- Modal para crear cliente -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title title-modal" id="editModalLabel">Editar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form method="POST">
                    <div>
                        <input type="hidden" name="room_number" id="edit-id">
                        <div class="input--container">
                            <label for="name" class="form-label">Nombre:</label>
                            <input type="text" name="name" id="name" required />
                        </div>
                        <div class="input--container">
                            <label for="last_name" class="form-label">Apellido:</label>
                            <input type="text" name="last_name" id="last_name" required />
                        </div>
                        <div class="input--container">
                            <label for="email" class="form-label">Correo Electrónico:</label>
                            <input type="text" name="email" id="email" required />
                        </div>
                        <div class="input--container">
                            <label for="phone" class="form-label">Teléfono:</label>
                            <input type="phone" name="phone" id="phone" required />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="editClient" class="primary-button">Guardar Cambios</button>
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

            document.getElementById('edit-id').value = id;
        });
    </script>
</body>
</html>
