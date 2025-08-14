<?php
// Configuración de la base de datos
$host = "localhost";
$port = 3306;
$user = "root";
$pass = "";
$database = "db_test";

// Establecer conexión
$conn = new mysqli($host, $user, $pass, $database, $port);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// Obtener parámetros
$id_eje = isset($_GET['id_eje']) ? (int)$_GET['id_eje'] : null;
$id_eje_recursivo = isset($_GET['id_eje_recursivo']) ? (int)$_GET['id_eje_recursivo'] : null;
$id_pla = isset($_GET['id_pla']) ? (int)$_GET['id_pla'] : null;
$fecha_inicio = isset($_GET['fecha_inicio']) && !empty($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
$fecha_fin = isset($_GET['fecha_fin']) && !empty($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;

// Consulta para obtener citas
$query = "SELECT c.*, e.nom_eje as nombre_ejecutivo, p.nom_pla as nombre_plantel 
          FROM citas c
          JOIN ejecutivo e ON c.id_eje = e.id_eje
          JOIN plantel p ON c.id_pla = p.id_pla
          WHERE ";

if ($id_eje) {
    $query .= "c.id_eje = $id_eje";
} elseif ($id_eje_recursivo) {
    $query .= "FIND_IN_SET($id_eje_recursivo, ObtenerJerarquiaEjecutivos(c.id_eje)) > 0";
} elseif ($id_pla) {
    $query .= "c.id_pla = $id_pla";
}

if ($fecha_inicio && $fecha_fin) {
    $query .= " AND c.fec_cit BETWEEN '$fecha_inicio' AND '$fecha_fin'";
}

$query .= " ORDER BY c.fec_cit DESC";

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Citas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .badge.bg-purple {
            background-color: #6a0dad;
        }
        .card-header {
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>
                            <?php 
                            if ($id_eje) {
                                echo "Citas del Ejecutivo";
                            } elseif ($id_eje_recursivo) {
                                echo "Citas Recursivas del Ejecutivo y Subordinados";
                            } elseif ($id_pla) {
                                echo "Citas del Plantel";
                            }
                            ?>
                        </h5>
                        <a href="index.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Regresar
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if ($fecha_inicio && $fecha_fin): ?>
                        <div class="alert alert-info mb-4">
                            Mostrando citas entre <?php echo date('d/m/Y', strtotime($fecha_inicio)); ?> y <?php echo date('d/m/Y', strtotime($fecha_fin)); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Fecha</th>
                                        <th>Ejecutivo</th>
                                        <th>Plantel</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id_cit']; ?></td>
                                        <td><?php echo htmlspecialchars($row['nom_cit']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($row['fec_cit'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['nombre_ejecutivo']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nombre_plantel']); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-warning">
                            No se encontraron citas con los criterios seleccionados.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>