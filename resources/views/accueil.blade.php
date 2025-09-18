<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="{{ asset('ursule/style/page1.css') }}">
    <title>Document</title>
</head>
<body>
      <!-- Barre de navigation -->
<nav class="navbar navbar-expand-lg p-3">
  <div class="container-fluid container-lg d-flex align-items-center justify-content-between">

    <!-- Logo -->
    <a class="navbar-brand d-flex align-items-center p-0 m-0" href="#">
      <img src="{{ asset('ursule/img/logo.png') }}" alt="Logo" style="height: 40px;">
      <span class="fw-bold text-dark ms-2">CPEG MARIE-ALAIN</span>
    </a>

    <!-- Bloc droit : bouton S'inscrire + hamburger -->
    <div class="d-flex align-items-center gap-3 d-lg-none">

      <!-- Bouton S'inscrire -->
      <a href="#apropos" class="btn btn-orange">S'inscrire</a>

      <!-- Bouton hamburger -->
      <button class="navbar-toggler " type="button" data-bs-toggle="collapse" data-bs-target="#menuNav">
        <span class="navbar-toggler-icon"></span>
      </button>

    </div>

    <!-- Menu -->
    <div class="collapse navbar-collapse" id="menuNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="#accueil">Accueil</a></li>
        <li class="nav-item"><a class="nav-link" href="#services">À propos</a></li>
        <li class="nav-item"><a class="nav-link" href="#contact">À propos</a></li>
        <li class="nav-item"><a class="nav-link" href="#apropos">À propos</a></li>
      </ul>
    </div>
    <div class="d-none d-lg-block">
        <a href="#apropos" class="btn btn-orange ms-4">S'inscrire</a>
    </div>
  </div>
</nav>
<section id="accueil" class="carousel-section">
  <div id="carouselEcole" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">

      <!-- Slide 1 -->
      <div class="carousel-item active">
        <div class="container">
          <div class="row align-items-center">
            <div class="col-lg-6 col-12 animate__animated animate__fadeInLeft">
              <div class="text-wrapper">
                <h2>Bienvenue à l'École CPEG MARIE-ALAIN</h2>
                <p>Un cadre d’apprentissage stimulant, sécurisé et ouvert à tous.</p>
                <a href="" class="btn bg-orange mt-2">Voir plus</a>
              </div>
            </div>
            <div class="col-lg-6 col-12 animate__animated animate__fadeInRight">
              <div class="image-decor-wrapper position-relative">
                <img src="{{ asset('ursule/img/ecole1.jpeg') }}" class="main-img img-fluid" alt="École Marialain">
                <img src="{{ asset('ursule/img/main-banner-shape-1.png') }}" class="decor decor-1" alt="Décoration 1">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Slide 2 -->
      <div class="carousel-item">
        <div class="container">
          <div class="row align-items-center">
            <div class="col-lg-6 col-12 animate__animated animate__fadeInLeft">
              <div class="text-wrapper">
                <h2>Renforcer les connaissances des étudiants</h2>
                <p>Nous mettons l’accent sur l’excellence académique, la curiosité intellectuelle et le développement des compétences clés.</p>
                <a href="/activites" class="btn bg-orange">Voir plus</a>
              </div>
            </div>
            <div class="col-lg-6 col-12 animate__animated animate__fadeInRight">
              <div class="image-decor-wrapper position-relative">
                <img src="{{ asset('ursule/img/ecole2.jpeg') }}" class="main-img img-fluid" alt="Activités">
                <img src="{{ asset('ursule/img/main-banner-shape-1.png') }}" class="decor decor-1" alt="Décoration 1">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Slide 3 -->
      <div class="carousel-item">
        <div class="container">
          <div class="row align-items-center">
            <div class="col-lg-6 col-12 animate__animated animate__fadeInLeft">
              <div class="text-wrapper">
                <h2>Encadrement pédagogique</h2>
                <p>Des enseignants passionnés et des méthodes innovantes pour chaque élève.</p>
                <a href="/pedagogie" class="btn bg-orange">Voir plus</a>
              </div>
            </div>
            <div class="col-lg-6 col-12 animate__animated animate__fadeInRight">
              <div class="image-decor-wrapper position-relative">
                <img src="{{ asset('ursule/img/ecole3.jpeg') }}" class="main-img img-fluid" alt="Pédagogie">
                <img src="{{ asset('ursule/img/main-banner-shape-1.png') }}" class="decor decor-1" alt="Décoration 1">
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Contrôles repositionnés -->
    <button class="carousel-control-prev custom-control" type="button" data-bs-target="#carouselEcole" data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next custom-control" type="button" data-bs-target="#carouselEcole" data-bs-slide="next">
      <span class="carousel-control-next-icon "></span>
    </button>
  </div>
</section>



    <a href="{{ route('home') }}" >home</a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>