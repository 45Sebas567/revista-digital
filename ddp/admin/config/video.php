<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../autorizacion.php';
if (empty($_SESSION['admin_logueado'])) {
    header('Location: ../login.php');
    exit();
}

require_once __DIR__ . '/../conexion.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$conexion = new conexion();
$existente = $id ? $conexion->obtenerVideo($id) : null;

if ($id && $existente === null) {
    $conexion->close();
    http_response_code(404);
    exit('Video no encontrado.');
}

$formulario = [
    'titulo' => $existente['titulo'] ?? '',
    'url_embed' => $existente['url_embed'] ?? '',
    'portada' => $existente['portada'] ?? '',
    'estado' => $existente['estado'] ?? 'borrador',
    'fecha_publicacion' => $existente['fecha_publicacion'] ?? date('Y-m-d'),
];
$error = '';

function escaparVideo(?string $valor): string
{
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}

function normalizarUrlVideo(string $url): string
{
    $partes = parse_url($url);
    $host = strtolower((string) ($partes['host'] ?? ''));
    $ruta = (string) ($partes['path'] ?? '');
    $consulta = [];
    parse_str((string) ($partes['query'] ?? ''), $consulta);

    if (str_contains($host, 'youtube.com') && !str_starts_with($ruta, '/embed/')) {
        $videoId = $consulta['v'] ?? '';
        return is_string($videoId) && $videoId !== ''
            ? 'https://www.youtube.com/embed/' . rawurlencode($videoId)
            : $url;
    }
    if ($host === 'youtu.be') {
        $videoId = trim($ruta, '/');
        return $videoId !== '' ? 'https://www.youtube.com/embed/' . rawurlencode($videoId) : $url;
    }
    return $url;
}

function guardarPortadaVideo(array $archivo): string
{
    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return '';
    if (($archivo['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) throw new RuntimeException('La portada no pudo cargarse.');
    $extension = strtolower(pathinfo((string) $archivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) throw new RuntimeException('La portada debe ser JPG, PNG o WEBP.');
    $directorio = __DIR__ . '/image';
    if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) throw new RuntimeException('No se pudo preparar la carpeta de imágenes.');
    $nombre = 'video-' . bin2hex(random_bytes(8)) . '.' . $extension;
    if (!move_uploaded_file((string) $archivo['tmp_name'], $directorio . DIRECTORY_SEPARATOR . $nombre)) throw new RuntimeException('No se pudo guardar la portada.');
    return 'config/image/' . $nombre;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['titulo', 'url_embed', 'estado', 'fecha_publicacion'] as $campo) {
        $formulario[$campo] = trim((string) ($_POST[$campo] ?? ''));
    }
    $formulario['url_embed'] = normalizarUrlVideo($formulario['url_embed']);

    if ($formulario['titulo'] === '' || $formulario['url_embed'] === '' ||
        !filter_var($formulario['url_embed'], FILTER_VALIDATE_URL) ||
        !in_array($formulario['estado'], ['borrador', 'publicado'], true) ||
        $formulario['fecha_publicacion'] === '') {
        $error = 'Completa los campos correctamente e indica una URL válida.';
    } else {
        try {
            $portada = guardarPortadaVideo($_FILES['portada'] ?? []);
            $formulario['portada'] = $portada !== ''
                ? $portada
                : (!empty($_POST['eliminar_portada']) ? '' : ($existente['portada'] ?? ''));
            $guardadoId = $conexion->guardarVideo(
                $formulario,
                (int) ($_SESSION['usuario_id'] ?? 0),
                $id
            );
            $conexion->close();
            header('Location: video.php?id=' . $guardadoId . '&guardado=1');
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
  <title><?= $id ? 'Editar' : 'Nuevo' ?> video | DDP</title>
  <link href="https://fonts.googleapis.com/css?family=Muli:300,300i,400,400i,600,600i,700,700i%7CComfortaa:300,400,700" rel="stylesheet">
  <link href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome.min.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="../theme-assets/css/vendors.css">
  <link rel="stylesheet" type="text/css" href="../theme-assets/css/app-lite.css">
  <link rel="stylesheet" type="text/css" href="../theme-assets/css/core/menu/menu-types/vertical-menu.css">
  <link rel="stylesheet" type="text/css" href="../assets/css/responsive-admin.css">
  <link rel="stylesheet" type="text/css" href="../assets/css/video.css">
  <link rel="stylesheet" type="text/css" href="../assets/css/paleta-roja.css">
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
  <div class="app-content content">
    <div class="content-wrapper">
      <div class="content-body videos-content videos-editor-content">
        <div class="videos-heading">
          <div><span class="videos-eyebrow">CONTENIDO MULTIMEDIA</span><h1><?= $id ? 'Editar video' : 'Nuevo video' ?></h1><p>Agrega el enlace de inserción y define su publicación.</p></div>
          <a class="videos-button videos-button-secondary" href="../video.php">Volver a videos</a>
        </div>
        <?php if ($guardado): ?><div class="videos-alert success">El video se guardó correctamente.</div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="videos-alert error"><?= escaparVideo($error) ?></div><?php endif; ?>
        <form class="videos-form" method="post" enctype="multipart/form-data">
          <?php if ($id): ?><input type="hidden" name="id" value="<?= (int) $id ?>"><?php endif; ?>
          <label>Título del video *
            <input type="text" name="titulo" maxlength="255" value="<?= escaparVideo($formulario['titulo']) ?>" required>
          </label>
          <label>URL de inserción *
            <input type="url" name="url_embed" maxlength="500" value="<?= escaparVideo($formulario['url_embed']) ?>" placeholder="https://www.youtube.com/embed/..." required>
            <small>Usa la URL de inserción proporcionada por YouTube u otra plataforma compatible.</small>
          </label>
          <label>Portada del video
            <?php if ($formulario['portada']): ?>
              <span class="video-cover-editor">
                <img class="video-cover-preview" id="video-cover-preview" src="../<?= escaparVideo($formulario['portada']) ?>" alt="Portada actual del video">
                <span class="video-cover-actions">
                  <button class="videos-button videos-button-secondary video-file-button" id="reemplazar-video" type="button">Reemplazar imagen</button>
                  <input class="video-file-input" id="video-file-input" type="file" name="portada" accept=".jpg,.jpeg,.png,.webp">
                  <input type="hidden" name="eliminar_portada" value="0">
                  <button class="videos-button video-delete-cover" type="button">Eliminar imagen</button>
                </span>
              </span>
            <?php else: ?>
              <input type="file" name="portada" accept=".jpg,.jpeg,.png,.webp">
            <?php endif; ?>
            <small>Opcional. Se guarda en <code>config/image</code> y aparecerá en el panel principal.</small>
          </label>
          <div class="videos-form-grid">
            <label>Estado
              <select name="estado"><option value="borrador" <?= $formulario['estado'] === 'borrador' ? 'selected' : '' ?>>Borrador</option><option value="publicado" <?= $formulario['estado'] === 'publicado' ? 'selected' : '' ?>>Publicado</option></select>
            </label>
            <label>Fecha de publicación *
              <input type="date" name="fecha_publicacion" value="<?= escaparVideo($formulario['fecha_publicacion']) ?>" required>
            </label>
          </div>
          <button class="videos-button" type="submit"><?= $id ? 'Guardar cambios' : 'Crear video' ?></button>
        </form>
      </div>
    </div>
  </div>
  <script src="../assets/js/menu.js"></script>
  <script>
    const portadaVideo = document.querySelector('input[name="portada"]');
    const vistaVideo = document.getElementById('video-cover-preview');
    document.getElementById('reemplazar-video')?.addEventListener('click', () => portadaVideo?.click());
    portadaVideo?.addEventListener('change', function () {
      const archivo = this.files?.[0];
      if (!archivo) return;
      if (vistaVideo) vistaVideo.src = URL.createObjectURL(archivo);
    });
    const eliminarVideo = document.querySelector('.video-delete-cover');
    eliminarVideo?.addEventListener('click', () => {
      const campo = document.querySelector('input[name="eliminar_portada"]');
      campo.value = campo.value === '1' ? '0' : '1';
      eliminarVideo.classList.toggle('is-selected', campo.value === '1');
      vistaVideo?.classList.toggle('is-marked-delete', campo.value === '1');
    });
  </script>
</body>
</html>