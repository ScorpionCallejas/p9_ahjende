<?php
// Configuración de la base de datos
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$pass = "";
$database = "db_test";
$port = 3307;

// Establecer conexión
$conn = new mysqli($host, $user, $pass, $database, $port);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// Función para obtener jerarquía de ejecutivos (todos los hijos)
function getJerarquiaEjecutivos($conn, $id_eje) {
    $ids = array();
    $query = "SELECT id_eje FROM ejecutivo WHERE id_padre = ? AND eli_eje = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_eje);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $ids[] = $row['id_eje'];
        $hijos = getJerarquiaEjecutivos($conn, $row['id_eje']);
        $ids = array_merge($ids, $hijos);
    }
    $stmt->close();
    
    return $ids;
}

// Función para obtener conteo acumulado (ejecutivo + todos los hijos)
function getConteoAcumulado($conn, $id_eje, $fecha_inicio, $fecha_fin) {
    $conteo = 0;
    
    // Obtener conteo individual del ejecutivo
    $query = "SELECT COUNT(*) as total FROM citas c WHERE c.id_eje = ?";
    if ($fecha_inicio && $fecha_fin) {
        $query .= " AND c.fec_cit BETWEEN ? AND ?";
    }
    
    $stmt = $conn->prepare($query);
    if ($fecha_inicio && $fecha_fin) {
        $stmt->bind_param("iss", $id_eje, $fecha_inicio, $fecha_fin);
    } else {
        $stmt->bind_param("i", $id_eje);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $row = $result->fetch_assoc();
        $conteo = $row['total'];
    }
    $stmt->close();
    
    // Obtener todos los hijos recursivamente
    $hijos_ids = getJerarquiaEjecutivos($conn, $id_eje);
    
    if (!empty($hijos_ids)) {
        $placeholders = implode(',', array_fill(0, count($hijos_ids), '?'));
        $query = "SELECT COUNT(*) as total FROM citas c WHERE c.id_eje IN ($placeholders)";
        if ($fecha_inicio && $fecha_fin) {
            $query .= " AND c.fec_cit BETWEEN ? AND ?";
        }
        
        $stmt = $conn->prepare($query);
        
        // Bind parameters
        $types = str_repeat('i', count($hijos_ids));
        $params = $hijos_ids;
        
        if ($fecha_inicio && $fecha_fin) {
            $types .= 'ss';
            $params[] = $fecha_inicio;
            $params[] = $fecha_fin;
        }
        
        $bind_params = array();
        $bind_params[] = &$types;
        for ($i=0; $i<count($params); $i++) {
            $bind_params[] = &$params[$i];
        }
        
        call_user_func_array(array($stmt, 'bind_param'), $bind_params);
        
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $row = $result->fetch_assoc();
            $conteo += $row['total'];
        }
        $stmt->close();
    }
    
    return $conteo;
}

// Función para obtener conteo de plantel
function getConteoPlantel($conn, $id_pla, $fecha_inicio, $fecha_fin) {
    $query = "SELECT COUNT(*) as total 
              FROM citas c 
              WHERE c.id_pla = ?";
    
    if ($fecha_inicio && $fecha_fin) {
        $query .= " AND c.fec_cit BETWEEN ? AND ?";
    }
    
    $stmt = $conn->prepare($query);
    
    if ($fecha_inicio && $fecha_fin) {
        $stmt->bind_param("iss", $id_pla, $fecha_inicio, $fecha_fin);
    } else {
        $stmt->bind_param("i", $id_pla);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $total = 0;
    if ($result) {
        $row = $result->fetch_assoc();
        $total = $row['total'];
    }
    $stmt->close();
    
    return $total;
}

// Función para construir árbol jerárquico
function construirArbolEjecutivos($conn, $id_padre = null, $fecha_inicio, $fecha_fin) {
    $arbol = array();
    $query = "SELECT e.id_eje, e.nom_eje, e.id_padre 
              FROM ejecutivo e 
              WHERE e.eli_eje = 1 AND e.id_padre " . ($id_padre ? "= ?" : "IS NULL") . "
              ORDER BY e.nom_eje";
    
    $stmt = $conn->prepare($query);
    if ($id_padre) {
        $stmt->bind_param("i", $id_padre);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Obtener conteos
        $conteo_individual = 0;
        $query_count_individual = "SELECT COUNT(*) as total FROM citas c WHERE c.id_eje = ?";
        if ($fecha_inicio && $fecha_fin) {
            $query_count_individual .= " AND c.fec_cit BETWEEN ? AND ?";
        }
        
        $stmt_individual = $conn->prepare($query_count_individual);
        if ($fecha_inicio && $fecha_fin) {
            $stmt_individual->bind_param("iss", $row['id_eje'], $fecha_inicio, $fecha_fin);
        } else {
            $stmt_individual->bind_param("i", $row['id_eje']);
        }
        $stmt_individual->execute();
        $result_individual = $stmt_individual->get_result();
        if ($result_individual) {
            $row_individual = $result_individual->fetch_assoc();
            $conteo_individual = $row_individual['total'];
        }
        $stmt_individual->close();
        
        $conteo_acumulado = getConteoAcumulado($conn, $row['id_eje'], $fecha_inicio, $fecha_fin);
        
        // Obtener planteles del ejecutivo
        $planteles_eje = array();
        $query_pla = "SELECT id_pla FROM ejecutivo_plantel WHERE id_eje = ".$row['id_eje'];
        $result_pla = $conn->query($query_pla);
        while($row_pla = $result_pla->fetch_assoc()) {
            $planteles_eje[] = $row_pla['id_pla'];
        }
        
        // Obtener hijos recursivamente
        $hijos = construirArbolEjecutivos($conn, $row['id_eje'], $fecha_inicio, $fecha_fin);
        
        $ejecutivo = array(
            'id' => 'eje_'.$row['id_eje'],
            'text' => $row['nom_eje'].
                " <a href='citas.php?tipo=particular&id_eje=".$row['id_eje']."&fecha_inicio=$fecha_inicio&fecha_fin=$fecha_fin' class='badge badge-light'>".$conteo_individual."</a>".
                " <a href='citas.php?tipo=recursivo&id_eje=".$row['id_eje']."&fecha_inicio=$fecha_inicio&fecha_fin=$fecha_fin' class='badge rounded-pill bg-purple text-white'>".$conteo_acumulado."</a>",
            'original_id' => $row['id_eje'],
            'icon' => 'fas fa-user',
            'type' => 'eje',
            'children' => $hijos,
            'planteles' => $planteles_eje
        );
        
        $arbol[] = $ejecutivo;
    }
    $stmt->close();
    
    return $arbol;
}

// Manejo de acciones
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    $action = $_GET['action'];
    $fecha_inicio = isset($_GET['fecha_inicio']) && !empty($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
    $fecha_fin = isset($_GET['fecha_fin']) && !empty($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
    
    // Validar fechas
    if ($fecha_inicio && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_inicio)) {
        echo json_encode(array('error' => 'Formato de fecha inicio inválido'));
        exit;
    }

    if ($fecha_fin && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin)) {
        echo json_encode(array('error' => 'Formato de fecha fin inválido'));
        exit;
    }

    if ($fecha_inicio && $fecha_fin && $fecha_inicio > $fecha_fin) {
        echo json_encode(array('error' => 'La fecha de inicio no puede ser mayor a la fecha fin'));
        exit;
    }
    
    if ($action == 'get_tree') {
        // Obtener todos los planteles
        $planteles = array();
        $query = "SELECT p.id_pla, p.nom_pla FROM plantel p";
        $result = $conn->query($query);
        
        while($row = $result->fetch_assoc()) {
            // Obtener conteo para el plantel
            $conteo_plantel = getConteoPlantel($conn, $row['id_pla'], $fecha_inicio, $fecha_fin);
            
            $planteles[$row['id_pla']] = array(
                'id' => 'pla_'.$row['id_pla'],
                'text' => $row['nom_pla'] . 
                    " <a href='citas.php?tipo=plantel&id_pla=".$row['id_pla']."&fecha_inicio=$fecha_inicio&fecha_fin=$fecha_fin' class='badge rounded-pill bg-purple text-white'>$conteo_plantel</a>",
                'children' => array(),
                'icon' => 'fas fa-building',
                'type' => 'pla'
            );
        }
        
        // Construir árbol de ejecutivos completo
        $arbol_ejecutivos = construirArbolEjecutivos($conn, null, $fecha_inicio, $fecha_fin);
        
        // Asignar ejecutivos a sus planteles
        foreach ($arbol_ejecutivos as $ejecutivo) {
            foreach ($ejecutivo['planteles'] as $plaId) {
                if (isset($planteles[$plaId])) {
                    $planteles[$plaId]['children'][] = $ejecutivo;
                }
            }
        }
        
        echo json_encode(array_values($planteles));
    }
    elseif ($action == 'get_planteles') {
        $query = "SELECT p.id_pla, p.nom_pla FROM plantel p";
        $result = $conn->query($query);
        
        $planteles = array();
        while($row = $result->fetch_assoc()) {
            // Obtener conteo para cada plantel
            $conteo_plantel = getConteoPlantel($conn, $row['id_pla'], $fecha_inicio, $fecha_fin);
            
            $planteles[] = array(
                'id_pla' => $row['id_pla'],
                'nom_pla' => $row['nom_pla'],
                'conteo' => $conteo_plantel
            );
        }
        
        echo json_encode($planteles);
    }
    elseif ($action == 'create') {
        $nombre = isset($_POST['nombre']) ? $conn->real_escape_string($_POST['nombre']) : '';
        $telefono = isset($_POST['telefono']) ? $conn->real_escape_string($_POST['telefono']) : '';
        $plantel = isset($_POST['plantel']) ? (int)$_POST['plantel'] : null;
        $id_padre = isset($_POST['id_padre']) ? (int)$_POST['id_padre'] : null;
        
        if (empty($nombre) || empty($telefono) || !$plantel) {
            echo json_encode(array('error' => 'Nombre, teléfono y plantel son obligatorios'));
            exit;
        }
        
        $conn->autocommit(false);
        $error = false;
        
        // 1. Insertar ejecutivo
        if ($id_padre) {
            $query = "INSERT INTO ejecutivo (nom_eje, tel_eje, id_padre) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $nombre, $telefono, $id_padre);
        } else {
            $query = "INSERT INTO ejecutivo (nom_eje, tel_eje) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $nombre, $telefono);
        }
        
        if (!$stmt->execute()) {
            $error = true;
        }
        $id_eje = $conn->insert_id;
        $stmt->close();
        
        // 2. Asignar al plantel
        if (!$error) {
            $query = "INSERT INTO ejecutivo_plantel (id_eje, id_pla) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $id_eje, $plantel);
            
            if (!$stmt->execute()) {
                $error = true;
            }
            $stmt->close();
        }
        
        if ($error) {
            $conn->rollback();
            echo json_encode(array('error' => 'Error al crear ejecutivo: ' . $conn->error));
        } else {
            $conn->commit();
            echo json_encode(array('success' => true, 'id' => $id_eje));
        }
        
        $conn->autocommit(true);
    }
    elseif ($action == 'update') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $nombre = isset($_POST['nombre']) ? $conn->real_escape_string($_POST['nombre']) : '';
        $telefono = isset($_POST['telefono']) ? $conn->real_escape_string($_POST['telefono']) : '';
        $id_padre = isset($_POST['id_padre']) ? (int)$_POST['id_padre'] : null;
        
        if ($id <= 0 || empty($nombre) || empty($telefono)) {
            echo json_encode(array('error' => 'Datos incompletos'));
            exit;
        }
        
        $query = "UPDATE ejecutivo SET nom_eje = ?, tel_eje = ?, id_padre = ? WHERE id_eje = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssii", $nombre, $telefono, $id_padre, $id);
        
        if ($stmt->execute()) {
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('error' => 'Error al actualizar ejecutivo: ' . $conn->error));
        }
        $stmt->close();
    }
    elseif ($action == 'delete') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        if ($id <= 0) {
            echo json_encode(array('error' => 'ID inválido'));
            exit;
        }
        
        // Eliminar ejecutivo (borrado lógico)
        $query = "UPDATE ejecutivo SET eli_eje = 0 WHERE id_eje = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('error' => 'Error al eliminar ejecutivo: ' . $conn->error));
        }
        $stmt->close();
    }
    elseif ($action == 'move_node') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $new_plantel = isset($_POST['new_plantel']) ? (int)$_POST['new_plantel'] : null;
        
        if ($id <= 0 || !$new_plantel) {
            echo json_encode(array('error' => 'Datos incompletos'));
            exit;
        }
        
        // Actualizar la asignación del plantel
        $query = "UPDATE ejecutivo_plantel SET id_pla = ? WHERE id_eje = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $new_plantel, $id);
        
        if ($stmt->execute()) {
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('error' => 'Error al mover ejecutivo: ' . $conn->error));
        }
        $stmt->close();
    }
    elseif ($action == 'get_details') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            echo json_encode(array('error' => 'ID inválido'));
            exit;
        }
        
        $query = "SELECT e.id_eje as id, e.nom_eje as nombre, e.tel_eje as telefono, 
                 e.id_padre, ep.id_pla as plantel_id, p.nom_pla as plantel_nombre
                 FROM ejecutivo e
                 JOIN ejecutivo_plantel ep ON ep.id_eje = e.id_eje
                 JOIN plantel p ON p.id_pla = ep.id_pla
                 WHERE e.id_eje = ? AND e.eli_eje = 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo json_encode(array(
                'success' => true,
                'nombre' => $row['nombre'],
                'telefono' => $row['telefono'],
                'id_padre' => $row['id_padre'],
                'plantel_id' => $row['plantel_id'],
                'plantel_nombre' => $row['plantel_nombre']
            ));
        } else {
            echo json_encode(array('error' => 'Ejecutivo no encontrado'));
        }
        $stmt->close();
    }
    else {
        echo json_encode(array('error' => 'Acción no válida'));
    }
    
    $conn->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ejecutivos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />
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
            color: var(--text-dark);
            padding-top: 20px;
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
        
        #ejecutivosTree {
            padding: 15px;
            background-color: white;
            border-radius: 5px;
            min-height: 500px;
            border: 1px solid var(--light-color);
            overflow: auto;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-danger {
            background-color: #D32F2F;
            border-color: #D32F2F;
        }
        
        .btn-outline-secondary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-secondary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(109, 76, 65, 0.25);
        }
        
        #selectedPlantel {
            padding: 8px;
            background-color: var(--background-color);
            border-radius: 4px;
            margin-bottom: 15px;
            min-height: 38px;
            border: 1px dashed var(--secondary-color);
        }
        
        .jstree-anchor {
            font-size: 14px;
        }
        
        .action-buttons {
            margin-top: 20px;
        }
        
        .status-message {
            display: none;
            margin-top: 15px;
        }
        
        .jstree-drop-ok {
            border: 1px dashed var(--primary-color) !important;
            background-color: rgba(109, 76, 65, 0.1) !important;
        }
        
        .jstree-wholerow-clicked {
            background: var(--light-color);
        }
        
        .jstree-wholerow-hovered {
            background: var(--background-color);
        }
        
        h1 {
            color: var(--primary-color);
            font-weight: 700;
        }
        
        .refresh-btn {
            background-color: var(--light-color);
            color: var(--text-dark);
        }
        
        .refresh-btn:hover {
            background-color: var(--secondary-color);
            color: var(--text-light);
        }
        
        .badge.bg-purple {
            background-color: #6a0dad;
            color: white;
        }
        
        .plantel-card {
            transition: transform 0.2s;
        }
        
        .plantel-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .jstree-icon.fas.fa-building {
            color: #6a0dad;
        }
        
        .jstree-icon.fas.fa-user {
            color: #4CAF50;
        }
        
        .badge-count {
            background-color: white;
            color: #333;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 12px;
            margin-left: 5px;
        }
        
        .jstree-anchor .badge {
            margin-left: 5px;
            font-size: 0.8em;
        }
        
        .jstree-anchor a.badge {
            text-decoration: none;
        }
        
        .ejecutivo-link, .plantel-link {
            color: inherit !important;
            text-decoration: none !important;
        }
        
        .ejecutivo-link:hover, .plantel-link:hover {
            text-decoration: underline !important;
        }
        
        .badge-light {
            background-color: #f8f9fa;
            color: #212529;
            border: 1px solid #dee2e6;
        }
        
        .jstree-anchor > i.jstree-themeicon {
            margin-right: 5px;
        }
        
        .jstree-clicked {
            background-color: #f0f0f0 !important;
        }
        
        .node-text {
            cursor: pointer;
        }
        
        .node-individual {
            color: #333;
        }
        
        .node-acumulado {
            color: #6a0dad;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h1><i class="fas fa-sitemap me-2"></i> Estructura Comercial</h1>
                <p class="text-muted">Gestión completa de ejecutivos</p>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-filter me-2"></i> Filtro de Fechas
                    </div>
                    <div class="card-body">
                        <form id="filtroFechas" class="row g-3">
                            <div class="col-md-4">
                                <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio">
                            </div>
                            <div class="col-md-4">
                                <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search me-1"></i> Aplicar Filtro
                                </button>
                                <button type="button" id="resetFiltro" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Limpiar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-network-wired me-2"></i> Árbol Organizacional</span>
                        <button id="refresh" class="btn btn-sm refresh-btn">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="ejecutivosTree"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header">
                        <i class="fas fa-user-tie me-2"></i>
                        <span id="formTitle">Nuevo Ejecutivo</span>
                    </div>
                    <div class="card-body">
                        <form id="ejecutivoForm">
                            <input type="hidden" id="ejecutivoId" value="0">
                            <input type="hidden" id="id_padre" value="">
                            
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Plantel</label>
                                <div id="selectedPlantel" class="p-2">
                                    <span class="text-muted">Seleccione un plantel en el árbol</span>
                                </div>
                                <small class="text-muted">Seleccione un plantel en el árbol o arrastre un ejecutivo a otro plantel</small>
                            </div>
                            
                            <div class="action-buttons d-flex flex-wrap gap-2">
                                <button type="submit" id="saveBtn" class="btn btn-primary flex-grow-1">
                                    <i class="fas fa-save me-1"></i> Guardar
                                </button>
                                <button type="button" id="cancelBtn" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Cancelar
                                </button>
                                <button type="button" id="deleteBtn" class="btn btn-danger" style="display: none;">
                                    <i class="fas fa-trash-alt me-1"></i> Eliminar
                                </button>
                            </div>
                            
                            <div id="statusMessage" class="status-message alert alert-dismissible fade show mt-3">
                                <span id="messageText"></span>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-building me-2"></i> Planteles</span>
                    </div>
                    <div class="card-body">
                        <div id="plantelesContainer" class="d-flex flex-wrap gap-3">
                            <!-- Los planteles se cargarán dinámicamente aquí -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>
    <script>
    $(function() {
        // Variables de estado
        var selectedNode = null;
        var selectedPlantel = null;
        var updatingTree = false;
        var fecha_inicio = null;
        var fecha_fin = null;
        
        // Inicializar jsTree
        $('#ejecutivosTree').jstree({
            'core': {
                'data': {
                    'url': function(node) {
                        return '?action=get_tree&fecha_inicio=' + fecha_inicio + '&fecha_fin=' + fecha_fin;
                    },
                    'data': function(node) {
                        return { 'id': node.id };
                    }
                },
                'check_callback': function (operation, node, parent, position, more) {
                    if (operation === 'move_node') {
                        // Solo permitir mover ejecutivos a planteles
                        if (node.type === 'eje' && parent.type === 'pla') {
                            return true;
                        }
                        return false;
                    }
                    return true;
                },
                'themes': {
                    'responsive': true,
                    'dots': true,
                    'icons': true
                },
                'multiple': false
            },
            'plugins': ['dnd', 'wholerow', 'types'],
            'types': {
                'default': { 'icon': 'fas fa-user' },
                'pla': { 'icon': 'fas fa-building' },
                'eje': { 'icon': 'fas fa-user' }
            },
            'dnd': {
                'copy': false,
                'inside_pos': 'last',
                'is_draggable': function(node) {
                    // Solo permitir arrastrar ejecutivos
                    return node && node.type === 'eje';
                }
            }
        })
        .on('move_node.jstree', function(e, data) {
            // Extraer los IDs
            var nodeId = data.node.id.replace('eje_', '');
            var parentId = data.parent.replace('pla_', '');
            
            $.post('?action=move_node', {
                id: nodeId,
                new_plantel: parentId
            }, function(response) {
                if (!response.success) {
                    showMessage(response.error || 'Error al mover el ejecutivo', 'danger');
                    $('#ejecutivosTree').jstree('refresh');
                } else {
                    showMessage('Ejecutivo movido correctamente', 'success');
                }
            }, 'json');
        })
        .on('changed.jstree', function(e, data) {
            if (updatingTree || !data.selected.length) return;
            
            selectedNode = data.instance.get_node(data.selected[0]);
            
            // Si es un ejecutivo, cargar detalles
            if (selectedNode.type === 'eje') {
                var ejecutivoId = selectedNode.id.replace('eje_', '');
                loadEjecutivoDetails(ejecutivoId);
            }
            // Si es un plantel, guardarlo para creación
            else if (selectedNode.type === 'pla' && $('#ejecutivoId').val() == '0') {
                selectedPlantel = selectedNode;
                $('#selectedPlantel').html('<i class="fas fa-building me-1"></i> ' + selectedNode.text);
                $('#id_padre').val(''); // Resetear padre cuando se selecciona plantel
            }
        })
        .on('ready.jstree', function() {
            // Modificar el HTML de los nodos para separar el texto
            $('#ejecutivosTree').find('.jstree-anchor').each(function() {
                var $anchor = $(this);
                var html = $anchor.html();
                
                // Extraer el texto del nodo (antes de los badges)
                var textMatch = html.match(/^([^<]+)/);
                if (textMatch) {
                    var nodeText = textMatch[1].trim();
                    var badges = html.replace(textMatch[0], '');
                    
                    $anchor.html(
                        '<i class="jstree-themeicon" role="presentation"></i>' +
                        '<a href="#" class="node-text">' + nodeText + '</a>' +
                        badges
                    );
                }
            });
            
            // Manejar clicks en el nombre del ejecutivo
            $('#ejecutivosTree').on('click', '.node-text', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var $anchor = $(this).closest('.jstree-anchor');
                var node = $('#ejecutivosTree').jstree(true).get_node($anchor);
                
                if (node.type === 'eje') {
                    $('#ejecutivosTree').jstree('select_node', node);
                }
                
                return false;
            });
        });
        
        // Permitir clics en los badges
        $('#ejecutivosTree').on('click', '.jstree-anchor a.badge', function(e) {
            e.stopPropagation();
            window.open($(this).attr('href'), '_self');
        });
        
        // Función para cargar detalles del ejecutivo
        function loadEjecutivoDetails(id) {
            $.get('?action=get_details&id=' + id, function(data) {
                if (data.success) {
                    updatingTree = true;
                    
                    $('#ejecutivoId').val(id);
                    $('#nombre').val(data.nombre);
                    $('#telefono').val(data.telefono);
                    $('#id_padre').val(data.id_padre || '');
                    $('#formTitle').html('<i class="fas fa-user-edit me-2"></i> Editar Ejecutivo');
                    $('#saveBtn').html('<i class="fas fa-save me-1"></i> Actualizar');
                    $('#deleteBtn').show();
                    
                    $('#selectedPlantel').html('<i class="fas fa-building me-1"></i> ' + data.plantel_nombre);
                    selectedPlantel = { id: 'pla_' + data.plantel_id, text: data.plantel_nombre };
                    
                    updatingTree = false;
                }
            }, 'json').fail(function() {
                showMessage('Error al cargar detalles', 'danger');
            });
        }
        
        // Función para cargar planteles con conteos
        function loadPlanteles(fecha_inicio, fecha_fin) {
            $.get('?action=get_planteles', {
                fecha_inicio: fecha_inicio,
                fecha_fin: fecha_fin
            }, function(data) {
                var html = '';
                if (data.length) {
                    $.each(data, function(i, plantel) {
                        html += '<div class="card plantel-card" style="width: 200px;">' +
                            '<div class="card-body text-center">' +
                            '<h5 class="card-title">' + plantel.nom_pla + '</h5>' +
                            '<div class="mt-3">' +
                            '<a href="citas.php?tipo=plantel&id_pla=' + plantel.id_pla + '&fecha_inicio=' + fecha_inicio + '&fecha_fin=' + fecha_fin + '" class="badge rounded-pill bg-purple text-white p-2" style="font-size: 1.1em;">' +
                            plantel.conteo +
                            '</a>' +
                            '</div>' +
                            '</div>' +
                            '</div>';
                    });
                } else {
                    html = '<p class="text-center text-muted">No hay planteles disponibles</p>';
                }
                $('#plantelesContainer').html(html);
            }, 'json');
        }
        
        // Botón Actualizar
        $('#refresh').click(function() {
            refreshTree();
        });
        
        // Función para refrescar el árbol
        function refreshTree() {
            fecha_inicio = $('#fecha_inicio').val();
            fecha_fin = $('#fecha_fin').val();
            
            $('#ejecutivosTree').jstree(true).refresh();
            loadPlanteles(fecha_inicio, fecha_fin);
            resetForm();
        }
        
        // Enviar formulario
        $('#ejecutivoForm').submit(function(e) {
            e.preventDefault();
            
            var id = $('#ejecutivoId').val();
            var nombre = $('#nombre').val().trim();
            var telefono = $('#telefono').val().trim();
            var plantel = selectedPlantel ? selectedPlantel.id.replace('pla_', '') : null;
            var id_padre = $('#id_padre').val();
            
            if (!nombre || !telefono || !plantel) {
                showMessage('Nombre, teléfono y plantel son obligatorios', 'danger');
                return;
            }
            
            var url = id == '0' ? '?action=create' : '?action=update';
            var data = {
                nombre: nombre,
                telefono: telefono,
                plantel: plantel
            };
            
            if (id != '0') {
                data.id = id;
                data.id_padre = id_padre;
            } else {
                data.id_padre = id_padre || null;
            }
            
            $.post(url, data, function(response) {
                if (response.success) {
                    refreshTree();
                    showMessage(id == '0' ? 'Ejecutivo creado correctamente' : 'Ejecutivo actualizado correctamente', 'success');
                } else {
                    showMessage(response.error || 'Error desconocido', 'danger');
                }
            }, 'json').fail(function() {
                showMessage('Error en la comunicación con el servidor', 'danger');
            });
        });
        
        // Botón Cancelar
        $('#cancelBtn').click(function() {
            resetForm();
        });
        
        // Botón Eliminar
        $('#deleteBtn').click(function() {
            var id = $('#ejecutivoId').val();
            if (id && id != '0') {
                if (confirm('¿Está seguro de eliminar este ejecutivo?')) {
                    $.post('?action=delete', { id: id }, function(response) {
                        if (response.success) {
                            refreshTree();
                            showMessage('Ejecutivo eliminado correctamente', 'success');
                        } else {
                            showMessage(response.error || 'Error al eliminar', 'danger');
                        }
                    }, 'json');
                }
            }
        });
        
        // Función para resetear el formulario
        function resetForm() {
            updatingTree = true;
            
            $('#ejecutivoId').val('0');
            $('#nombre').val('');
            $('#telefono').val('');
            $('#id_padre').val('');
            $('#formTitle').html('<i class="fas fa-user-plus me-2"></i> Nuevo Ejecutivo');
            $('#saveBtn').html('<i class="fas fa-save me-1"></i> Guardar');
            $('#deleteBtn').hide();
            selectedNode = null;
            selectedPlantel = null;
            $('#selectedPlantel').html('<span class="text-muted">Seleccione un plantel en el árbol</span>');
            
            $('#ejecutivosTree').jstree('deselect_all', true);
            
            updatingTree = false;
        }
        
        // Función para mostrar mensajes de estado
        function showMessage(text, type) {
            var $msg = $('#statusMessage');
            $msg.removeClass('alert-success alert-danger alert-warning')
                .addClass('alert-' + type)
                .show();
            $('#messageText').text(text);
            setTimeout(function() {
                $msg.fadeOut();
            }, 5000);
        }
        
        // Manejar envío del formulario de filtro
        $('#filtroFechas').submit(function(e) {
            e.preventDefault();
            refreshTree();
        });
        
        // Botón limpiar filtro
        $('#resetFiltro').click(function() {
            $('#fecha_inicio').val('');
            $('#fecha_fin').val('');
            refreshTree();
        });
        
        // Cargar planteles al inicio
        refreshTree();
    });
    </script>
</body>
</html>
