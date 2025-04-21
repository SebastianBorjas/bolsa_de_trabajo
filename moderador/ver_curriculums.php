<?php
require_once 'seguridad_moderador.php'; // En el mismo directorio (moderador/)
require_once '../conexion.php'; // En curriculums/

// Incluir PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

// Obtener el usuario de la sesión
$usuario_sesion = $_SESSION['usuario'];

// Obtener el id_usuario desde la tabla usuarios
$stmt_usuario = $conn->prepare("SELECT id_usuario FROM usuarios WHERE usuario = :usuario AND tipo_usuario = 'moderador'");
$stmt_usuario->execute(['usuario' => $usuario_sesion]);
$usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

if ($usuario === false) {
    die("Error: No se encontró el usuario moderador en la base de datos.");
}

$id_usuario = $usuario['id_usuario'];

// Obtener el id_plantel del moderador (predeterminado)
$stmt_mod = $conn->prepare("SELECT id_plantel FROM moderadores WHERE id_usuario = :id_usuario");
$stmt_mod->execute(['id_usuario' => $id_usuario]);
$moderador = $stmt_mod->fetch(PDO::FETCH_ASSOC);

if ($moderador === false) {
    die("Error: No se encontró el moderador asociado al usuario actual.");
}

$id_plantel_predeterminado = $moderador['id_plantel'];

// Consultar todos los planteles
$stmt_planteles = $conn->prepare("SELECT id_plantel, nombre_plantel FROM planteles ORDER BY nombre_plantel");
$stmt_planteles->execute();
$planteles = $stmt_planteles->fetchAll(PDO::FETCH_ASSOC);

// Determinar el plantel seleccionado (por POST o predeterminado)
$id_plantel = $_POST['id_plantel'] ?? $id_plantel_predeterminado;

// Consultar áreas del plantel seleccionado (para el filtro de área)
$stmt_areas = $conn->prepare("SELECT id_area, nombre_area FROM areas WHERE id_plantel = :id_plantel ORDER BY nombre_area");
$stmt_areas->execute(['id_plantel' => $id_plantel]);
$areas = $stmt_areas->fetchAll(PDO::FETCH_ASSOC);

// Consultar empleados del plantel seleccionado (mostrar subáreas en lugar de áreas)
$sql = "
    SELECT 
        e.id_empleado, 
        e.nombre_completo, 
        e.celular, 
        e.correo, 
        e.estudios, 
        e.edad, 
        e.ruta_curriculum, 
        e.experiencia,
        s1.id_subarea AS id_subarea1, 
        s1.nombre_subarea AS nombre_subarea1,
        s1.id_area AS id_area1,
        s2.id_subarea AS id_subarea2, 
        s2.nombre_subarea AS nombre_subarea2,
        s2.id_area AS id_area2,
        s3.id_subarea AS id_subarea3, 
        s3.nombre_subarea AS nombre_subarea3,
        s3.id_area AS id_area3,
        e.dispuesto
    FROM empleados e
    LEFT JOIN subareas s1 ON e.area1 = s1.id_subarea
    LEFT JOIN subareas s2 ON e.area2 = s2.id_subarea
    LEFT JOIN subareas s3 ON e.area3 = s3.id_subarea
    WHERE e.id_plantel = :id_plantel
    ORDER BY e.nombre_completo
";
$stmt = $conn->prepare($sql);
$stmt->execute(['id_plantel' => $id_plantel]);
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Opciones de experiencia
$opciones_experiencia = [
    'Sin experiencia',
    '0-1 años de experiencia',
    '1-5 años de experiencia',
    'mas de 5 años de experiencia'
];

// Opciones para "Dispuesto a viajar" (valores en minúsculas y sin tildes para coincidir con la base de datos)
$opciones_dispuesto = [
    'todos' => 'Todos',
    'si' => 'Sí',
    'no' => 'No'
];

// Lógica para enviar el correo (individual o múltiple)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enviar_correo'])) {
    $destinatario = $_POST['destinatario'] ?? '';
    $asunto = $_POST['asunto'] ?? 'Currículum del candidato';
    $contenido = $_POST['contenido'] ?? '';

    // Determinar si es un envío individual o múltiple
    if (isset($_POST['id_empleados']) && !empty($_POST['id_empleados'])) {
        // Envío múltiple
        $id_empleados = json_decode($_POST['id_empleados'], true);
    } elseif (isset($_POST['id_empleado']) && !empty($_POST['id_empleado'])) {
        // Envío individual
        $id_empleados = [$_POST['id_empleado']];
    } else {
        $id_empleados = [];
    }

    // Validar que $id_empleados no esté vacío
    if (empty($id_empleados)) {
        $mensaje_error = "Error: No se recibieron los IDs de los empleados.";
    } else {
        // Consulta para obtener las rutas de los currículums y nombres completos
        $placeholders = implode(',', array_fill(0, count($id_empleados), '?'));
        $query = "SELECT id_empleado, nombre_completo, ruta_curriculum 
                  FROM empleados 
                  WHERE id_empleado IN ($placeholders) AND id_plantel = ?";
        $stmt_empleados = $conn->prepare($query);
        $params = array_merge($id_empleados, [$id_plantel]);
        $stmt_empleados->execute($params);
        $empleados_seleccionados = $stmt_empleados->fetchAll(PDO::FETCH_ASSOC);

        if ($empleados_seleccionados) {
            $mail = new PHPMailer(true);
            try {
                // Configuración SMTP para Titan
                $mail->isSMTP();
                $mail->Host = 'smtp.titan.email';
                $mail->SMTPAuth = true;
                $mail->Username = 'jefecapacitacion@canacintramonclova.org';
                $mail->Password = '&3c@^t#83e%2';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                // Remitente y destinatario
                $mail->setFrom('jefecapacitacion@canacintramonclova.org', 'Sistema de Currículums');
                $mail->addAddress($destinatario);

                // Contenido del correo
                $mail->isHTML(true);
                $mail->Subject = $asunto;
                $mail->Body = $contenido ?: "<p>Adjunto los currículums de los candidatos.</p>";

                // Adjuntar los PDFs
                foreach ($empleados_seleccionados as $empleado) {
                    if ($empleado['ruta_curriculum']) {
                        $ruta_completa = $_SERVER['DOCUMENT_ROOT'] . '/curriculums/curriculum/' . basename($empleado['ruta_curriculum']);
                        if (file_exists($ruta_completa)) {
                            $mail->addAttachment($ruta_completa, 'Curriculum_' . $empleado['nombre_completo'] . '.pdf');
                        } else {
                            $mail->Body .= "<p><strong>Nota:</strong> No se pudo adjuntar el PDF de " . htmlspecialchars($empleado['nombre_completo']) . " porque no se encontró en el servidor.</p>";
                            error_log("Error: El archivo no existe en $ruta_completa");
                        }
                    }
                }

                $mail->send();
                $mensaje_exito = "Correo enviado con éxito.";
            } catch (Exception $e) {
                $mensaje_error = "Error al enviar el correo: " . $mail->ErrorInfo;
            }
        } else {
            $mensaje_error = "No se encontraron los empleados con los IDs proporcionados.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Currículums</title>
    <link rel="stylesheet" href="estilos_moderador2.css">
    <link rel="stylesheet" href="estilos_ver_curriculums2.css">
</head>
<body>
    <div class="container">
        <button class="menu-toggle">☰</button>
        <div class="sidebar" id="sidebar">
            <div class="sidebar-buttons">
                <button onclick="window.location.href='registrar_area.php'">Registrar Área</button>
                <button onclick="window.location.href='inicio_moderador.php'">Volver al Inicio</button>
                <button onclick="window.location.href='../logout.php'">Cerrar Sesión</button>
            </div>
        </div>
        <div class="content">
            <h1>Currículums</h1>

            <!-- Mensajes de éxito o error -->
            <?php if (isset($mensaje_exito)): ?>
                <p class="mensaje success"><?php echo $mensaje_exito; ?></p>
            <?php elseif (isset($mensaje_error)): ?>
                <p class="mensaje error"><?php echo $mensaje_error; ?></p>
            <?php endif; ?>

            <!-- Filtro de plantel -->
            <div class="filter-group">
                <div class="form-group">
                    <label for="filtroPlantel">Seleccionar Plantel:</label>
                    <form method="POST" id="formPlantel">
                        <select id="filtroPlantel" name="id_plantel" onchange="this.form.submit()">
                            <?php foreach ($planteles as $plantel): ?>
                                <option value="<?php echo $plantel['id_plantel']; ?>" 
                                    <?php echo $plantel['id_plantel'] == $id_plantel ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($plantel['nombre_plantel']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Filtros adicionales -->
            <div class="filter-group">
                <div class="form-group">
                    <label for="buscarNombre">Buscar por Nombre:</label>
                    <input type="text" id="buscarNombre" placeholder="Ingresa nombre" class="search-input">
                </div>
                <div class="form-group">
                    <label for="filtroArea">Filtrar por Área:</label>
                    <select id="filtroArea" class="filter-select">
                        <option value="">Todas las áreas</option>
                        <?php foreach ($areas as $area): ?>
                            <option value="<?php echo $area['id_area']; ?>">
                                <?php echo htmlspecialchars($area['nombre_area']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="filtroSubarea">Filtrar por Subárea:</label>
                    <select id="filtroSubarea" class="filter-select" disabled>
                        <option value="">Todas las subáreas</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="filtroExperiencia">Filtrar por Experiencia:</label>
                    <select id="filtroExperiencia" class="filter-select">
                        <option value="">Todas las experiencias</option>
                        <?php foreach ($opciones_experiencia as $experiencia): ?>
                            <option value="<?php echo htmlspecialchars($experiencia); ?>">
                                <?php echo htmlspecialchars($experiencia); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="filtroDispuesto">Filtrar por Dispuesto a Viajar:</label>
                    <select id="filtroDispuesto" class="filter-select">
                        <?php foreach ($opciones_dispuesto as $valor => $texto): ?>
                            <option value="<?php echo htmlspecialchars($valor); ?>">
                                <?php echo htmlspecialchars($texto); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Lista de empleados -->
            <?php if (empty($empleados)): ?>
                <p class="mensaje error">No hay empleados registrados para este plantel.</p>
            <?php else: ?>
                <div class="planteles-container">
                    <div class="plantel-table">
                        <h2>Lista de Candidatos</h2>
                        <table id="empleadosTable">
                            <thead>
                                <tr>
                                    <th id="botonesColspan" colspan="11">
                                        <button id="enviarVariosBtn" class="action-button">Enviar varios</button>
                                        <button id="enviarSeleccionadosBtn" class="action-button" style="display: none;">Enviar</button>
                                    </th>
                                </tr>
                                <tr>
                                    <th class="checkbox-header" style="display: none;">Seleccionar</th>
                                    <th>Nombre Completo</th>
                                    <th>Celular</th>
                                    <th>Correo</th>
                                    <th>Estudios</th>
                                    <th>Edad</th>
                                    <th>Experiencia</th>
                                    <th>Área 1</th>
                                    <th>Área 2</th>
                                    <th>Área 3</th>
                                    <th>Dispuesto a viajar</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($empleados as $empleado): ?>
                                    <tr>
                                        <td class="checkbox-cell" style="display: none;">
                                            <input type="checkbox" class="empleado-checkbox" value="<?php echo $empleado['id_empleado']; ?>" data-nombre="<?php echo htmlspecialchars($empleado['nombre_completo']); ?>">
                                        </td>
                                        <td><?php echo htmlspecialchars($empleado['nombre_completo']); ?></td>
                                        <td><?php echo htmlspecialchars($empleado['celular']); ?></td>
                                        <td><?php echo htmlspecialchars($empleado['correo']); ?></td>
                                        <td><?php echo htmlspecialchars($empleado['estudios']); ?></td>
                                        <td><?php echo htmlspecialchars($empleado['edad']); ?></td>
                                        <td><?php echo htmlspecialchars($empleado['experiencia']); ?></td>
                                        <td><?php echo htmlspecialchars($empleado['nombre_subarea1'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($empleado['nombre_subarea2'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($empleado['nombre_subarea3'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($empleado['dispuesto']); ?></td>
                                        <td>
                                            <?php if ($empleado['ruta_curriculum']): ?>
                                                <button class="preview-button" onclick="mostrarVistaPrevia('/curriculums/curriculum/<?php echo htmlspecialchars(basename($empleado['ruta_curriculum'])); ?>')">Previsualizar</button>
                                                <a href="/curriculums/curriculum/<?php echo htmlspecialchars(basename($empleado['ruta_curriculum'])); ?>" download class="download-link">Descargar</a>
                                                <button class="send-button" onclick="mostrarFormularioCorreo('<?php echo $empleado['id_empleado']; ?>', '<?php echo htmlspecialchars($empleado['nombre_completo']); ?>')">Enviar</button>
                                            <?php else: ?>
                                                No disponible
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para previsualización -->
    <div id="vistaPreviaModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarVistaPrevia()">×</span>
            <iframe id="vistaPreviaIframe" src=""></iframe>
        </div>
    </div>

    <!-- Modal para formulario de correo -->
    <div id="emailModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarFormularioCorreo()">×</span>
            <h2>Enviar Currículum por Correo</h2>
            <form method="POST" class="email-form" id="emailForm">
                <input type="hidden" name="id_empleado" id="id_empleado">
                <input type="hidden" name="id_empleados" id="id_empleados">
                <input type="hidden" name="enviar_correo" value="1">
                <input type="hidden" name="id_plantel" value="<?php echo $id_plantel; ?>">
                <div>
                    <label for="destinatario">Correo Destinatario:</label>
                    <input type="email" name="destinatario" id="destinatario" required>
                </div>
                <div>
                    <label for="asunto">Asunto:</label>
                    <input type="text" name="asunto" id="asunto" placeholder="Currículum del candidato">
                </div>
                <div>
                    <label for="contenido">Contenido (opcional):</label>
                    <textarea name="contenido" id="contenido" rows="5" placeholder="Escribe un mensaje opcional"></textarea>
                </div>
                <button type="submit">Enviar</button>
            </form>
        </div>
    </div>

    <script src="js_moderador.js"></script>
    <script>
        const buscarNombre = document.getElementById('buscarNombre');
        const filtroArea = document.getElementById('filtroArea');
        const filtroSubarea = document.getElementById('filtroSubarea');
        const filtroExperiencia = document.getElementById('filtroExperiencia');
        const filtroDispuesto = document.getElementById('filtroDispuesto');
        const empleadosTable = document.getElementById('empleadosTable').getElementsByTagName('tbody')[0];
        const empleadosOriginales = <?php echo json_encode($empleados); ?>;
        const vistaPreviaModal = document.getElementById('vistaPreviaModal');
        const emailModal = document.getElementById('emailModal');
        const iframe = document.getElementById('vistaPreviaIframe');
        const enviarVariosBtn = document.getElementById('enviarVariosBtn');
        const enviarSeleccionadosBtn = document.getElementById('enviarSeleccionadosBtn');
        const checkboxHeader = document.querySelector('.checkbox-header');
        const botonesColspan = document.getElementById('botonesColspan');
        let seleccionMultipleActiva = false;

        // Cargar subáreas dinámicamente según el área seleccionada
        filtroArea.addEventListener('change', function() {
            const idArea = this.value;
            if (!idArea) {
                filtroSubarea.innerHTML = '<option value="">Todas las subáreas</option>';
                filtroSubarea.disabled = true;
                filtrarEmpleados();
                return;
            }

            fetch('obtener_subareas.php?id_area=' + idArea)
                .then(response => response.json())
                .then(data => {
                    filtroSubarea.innerHTML = '<option value="">Todas las subáreas</option>';
                    if (data.length === 0) {
                        filtroSubarea.disabled = true;
                    } else {
                        data.forEach(subarea => {
                            const option = document.createElement('option');
                            option.value = subarea.id_subarea;
                            option.textContent = subarea.nombre_subarea;
                            filtroSubarea.appendChild(option);
                        });
                        filtroSubarea.disabled = false;
                    }
                    filtrarEmpleados();
                })
                .catch(error => {
                    console.error('Error al cargar subáreas:', error);
                    filtroSubarea.innerHTML = '<option value="">Error al cargar subáreas</option>';
                    filtroSubarea.disabled = true;
                    filtrarEmpleados();
                });
        });

        function filtrarEmpleados() {
            const texto = buscarNombre.value.toLowerCase();
            const idArea = filtroArea.value;
            const idSubarea = filtroSubarea.value;
            const experiencia = filtroExperiencia.value;
            const dispuesto = filtroDispuesto.value;

            const empleadosFiltrados = empleadosOriginales.filter(empleado => {
                const coincideNombre = empleado.nombre_completo.toLowerCase().includes(texto);
                const coincideArea = !idArea || 
                    empleado.id_area1 == idArea || 
                    empleado.id_area2 == idArea || 
                    empleado.id_area3 == idArea;
                const coincideSubarea = !idSubarea || 
                    empleado.id_subarea1 == idSubarea || 
                    empleado.id_subarea2 == idSubarea || 
                    empleado.id_subarea3 == idSubarea;
                const coincideExperiencia = !experiencia || empleado.experiencia === experiencia;
                const coincideDispuesto = dispuesto === 'todos' || 
                    (dispuesto === 'si' && empleado.dispuesto === 'si') || 
                    (dispuesto === 'no' && empleado.dispuesto === 'no');
                return coincideNombre && coincideArea && coincideSubarea && coincideExperiencia && coincideDispuesto;
            });
            mostrarEmpleados(empleadosFiltrados);
        }

        function mostrarEmpleados(empleados) {
            empleadosTable.innerHTML = '';
            empleados.forEach(empleado => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="checkbox-cell" style="display: ${seleccionMultipleActiva ? 'table-cell' : 'none'};">
                        <input type="checkbox" class="empleado-checkbox" value="${empleado.id_empleado}" data-nombre="${empleado.nombre_completo}">
                    </td>
                    <td>${empleado.nombre_completo}</td>
                    <td>${empleado.celular}</td>
                    <td>${empleado.correo}</td>
                    <td>${empleado.estudios}</td>
                    <td>${empleado.edad}</td>
                    <td>${empleado.experiencia}</td>
                    <td>${empleado.nombre_subarea1 || 'N/A'}</td>
                    <td>${empleado.nombre_subarea2 || 'N/A'}</td>
                    <td>${empleado.nombre_subarea3 || 'N/A'}</td>
                    <td>${empleado.dispuesto}</td>
                    <td>
                        ${empleado.ruta_curriculum ? 
                            `<button class="preview-button" onclick="mostrarVistaPrevia('/curriculums/curriculum/${encodeURIComponent(basename(empleado.ruta_curriculum))}')">Previsualizar</button>
                             <a href="/curriculums/curriculum/${encodeURIComponent(basename(empleado.ruta_curriculum))}" download class="download-link">Descargar</a>
                             <button class="send-button" onclick="mostrarFormularioCorreo('${empleado.id_empleado}', '${empleado.nombre_completo}')">Enviar</button>` 
                            : 'No disponible'}
                    </td>
                `;
                empleadosTable.appendChild(row);
            });
        }

        function basename(path) {
            return path.split('/').pop();
        }

        function mostrarVistaPrevia(ruta) {
            iframe.src = ruta;
            vistaPreviaModal.style.display = 'flex';
        }

        function cerrarVistaPrevia() {
            vistaPreviaModal.style.display = 'none';
            iframe.src = '';
        }

        function mostrarFormularioCorreo(id_empleado, nombre_completo) {
            document.getElementById('id_empleado').value = id_empleado;
            document.getElementById('id_empleados').value = ''; // Limpiar el campo de selección múltiple
            document.getElementById('asunto').value = `Canacintra. CV ${nombre_completo}`;
            emailModal.style.display = 'flex';
        }

        function cerrarFormularioCorreo() {
            emailModal.style.display = 'none';
            document.getElementById('id_empleado').value = '';
            document.getElementById('id_empleados').value = '';
            document.getElementById('destinatario').value = '';
            document.getElementById('asunto').value = '';
            document.getElementById('contenido').value = '';
        }

        enviarVariosBtn.addEventListener('click', function() {
            seleccionMultipleActiva = !seleccionMultipleActiva;
            if (seleccionMultipleActiva) {
                enviarVariosBtn.textContent = 'Cancelar selección';
                enviarSeleccionadosBtn.style.display = 'inline-block';
                checkboxHeader.style.display = 'table-cell';
                botonesColspan.setAttribute('colspan', '12'); // 11 columnas + 1 de selección
            } else {
                enviarVariosBtn.textContent = 'Enviar varios';
                enviarSeleccionadosBtn.style.display = 'none';
                checkboxHeader.style.display = 'none';
                botonesColspan.setAttribute('colspan', '11'); // Solo las 11 columnas originales
                // Desmarcar todos los checkboxes
                document.querySelectorAll('.empleado-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
            }
            mostrarEmpleados(empleadosOriginales.filter(empleado => {
                const texto = buscarNombre.value.toLowerCase();
                const idArea = filtroArea.value;
                const idSubarea = filtroSubarea.value;
                const experiencia = filtroExperiencia.value;
                const dispuesto = filtroDispuesto.value;

                const coincideNombre = empleado.nombre_completo.toLowerCase().includes(texto);
                const coincideArea = !idArea || 
                    empleado.id_area1 == idArea || 
                    empleado.id_area2 == idArea || 
                    empleado.id_area3 == idArea;
                const coincideSubarea = !idSubarea || 
                    empleado.id_subarea1 == idSubarea || 
                    empleado.id_subarea2 == idSubarea || 
                    empleado.id_subarea3 == idSubarea;
                const coincideExperiencia = !experiencia || empleado.experiencia === experiencia;
                const coincideDispuesto = dispuesto === 'todos' || 
                    (dispuesto === 'si' && empleado.dispuesto === 'si') || 
                    (dispuesto === 'no' && empleado.dispuesto === 'no');
                return coincideNombre && coincideArea && coincideSubarea && coincideExperiencia && coincideDispuesto;
            }));
        });

        enviarSeleccionadosBtn.addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('.empleado-checkbox:checked');
            if (checkboxes.length === 0) {
                alert('Por favor, selecciona al menos un empleado para enviar.');
                return;
            }

            const idEmpleados = Array.from(checkboxes).map(checkbox => checkbox.value);
            document.getElementById('id_empleados').value = JSON.stringify(idEmpleados);
            document.getElementById('id_empleado').value = '';
            document.getElementById('asunto').value = "Canacintra. CV's";
            emailModal.style.display = 'flex';
        });

        buscarNombre.addEventListener('input', filtrarEmpleados);
        filtroArea.addEventListener('change', filtrarEmpleados);
        filtroSubarea.addEventListener('change', filtrarEmpleados);
        filtroExperiencia.addEventListener('change', filtrarEmpleados);
        filtroDispuesto.addEventListener('change', filtrarEmpleados);

        window.onclick = function(event) {
            if (event.target == vistaPreviaModal) {
                cerrarVistaPrevia();
            } else if (event.target == emailModal) {
                cerrarFormularioCorreo();
            }
        }
    </script>
</body>
</html>