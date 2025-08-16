<?php
// Conexión a la base de datos
$host = "localhost";
$user = "root";
$pass = "";
$database = "db_test";
$port = 3307;

$conn = new mysqli($host, $user, $pass, $database, $port);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// Obtener parámetros
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$id_eje = isset($_GET['id_eje']) ? (int)$_GET['id_eje'] : 0;
$id_pla = isset($_GET['id_pla']) ? (int)$_GET['id_pla'] : 0;
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
$search_term = isset($_GET['search_term']) ? $_GET['search_term'] : '';
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'fecha_desc';

$where_fechas = '';
if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    $where_fechas = " AND fec_cit BETWEEN '$fecha_inicio' AND '$fecha_fin'";
}

// Función para obtener IDs de descendientes
function obtenerDescendientes($conn, $id_eje) {
    $ids = array($id_eje);
    $query = "SELECT id_eje FROM ejecutivo WHERE id_padre = $id_eje AND eli_eje = 1";
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        $hijos = obtenerDescendientes($conn, $row['id_eje']);
        $ids = array_merge($ids, $hijos);
    }
    return $ids;
}

// Construir consulta base según tipo
if ($tipo === 'particular') {
    $sql = "SELECT c.*, e.nom_eje as nombre_ejecutivo, p.nom_pla as nombre_plantel 
            FROM citas c
            JOIN ejecutivo e ON c.id_eje = e.id_eje
            JOIN plantel p ON c.id_pla = p.id_pla
            WHERE c.id_eje = $id_eje $where_fechas";
    $titulo = "Citas del Ejecutivo";
} 
elseif ($tipo === 'recursivo') {
    $ids = obtenerDescendientes($conn, $id_eje);
    $lista_ids = implode(',', $ids);
    $sql = "SELECT c.*, e.nom_eje as nombre_ejecutivo, p.nom_pla as nombre_plantel 
            FROM citas c
            JOIN ejecutivo e ON c.id_eje = e.id_eje
            JOIN plantel p ON c.id_pla = p.id_pla
            WHERE c.id_eje IN ($lista_ids) $where_fechas";
    $titulo = "Citas Recursivas del Ejecutivo y Subordinados";
} 
elseif ($tipo === 'plantel') {
    $sql = "SELECT c.*, e.nom_eje as nombre_ejecutivo, p.nom_pla as nombre_plantel 
            FROM citas c
            JOIN ejecutivo e ON c.id_eje = e.id_eje
            JOIN plantel p ON c.id_pla = p.id_pla
            WHERE c.id_pla = $id_pla $where_fechas";
    $titulo = "Citas del Plantel";
} 
else {
    die("Tipo de consulta no válido");
}

// Añadir búsqueda por texto si existe
if (!empty($search_term)) {
    $search_term = $conn->real_escape_string($search_term);
    $sql .= " AND (c.nom_cit LIKE '%$search_term%' OR e.nom_eje LIKE '%$search_term%' OR p.nom_pla LIKE '%$search_term%' OR c.id_cit LIKE '%$search_term%')";
}

// Añadir ordenación
switch ($orden) {
    case 'fecha_asc':
        $sql .= " ORDER BY c.fec_cit ASC";
        break;
    case 'nombre_asc':
        $sql .= " ORDER BY c.nom_cit ASC";
        break;
    case 'nombre_desc':
        $sql .= " ORDER BY c.nom_cit DESC";
        break;
    default:
        $sql .= " ORDER BY c.fec_cit DESC";
}

// Ejecutar consulta
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Citas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6D4C41;
            --secondary-color: #8D6E63;
            --light-color: #D7CCC8;
            --background-color: #EFEBE9;
            --text-dark: #3E2723;
            --text-light: #FFFFFF;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-color);
            padding: 20px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            background-color: white;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: var(--text-light);
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
            padding: 12px 20px;
        }
        
        .badge.bg-purple {
            background-color: #6a0dad;
        }
        
        .btn-outline-secondary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-secondary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(109, 76, 65, 0.1);
        }
        
        /* Estilos para el buscador */
        .search-container {
            margin-bottom: 25px;
            position: relative;
        }
        
        .search-box {
            border: 2px solid var(--light-color);
            border-radius: 30px;
            padding: 12px 20px 12px 45px;
            width: 100%;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .search-box:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 2px 15px rgba(109, 76, 65, 0.2);
        }
        
        .search-icon {
            position: absolute;
            left: 18px;
            top: 14px;
            color: var(--secondary-color);
            font-size: 18px;
        }
        
        .search-btn {
            position: absolute;
            right: 5px;
            top: 5px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 7px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .search-btn:hover {
            background-color: var(--secondary-color);
        }
        
        .filter-options {
            margin-top: 15px;
        }
        
        .filter-group {
            margin-bottom: 15px;
        }
        
        .filter-label {
            display: block;
            margin-bottom: 5px;
            color: var(--text-dark);
            font-weight: 500;
        }
        
        .filter-select, .date-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--light-color);
            border-radius: 6px;
            background-color: white;
        }
        
        @media (min-width: 768px) {
            .filter-options {
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
            }
            
            .filter-group {
                flex: 1;
                min-width: 200px;
                margin-bottom: 0;
            }
            
            .date-inputs {
                display: flex;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <!-- Buscador -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-search me-2"></i> Buscar Citas
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <input type="hidden" name="tipo" value="<?php echo htmlspecialchars($tipo); ?>">
                            <input type="hidden" name="id_eje" value="<?php echo $id_eje; ?>">
                            <input type="hidden" name="id_pla" value="<?php echo $id_pla; ?>">
                            
                            <div class="search-container">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" class="search-box" name="search_term" placeholder="Buscar por nombre, ejecutivo o plantel..." 
                                       value="<?php echo htmlspecialchars($search_term); ?>">
                                <button type="submit" class="search-btn">Buscar</button>
                            </div>
                            
                            <!-- Busquedas por filtro -->
                            <div class="filter-options">
                                <div class="filter-group">
                                    <label class="filter-label">Fecha desde:</label>
                                    <input type="date" class="date-input" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
                                </div>
                                
                                <div class="filter-group">
                                    <label class="filter-label">Fecha hasta:</label>
                                    <input type="date" class="date-input" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>">
                                </div>
                                
                                <div class="filter-group">
                                    <label class="filter-label">Ordenar por:</label>
                                    <select class="filter-select" name="orden">
                                        <option value="fecha_desc" <?php echo ($orden == 'fecha_desc') ? 'selected' : ''; ?>>Fecha (más reciente primero)</option>
                                        <option value="fecha_asc" <?php echo ($orden == 'fecha_asc') ? 'selected' : ''; ?>>Fecha (más antigua primero)</option>
                                        <option value="nombre_asc" <?php echo ($orden == 'nombre_asc') ? 'selected' : ''; ?>>Nombre (A-Z)</option>
                                        <option value="nombre_desc" <?php echo ($orden == 'nombre_desc') ? 'selected' : ''; ?>>Nombre (Z-A)</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Resultados -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>
                            <?php echo $titulo; ?>
                        </h5>
                        <a href="index.php" class="btn btn-sm btn-outline-light">
                            <i class="fas fa-arrow-left me-1"></i> Regresar
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($fecha_inicio) || !empty($fecha_fin) || !empty($search_term)): ?>
                            <div class="alert alert-info mb-4">
                                <i class="fas fa-filter me-2"></i>
                                Filtros aplicados: 
                                <?php if (!empty($fecha_inicio) && !empty($fecha_fin)): ?>
                                    <span class="badge bg-secondary"><?php echo date('d/m/Y', strtotime($fecha_inicio)); ?> al <?php echo date('d/m/Y', strtotime($fecha_fin)); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($search_term)): ?>
                                    <span class="badge bg-secondary">Búsqueda: "<?php echo htmlspecialchars($search_term); ?>"</span>
                                <?php endif; ?>
                                <a href="?tipo=<?php echo urlencode($tipo); ?>&id_eje=<?php echo $id_eje; ?>&id_pla=<?php echo $id_pla; ?>" class="ms-2 text-decoration-none">Limpiar filtros</a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
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
                                        <?php while ($row = $result->fetch_assoc()): ?>
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
                                <i class="fas fa-exclamation-triangle me-2"></i>
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
