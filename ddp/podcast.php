<!--
Author: W3layouts
Author URL: http://w3layouts.com
-->
<?php
require_once __DIR__ . '/admin/conexion.php';

$conexionPublica = new conexion();
$podcastsPublicos = array_values(array_filter(
    $conexionPublica->obtenerPodcasts(),
    static fn (array $item): bool => strtolower((string) $item['estado']) === 'publicado'
));
$videosPublicos = array_values(array_filter(
    $conexionPublica->obtenerVideos(),
    static fn (array $item): bool => strtolower((string) $item['estado']) === 'publicado'
));
$conexionPublica->close();

$porPagina = 15;
$totalPaginas = max(1, (int) ceil(count($podcastsPublicos) / $porPagina));
$paginaActual = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
$paginaActual = max(1, min($paginaActual, $totalPaginas));
$podcastsPagina = array_slice($podcastsPublicos, ($paginaActual - 1) * $porPagina, $porPagina);

$totalPaginasVideos = max(1, (int) ceil(count($videosPublicos) / $porPagina));
$paginaActualVideos = filter_input(INPUT_GET, 'vpage', FILTER_VALIDATE_INT) ?: 1;
$paginaActualVideos = max(1, min($paginaActualVideos, $totalPaginasVideos));
$videosPagina = array_slice($videosPublicos, ($paginaActualVideos - 1) * $porPagina, $porPagina);

$pestanaActiva = (filter_input(INPUT_GET, 'tab', FILTER_DEFAULT) === 'videos' || filter_input(INPUT_GET, 'vpage', FILTER_VALIDATE_INT)) ? 'videos' : 'podcast';

function escaparPodcastPublico(?string $valor): string
{
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}

function fechaPodcastPublico(?string $valor): string
{
    if (!$valor) {
        return '';
    }
    $fecha = DateTime::createFromFormat('Y-m-d', $valor);
    return $fecha ? $fecha->format('d/m/Y') : $valor;
}

function imagenPodcastPublico(?string $ruta, string $predeterminada = 'assets/images/podcast.png'): string
{
    $ruta = trim((string) $ruta);
    if ($ruta === '') {
        return $predeterminada;
    }
    if (str_starts_with($ruta, 'config/')) {
        return 'admin/' . $ruta;
    }
    if (str_starts_with($ruta, 'image/')) {
        return 'admin/config/' . $ruta;
    }
    return $ruta;
}

function enlaceOriginalVideo(?string $urlEmbed): string
{
    $urlEmbed = trim((string) $urlEmbed);
    if ($urlEmbed === '') {
        return '';
    }
    if (preg_match('#youtube\.com/embed/([^?]+)#', $urlEmbed, $coincidencia)) {
        return 'https://www.youtube.com/watch?v=' . $coincidencia[1];
    }
    return $urlEmbed;
}

function enlaceOriginalPodcast(?string $urlEmbed): string
{
    $urlEmbed = trim((string) $urlEmbed);
    if ($urlEmbed === '') {
        return '';
    }
    if (preg_match('#youtube\.com/embed/([^?]+)#', $urlEmbed, $coincidencia)) {
        return 'https://www.youtube.com/watch?v=' . $coincidencia[1];
    }
    if (str_contains($urlEmbed, 'open.spotify.com/embed')) {
        return str_replace('open.spotify.com/embed', 'open.spotify.com', $urlEmbed);
    }
    return $urlEmbed;
}
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>DDP Noticias - Diálogo y Desarrollo Perú</title>

    <!-- Google fonts -->
    
	<link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600&amp;subset=latin-ext,vietnamese" rel="stylesheet">
    
    <!-- Template CSS -->
	
    <link rel="stylesheet" href="assets/css/style-starter.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
      .boletin-title-safe,
      .boletin-summary-safe { overflow-wrap: anywhere; white-space: normal; word-break: break-word; }
      .boletin-summary-safe { line-height: 1.5; margin-top: .65rem; }
    </style>
  </head>
  <body>

<!-- header -->
<header id="site-header" class="fixed-top">
  <div class="container">
      <nav class="navbar navbar-expand-lg stroke">
      <a class="navbar-brand" href="#index.html">
          <img src="assets/images/logo.png" alt="Your logo" title="Your logo" style="height:75px;" />
      </a> 
          <button class="navbar-toggler  collapsed bg-gradient" type="button" data-toggle="collapse"
              data-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false"
              aria-label="Toggle navigation">
              <span class="navbar-toggler-icon fa icon-expand fa-bars"></span>
              <span class="navbar-toggler-icon fa icon-close fa-times"></span>
              </span>
          </button>

          <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
              <ul class="navbar-nav ml-auto">
                  <li class="nav-item">
                      <a class="nav-link" href="index.php">Inicio <span class="sr-only">(current)</span></a>
                  </li>
                  <li class="nav-item @@about__active">
                      <a class="nav-link" href="index.php#actualidad">Actualidad</a>
                  </li>
				  <li class="nav-item @@about__active">
                      <a class="nav-link" href="reportajes-1.php">Reportajes</a>
                  </li>
				  <li class="nav-item active">
                      <a class="nav-link" href="podcast.php">Podcast y Videos</a>
                  </li>
				  <li class="nav-item @@about__active">
                      <a class="nav-link" href="boletines.php">Boletín NTEP</a>
                  </li>
				  <li class="nav-item @@about__active">
                      <a class="nav-link" href="about.html">Alianzas</a>
                  </li>
                  <li class="nav-item @@contact__active">
                      <a class="nav-link" href="contact.html">Sobre D&D</a>
                  </li>				  
                  <li class="ml-2">
                      <a href="#btn" class="btn btn-style btn-outline-secondary">Contacto</a>
                  </li>
              </ul>
          </div>
      </nav>
  </div>
</header>
<!-- //header -->
<section class="breadcrumb-area py-sm-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="breadcrumb-contents">
                    <h2 class="title-big">Podcast y Videos</h2>
                    <div class="breadcrumb">
                        <ul>
                            <li>
                                <a href="index.php">Inicio</a>
                            </li>
                            <li class="active">
                                 Podcast
                            </li>
                        </ul>
                    </div>
                </div>
            </div><!-- end .col-md-12 -->
        </div><!-- end .row -->
    </div><!-- end .container -->
</section>
<div class="grids-block-5 py-5">
    <!-- grids block 5 -->
    <section class="py-lg-4 py-md-3">
        <div class="container">
            <ul class="nav nav-tabs justify-content-center mb-5" id="multimediaTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link<?= $pestanaActiva === 'podcast' ? ' active' : '' ?>" id="tab-podcast-link" data-toggle="tab" href="#tab-podcast" role="tab" aria-controls="tab-podcast" aria-selected="<?= $pestanaActiva === 'podcast' ? 'true' : 'false' ?>">Podcast</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= $pestanaActiva === 'videos' ? ' active' : '' ?>" id="tab-videos-link" data-toggle="tab" href="#tab-videos" role="tab" aria-controls="tab-videos" aria-selected="<?= $pestanaActiva === 'videos' ? 'true' : 'false' ?>">Videos</a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade<?= $pestanaActiva === 'podcast' ? ' show active' : '' ?>" id="tab-podcast" role="tabpanel" aria-labelledby="tab-podcast-link">
                    <div class="row">
                        <?php foreach ($podcastsPagina as $podcast): ?>
                        <?php $portada = imagenPodcastPublico($podcast['portada'] ?? ''); ?>
                          <div class="col-lg-4 col-md-6 grids5-info">
                            <img src="<?= escaparPodcastPublico($portada) ?>" alt="<?= escaparPodcastPublico($podcast['titulo']) ?>" class="img-fluid" style="width: 100%; height: 220px; object-fit: cover;" />
                            <div class="blog-info">
                                <h5><?= escaparPodcastPublico($podcast['titulo']) ?></h5>
                                <p><?= fechaPodcastPublico($podcast['fecha_publicacion']) ?></p>
                                <div class="podcast-player-wrap mt-3" data-embed-url="<?= escaparPodcastPublico($podcast['url_embed']) ?>">
                                  <button type="button" class="btn podcast-play-btn"><i class="fa fa-play"></i> Reproducir aquí</button>
                                </div>
                                <?php $enlaceOriginal = enlaceOriginalPodcast($podcast['url_embed'] ?? ''); ?>
                                <?php if ($enlaceOriginal !== ''): ?>
                                  <a href="<?= escaparPodcastPublico($enlaceOriginal) ?>" target="_blank" rel="noopener" class="btn mt-2 p-0">
                                    Ver en <?= str_contains($enlaceOriginal, 'spotify') ? 'Spotify' : 'YouTube' ?> <i class="fa fa-arrow-right"></i>
                                  </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if ($podcastsPagina === []): ?><div class="col-12"><p>Aún no hay podcasts publicados.</p></div><?php endif; ?>
                    </div>
                    <div class="pagination">
                        <ul>
                            <?php if ($paginaActual > 1): ?><li class="prev"><a href="podcast.php?tab=podcast&page=<?= $paginaActual - 1 ?>"> Ant</a></li><?php endif; ?>
                            <?php for ($pagina = 1; $pagina <= $totalPaginas; $pagina++): ?>
                            <li><a href="podcast.php?tab=podcast&page=<?= $pagina ?>" class="<?= $pagina === $paginaActual ? 'active' : '' ?>"><?= $pagina ?></a></li>
                            <?php endfor; ?>
                            <?php if ($paginaActual < $totalPaginas): ?><li class="next"><a href="podcast.php?tab=podcast&page=<?= $paginaActual + 1 ?>"> Sig </a></li><?php endif; ?>
                        </ul>
                    </div>
                </div>
                <div class="tab-pane fade<?= $pestanaActiva === 'videos' ? ' show active' : '' ?>" id="tab-videos" role="tabpanel" aria-labelledby="tab-videos-link">
                    <div class="row">
                        <?php foreach ($videosPagina as $video): ?>
                        <?php $portadaVideo = imagenPodcastPublico($video['portada'] ?? '', 'assets/images/video.jpg'); ?>
                          <div class="col-lg-4 col-md-6 grids5-info">
                            <img src="<?= escaparPodcastPublico($portadaVideo) ?>" alt="<?= escaparPodcastPublico($video['titulo']) ?>" class="img-fluid" style="width: 100%; height: 220px; object-fit: cover;" />
                            <div class="blog-info">
                                <h5><?= escaparPodcastPublico($video['titulo']) ?></h5>
                                <p><?= fechaPodcastPublico($video['fecha_publicacion']) ?></p>
                                <div class="podcast-player-wrap mt-3" data-embed-url="<?= escaparPodcastPublico($video['url_embed']) ?>">
                                  <button type="button" class="btn podcast-play-btn"><i class="fa fa-play"></i> Ver aquí</button>
                                </div>
                                <?php $enlaceOriginalV = enlaceOriginalVideo($video['url_embed'] ?? ''); ?>
                                <?php if ($enlaceOriginalV !== ''): ?>
                                  <a href="<?= escaparPodcastPublico($enlaceOriginalV) ?>" target="_blank" rel="noopener" class="btn mt-2 p-0">
                                    Ver en YouTube <i class="fa fa-arrow-right"></i>
                                  </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if ($videosPagina === []): ?><div class="col-12"><p>Aún no hay videos publicados.</p></div><?php endif; ?>
                    </div>
                    <div class="pagination">
                        <ul>
                            <?php if ($paginaActualVideos > 1): ?><li class="prev"><a href="podcast.php?tab=videos&vpage=<?= $paginaActualVideos - 1 ?>"> Ant</a></li><?php endif; ?>
                            <?php for ($pagina = 1; $pagina <= $totalPaginasVideos; $pagina++): ?>
                            <li><a href="podcast.php?tab=videos&vpage=<?= $pagina ?>" class="<?= $pagina === $paginaActualVideos ? 'active' : '' ?>"><?= $pagina ?></a></li>
                            <?php endfor; ?>
                            <?php if ($paginaActualVideos < $totalPaginasVideos): ?><li class="next"><a href="podcast.php?tab=videos&vpage=<?= $paginaActualVideos + 1 ?>"> Sig </a></li><?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<!-- // grids block 5 -->
<!-- footer block -->
<section class="w3l-footer-29-main py-5" id="footer">
  <div class="footer-29 py-md-3">
    <div class="container">
      <div class="row footer-top-29">
        <div class="col-lg-6 col-md-6 footer-list-29 footer-1">
          <h6 class="footer-title-29">Quiénes Somos</h6>
          <p>Somos un espacio de periodismo independiente que busca visibilizar las acciones de diálogo en el país desde una mirada constructiva.</p>
          <div class="main-social-footer-29">
            <a target="_blank" href="https://www.facebook.com/DialogoyDesarrolloPeru" class="facebook"><span class="fas fa-facebook-square"></span></a>
            <a target="_blank" href="https://www.tiktok.com/@dialogo.y.desarrollo" class="twitter"><img src="assets/images/tiktokp.png"></a>
            <a target="_blank" href="https://www.instagram.com/dialogo.y.desarrollo/" class="instagram"><span class="fas fa-instagram"></span></a>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 footer-list-29 footer-2 mt-md-0 mt-5">
          <ul>
            <h6 class="footer-title-29">Contenido</h6>
            <li><a href="#url">Noticias</a></li>
            <li><a href="#url">Videos</a></li>
            <li><a href="#url">Posdcast.</a></li>
          </ul>
        </div>
        <div class="col-lg-3 col-md-6 mt-lg-0 mt-5 footer-list-29 footer-3">
          <div class="properties">
            <h6 class="footer-title-29">Contacto</h6>
            <ul>
            <li><a href="#url">info@dialogoydesarrollo.com.pe</a></li>
          </ul>
          </div>
        </div>
      </div>
      <div class="bottom-copies text-center">
		<p class="copy-footer-29">© 2025 Diálogo y Desarrollo Perú. All rights reserved | Designed by <a target="_blank" href="https://www.wsperu.info">WebSolutions</a></p>
	</div>
    </div>
  </div>
  <!-- move top -->
  <button onclick="topFunction()" id="movetop" title="Go to top">
    <span class="fa fa-angle-up"></span>
  </button>
  <script>
    window.onscroll = function () {
      scrollFunction()
    };

    function scrollFunction() {
      if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        document.getElementById("movetop").style.display = "block";
      } else {
        document.getElementById("movetop").style.display = "none";
      }
    }

    function topFunction() {
      document.body.scrollTop = 0;
      document.documentElement.scrollTop = 0;
    }
  </script>
  <!-- /move top -->
</section>
<!-- //footer block -->

<!-- Template JavaScript -->
<script src="assets/js/jquery-3.3.1.min.js"></script>

<script src="assets/js/theme-change.js"></script>

<script src="assets/js/lightbox-plus-jquery.min.js"></script>

<script src="assets/js/easyResponsiveTabs.js"></script>
<script type="text/javascript">
  $(document).ready(function () {
    $('#parentHorizontalTab').easyResponsiveTabs({
      type: 'default',
      width: 'auto',
      fit: true,
      tabidentify: 'hor_1',
      activate: function (event) {
        var $tab = $(this);
        var $info = $('#nested-tabInfo');
        var $name = $('span', $info);
        $name.text($tab.text());
        $info.show();
      }
    });
  });
</script>

<script src="assets/js/owl.carousel.js"></script>
<script>
  $(document).ready(function () {
    $('.owl-logos').owlCarousel({
      loop: true, margin: 0, nav: false, responsiveClass: true,
      autoplay: true, autoplayTimeout: 5000, autoplaySpeed: 1000, autoplayHoverPause: false,
      responsive: { 0: { items: 2, nav: false }, 480: { items: 2, nav: false }, 568: { items: 3, nav: false }, 1000: { items: 5, nav: false } }
    })
  })
</script>
<script>
  $(document).ready(function () {
    $("#owl-demo1").owlCarousel({
      loop: true, margin: 20, responsiveClass: true,
      responsive: { 0: { items: 1, nav: true }, 768: { items: 2, nav: false }, 1000: { items: 3, nav: true, loop: false } }
    })
  })
</script>

<script>
  $(document).ready(function () {
    $('.owl-carousel').owlCarousel({
      loop: true, margin: 0, responsiveClass: true,
      responsive: { 0: { items: 1, nav: true }, 400: { items: 2, nav: true, margin: 20 }, 768: { items: 3, nav: true, margin: 20 }, 1000: { items: 4, nav: true, loop: true, margin: 25 } }
    })
  })
</script>

<script>
  (() => {
    const deadlineDate = new Date('January 27, 2025 23:59:59').getTime();
    const countdownDays = document.querySelector('.countdown__days .number');
    const countdownHours = document.querySelector('.countdown__hours .number');
    const countdownMinutes = document.querySelector('.countdown__minutes .number');
    const countdownSeconds = document.querySelector('.countdown__seconds .number');
    setInterval(() => {
      const currentDate = new Date().getTime();
      const distance = deadlineDate - currentDate;
      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);
      countdownDays.innerHTML = days;
      countdownHours.innerHTML = hours;
      countdownMinutes.innerHTML = minutes;
      countdownSeconds.innerHTML = seconds;
    }, 1000);
  })();
</script>

<script src="assets/js/jquery.magnific-popup.min.js"></script>
<script>
  $(document).ready(function () {
    $('.popup-with-zoom-anim').magnificPopup({ type: 'inline', fixedContentPos: false, fixedBgPos: true, overflowY: 'auto', closeBtnInside: true, preloader: false, midClick: true, removalDelay: 300, mainClass: 'my-mfp-zoom-in' });
    $('.popup-with-move-anim').magnificPopup({ type: 'inline', fixedContentPos: false, fixedBgPos: true, overflowY: 'auto', closeBtnInside: true, preloader: false, midClick: true, removalDelay: 300, mainClass: 'my-mfp-slide-bottom' });
  });
</script>

<script>
  $(function () {
    $('.navbar-toggler').click(function () {
      $('body').toggleClass('noscroll');
    })
  });
</script>

<script>
  $(window).on("scroll", function () {
    var scroll = $(window).scrollTop();
    if (scroll >= 80) {
      $("#site-header").addClass("nav-fixed");
    } else {
      $("#site-header").removeClass("nav-fixed");
    }
  });
  $(".navbar-toggler").on("click", function () {
    $("header").toggleClass("active");
  });
  $(document).on("ready", function () {
    if ($(window).width() > 991) {
      $("header").removeClass("active");
    }
    $(window).on("resize", function () {
      if ($(window).width() > 991) {
        $("header").removeClass("active");
      }
    });
  });
</script>

<script src="assets/js/bootstrap.min.js"></script>

<script>
  document.querySelectorAll('.podcast-play-btn').forEach(function (boton) {
    boton.addEventListener('click', function () {
      var contenedor = boton.parentElement;
      var url = contenedor.getAttribute('data-embed-url');
      contenedor.innerHTML = '<div class="ratio ratio-16x9"><iframe src="' + url + '" frameborder="0" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy" allowfullscreen></iframe></div>';
    });
  });
</script>

</body>

</html>