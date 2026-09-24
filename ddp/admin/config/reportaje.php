<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../autorizacion.php';
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if (empty($_SESSION['admin_logueado'])) {
    header('Location: ../login.php');
    exit();
}

require_once __DIR__ . '/../conexion.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$conexion = new conexion();
$existente = $id ? $conexion->obtenerReportaje($id) : null;

if ($id && $existente === null) {
    $conexion->close();
    http_response_code(404);
    exit('Reportaje no encontrado.');
}

$formulario = [
    'titulo' => $existente['titulo'] ?? '',
    'resumen_corto' => $existente['resumen_corto'] ?? '',
    'desarrollo' => $existente['desarrollo'] ?? '',
    'foto_principal' => $existente['foto_principal'] ?? '',
    'pdf_adjunto' => $existente['pdf_adjunto'] ?? '',
    'estado' => $existente['estado'] ?? 'borrador',
    'fecha_publicacion' => $existente['fecha_publicacion'] ?? date('Y-m-d'),
    'es_destacado' => (int) ($existente['es_destacado'] ?? 0),
    'autor_nombres' => $existente['autor_nombres'] ?? '',
    'autor_ap_paterno' => $existente['autor_ap_paterno'] ?? '',
    'autor_ap_materno' => $existente['autor_ap_materno'] ?? '',
];
$contenidoGuardado = json_decode((string) $formulario['desarrollo'], true);
if (is_array($contenidoGuardado) && isset($contenidoGuardado[0]['contenido'])) {
    $parrafos = array_map(static fn (array $parrafo): array => [
        'titulo' => trim((string) ($parrafo['titulo'] ?? '')),
        'contenido' => trim((string) ($parrafo['contenido'] ?? '')),
    ], $contenidoGuardado);
} else {
    $parrafos = array_map(static fn (string $parrafo): array => ['titulo' => '', 'contenido' => $parrafo], preg_split('/\R\s*\R/', trim((string) $formulario['desarrollo'])) ?: []);
}
$parrafos = array_values(array_filter($parrafos, static fn (array $parrafo): bool => $parrafo['contenido'] !== ''));
if ($parrafos === []) {
    $parrafos = [['titulo' => '', 'contenido' => '']];
}
$error = '';

function escaparReportaje(?string $valor): string
{
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}

function palabrasReportaje(string $texto): int
{
    return preg_match_all('/\S+/u', trim($texto)) ?: 0;
}

function guardarArchivoReportaje(array $archivo, string $directorio, array $extensiones, string $prefijo, string $ruta): string
{
    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if (($archivo['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo cargar el archivo.');
    }
    $extension = strtolower(pathinfo((string) $archivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $extensiones, true)) {
        throw new RuntimeException('El formato del archivo no es válido.');
    }
    if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) {
        throw new RuntimeException('No se pudo preparar la carpeta de archivos.');
    }
    $nombre = $prefijo . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
    if (!move_uploaded_file((string) $archivo['tmp_name'], $directorio . DIRECTORY_SEPARATOR . $nombre)) {
        throw new RuntimeException('No se pudo guardar el archivo.');
    }
    return $ruta . $nombre;
}

function guardarImagenReportaje(array $archivo): string
{
    return guardarArchivoReportaje($archivo, __DIR__ . '/image', ['jpg', 'jpeg', 'png', 'webp'], 'reportaje', 'config/image/');
}

function guardarPdfReportaje(array $archivo): string
{
    return guardarArchivoReportaje($archivo, __DIR__ . '/Pdf', ['pdf'], 'reportaje', 'config/Pdf/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['titulo', 'resumen_corto', 'desarrollo', 'estado', 'fecha_publicacion', 'autor_nombres', 'autor_ap_paterno', 'autor_ap_materno'] as $campo) {
        $formulario[$campo] = trim((string) ($_POST[$campo] ?? $formulario[$campo]));
    }
    if (isset($_POST['parrafos']) && is_array($_POST['parrafos'])) {
        $parrafos = array_values(array_filter(array_map(
            static fn ($parrafo): array => [
                'titulo' => trim((string) ($parrafo['titulo'] ?? '')),
                'contenido' => trim((string) ($parrafo['contenido'] ?? '')),
            ],
            $_POST['parrafos']
        ), static fn (array $parrafo): bool => $parrafo['contenido'] !== ''));
        foreach ($parrafos as &$parrafo) {
            if (mb_strlen($parrafo['titulo']) > 100) {
                $error = 'Cada título de párrafo puede tener como máximo 100 caracteres.';
                break;
            }
        }
        unset($parrafo);
        $formulario['desarrollo'] = json_encode($parrafos, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
    $formulario['es_destacado'] = isset($_POST['es_destacado']) ? 1 : 0;

    if ($formulario['titulo'] === '' || $formulario['desarrollo'] === '' ||
        mb_strlen($formulario['titulo']) > 100 || $error !== '' ||
        !in_array($formulario['estado'], ['borrador', 'publicado'], true) ||
        $formulario['fecha_publicacion'] === '') {
        $error = 'Completa los campos obligatorios del reportaje.';
    } else {
        try {
            $foto = guardarImagenReportaje($_FILES['foto_principal'] ?? []);
            $pdf = guardarPdfReportaje($_FILES['pdf_adjunto'] ?? []);
            $nuevasFotos = [];
            $reemplazosFotos = [];
            foreach (($_FILES['reemplazar_imagen']['name'] ?? []) as $fotoId => $nombre) {
                $archivo = [
                    'name' => $nombre,
                    'type' => $_FILES['reemplazar_imagen']['type'][$fotoId] ?? '',
                    'tmp_name' => $_FILES['reemplazar_imagen']['tmp_name'][$fotoId] ?? '',
                    'error' => $_FILES['reemplazar_imagen']['error'][$fotoId] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $_FILES['reemplazar_imagen']['size'][$fotoId] ?? 0,
                ];
                $url = guardarImagenReportaje($archivo);
                if ($url !== '') {
                    $reemplazosFotos[(int) $fotoId] = $url;
                }
            }
            foreach (($_FILES['galeria']['name'] ?? []) as $indice => $nombre) {
                $archivo = [
                    'name' => $nombre,
                    'type' => $_FILES['galeria']['type'][$indice] ?? '',
                    'tmp_name' => $_FILES['galeria']['tmp_name'][$indice] ?? '',
                    'error' => $_FILES['galeria']['error'][$indice] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $_FILES['galeria']['size'][$indice] ?? 0,
                ];
                $url = guardarImagenReportaje($archivo);
                if ($url !== '') {
                    $nuevasFotos[] = [
                        'url' => $url,
                        'orden' => $_POST['galeria_orden'][$indice] ?? ($indice + 1),
                        'parrafo_despues' => $_POST['galeria_parrafo'][$indice] ?? 0,
                        'descripcion' => trim((string) ($_POST['galeria_descripcion'][$indice] ?? '')),
                    ];
                }
            }
            foreach ($_POST['fotos_existentes'] ?? [] as $ubicacion) {
                if (palabrasReportaje((string) ($ubicacion['descripcion'] ?? '')) > 125) {
                    throw new RuntimeException('Cada descripción de imagen puede tener como máximo 125 palabras.');
                }
            }
            foreach ($nuevasFotos as $nuevaFoto) {
                if (palabrasReportaje($nuevaFoto['descripcion']) > 125) {
                    throw new RuntimeException('Cada descripción de imagen puede tener como máximo 125 palabras.');
                }
            }

            $datos = $formulario;
            $datos['foto_principal'] = $foto !== '' ? $foto : ($existente['foto_principal'] ?? '');
            if (isset($_POST['eliminar_foto_principal'])) {
                $datos['foto_principal'] = '';
            }
            $datos['pdf_adjunto'] = $pdf !== '' ? $pdf : ($existente['pdf_adjunto'] ?? '');
            $datos['autor_id'] = (int) ($existente['autor_id'] ?? 0);
            $datos['fotos_existentes'] = $_POST['fotos_existentes'] ?? [];
            $datos['fotos_eliminar'] = $_POST['fotos_eliminar'] ?? [];
            $datos['fotos_reemplazos'] = $reemplazosFotos;
            $datos['fotos_nuevas'] = $nuevasFotos;
            $guardadoId = $conexion->guardarReportaje($datos, (int) ($_SESSION['usuario_id'] ?? 0), $id);
            $conexion->close();
            header('Location: reportaje.php?id=' . $guardadoId . '&guardado=1');
            exit();
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $error = $exception->getMessage();
        }
    }
}

$conexion->close();
$guardado = isset($_GET['guardado']);
?>
<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $id ? 'Editar' : 'Nuevo' ?> reportaje | DDP</title>
  <link href="https://fonts.googleapis.com/css?family=Muli:300,300i,400,400i,600,600i,700,700i%7CComfortaa:300,400,700" rel="stylesheet">
  <link href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome.min.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="../theme-assets/css/vendors.css">
  <link rel="stylesheet" type="text/css" href="../theme-assets/css/app-lite.css">
  <link rel="stylesheet" type="text/css" href="../theme-assets/css/core/menu/menu-types/vertical-menu.css">
  <link rel="stylesheet" type="text/css" href="../assets/css/responsive-admin.css">
  <link rel="stylesheet" type="text/css" href="../assets/css/reportaje_edit.css">
  <link rel="stylesheet" type="text/css" href="../assets/css/paleta-roja.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="vertical-layout vertical-menu 2-columns menu-expanded fixed-navbar" data-open="click" data-menu="vertical-menu" data-color="bg-chartbg" data-col="2-columns">
  <div class="main-menu menu-fixed menu-light menu-accordion menu-shadow" data-scroll-to-active="true" data-img="../theme-assets/images/backgrounds/02.jpg">
    <div class="navbar-header">
      <ul class="nav navbar-nav flex-row">
        <li class="nav-item mr-auto"><a class="navbar-brand" href="../index.php"><img class="brand-logo" alt="admin logo" src="../theme-assets/images/logo/logo.png"><h3 class="brand-text">DDP</h3></a></li>
        <li class="nav-item d-md-none"><a class="nav-link close-navbar"><i class="ft-x"></i></a></li>
      </ul>
    </div>
    <div class="main-menu-content">
      <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
        <li class="active"><a href="../index.php"><i class="ft-home"></i><span class="menu-title">Inicio</span></a></li>
        <li class="nav-item"><a href="../Reportaje.php"><i class="ft-book"></i><span class="menu-title">Reportaje</span></a></li>
        <li class="nav-item"><a href="../Noticias.php"><i class="la la-newspaper-o"></i><span class="menu-title">Noticias</span></a></li>
        <li class="nav-item"><a href="../Podcast.php"><i class="ft-music"></i><span class="menu-title">Podcast</span></a></li>
        <li class="nav-item active"><a href="../video.php"><i class="ft-play"></i><span class="menu-title">videos</span></a></li>
        <li class="nav-item"><a href="../boletines.php"><i class="la la-leanpub"></i><span class="menu-title">Boletines</span></a></li>
        <?php if (!esEditor()): ?><li class="nav-item"><a href="../usuarios.php"><i class="ft-user"></i><span class="menu-title">usuarios</span></a></li><?php endif; ?>
        <li class="nav-item"><a href="../logout.php"><i class="ft-power"></i><span class="menu-title">Cerrar Sesion</span></a></li>
      </ul>
    </div>
    <div class="navigation-background"></div>
  </div>
  <main class="editor-page">
    <div class="editor-heading"><div><span class="editor-eyebrow">CONTENIDO EDITORIAL</span><h1><?= $id ? 'Editar reportaje' : 'Nuevo reportaje' ?></h1><p>Completa la información y administra los archivos del reportaje.</p></div><a class="editor-back" href="../Reportaje.php">Volver a reportajes</a></div>
    <?php if ($guardado): ?><div class="editor-alert success">El reportaje se guardó correctamente.</div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="editor-alert error"><?= escaparReportaje($error) ?></div><?php endif; ?>
    <form class="editor-form" method="post" enctype="multipart/form-data">
      <?php if ($id): ?><input type="hidden" name="id" value="<?= (int) $id ?>"><?php endif; ?>
      <section class="editor-section"><h2>Información principal</h2><div class="editor-grid">
        <label class="wide">Título principal *<input type="text" name="titulo" maxlength="100" value="<?= escaparReportaje($formulario['titulo']) ?>" required><small>Máximo 100 caracteres.</small></label>
        <label>Estado<select name="estado"><option value="borrador" <?= $formulario['estado'] === 'borrador' ? 'selected' : '' ?>>Borrador</option><option value="publicado" <?= $formulario['estado'] === 'publicado' ? 'selected' : '' ?>>Publicado</option></select></label>
        <label>Fecha de publicación *<input type="date" name="fecha_publicacion" value="<?= escaparReportaje($formulario['fecha_publicacion']) ?>" required></label>
        <label class="wide">Resumen corto<input type="text" name="resumen_corto" maxlength="500" value="<?= escaparReportaje($formulario['resumen_corto']) ?>"></label>
        <div class="wide paragraph-editor"><div class="paragraph-editor-heading"><strong>Contenido por párrafos *</strong><button type="button" class="editor-add editor-button" id="agregar-parrafo">+ Agregar párrafo</button></div><div id="parrafos-editor"><?php foreach ($parrafos as $indice => $parrafo): ?><div class="paragraph-row"><span class="paragraph-number">Párrafo <?= $indice + 1 ?></span><input type="text" name="parrafos[<?= $indice ?>][titulo]" maxlength="100" placeholder="Título opcional en negrita (máximo 100 caracteres)" value="<?= escaparReportaje($parrafo['titulo']) ?>"><textarea name="parrafos[<?= $indice ?>][contenido]" rows="4" required><?= escaparReportaje($parrafo['contenido']) ?></textarea><?php if ($indice > 0): ?><button type="button" class="editor-remove editor-button editor-button-danger remove-paragraph">Eliminar párrafo</button><?php endif; ?></div><?php endforeach; ?></div><small>El título es opcional y aparecerá en negrita.</small></div>
        <label class="check"><input type="checkbox" name="es_destacado" <?= $formulario['es_destacado'] ? 'checked' : '' ?>> Marcar como destacado</label>
      </div></section>
      <section class="editor-section"><h2>Autor <small>(opcional)</small></h2><div class="editor-grid">
        <label>Nombres<input type="text" name="autor_nombres" value="<?= escaparReportaje($formulario['autor_nombres']) ?>"></label>
        <label>Apellido paterno<input type="text" name="autor_ap_paterno" value="<?= escaparReportaje($formulario['autor_ap_paterno']) ?>"></label>
        <label>Apellido materno<input type="text" name="autor_ap_materno" value="<?= escaparReportaje($formulario['autor_ap_materno']) ?>"></label>
      </div></section>
      <section class="editor-section"><h2>Archivos</h2><div class="editor-grid">
        <div class="field-label">Imagen principal<?php if ($formulario['foto_principal']): ?><small>Actual: <?= escaparReportaje($formulario['foto_principal']) ?></small><img id="imagen-principal-preview" class="principal-image-preview" src="../<?= escaparReportaje($formulario['foto_principal']) ?>" alt="Imagen principal actual"><?php else: ?><img id="imagen-principal-preview" class="principal-image-preview is-hidden" alt="Vista previa de imagen principal"><?php endif; ?><span class="photo-actions"><label class="editor-button editor-button-secondary replace-principal" title="Reemplazar imagen"><i class="bi bi-box-arrow-up"></i><span>Reemplazar</span><input id="imagen-principal-input" class="replace-photo-input" type="file" name="foto_principal" accept=".jpg,.jpeg,.png,.webp"></label><?php if ($formulario['foto_principal']): ?><label class="editor-button editor-button-danger delete-photo" title="Eliminar imagen"><i class="bi bi-trash-fill"></i><span>Eliminar</span><input type="checkbox" name="eliminar_foto_principal"></label><?php endif; ?></span></div>
        <label>PDF adjunto<input type="file" name="pdf_adjunto" accept=".pdf"><?php if ($formulario['pdf_adjunto']): ?><small>Actual: <?= escaparReportaje($formulario['pdf_adjunto']) ?></small><?php endif; ?></label>
        <label class="wide">Galería de imágenes<input id="galeria" type="file" name="galeria[]" accept=".jpg,.jpeg,.png,.webp" multiple><small>Selecciona imágenes y luego define su orden y párrafo.</small></label>
      </div>
      <div id="galeria-config" class="gallery-config"></div>
      <?php if (!empty($existente['fotos'])): ?><div class="existing-gallery"><strong>Galería actual</strong><?php foreach ($existente['fotos'] as $foto): ?><div class="existing-gallery-item"><img class="gallery-image-preview" src="../<?= escaparReportaje($foto['url_foto']) ?>" alt="Imagen actual"><label>Orden<input type="number" min="1" name="fotos_existentes[<?= (int) $foto['id'] ?>][orden]" value="<?= max(1, (int) $foto['orden']) ?>"></label><label>Mostrar después de<select class="paragraph-location" name="fotos_existentes[<?= (int) $foto['id'] ?>][parrafo_despues]"><?php for ($ubicacion = 0; $ubicacion <= count($parrafos); $ubicacion++): ?><option value="<?= $ubicacion ?>" <?= (int) $foto['parrafo_despues'] === $ubicacion ? 'selected' : '' ?>><?= $ubicacion === 0 ? 'Antes del párrafo 1' : 'Después del párrafo ' . $ubicacion ?></option><?php endfor; ?></select></label><label>Descripción<textarea class="image-description" name="fotos_existentes[<?= (int) $foto['id'] ?>][descripcion]" rows="3" maxlength="1250" placeholder="Descripción de la imagen (máximo 125 palabras)"><?= escaparReportaje($foto['descripcion'] ?? '') ?></textarea><small class="word-counter">0/125 palabras</small></label><div class="photo-actions"><label class="editor-button editor-button-secondary" title="Reemplazar imagen"><i class="bi bi-box-arrow-up"></i><span>Reemplazar</span><input class="replace-photo-input" type="file" name="reemplazar_imagen[<?= (int) $foto['id'] ?>]" accept=".jpg,.jpeg,.png,.webp"></label><label class="editor-button editor-button-danger delete-photo" title="Eliminar imagen"><i class="bi bi-trash-fill"></i><span>Eliminar</span><input type="checkbox" name="fotos_eliminar[]" value="<?= (int) $foto['id'] ?>"></label></div></div><?php endforeach; ?></div><?php endif; ?>
      </section>
      <button class="editor-submit" type="submit"><?= $id ? 'Guardar cambios' : 'Crear reportaje' ?></button>
    </form>
  </main>
  <script>
    const galeria = document.getElementById('galeria');
    const configuracion = document.getElementById('galeria-config');
    const parrafosEditor = document.getElementById('parrafos-editor');
    const leerVistaPrevia = (archivo, imagen) => {
      if (!archivo || !imagen) return;
      imagen.src = URL.createObjectURL(archivo);
      imagen.classList.remove('is-hidden');
    };
    const opcionesParrafo = () => Array.from(parrafosEditor.querySelectorAll('textarea')).map((_, indice) => '<option value="' + (indice + 1) + '">Después del párrafo ' + (indice + 1) + '</option>').join('');
    const actualizarParrafos = () => {
      document.querySelectorAll('.paragraph-number').forEach((elemento, indice) => { elemento.textContent = 'Párrafo ' + (indice + 1); });
      document.querySelectorAll('.paragraph-location, .new-paragraph-location').forEach((select) => {
        const valor = select.value;
        select.innerHTML = '<option value="0">Antes del párrafo 1</option>' + opcionesParrafo();
        select.value = valor;
      });
    };
    document.getElementById('agregar-parrafo').addEventListener('click', () => {
      const fila = document.createElement('div');
      fila.className = 'paragraph-row';
      const indice = parrafosEditor.querySelectorAll('.paragraph-row').length;
      fila.innerHTML = '<span class="paragraph-number"></span><input type="text" maxlength="100" name="parrafos[' + indice + '][titulo]" placeholder="Título opcional en negrita (máximo 100 caracteres)"><textarea name="parrafos[' + indice + '][contenido]" rows="4" required></textarea><button type="button" class="editor-remove editor-button editor-button-danger remove-paragraph">Eliminar párrafo</button>';
      parrafosEditor.appendChild(fila);
      actualizarParrafos();
    });
    parrafosEditor.addEventListener('click', (evento) => {
      if (evento.target.classList.contains('remove-paragraph')) { evento.target.closest('.paragraph-row').remove(); actualizarParrafos(); }
    });
    galeria.addEventListener('change', () => {
      configuracion.innerHTML = '';
      Array.from(galeria.files).forEach((archivo, indice) => {
        const fila = document.createElement('div');
        fila.className = 'gallery-config-row';
        fila.innerHTML = '<span class="gallery-file-name"></span><label>Orden<input type="number" name="galeria_orden[' + indice + ']" min="1" value="' + (indice + 1) + '" required></label><label>Mostrar después de<select class="new-paragraph-location" name="galeria_parrafo[' + indice + ']"><option value="0">Antes del párrafo 1</option>' + opcionesParrafo() + '</select></label><label>Descripción<textarea class="image-description" name="galeria_descripcion[' + indice + ']" rows="3" maxlength="1250" placeholder="Descripción (máximo 125 palabras)"></textarea><small class="word-counter">0/125 palabras</small></label>';
        fila.querySelector('.gallery-file-name').textContent = archivo.name;
        configuracion.appendChild(fila);
      });
      configurarContadores();
    });
    function configurarContadores() {
      document.querySelectorAll('.image-description').forEach((campo) => {
        const contador = campo.parentElement.querySelector('.word-counter');
        const actualizar = () => {
          const texto = campo.value.trim();
          const palabrasArray = texto === '' ? [] : texto.split(/\s+/);
          if (palabrasArray.length > 125) {
            campo.value = palabrasArray.slice(0, 125).join(' ') + ' ';
          }
          const palabras = campo.value.trim() === '' ? 0 : campo.value.trim().split(/\s+/).length;
          contador.textContent = palabras + '/125 palabras';
          contador.classList.toggle('word-limit-reached', palabras > 125);
        };
        campo.addEventListener('input', actualizar);
        actualizar();
      });
    }
    configurarContadores();
    document.getElementById('imagen-principal-input').addEventListener('change', (evento) => {
      leerVistaPrevia(evento.target.files[0], document.getElementById('imagen-principal-preview'));
    });
    document.querySelectorAll('.existing-gallery-item').forEach((fila) => {
      const reemplazo = fila.querySelector('.replace-photo-input');
      const eliminar = fila.querySelector('.delete-photo input');
      reemplazo.addEventListener('change', (evento) => leerVistaPrevia(evento.target.files[0], fila.querySelector('.gallery-image-preview')));
      eliminar.addEventListener('change', () => fila.classList.toggle('photo-marked-delete', eliminar.checked));
    });
    const eliminarPrincipal = document.querySelector('input[name="eliminar_foto_principal"]');
    if (eliminarPrincipal) {
      eliminarPrincipal.addEventListener('change', () => document.getElementById('imagen-principal-preview').classList.toggle('photo-marked-delete', eliminarPrincipal.checked));
    }
    actualizarParrafos();
  </script>
  <script src="../assets/js/menu.js"></script>
</body>
</html>
