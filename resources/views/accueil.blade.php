<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="{{ asset('ursule/style/page1.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sacramento&display=swap" rel="stylesheet">
    <script src="{{ asset('ursule/js/js1.js') }}"defer ></script>
    <title>CPEG MARIE-ALAIN</title>
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



      <!-- Bouton hamburger -->
      <button class="navbar-toggler " type="button" data-bs-toggle="collapse" data-bs-target="#menuNav">
        <span class="navbar-toggler-icon"></span>
      </button>


    <!-- Menu -->
    <div class="collapse navbar-collapse" id="menuNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="#accueil">Accueil</a></li>
        <li class="nav-item"><a class="nav-link" href="#a-propos">À propos</a></li>
        <li class="nav-item"><a class="nav-link" href="#programmes">Programmes</a></li>
        <li class="nav-item"><a class="nav-link" href="#cours">Cours</a></li>
<<<<<<< HEAD
=======

        <li class="nav-item"><a class="nav-link" href="#activites">Activités</a></li>
        <li class="nav-item"><a class="nav-link" href="#admin">Administration</a></li>
        <li class="nav-item"><a class="nav-link" href="#galerie">Galerie</a></li>
      </ul>
    </div>
    <div class="d-none d-lg-block">
        <a href="{{ route('students.create') }}" class="btn btn-orange ms-4">S'inscrire</a>
<!-- 
>>>>>>> 00716f68044d81f210a9471f8b687e0882439ee8
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            Ressources
          </a>
<<<<<<< HEAD
          <ul class="dropdown-menu">
=======
           <ul class="dropdown-menu">
>>>>>>> 00716f68044d81f210a9471f8b687e0882439ee8
            <li><a class="dropdown-item" href="#activites">Activités</a></li>
            <li><a class="dropdown-item" href="#admin">Administration</a></li>
            <li><a class="dropdown-item" href="#galerie">Galerie</a></li>
          </ul>
        </li>
        <li class="nav-item">
        <a href="#apropos" class="btn btn-orange ms-4">S'inscrire</a>
        </li>
<<<<<<< HEAD
      </ul>
      
=======
      </ul>-->
      

>>>>>>> 00716f68044d81f210a9471f8b687e0882439ee8
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
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
    <img src="{{ asset('ursule/img/main-banner-shape-1.png') }}" class="decor decor-1" alt="Décoration 1">
    <img src="{{ asset('ursule/img/decora2.png') }}" class="decor decor-2" alt="Décoration 2">
    <img src="{{ asset('ursule/img/decora3.png') }}" class="decor decor-3" alt="Décoration 2">
    <!-- Contrôles repositionnés -->
    <button class="carousel-control-prev custom-control ms-3" type="button" data-bs-target="#carouselEcole" data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next custom-control me-3" type="button" data-bs-target="#carouselEcole" data-bs-slide="next">
      <span class="carousel-control-next-icon "></span>
    </button>
  </div>
</section>

<section id="a-propos" class="section-apropos py-5">
  <div class="container">
    <div class="row align-items-center">

      <!-- Texte dynamique -->
      <div class="col-lg-7 col-12 mb-4 mb-lg-0">
        <span class="who-we">Qui somme nous</span>
        <h2 class="section-title text-start">À propos </h2>
        <div id="ecole-description" class="section-description"></div>
        <blockquote class="founder-quote">
            “L’éducation est la racine du progrès. À CPEG MARIE-ALAIN, nous cultivons les talents de demain.”
        </blockquote>
        <a href="#inscription" class="btn bg-orange mt-3">S'inscrire</a>
      </div>
        <!-- Image animée -->
      <div class="col-lg-5 col-12 text-center">
        <div class="image-wrapper">
          <img src="{{ asset('ursule/img/ecole3.jpeg') }}" alt="Fondateur" class="img-wave-border">
        </div>
      </div>

    </div>
  </div>
</section>

<section id="programmes" class="py-5 ">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold">Nos Programmes</h2>
      <p class="text-muted">Des parcours pédagogiques adaptés à chaque âge, enrichis par des méthodes modernes et des environnements stimulants.</p>
    </div>

    <div class="row g-4">
      <!-- Maternelle -->
      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0 programme-card">
          <img src="{{ asset('ursule/img/ecole2.jpeg') }}" class="card-img-top" alt="Programme Maternelle">
          <div class="card-body">
            <h5 class="card-title text-orange">Maternelle</h5>
            <p class="card-text">Éveil sensoriel, jeux éducatifs et socialisation dans un cadre bienveillant.</p>
            <!-- Contribution -->
            <div class="d-flex align-items-center justify-content-between contribution-line">
                <span class="fw-semibold text-muted">Contribution annuelle</span>
                <span class="badge rounded-pill bg-gradient-orange">50 000 – 100 000 FCFA</span>
            </div>
            <a href="#inscription" class="btn btn-glass btn-sm mt-4 w-70">Rejoindre le programme</a>
          </div>
        </div>
      </div>

      <!-- Primaire -->
      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0 programme-card">
          <img src="{{ asset('ursule/img/ecole2.jpeg') }}" class="card-img-top" alt="Programme Primaire">
          <div class="card-body">
            <h5 class="card-title text-orange">Primaire</h5>
            <p class="card-text">Maîtrise des fondamentaux, initiation aux langues et aux sciences.</p>
            <!-- Contribution -->
            <div class="d-flex align-items-center justify-content-between contribution-line">
                <span class="fw-semibold text-muted">Contribution annuelle</span>
                <span class="badge rounded-pill bg-gradient-orange">50 000 – 100 000 FCFA</span>
            </div>
            <a href="#inscription" class="btn btn-glass mt-4 w-70">Rejoindre le programme</a>
          </div>
        </div>
      </div>

      <!-- Collège -->
      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0 programme-card">
          <img src="{{ asset('ursule/img/ecole2.jpeg') }}" class="card-img-top" alt="Programme Collège">
          <div class="card-body">
            <h5 class="card-title text-orange">Collège</h5>
            <p class="card-text">Renforcement académique, projets interdisciplinaires et ouverture numérique.</p>
            <!-- Contribution -->
            <div class="d-flex align-items-center justify-content-between contribution-line">
                <span class="fw-semibold text-muted">Contribution annuelle</span>
                <span class="badge rounded-pill bg-gradient-orange">50 000 – 100 000 FCFA</span>
            </div>
            <a href="#inscription" class="btn btn-glass mt-4 w-70">Rejoindre le programme</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="cours" class="py-5">
  <div class="container">
    <p class="who-we text-center">Cours</p>
    <h2 class="text-center mb-4">Nos Cours</h2>
    <p class="text-center mb-5">Découvrez les matières enseignées dans notre établissement, avec leur description et les niveaux concernés.</p>

    <div class="row g-4" id="coursList">
      <!-- Cartes visibles par défaut -->
      <div class="col-md-4">
        <div class="cours-card h-100">
          <div class="card-body">
            <h5 class="cours-title"><span class="bg-3">01</span>Mathématiques</h5>
            <p class="cours-text">Étude des nombres, calculs, géométrie, logique et résolution de problèmes.</p>
            <p class="cours-niveau">📘 Primaire, 🎓 Collège</p>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="cours-card h-100">
          <div class="card-body">
            <h5 class="cours-title"><span class="bg-3">02</span>Français</h5>
            <p class="cours-text">Lecture, écriture, grammaire, vocabulaire, expression orale et écrite.</p>
            <p class="cours-niveau">🧒 Maternelle, 📘 Primaire, 🎓 Collège</p>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="cours-card h-100">
          <div class="card-body">
            <h5 class="cours-title"><span class="bg-3">03</span>Sciences</h5>
            <p class="cours-text">Découverte du vivant, des phénomènes naturels et de l’environnement.</p>
            <p class="cours-niveau">📘 Primaire</p>
          </div>
        </div>
      </div>

      <!-- Bloc caché -->
      <div class="collapse" id="moreCourses">
        <div class="row g-4 mt-2">
          <div class="col-md-4">
            <div class="cours-card h-100">
              <div class="card-body">
                <h5 class="cours-title"><span class="bg-3">04</span>Histoire-Géographie</h5>
                <p class="cours-text">Compréhension du passé, des civilisations et des repères géographiques.</p>
                <p class="cours-niveau">📘 Primaire, 🎓 Collège</p>
              </div>
            </div>
          </div>


          <div class="col-md-4">
            <div class="cours-card h-100">
              <div class="card-body">
                <h5 class="cours-title"><span class="bg-3">06</span>Anglais</h5>
                <p class="cours-text">Initiation et approfondissement de la langue anglaise : vocabulaire, grammaire, expression.</p>
                <p class="cours-niveau">📘 Primaire, 🎓 Collège</p>
              </div>
            </div>
          </div>


          <div class="col-md-4">
            <div class="cours-card h-100">
              <div class="card-body">
                <h5 class="cours-title"><span class="bg-3">08</span>Éducation Physique</h5>
                <p class="cours-text">Activités sportives pour développer la motricité, la coordination et l’esprit d’équipe.</p>
                <p class="cours-niveau">🧒 Maternelle, 📘 Primaire, 🎓 Collège</p>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="cours-card h-100">
              <div class="card-body">
                <h5 class="cours-title"><span class="bg-3">09</span>Physique-Chimie</h5>
                <p class="cours-text">Étude des phénomènes physiques, lois scientifiques et réactions chimiques.</p>
                <p class="cours-niveau">🎓 Collège</p>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="cours-card h-100">
              <div class="card-body">
                <h5 class="cours-title"><span class="bg-3">10</span>Langage oral</h5>
                <p class="cours-text">Développement du vocabulaire, de l’écoute et de l’expression orale.</p>
                <p class="cours-niveau">🧒 Maternelle</p>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="cours-card h-100">
              <div class="card-body">
                <h5 class="cours-title"><span class="bg-3">11</span>Éveil sensoriel</h5>
                <p class="cours-text">Activités pour stimuler les sens, la curiosité et la motricité fine.</p>
                <p class="cours-niveau">🧒 Maternelle</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Bouton intégré dans la liste -->
      <div class="cours-toggle-wrapper">
        <button id="toggleBtn" class="btn btn-vp toggle-btn" type="button" data-bs-toggle="collapse" data-bs-target="#moreCourses" aria-expanded="false" aria-controls="moreCourses">
          Voir plus
        </button>
      </div>
    </div>
  </div>
</section>


<section id="activites" class="timeline-section">
  <div class="container">
    <p class="who-we text-center">Les activités que nous organisons</p>
    <h2 class="section-title" > Nos Activités</h2>
    <div class="timeline">

      <!-- Activité 1 -->
      <div class="timeline-item left">
        <div class="timeline-content">
          <h4>Remise des Prix aux Lauréats 🏆</h4>
          <p>Célébration des élèves méritants avec trophées et reconnaissance publique.</p>
        </div>
      </div>

      <!-- Activité 2 -->
      <div class="timeline-item right">
        <div class="timeline-content">
          <h4>Journée Culturelle 🎭</h4>
          <p>Expositions, spectacles et valorisation des cultures locales.</p>
        </div>
      </div>

      <!-- Activité 3 -->
      <div class="timeline-item left">
        <div class="timeline-content">
          <h4>Club de Musique 🎶</h4>
          <p>Pratique instrumentale, chant et participation à des événements artistiques.</p>
        </div>
      </div>

    </div>
  </div>
</section>

 <section id="admin"class="teachers-section p-5">
        <div class="container-fluid text-center">
           <p class="who-we text-center">Nos membres d'administration</p>
            <h2 class="mb-4"  >Nos dirigeants</h2>
            <div class="row g-4 justify-content-center">
                
                <div class="col-md-6 col-lg-3">
                    <div class="teacher-card text-center p-3 rounded ">
                      <div class="image">
                        <img src="{{ asset('ursule/img/ecole1.jpeg') }}" alt="Glims Bond" class="teacher-img rounded-circle ">
                      </div>
                        
                        <h5 class="teacher-name mt-3">Nom Prenom</h5>
                        <p class="teacher-title">Statut</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="teacher-card text-center p-3 rounded ">
                        <div class="image">
                          <img src="{{ asset('ursule/img/ecole1.jpeg') }}" alt="Glims Bond" class="teacher-img rounded-circle ">
                        </div>
                        <h5 class="teacher-name mt-3">Nom Prenom</h5>
                        <p class="teacher-title">Statut</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="teacher-card text-center p-3 rounded ">
                        <div class="image">
                          <img src="{{ asset('ursule/img/ecole1.jpeg') }}" alt="Glims Bond" class="teacher-img rounded-circle ">
                        </div>
                        <h5 class="teacher-name mt-3">Nom Prenom</h5>
                        <p class="teacher-title">Staut</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="teacher-card text-center p-3 rounded ">
                        <div class="image">
                          <img src="{{ asset('ursule/img/ecole1.jpeg') }}" alt="Glims Bond" class="teacher-img rounded-circle ">
                        </div>
                        <h5 class="teacher-name">Nom Prenom</h5>
                        <p class="teacher-title">Statut</p>
                    </div>
                </div>

            </div>
        </div>
</section>


<section id="galerie" >
  <div class="container">
    <p class="who-we text-center">Galerie de nos activités</p>
    <h2 class="section-title " >Galerie</h2>

    <div class="row g-4">

      <div class="col-12 col-sm-6 col-md-4">
        <div class="overflow-hidden rounded shadow-sm">
          <img src="{{ asset('ursule/img/galerie1.jpeg') }}" alt="Remise des prix" class="img-fluid gallery-img">
        </div>
      </div>

      <div class="col-12 col-sm-6 col-md-4">
        <div class="overflow-hidden rounded shadow-sm">
          <img src="{{ asset('ursule/img/galerie2.jpeg') }}" alt="Journée culturelle" class="img-fluid gallery-img">
        </div>
      </div>

      <div class="col-12 col-sm-6 col-md-4">
        <div class="overflow-hidden rounded shadow-sm">
          <img src="{{ asset('ursule/img/galerie3.jpeg') }}" alt="Club de musique" class="img-fluid gallery-img">
        </div>
      </div>

      <div class="col-12 col-sm-6 col-md-4">
        <div class="overflow-hidden rounded shadow-sm">
          <img src="{{ asset('ursule/img/galerie4.jpeg') }}" alt="Spectacle" class="img-fluid gallery-img">
        </div>
      </div>

      <div class="col-12 col-sm-6 col-md-4">
        <div class="overflow-hidden rounded shadow-sm">
          <img src="{{ asset('ursule/img/galerie5.jpeg') }}" alt="Exposition" class="img-fluid gallery-img">
        </div>
      </div>

      <div class="col-12 col-sm-6 col-md-4">
        <div class="overflow-hidden rounded shadow-sm">
          <img src="{{ asset('ursule/img/galerie6.jpeg') }}" alt="Concert" class="img-fluid gallery-img">
        </div>
      </div>

    </div>
  </div>
</section>

<footer class="footer text-light  pb-4">
  <div class="container p-3">
    <div class="row gy-5 grop">

      <!-- À propos -->
      <div class="col-md-3">
        <h5 class="footer-title">À propos</h5>
        <hr class="footer-separator">
        <p class="footer-text">
          Nous sommes un établissement dédié à l’épanouissement des jeunes à travers l’éducation, la culture et les arts.<br><br>
          Notre mission est de former des citoyens responsables et créatifs dans un environnement stimulant.
        </p>
      </div>

      <div class="col-md-3">
        <h5 class="footer-title"> Localisation</h5>
        <hr class="footer-separator">
        <div class="footer-map">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1982.2796390648748!2d2.3295801382923!3d6.450581963616354!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1024a947e07ab415%3A0x744c26ef40122789!2sComplexe%20Scolaire%20Mari%C3%A9e%20Alain!5e0!3m2!1sfr!2sbj!4v1758535641742!5m2!1sfr!2sbj"
            width="100%" height="150" style="border:0;" allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
          </iframe>
          <p class="footer-text">
            📍Mono, Bénin<br>
          </p>
          
        </div>
      </div>

      <!-- Contact -->
      <div class="col-md-3">
        <h5 class="footer-title">Contact</h5>
        <hr class="footer-separator">
        <form>
          <input type="text" class="form-control form-control-sm mb-3" placeholder="Nom">
          <input type="email" class="form-control form-control-sm mb-3" placeholder="Email">
          <textarea class="form-control form-control-sm mb-3" rows="2" placeholder="Message"></textarea>
          <button type="submit" class="btn btn-sm btn-danger w-100">Envoyer</button>
        </form>
      </div>

      <!-- Coordonnées -->
      <div class="col-md-3">
        <h5 class="footer-title">Coordonnées</h5>
        <hr class="footer-separator">
        <p class="footer-text d-flex flex-column gap-3">
          <span><i class="bi bi-geo-alt-fill text-danger me-2"></i> Quartier Houégbo, Lokossa</span>
          <span><i class="bi bi-telephone-fill text-danger me-2"></i> +229 97 00 00 00</span>
          <span><i class="bi bi-envelope-fill text-danger me-2"></i> contact@etablissement.bj</span>
        </p>
      </div>

    </div>

    <hr class="border-light mt-5">
    <div class="text-center small">
      &copy; 2025 CPEG MARIE-ALAIN — Tous droits réservés.
    </div>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>