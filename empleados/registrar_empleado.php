<?php
session_start(); // Start session for message handling
require_once '../conexion.php';

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check for session message
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']); // Clear the message after displaying
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_plantel = $_POST['id_plantel'];
    $nombre = trim($_POST['nombre']);
    $apellido_paterno = trim($_POST['apellido_paterno']);
    $apellido_materno = trim($_POST['apellido_materno']);
    $nombre_completo = $nombre . ' ' . $apellido_paterno . ' ' . $apellido_materno;
    $correo = trim($_POST['correo']);
    $celular = trim($_POST['celular']);
    $estudios = $_POST['estudios'];
    $edad = $_POST['edad'];
    $experiencia = $_POST['experiencia'];
    $dispuesto = $_POST['dispuesto'];
    $subareas_seleccionadas = isset($_POST['areas']) ? $_POST['areas'] : [];
    $sugerencia = isset($_POST['sugerencia']) ? trim($_POST['sugerencia']) : '';
    $fecha_registro = date('Y-m-d');

    error_log("Subáreas seleccionadas: " . print_r($subareas_seleccionadas, true));
    error_log("Sugerencia: " . $sugerencia);

    // Validar correo (obligatorio)
    if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Error: Debes proporcionar un correo válido.";
    }

    // Validar subáreas o sugerencia
    if (count($subareas_seleccionadas) < 1 && empty($sugerencia)) {
        $mensaje = "Error: Debes seleccionar al menos 1 subárea o ingresar una sugerencia.";
    } elseif (count($subareas_seleccionadas) > 3) {
        $mensaje = "Error: No puedes seleccionar más de 3 subáreas.";
    } else {
        $curriculum_dir = '../curriculum';
        if (!file_exists($curriculum_dir)) {
            mkdir($curriculum_dir, 0777, true);
        }

        $ruta_curriculum = null;
        if (isset($_FILES['curriculum']) && $_FILES['curriculum']['error'] == UPLOAD_ERR_OK) {
            $file_name = $_FILES['curriculum']['name'];
            $file_tmp = $_FILES['curriculum']['tmp_name'];
            $file_type = mime_content_type($file_tmp);
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $file_size = $_FILES['curriculum']['size'];
            $max_size = 2 * 1024 * 1024; // 2MB in bytes

            // Verificar tipo de archivo y tamaño
            if ($file_extension !== 'pdf' || $file_type !== 'application/pdf') {
                $mensaje = "Error: Solo se permiten archivos PDF.";
            } elseif ($file_size > $max_size) {
                $mensaje = "Error: El archivo excede el límite de 2MB.";
            } else {
                $ruta_curriculum = $curriculum_dir . '/' . uniqid() . '_' . basename($file_name);
                if (move_uploaded_file($file_tmp, $ruta_curriculum)) {
                    try {
                        $area1 = isset($subareas_seleccionadas[0]) ? $subareas_seleccionadas[0] : null;
                        $area2 = isset($subareas_seleccionadas[1]) ? $subareas_seleccionadas[1] : null;
                        $area3 = isset($subareas_seleccionadas[2]) ? $subareas_seleccionadas[2] : null;
                        $asignado = !empty($sugerencia) ? 'no' : 'sí';

                        $stmt = $conn->prepare("INSERT INTO empleados (id_plantel, nombre_completo, celular, correo, estudios, edad, ruta_curriculum, experiencia, area1, area2, area3, dispuesto, fecha_registro, asignado, sugerencia) 
                            VALUES (:id_plantel, :nombre_completo, :celular, :correo, :estudios, :edad, :ruta_curriculum, :experiencia, :area1, :area2, :area3, :dispuesto, :fecha_registro, :asignado, :sugerencia)");
                        $stmt->bindParam(':id_plantel', $id_plantel);
                        $stmt->bindParam(':nombre_completo', $nombre_completo);
                        $stmt->bindParam(':celular', $celular);
                        $stmt->bindParam(':correo', $correo);
                        $stmt->bindParam(':estudios', $estudios);
                        $stmt->bindParam(':edad', $edad);
                        $stmt->bindParam(':ruta_curriculum', $ruta_curriculum);
                        $stmt->bindParam(':experiencia', $experiencia);
                        $stmt->bindParam(':area1', $area1, PDO::PARAM_INT);
                        $stmt->bindParam(':area2', $area2, PDO::PARAM_INT);
                        $stmt->bindParam(':area3', $area3, PDO::PARAM_INT);
                        $stmt->bindParam(':dispuesto', $dispuesto);
                        $stmt->bindParam(':fecha_registro', $fecha_registro);
                        $stmt->bindParam(':asignado', $asignado);
                        $stmt->bindParam(':sugerencia', $sugerencia);
                        $stmt->execute();

                        // Send confirmation email
                        $email_config = include('email_config.php');
                        $mail = new PHPMailer(true);
                        try {
                            // SMTP configuration
                            $mail->isSMTP();
                            $mail->Host = $email_config['smtp_host'];
                            $mail->SMTPAuth = true;
                            $mail->Username = $email_config['smtp_username'];
                            $mail->Password = $email_config['smtp_password'];
                            $mail->SMTPSecure = $email_config['smtp_secure'];
                            $mail->Port = $email_config['smtp_port'];

                            // Email settings
                            $mail->setFrom($email_config['from_email'], $email_config['from_name']);
                            $mail->addAddress($correo);
                            $mail->isHTML(true);
                            $mail->Subject = 'Registro - Bolsa de Trabajo';
                            $mail->Body = "
                                <h2>¡Registro Exitoso!</h2>
                                <p>Estimado/a $nombre_completo,</p>
                                <p>Hemos recibido correctamente tu registro en Bolsa de Trabajo.</p>
                                <p>Gracias por tu interés. Nos pondremos en contacto contigo si hay oportunidades que coincidan con tu perfil.</p>
                                <p>Atentamente,<br>Canacintra Monclova</p>
                            ";
                            $mail->AltBody = "Registro Exitoso\n\nEstimado/a $nombre_completo,\nHemos recibido correctamente tu registro.\nDetalles:\n- Localidad: " . htmlspecialchars($_POST['id_plantel']) . "\n- Fecha: $fecha_registro\n- Correo: $correo\n- Celular: $celular\n\nGracias por tu interés.";

                            $mail->send();
                        } catch (Exception $e) {
                            error_log("Error al enviar correo: {$mail->ErrorInfo}");
                            // Don't fail the registration if email sending fails
                        }

                        // Store success message in session and redirect
                        $_SESSION['mensaje'] = "Registrado exitosamente.";
                        header("Location: " . $_SERVER['PHP_SELF']);
                        exit();

                    } catch(PDOException $e) {
                        $mensaje = "Error: " . $e->getMessage();
                        if ($ruta_curriculum && file_exists($ruta_curriculum)) {
                            unlink($ruta_curriculum);
                        }
                    }
                } else {
                    $mensaje = "Error al subir el archivo.";
                }
            }
        } else {
            $mensaje = "Error: Debes subir un archivo PDF.";
        }
    }
}

// Obtener planteles para el select
$stmt_planteles = $conn->prepare("SELECT id_plantel, nombre_plantel FROM planteles ORDER BY nombre_plantel");
$stmt_planteles->execute();
$planteles = $stmt_planteles->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Registrar Curriculum</title>
   <link rel="stylesheet" href="estilos_empleado.css?v=<?php echo time(); ?>">
   <link rel="icon" type="image/x-icon" href="../imagenes/logo-formulario.png">
   <style>
      /* Estilos adicionales para la sección dinámica de subáreas */
      .subareas-container {
          margin-top: 20px;
      }
      .area-group {
          border: 2px solid #001164;
          border-radius: 10px;
          background: #f0f4f8;
          padding: 10px;
          margin-bottom: 15px;
      }
      .area-group[data-area-id] > h3 {
          color: #001164;
          font-size: 16px;
          margin-bottom: 10px;
      }
      .subareas-grid {
          display: flex;
          flex-wrap: wrap;
          gap: 10px;
      }
      .subarea-card {
          flex: 1 0 calc(33.33% - 10px);
          background: #ffffff;
          border: 2px solid #001164;
          border-radius: 10px;
          padding: 10px;
          text-align: center;
          cursor: pointer;
          position: relative;
          transition: transform 0.2s;
      }
      .subarea-card:hover {
          transform: scale(1.05);
      }
      .subarea-card .info-icon {
          position: absolute;
          top: 5px;
          right: 5px;
          background: #e62e23;
          color: #fff;
          border-radius: 50%;
          width: 20px;
          height: 20px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          font-size: 12px;
          cursor: pointer;
      }
      .selected-grid {
          margin-top: 20px;
          display: flex;
          flex-wrap: wrap;
          gap: 10px;
      }
      .selected-card {
          background: #007bff;
          color: #fff;
          border: none;
          border-radius: 10px;
          cursor: pointer;
          transition: all 0.3s ease;
          box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4);
          text-transform: uppercase;
      }
      .selected-card:hover {
          background: #0056b3;
          transform: translateY(-2px);
          box-shadow: 0 6px 20px rgba(0, 123, 255, 0.6);
      }
      .selected-card .remove-btn {
          position: absolute;
          top: 5px;
          right: 5px;
          background: #ffffff;
          color: #e62e23;
          border: none;
          border-radius: 50%;
          width: 20px;
          height: 20px;
          font-size: 12px;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
      }
      .tooltip {
          position: absolute;
          background: #ffffff;
          border: 1px solid #001164;
          padding: 5px 10px;
          border-radius: 5px;
          font-size: 13px;
          z-index: 100;
          box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
      }
      .sugerencia-container {
          margin-top: 20px;
          display: none;
      }
      .sugerencia-container textarea {
          width: 100%;
          height: 100px;
          padding: 10px;
          border: 2px solid #001164;
          border-radius: 10px;
          resize: none;
      }
   </style>
</head>
<body>
   <div class="background-logo">
      <a href="../index.php">
         <img src="../imagenes/logo-fondo.png" alt="Logo de fondo">
      </a>
   </div>

   <div class="container">
      <div class="content">
         <h1>Registrar Curriculum</h1>
         <?php if (isset($mensaje)): ?>
            <p class="mensaje <?php echo strpos($mensaje, 'Error') === false ? 'success' : 'error'; ?>">
               <?php echo htmlspecialchars($mensaje); ?>
            </p>
         <?php endif; ?>
         <form action="" method="POST" class="form-registro" enctype="multipart/form-data">
            <div class="form-group">
               <label for="id_plantel">Localidad:</label>
               <select id="id_plantel" name="id_plantel" required>
                  <option value="">Selecciona una localidad</option>
                  <?php foreach ($planteles as $plantel): ?>
                     <option value="<?php echo $plantel['id_plantel']; ?>">
                        <?php echo htmlspecialchars($plantel['nombre_plantel']); ?>
                     </option>
                  <?php endforeach; ?>
               </select>
            </div>
            <div class="form-group">
               <label for="nombre">Nombre:</label>
               <input type="text" id="nombre" name="nombre" required placeholder="Nombre">
            </div>
            <div class="form-group">
               <label for="apellido_paterno">Apellido Paterno:</label>
               <input type="text" id="apellido_paterno" name="apellido_paterno" required placeholder="Apellido Paterno">
            </div>
            <div class="form-group">
               <label for="apellido_materno">Apellido Materno:</label>
               <input type="text" id="apellido_materno" name="apellido_materno" required placeholder="Apellido Materno">
            </div>
            <div class="form-group">
               <label for="correo">Correo:</label>
               <input type="email" id="correo" name="correo" required placeholder="correo@ejemplo.com">
            </div>
            <div class="form-group">
               <label for="celular">Teléfono:</label>
               <input type="tel" id="celular" name="celular" required placeholder="1234567890">
            </div>
            <div class="form-group">
               <label for="edad">Edad:</label>
               <input type="number" id="edad" name="edad" required min="18" max="100" placeholder="Edad">
            </div>
            <div class="form-group">
               <label for="estudios">Nivel de Estudios:</label>
               <select id="estudios" name="estudios" required>
                  <option value="">Selecciona nivel de estudios</option>
                  <option value="primaria">Primaria</option>
                  <option value="secundaria">Secundaria</option>
                  <option value="preparatoria">Preparatoria</option>
                  <option value="universidad">Universidad</option>
                  <option value="maestria">Maestría</option>
                  <option value="doctorado">Doctorado</option>
               </select>
            </div>
            <div class="form-group">
               <label for="experiencia">Experiencia:</label>
               <select id="experiencia" name="experiencia" required>
                  <option value="">Selecciona nivel de experiencia</option>
                  <option value="Sin experiencia">Sin experiencia</option>
                  <option value="0-1 años de experiencia">0-1 años de experiencia</option>
                  <option value="1-5 años de experiencia">1-5 años de experiencia</option>
                  <option value="mas de 5 años de experiencia">Más de 5 años de experiencia</option>
               </select>
            </div>
            <div class="form-group">
               <label for="dispuesto">¿Dispuesto a viajar?:</label>
               <select id="dispuesto" name="dispuesto" required>
                  <option value="">Selecciona una opción</option>
                  <option value="si">Sí</option>
                  <option value="no">No</option>
               </select>
            </div>
            <div class="form-group">
               <label for="curriculum">Curriculum (solo PDF, máximo 2MB):</label>
               <input type="file" id="curriculum" name="curriculum" accept=".pdf" required>
            </div>
            <div class="form-group">
               <label for="subareas">Subáreas (selecciona al menos 1, máximo 3, o ingresa una sugerencia):</label>
               <div id="selected-subareas" class="selected-grid"></div>
               <div id="available-subareas" class="subareas-container"></div>
               <div class="area-group" id="otra-profesion-group">
                  <h3 id="otra-profesion-title">Otra profesión?</h3>
                  <div class="sugerencia-container" id="sugerencia-container">
                     <textarea id="sugerencia" name="sugerencia" placeholder="Ingresa una sugerencia de área o subárea"></textarea>
                  </div>
               </div>
               <button type="submit" class="submit-button">Registrar</button>
            </div>
         </form>
      </div>
   </div>                
   <script>
      // Validación del archivo: solo se permiten PDF y tamaño máximo de 2MB
      document.getElementById('curriculum').addEventListener('change', function(event) {
         const file = event.target.files[0];
         if (file) {
            const extension = file.name.split('.').pop().toLowerCase();
            const maxSize = 2 * 1024 * 1024; // 2MB en bytes
            if (extension !== 'pdf') {
               alert('Error: Solo se permiten archivos PDF.');
               event.target.value = '';
            } else if (file.size > maxSize) {
               alert('Error: El archivo excede el límite de 2MB.');
               event.target.value = '';
            }
         }
      });

      // Lógica para la selección dinámica de subáreas
      let selectedSubareas = {};

      function cargarSubareas() {
         const availableContainer = document.getElementById('available-subareas');
         availableContainer.innerHTML = '';
         document.getElementById('selected-subareas').innerHTML = '';
         selectedSubareas = {};

         // Cargar áreas y subáreas solo para id_plantel = 1
         fetch('obtener_areas.php?id_plantel=1')
            .then(response => response.json())
            .then(data => {
               if (!data.areas || data.areas.length === 0) {
                  availableContainer.innerHTML = '<p>No hay subáreas disponibles.</p>';
                  return;
               }
               data.areas.forEach(area => {
                  if (data.subareas[area.id_area] && data.subareas[area.id_area].length > 0) {
                     const areaGroup = document.createElement('div');
                     areaGroup.classList.add('area-group');
                     areaGroup.dataset.areaId = area.id_area;

                     const areaTitle = document.createElement('h3');
                     areaTitle.textContent = area.nombre_area;
                     
                     areaTitle.addEventListener('click', function(e) {
                        e.stopPropagation();
                        document.querySelectorAll('.area-group.expanded').forEach(group => {
                           if (group !== areaGroup) {
                              group.classList.remove('expanded');
                           }
                        });
                        areaGroup.classList.toggle('expanded');
                     });
                     
                     areaGroup.appendChild(areaTitle);

                     const subareasGrid = document.createElement('div');
                     subareasGrid.classList.add('subareas-grid');

                     data.subareas[area.id_area].forEach(subarea => {
                        const card = document.createElement('div');
                        card.classList.add('subarea-card');
                        card.dataset.id = subarea.id_subarea;
                        card.dataset.areaId = area.id_area;
                        card.dataset.descripcion = subarea.descripcion || 'Sin descripción';
                        card.textContent = subarea.nombre_subarea;

                        const infoIcon = document.createElement('span');
                        infoIcon.classList.add('info-icon');
                        infoIcon.textContent = 'i';
                        infoIcon.addEventListener('click', function(e) {
                           e.stopPropagation();
                           mostrarTooltip(e.currentTarget, subarea.descripcion || 'Sin descripción');
                        });
                        card.appendChild(infoIcon);

                        card.addEventListener('click', function(e) {
                           e.stopPropagation();
                           seleccionarSubarea(card);
                        });

                        subareasGrid.appendChild(card);
                     });

                     areaGroup.appendChild(subareasGrid);
                     availableContainer.appendChild(areaGroup);
                  }
               });
            })
            .catch(error => {
               availableContainer.innerHTML = '<p>Error al cargar las subáreas.</p>';
               console.error('Error al cargar las subáreas:', error);
            });
      }

      function seleccionarSubarea(card) {
         if (Object.keys(selectedSubareas).length >= 3) {
            alert('No puedes seleccionar más de 3 subáreas.');
            return;
         }
         const id = card.dataset.id;
         if (selectedSubareas[id]) return;
         card.parentNode.removeChild(card);
         agregarSubareaSeleccionada(card);
      }

      function agregarSubareaSeleccionada(card) {
         const selectedContainer = document.getElementById('selected-subareas');
         card.classList.add('selected-card');

         const removeBtn = document.createElement('button');
         removeBtn.classList.add('remove-btn');
         removeBtn.textContent = 'X';
         removeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            removerSubarea(card);
         });
         card.appendChild(removeBtn);

         const inputHidden = document.createElement('input');
         inputHidden.type = 'hidden';
         inputHidden.name = 'areas[]';
         inputHidden.value = card.dataset.id;
         card.appendChild(inputHidden);

         selectedSubareas[card.dataset.id] = card;
         selectedContainer.appendChild(card);
      }

      function removerSubarea(card) {
         delete selectedSubareas[card.dataset.id];
         card.parentNode.removeChild(card);
         card.classList.remove('selected-card');
         const removeBtn = card.querySelector('.remove-btn');
         if (removeBtn) removeBtn.remove();
         const inputHidden = card.querySelector('input[type="hidden"]');
         if (inputHidden) inputHidden.remove();

         const availableContainer = document.getElementById('available-subareas');
         const group = availableContainer.querySelector(`.area-group[data-area-id="${card.dataset.areaId}"] .subareas-grid`);
         if (group) {
            group.appendChild(card);
         } else {
            availableContainer.appendChild(card);
         }
      }

      function mostrarTooltip(target, texto) {
         const existing = document.querySelector('.tooltip');
         if (existing) existing.remove();

         const tooltip = document.createElement('div');
         tooltip.classList.add('tooltip');
         tooltip.textContent = texto;
         document.body.appendChild(tooltip);

         const rect = target.getBoundingClientRect();
         tooltip.style.top = (rect.top + window.scrollY + 25) + 'px';
         tooltip.style.left = (rect.left + window.scrollX) + 'px';

         setTimeout(() => {
            if (tooltip.parentNode) tooltip.parentNode.removeChild(tooltip);
         }, 3000);
      }

      // Cargar subáreas al iniciar la página
      document.addEventListener('DOMContentLoaded', function() {
         cargarSubareas();
         // Configurar el evento para "Otra profesión"
         const otraProfesionTitle = document.getElementById('otra-profesion-title');
         const sugerenciaContainer = document.getElementById('sugerencia-container');
         otraProfesionTitle.addEventListener('click', function(e) {
            e.stopPropagation();
            document.querySelectorAll('.area-group.expanded').forEach(group => {
               if (group !== document.getElementById('otra-profesion-group')) {
                  group.classList.remove('expanded');
               }
            });
            document.getElementById('otra-profesion-group').classList.toggle('expanded');
            sugerenciaContainer.style.display = document.getElementById('otra-profesion-group').classList.contains('expanded') ? 'block' : 'none';
         });
      });

      document.addEventListener('click', function(e) {
         if (!e.target.closest('.area-group')) {
            document.querySelectorAll('.area-group.expanded').forEach(group => {
               group.classList.remove('expanded');
               if (group.id === 'otra-profesion-group') {
                  document.getElementById('sugerencia-container').style.display = 'none';
               }
            });
         }
      });
   </script>
</body>
</html>