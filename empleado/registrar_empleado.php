<?php
require_once '../conexion.php';

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
    // Los IDs de las subáreas se mandan mediante los inputs hidden generados dinámicamente.
    $subareas_seleccionadas = isset($_POST['areas']) ? $_POST['areas'] : [];
    $fecha_registro = date('Y-m-d'); // Fecha actual

    // Depuración: imprimir las subáreas seleccionadas en el log del servidor.
    error_log("Subáreas seleccionadas: " . print_r($subareas_seleccionadas, true));

    // Validar correo si se proporciona
    if (!empty($correo)) {
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $mensaje = "Error: El correo proporcionado no es válido.";
        }
    } else {
        $correo = null;
    }

    // Validar que se seleccione al menos 1 y no más de 3 subáreas
    if (count($subareas_seleccionadas) < 1) {
        $mensaje = "Error: Debes seleccionar al menos 1 subárea.";
    } elseif (count($subareas_seleccionadas) > 3) {
        $mensaje = "Error: No puedes seleccionar más de 3 subáreas.";
    } else {
        // Crear carpeta para los curriculums si no existe
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

            // Verificar si es un archivo PDF
            if ($file_extension !== 'pdf' || $file_type !== 'application/pdf') {
                $mensaje = "Error: Solo se permiten archivos PDF.";
            } else {
                $ruta_curriculum = $curriculum_dir . '/' . uniqid() . '_' . basename($file_name);
                if (move_uploaded_file($file_tmp, $ruta_curriculum)) {
                    try {
                        // Asignar las subáreas a area1, area2 y area3 (según el orden de selección)
                        $area1 = $subareas_seleccionadas[0];
                        $area2 = isset($subareas_seleccionadas[1]) ? $subareas_seleccionadas[1] : null;
                        $area3 = isset($subareas_seleccionadas[2]) ? $subareas_seleccionadas[2] : null;

                        $stmt = $conn->prepare("INSERT INTO empleados (id_plantel, nombre_completo, celular, correo, estudios, edad, ruta_curriculum, experiencia, area1, area2, area3, dispuesto, fecha_registro) 
                            VALUES (:id_plantel, :nombre_completo, :celular, :correo, :estudios, :edad, :ruta_curriculum, :experiencia, :area1, :area2, :area3, :dispuesto, :fecha_registro)");
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
                        $stmt->execute();

                        $mensaje = "Empleado registrado exitosamente.";
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
   <title>Registrar Empleado</title>
   <link rel="stylesheet" href="estilos_empleado.css?v=<?php echo time(); ?>">
   <style>
      /* Estilos adicionales para la sección dinámica de subáreas */

      /* Contenedor para las subáreas disponibles */
      .subareas-container {
          margin-top: 20px;
      }
      /* Grupo de subáreas por área */
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
      /* Grid de tarjetas para las subáreas */
      .subareas-grid {
          display: flex;
          flex-wrap: wrap;
          gap: 10px;
      }
      /* Tarjeta de subárea disponible */
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
      /* Icono de información en cada tarjeta */
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
      /* Contenedor para las subáreas seleccionadas */
      .selected-grid {
          margin-top: 20px;
          display: flex;
          flex-wrap: wrap;
          gap: 10px;
      }
      /* Tarjeta para subárea seleccionada */
      .selected-card {
        background: #007bff; /* Azul */
        color: #fff;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4); /* Sombra azul */
        text-transform: uppercase;
      }
        .selected-card:hover {
            background: #0056b3; /* Azul más oscuro */
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.6); /* Sombra azul más intensa */
        }
      /* Botón para eliminar la selección */
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
      /* Tooltip para la descripción de la subárea */
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
   </style>
</head>
<body>
   <!-- Logo en el fondo de la página convertido en botón -->
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
               <label for="correo">Correo (opcional):</label>
               <input type="email" id="correo" name="correo" placeholder="correo@ejemplo.com (opcional)">
            </div>
            <div class="form-group">
               <label for="celular">Celular:</label>
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
               <label for="curriculum">Curriculum (solo PDF):</label>
               <input type="file" id="curriculum" name="curriculum" accept=".pdf" required>
               
               
            </div>
            <!-- Sección dinámica de selección de subáreas -->
            <div class="form-group">
               <label for="subareas">Subáreas (selecciona al menos 1, máximo 3):</label>
               <button type="submit" class="submit-button">Registrar</button>
               <div id="selected-subareas" class="selected-grid"></div>
               
               <!-- Aquí se cargarán los subáreas disponibles -->
               <div id="available-subareas" class="subareas-container"></div>
            </div>
            
         </form>
      </div>
   </div>                
   <script>
      // Validación del archivo: solo se permiten PDF
      document.getElementById('curriculum').addEventListener('change', function(event) {
         const file = event.target.files[0];
         if (file) {
            const extension = file.name.split('.').pop().toLowerCase();
            if (extension !== 'pdf') {
               alert('Error: Solo se permiten archivos PDF.');
               event.target.value = '';
            }
         }
      });

      // Lógica para la selección dinámica de subáreas
      let selectedSubareas = {};

      // Cargar las subáreas disponibles según la localidad seleccionada
      function cargarSubareas() {
         const idPlantel = document.getElementById('id_plantel').value;
         const availableContainer = document.getElementById('available-subareas');
         availableContainer.innerHTML = ''; // Limpiar contenedor
         // Reiniciar las subáreas seleccionadas al cambiar de plantel
         document.getElementById('selected-subareas').innerHTML = '';
         selectedSubareas = {};

         fetch('obtener_areas.php?id_plantel=' + idPlantel)
            .then(response => response.json())
            .then(data => {
               if (!data.areas || data.areas.length === 0) {
                  availableContainer.innerHTML = '<p>No hay subáreas disponibles para esta localidad.</p>';
                  return;
               }
               data.areas.forEach(area => {
                  if (data.subareas[area.id_area] && data.subareas[area.id_area].length > 0) {
                     const areaGroup = document.createElement('div');
                     areaGroup.classList.add('area-group');
                     areaGroup.dataset.areaId = area.id_area;

                     const areaTitle = document.createElement('h3');
                     areaTitle.textContent = area.nombre_area;
                     
                     // Agregar listener para desplegar/contraer la subárea
                     areaTitle.addEventListener('click', function(e) {
                        // Evitar que la acción se propague a otros manejadores
                        e.stopPropagation();
                        
                        // Cerrar otros grupos expandidos
                        document.querySelectorAll('.area-group.expanded').forEach(group => {
                           if (group !== areaGroup) {
                              group.classList.remove('expanded');
                           }
                        });
                        
                        // Alterna la visibilidad del contenedor actual
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

                        // Ícono de información (tooltip)
                        const infoIcon = document.createElement('span');
                        infoIcon.classList.add('info-icon');
                        infoIcon.textContent = 'i';
                        infoIcon.addEventListener('click', function(e) {
                           e.stopPropagation();
                           mostrarTooltip(e.currentTarget, subarea.descripcion || 'Sin descripción');
                        });
                        card.appendChild(infoIcon);

                        // Seleccionar la subárea
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

      // Función para mover una subárea a la sección de seleccionadas
      function seleccionarSubarea(card) {
         if (Object.keys(selectedSubareas).length >= 3) {
            alert('No puedes seleccionar más de 3 subáreas.');
            return;
         }
         const id = card.dataset.id;
         if (selectedSubareas[id]) return; // Evitar duplicados
         card.parentNode.removeChild(card);
         agregarSubareaSeleccionada(card);
      }

      // Agregar la tarjeta a la sección de subáreas seleccionadas
      function agregarSubareaSeleccionada(card) {
         const selectedContainer = document.getElementById('selected-subareas');
         card.classList.add('selected-card');

         // Botón para remover la subárea seleccionada
         const removeBtn = document.createElement('button');
         removeBtn.classList.add('remove-btn');
         removeBtn.textContent = 'X';
         removeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            removerSubarea(card);
         });
         card.appendChild(removeBtn);

         // Input hidden para enviar el ID de la subárea en el formulario
         const inputHidden = document.createElement('input');
         inputHidden.type = 'hidden';
         inputHidden.name = 'areas[]';
         inputHidden.value = card.dataset.id;
         card.appendChild(inputHidden);

         selectedSubareas[card.dataset.id] = card;
         selectedContainer.appendChild(card);
      }

      // Remover una subárea de la selección y devolverla a la lista de disponibles
      function removerSubarea(card) {
         delete selectedSubareas[card.dataset.id];
         card.parentNode.removeChild(card);
         card.classList.remove('selected-card');
         const removeBtn = card.querySelector('.remove-btn');
         if (removeBtn) removeBtn.remove();
         const inputHidden = card.querySelector('input[type="hidden"]');
         if (inputHidden) inputHidden.remove();

         // Ubicar la tarjeta en su grupo original (por área)
         const availableContainer = document.getElementById('available-subareas');
         const group = availableContainer.querySelector(`.area-group[data-area-id="${card.dataset.areaId}"] .subareas-grid`);
         if (group) {
            group.appendChild(card);
         } else {
            availableContainer.appendChild(card);
         }
      }

      // Mostrar un tooltip al lado del icono con la descripción de la subárea
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

         // Remover el tooltip automáticamente después de 3 segundos
         setTimeout(() => {
            if (tooltip.parentNode) tooltip.parentNode.removeChild(tooltip);
         }, 3000);
      }

      // Cargar subáreas dinámicamente al cambiar la localidad
      document.getElementById('id_plantel').addEventListener('change', function() {
         cargarSubareas();
      });
      document.addEventListener('click', function(e) {
         // Si el click no ocurre dentro de un .area-group, se cierran los grupos expandidos.
         if (!e.target.closest('.area-group')) {
            document.querySelectorAll('.area-group.expanded').forEach(group => {
               group.classList.remove('expanded');
            });
         }
      });

   </script>
</body>
</html>
