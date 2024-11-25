<?php
// Inicializar variables para mostrar resultados
$total_reservaciones = 0;
$total_precio = 0.0; // Cambiado a 0.0 para asegurar que es un float
$total_clientes = 0;
$ingresos_dia = []; // Array para almacenar ingresos por día
$fechas = []; // Array para almacenar las fechas
$ocupacion_actual = 0; // Variable para almacenar la ocupación actual
$porcentaje = 0; // Variable para almacenar el porcentaje de ocupación

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener el rango seleccionado
    $rango = intval($_POST['rango']);

    // Definir la consulta SQL en función del rango seleccionado
    switch ($rango) {
        case 7:
            $fecha_limite = "CURDATE() - INTERVAL 7 DAY";
            break;
        case 30:
            $fecha_limite = "CURDATE() - INTERVAL 30 DAY";
            break;
        case 90:
            $fecha_limite = "CURDATE() - INTERVAL 90 DAY";
            break;
        case 365:
            $fecha_limite = "CURDATE() - INTERVAL 1 YEAR";
            break;
        default:
            $fecha_limite = "CURDATE()"; // Por si acaso
            break;
    }

    // Conexión a la base de datos
    $conn = mysqli_connect('db', 'php_docker', 'password', 'php_docker');

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Crear la consulta SQL para reservaciones
    $sql_reservaciones = "SELECT COUNT(*) AS total_reservaciones, SUM(price) AS total_precio
                          FROM reservations
                          WHERE start_date >= $fecha_limite";

    // Ejecutar la consulta de reservaciones
    $result_reservaciones = $conn->query($sql_reservaciones);

    // Verificar y almacenar los resultados de reservaciones
    if ($result_reservaciones->num_rows > 0) {
        $row = $result_reservaciones->fetch_assoc();
        $total_reservaciones = $row['total_reservaciones'];
        $total_precio = $row['total_precio'] !== null ? (float)$row['total_precio'] : 0.0; // Asegurar que no sea null
    } else {
        $total_reservaciones = 0;
        $total_precio = 0.0; // Asegurar que sea un float
    }

    // Crear la consulta SQL para contar nuevos clientes
    $sql_clientes = "SELECT COUNT(*) AS total_clientes
                     FROM users";

    // Ejecutar la consulta de clientes
    $result_clientes = $conn->query($sql_clientes);

    // Verificar y almacenar los resultados de clientes
    if ($result_clientes->num_rows > 0) {
        $row = $result_clientes->fetch_assoc();
        $total_clientes = $row['total_clientes'];
    } else {
        $total_clientes = 0;
    }

    // Crear la consulta SQL para calcular ingresos por día
    $sql_ingresos_por_dia = "SELECT DATE(start_date) AS fecha, SUM(price) AS ingresos_dia
                              FROM reservations
                              WHERE start_date >= $fecha_limite
                              GROUP BY DATE(start_date)
                              ORDER BY DATE(start_date)";

    // Ejecutar la consulta de ingresos por día
    $result_ingresos = $conn->query($sql_ingresos_por_dia);

    // Almacenar los ingresos y las fechas
    if ($result_ingresos->num_rows > 0) {
        while ($row = $result_ingresos->fetch_assoc()) {
            $ingresos_dia[] = $row['ingresos_dia'] !== null ? (float)$row['ingresos_dia'] : 0.0; // Asegurar que no sea null
            $fechas[] = $row['fecha'];
        }
    }

    // Crear la consulta SQL para calcular la ocupación total
    $sql_ocupacion_total = "SELECT COUNT(DISTINCT id_room) AS ocupacion_total
                             FROM reservations
                             WHERE CURDATE() BETWEEN start_date AND finish_date";

    // Ejecutar la consulta de ocupación total
    $result_ocupacion = $conn->query($sql_ocupacion_total);

    // Verificar y almacenar el resultado de la ocupación total
    if ($result_ocupacion->num_rows > 0) {
        $row = $result_ocupacion->fetch_assoc();
        $ocupacion_actual = $row['ocupacion_total'];
    } else {
        $ocupacion_actual = 0;
    }

    // Número de habitaciones
    $sql_numero_habitaciones = "SELECT COUNT(*) AS total_habitaciones FROM rooms";

    // Ejecutar la consulta para obtener el número total de habitaciones
    $result_habitaciones = $conn->query($sql_numero_habitaciones);

    // Verificar y almacenar el resultado del número de habitaciones
    if ($result_habitaciones->num_rows > 0) {
        $row = $result_habitaciones->fetch_assoc();
        $total_habitaciones = $row['total_habitaciones'];
    } else {
        $total_habitaciones = 0;
    }

    // Calcular el porcentaje de ocupación
    if ($total_habitaciones > 0) {
        $porcentaje = round(($ocupacion_actual * 100) / $total_habitaciones, 2);
    } else {
        $porcentaje = 0; // Evitar división por cero
    }

    // Cerrar la conexión
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración</title>
    <!-- Iconos -->
    <script src ="https://kit.fontawesome.com/a6992b7fd0.js" crossorigin="anonymous"></script>
    <!-- Estilos -->
    <link rel="stylesheet" href="../styles/main.css">
    <link rel="stylesheet" href="../styles/admin.css">
    <link rel="stylesheet" href="../styles/reports.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <section>
        <section class="menu">
            <header>
                <p>Digital Stay - Hotel Gerencia</p>
            </header>
        </section>
    </section>
    
    <section class="top--container">
        <p>Panel de Administración</p>

        <form id="rangoForm" method="POST">
            <select name="rango" id="rango" onchange="document.getElementById('rangoForm').submit();">
                <option value="7" <?php if(isset($rango) && $rango == 7) echo 'selected'; ?>>Últimos 7 días</option>
                <option value="30" <?php if(isset($rango) && $rango == 30) echo 'selected'; ?>>Últimos 30 días</option>
                <option value="90" <?php if(isset($rango) && $rango == 90) echo 'selected'; ?>>Últimos 90 días</option>
                <option value="365" <?php if(isset($rango) && $rango == 365) echo 'selected'; ?>>Todo el año</option>
            </select>
        </form>
    </section>

    <section id="stats--container">
        <div class="small-item div1">
            <div>
                <p>Ingresos totales</p>
                <i class="fa-solid fa-dollar-sign"></i>
            </div>
            <div>
                <p class="data"><?php echo "$" . number_format($total_precio, 2); ?></p>
            </div>
        </div>
        <div class="small-item div2">
            <div>
                <p>Clientes totales</p>
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <div>
                <p class="data"><?php echo $total_clientes; ?></p>
            </div>
        </div>
        <div class="small-item div3">
            <div>
                <p>Reservaciones hechas</p>
                <i class="fa-regular fa-calendar"></i>
            </div>
            <div>
                <p class="data"><?php echo $total_reservaciones; ?></p>
            </div>
        </div>
        <div class="small-item div4">
            <div>
                <p>Ocupación actual</p>
                <i class="fa-solid fa-bed"></i>
            </div>
            <div>
                <p class="data"><?php echo $porcentaje; ?>%</p>
            </div>
        </div>
    </section>

    <div class="chart-container">
        <p>Ingresos por Día</p>
        <canvas id="ingresosPorDiaChart"></canvas>
    </div>

    <script>
        const ctx = document.getElementById('ingresosPorDiaChart').getContext('2d');
        const ingresosPorDiaChart = new Chart(ctx, {
            type: 'line', // Tipo de gráfico (puede ser 'bar', 'line', etc.)
            data: {
                labels: <?php echo json_encode($fechas); ?>, // Fechas
                datasets: [{
                    label: 'Ingresos por Día',
                    data: <?php echo json_encode($ingresos_dia); ?>, // Ingresos
                    borderColor: '#0284C7',
                    backgroundColor: '#0284C7', // Corregido
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>