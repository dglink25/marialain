<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="{{ asset('ursule/style/page1.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sacramento&display=swap" rel="stylesheet">
    <script src="{{ asset('ursule/js/js1.js') }}"defer ></script>
    <title>CPEG MARIE-ALAIN</title>
    <style>
        /* Styles pour la galerie modale */
        .gallery-img {
            cursor: pointer;
            transition: transform 0.3s ease;
            height: 250px;
            object-fit: cover;
            width: 100%;
        }
        
        .gallery-img:hover {
            transform: scale(1.03);
        }
        
        .modal-gallery {
            z-index: 1060;
        }
        
        .modal-gallery .modal-content {
            background-color: transparent;
            border: none;
        }
        
        .modal-gallery .modal-body {
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .modal-gallery img {
            max-height: 85vh;
            max-width: 100%;
            object-fit: contain;
        }
        
        .modal-gallery .btn-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            opacity: 1;
            z-index: 1070;
        }
        
        .modal-gallery .modal-dialog {
            max-width: 95%;
            margin: 10px auto;
        }
        
        /* Améliorations responsive générales */
        @media (max-width: 768px) {
            .navbar-brand span {
                font-size: 0.9rem;
            }
            
            .carousel-section .text-wrapper h2 {
                font-size: 1.5rem;
            }
            
            .carousel-section .text-wrapper p {
                font-size: 0.9rem;
            }
            
            .teacher-img {
                width: 120px !important;
                height: 120px !important;
            }
            
            .footer .row {
                text-align: center;
            }
            
            .footer-map iframe {
                height: 200px;
            }
        }
        
        @media (max-width: 576px) {
            .programme-card .card-body {
                padding: 1rem;
            }
            
            .contribution-line {
                flex-direction: column;
                gap: 0.5rem;
                align-items: flex-start !important;
            }
            
            .cours-card .card-body {
                padding: 1.25rem;
            }
            
            .timeline-content {
                padding: 1rem;
            }
            
            .timeline-content h4 {
                font-size: 1.1rem;
            }
        }
        
        /* Amélioration de la navigation mobile */
        .navbar-toggler {
            border: none;
            padding: 0.25rem 0.5rem;
        }
        
        .navbar-toggler:focus {
            box-shadow: none;
        }
        
        /* Amélioration du footer */
        .footer {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }
        
        .footer-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .footer-separator {
            width: 50px;
            height: 2px;
            background-color: #ff6b35;
            opacity: 1;
            margin: 0 0 1rem 0;
        }
        
        .footer-text {
            font-size: 0.9rem;
            line-height: 1.6;
        }
        
        /* Amélioration des cartes de programmes */
        .programme-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .programme-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        
        /* Amélioration de la timeline */
        .timeline-item {
            margin-bottom: 2rem;
        }
        
        @media (max-width: 768px) {
            .timeline-item.left, 
            .timeline-item.right {
                padding: 0 15px;
            }
            
            .timeline-content {
                margin: 0;
            }
        }
    </style>
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNav">
                <span class="navbar-toggler-icon"></span>
            </button> 

            <!-- Menu -->
            <div class="collapse navbar-collapse" id="menuNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#a-propos">À propos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#programmes">Programmes</a></li>
                    <li class="nav-item"><a class="nav-link" href="#cours">Cours</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Ressources
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#activites">Activités</a></li>
                            <li><a class="dropdown-item" href="#galerie">Galerie</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('students.create') }}" class="btn btn-orange ms-4">Inscrire mon enfant</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="btn btn-orange ms-4" data-bs-toggle="modal" data-bs-target="#parentLoginModal">
                            Espace Parent
                        </a>
                    </li>
                </ul>
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
                                    <p>
                                        Le Complexe scolaire Marie-Alain se distingue par un climat éducatif stimulant et bienveillant, où l'autonomie, la discipline et la confiance en soi sont encouragées.
                                    </p>
                                    <a href="#a-propos" class="btn bg-orange mt-1">Savoir plus</a>
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
                                    <p>
                                        Nous formons des jeunes compétents, responsables et ouverts sur le monde, capables de relever les défis d'une société en constante mutation, tout en restant ancrés dans les valeurs universelles de respect, de travail et de solidarité.
                                    </p>
                                    <a href="#cours" class="btn bg-orange mt-1">Savoir plus</a>
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
                                    <p>
                                        Notre équipe pédagogique, 
                                        passionnée et attentive, accompagne chaque enfant selon son rythme, en
                                        veillant à l'équilibre entre exigence scolaire et développement personnel.
                                    </p>
                                    <a href="#admin" class="btn bg-orange mt-1">Savoir plus</a>
                                </div>
                            </div>
                            <div class="col-lg-6 col-12 animate__animated animate__fadeInRight">
                                <div class="image-decor-wrapper position-relative">
                                    <img src="{{ asset('ursule/img/galerie1.jpeg') }}" class="main-img img-fluid" alt="Pédagogie">
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
                <div class="col-lg-7 col-12 mb-4 mb-lg-0" style="text-align: justify;">
                    <span class="who-we">Qui somme nous</span>
                    <h2 class="section-title text-start mb-4">À propos </h2>
                    <div id="ecole-description" class="section-description"></div>
                    <blockquote class="founder-quote">
                        "Une mission : former des esprits ouverts et responsables"
                    </blockquote>
                    <a href="{{ route('students.create') }}" class="btn bg-orange mt-3">S'inscrire</a>
                </div>
                <!-- Image animée -->
                <div class="col-lg-5 col-12 text-center">
                    <div class="image-wrapper">
                        <img src="{{ asset('ursule/img/istockphoto-1339122137-612x612.jpg') }}" alt="Fondateur" class="img-wave-border">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="programmes" class="py-5 ">
        <div class="container">
            <div class="text-center mb-5">
                <p class="who-we text-center">Notre programme</p>
                <h2 class="fw-bold">Nos Programmes</h2>
                <p class="text-muted">Au Complexe scolaire Marie-Alain, nous croyons que chaque enfant possède un potentiel unique qui ne demande qu'à être révélé et cultivé. Notre mission est d'offrir à chaque élève un accompagnement attentif et personnalisé, où l'écoute, l'encouragement et le respect de son rythme constituent les bases de l'apprentissage.</p>
            </div>

            <div class="row g-4">
                <!-- Maternelle -->
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0 programme-card">
                        <img src="{{ asset('ursule/img/marternelle.png') }}" class="card-img-top" alt="Programme Maternelle">
                        <div class="card-body">
                            <h5 class="card-title text-orange">Maternelle</h5>
                            <p class="card-text">Éveil sensoriel, jeux éducatifs et socialisation dans un cadre bienveillant.</p>
                            <!-- Contribution -->
                            <div class="d-flex align-items-center justify-content-between contribution-line">
                                <span class="fw-semibold text-muted">Contribution annuelle</span>
                                <span class="badge rounded-pill bg-gradient-orange">15 000 – 30 000 FCFA</span>
                            </div>
                            <a href="{{ route('students.create') }}" class="btn btn-glass btn-sm mt-4 w-70">Rejoindre le programme</a>
                        </div>
                    </div>
                </div>

                <!-- Primaire -->
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0 programme-card">
                        <img src="{{ asset('ursule/img/galerie12.jpeg') }}" class="card-img-top" alt="Programme Primaire">
                        <div class="card-body">
                            <h5 class="card-title text-orange">Primaire</h5>
                            <p class="card-text">Maîtrise des fondamentaux, initiation aux langues et aux sciences.</p>
                            <!-- Contribution -->
                            <div class="d-flex align-items-center justify-content-between contribution-line">
                                <span class="fw-semibold text-muted">Contribution annuelle</span>
                                <span class="badge rounded-pill bg-gradient-orange">35 000 – 60 000 FCFA</span>
                            </div>
                            <a href="{{ route('students.create') }}" class="btn btn-glass mt-4 w-70">Rejoindre le programme</a>
                        </div>
                    </div>
                </div>

                <!-- Collège -->
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0 programme-card">
                        <img src="{{ asset('ursule/img/galerie10.jpeg') }}" class="card-img-top" alt="Programme Collège">
                        <div class="card-body">
                            <h5 class="card-title text-orange">Collège</h5>
                            <p class="card-text">Renforcement académique, projets interdisciplinaires et ouverture numérique.</p>
                            <!-- Contribution -->
                            <div class="d-flex align-items-center justify-content-between contribution-line">
                                <span class="fw-semibold text-muted">Contribution annuelle</span>
                                <span class="badge rounded-pill bg-gradient-orange">60 000 – 120 000 FCFA</span>
                            </div>
                            <a href="{{ route('students.create') }}" class="btn btn-glass mt-4 w-70">Rejoindre le programme</a>
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
            <p class="text-center mb-5">Découvrez quelque matières enseignées dans notre établissement, avec leur description et les niveaux concernés.</p>

            <div class="row g-4" id="coursList">
                <!-- Cartes visibles par défaut -->
                <div class="col-md-4">
                    <div class="cours-card h-100">
                        <div class="card-body">
                            <h5 class="cours-title"><span class="bg-3">01</span>Mathématiques</h5>
                            <p class="cours-text">Étude des nombres, calculs, géométrie, logique et résolution de problèmes.</p>
                            <p class="cours-niveau">
                                <i class="fas fa-book text-warning ms-3 me-2"></i> Primaire
                                <i class="fas fa-graduation-cap text-warning ms-3 me-2"></i> Collège
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="cours-card h-100">
                        <div class="card-body">
                            <h5 class="cours-title"><span class="bg-3">02</span>Français</h5>
                            <p class="cours-text">Lecture, écriture, grammaire, vocabulaire, expression orale et écrite.</p>
                            <p class="cours-niveau">
                                <i class="fas fa-child text-warning me-2"></i> Maternelle
                                <i class="fas fa-book text-warning ms-3 me-2"></i> Primaire
                                <i class="fas fa-graduation-cap text-warning ms-3 me-2"></i> Collège
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="cours-card h-100">
                        <div class="card-body">
                            <h5 class="cours-title">
                                <span class="bg-3">03</span> Espagnol
                            </h5>
                            <p class="cours-text">
                                Apprentissage de la langue espagnole : vocabulaire, grammaire, expression orale et écrite.
                            </p>
                            <p class="cours-niveau">
                                <i class="fas fa-graduation-cap text-warning ms-3 me-2"></i> Collège
                            </p>
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
                                    <p class="cours-niveau">
                                        <i class="fas fa-book text-warning ms-3 me-2"></i> Primaire
                                        <i class="fas fa-graduation-cap text-warning ms-3 me-2"></i> Collège
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="cours-card h-100">
                                <div class="card-body">
                                    <h5 class="cours-title"><span class="bg-3">06</span>Anglais</h5>
                                    <p class="cours-text">Initiation et approfondissement de la langue anglaise : vocabulaire, grammaire, expression.</p>
                                    <p class="cours-niveau">
                                        <i class="fas fa-book text-warning ms-3 me-2"></i> Primaire
                                        <i class="fas fa-graduation-cap text-warning ms-3 me-2"></i> Collège
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="cours-card h-100">
                                <div class="card-body">
                                    <h5 class="cours-title"><span class="bg-3">08</span>Éducation Physique</h5>
                                    <p class="cours-text">Activités sportives pour développer la motricité, la coordination et l'esprit d'équipe.</p>
                                    <p class="cours-niveau">
                                        <i class="fas fa-child text-warning me-2"></i> Maternelle
                                        <i class="fas fa-book text-warning ms-3 me-2"></i> Primaire
                                        <i class="fas fa-graduation-cap text-warning ms-3 me-2"></i> Collège
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="cours-card h-100">
                                <div class="card-body">
                                    <h5 class="cours-title"><span class="bg-3">09</span>Physique-Chimie</h5>
                                    <p class="cours-text">Étude des phénomènes physiques, lois scientifiques et réactions chimiques.</p>
                                    <p class="cours-niveau">
                                        <i class="fas fa-graduation-cap text-warning ms-3 me-2"></i> Collège
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="cours-card h-100">
                                <div class="card-body">
                                    <h5 class="cours-title"><span class="bg-3">10</span>Langage oral</h5>
                                    <p class="cours-text">Développement du vocabulaire, de l'écoute et de l'expression orale.</p>
                                    <p class="cours-niveau">
                                        <i class="fas fa-child text-warning me-2"></i> Maternelle
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="cours-card h-100">
                                <div class="card-body">
                                    <h5 class="cours-title"><span class="bg-3">11</span>Éveil sensoriel</h5>
                                    <p class="cours-text">Activités pour stimuler les sens, la curiosité et la motricité fine.</p>
                                    <p class="cours-niveau">
                                        <i class="fas fa-child text-warning me-2"></i> Maternelle
                                    </p>
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
            <p class="text-center mb-5">
                Le Complexe scolaire Marie-Alain propose un large éventail d'activités extra-scolaires, soigneusement sélectionnées pour répondre aux besoins et aux passions de chaque enfant.
            </p>
            <div class="timeline">

                <!-- Activité 1 -->
                <div class="timeline-item left">
                    <div class="timeline-content">
                        <h4>Remise des Prix aux Lauréats <i class="fas fa-trophy text-orange"></i></h4>
                        <p>Célébration des élèves méritants avec trophées et reconnaissance publique.</p>
                    </div>
                </div>

                <!-- Activité 2 -->
                <div class="timeline-item right">
                    <div class="timeline-content">
                        <h4>Sports collectifs et individuels <i class="fas fa-futbol text-orange"></i></h4>
                        <p>Football, basketball, athlétisme, gymnastique, arts martiaux… pour renforcer la coordination, l'endurance et l'esprit d'équipe.</p>
                    </div>
                </div>

                <!-- Activité 3 -->
                <div class="timeline-item left">
                    <div class="timeline-content">
                        <h4>Activités artistiques <i class="fas fa-palette text-orange"></i></h4>
                        <p>Danse, théâtre, arts plastiques, musique et chant, pour éveiller la sensibilité, stimuler l'imagination et favoriser l'expression des émotions .</p>
                    </div>
                </div>

                <div class="timeline-item right">
                    <div class="timeline-content">
                        <h4>Ateliers culturels et scientifiques <i class="fas fa-flask text-orange"></i></h4>
                        <p>clubs de lecture, d'écriture, d'informatique, d'échecs ou de sciences, pour encourager la curiosité intellectuelle et le goût de l'exploration.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="galerie">
        <div class="container">
            <p class="who-we text-center">Galerie de nos activités</p>
            <h2 class="section-title " >Galerie</h2>

            <div class="row g-4">
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="overflow-hidden rounded shadow-sm">
                        <img src="{{ asset('ursule/img/galerie1.jpeg') }}" alt="Remise des prix" class="img-fluid gallery-img" data-bs-toggle="modal" data-bs-target="#galleryModal" data-img-src="{{ asset('ursule/img/galerie1.jpeg') }}">
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <div class="overflow-hidden rounded shadow-sm">
                        <img src="{{ asset('ursule/img/galerie2.jpeg') }}" alt="Journée culturelle" class="img-fluid gallery-img" data-bs-toggle="modal" data-bs-target="#galleryModal" data-img-src="{{ asset('ursule/img/galerie2.jpeg') }}">
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <div class="overflow-hidden rounded shadow-sm">
                        <img src="{{ asset('ursule/img/galerie3.jpeg') }}" alt="Club de musique" class="img-fluid gallery-img" data-bs-toggle="modal" data-bs-target="#galleryModal" data-img-src="{{ asset('ursule/img/galerie3.jpeg') }}">
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <div class="overflow-hidden rounded shadow-sm">
                        <img src="{{ asset('ursule/img/galerie4.jpeg') }}" alt="Spectacle" class="img-fluid gallery-img" data-bs-toggle="modal" data-bs-target="#galleryModal" data-img-src="{{ asset('ursule/img/galerie4.jpeg') }}">
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <div class="overflow-hidden rounded shadow-sm">
                        <img src="{{ asset('ursule/img/galerie5.jpeg') }}" alt="Exposition" class="img-fluid gallery-img" data-bs-toggle="modal" data-bs-target="#galleryModal" data-img-src="{{ asset('ursule/img/galerie5.jpeg') }}">
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <div class="overflow-hidden rounded shadow-sm">
                        <img src="{{ asset('ursule/img/galerie6.jpeg') }}" alt="Concert" class="img-fluid gallery-img" data-bs-toggle="modal" data-bs-target="#galleryModal" data-img-src="{{ asset('ursule/img/galerie6.jpeg') }}">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal pour la galerie -->
    <div class="modal fade modal-gallery" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 p-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <img id="modalImage" src="" alt="" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <footer class="footer text-light pb-4">
        <div class="container p-3">
            <div class="row gy-5 grop">
                <!-- À propos -->
                <div class="col-md-3">
                    <h5 class="footer-title">À propos</h5>
                    <hr class="footer-separator">
                    <p class="footer-text">
                        Nous sommes un établissement dédié à l'épanouissement des jeunes à travers l'éducation, la culture et les arts.<br><br>
                        Notre mission est de former des citoyens responsables et créatifs dans un environnement stimulant.
                    </p>
                </div>

                <div class="col-md-3">
                    <h5 class="footer-title"> Localisation</h5>
                    <hr class="footer-separator">
                    <div class="footer-map">
                        <iframe
                            <iframe
                              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3964.123456789012!2d2.3466118!3d6.4502548!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwMjcnMDAuOSJOIDLCsDIwJzQ3LjgiRQ!5e0!3m2!1sfr!2sbj!4v1234567890"
                              width="100%" 
                              height="150" 
                              style="border:0;" 
                              allowfullscreen="" 
                              loading="lazy" 
                              referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                            
                        <p class="footer-text">
                            Aitchédji Abomey-Calavi, Bénin<br>
                        </p>
                    </div>
                </div>

                <!-- Contact -->
                <div class="col-md-3">
                    <h5 class="footer-title">Contact</h5>
                    <hr class="footer-separator">
                    <form id="contactForm">
                        @csrf
                        <input type="text" name="name" class="form-control form-control-sm mb-3" placeholder="Nom" required>
                        <input type="email" name="email" class="form-control form-control-sm mb-3" placeholder="Email" required>
                        <textarea name="message" class="form-control form-control-sm mb-3" rows="2" placeholder="Message" required></textarea>
                        <button type="submit" class="btn btn-sm btn-danger w-100">Envoyer</button>
                    </form>

                    <div id="formResponse" class="mt-3"></div>

                    <script>
                    document.getElementById('contactForm').addEventListener('submit', async function (e) {
                        e.preventDefault();

                        const form = e.target;
                        const responseDiv = document.getElementById('formResponse');
                        const formData = new FormData(form);

                        // Envoi AJAX
                        const response = await fetch("{{ route('contact.send') }}", {
                            method: "POST",
                            headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                            body: formData
                        });

                        const result = await response.json();

                        if (result.success) {
                            responseDiv.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                            form.reset();
                        } else {
                            responseDiv.innerHTML = `<div class="alert alert-danger">${result.message}</div>`;
                        }
                    });
                    </script>

                </div>

                <!-- Coordonnées -->
                <div class="col-md-3">
                    <h5 class="footer-title">Coordonnées</h5>
                    <hr class="footer-separator">
                    <p class="footer-text d-flex flex-column gap-3">
                        <span><i class="bi bi-geo-alt-fill text-danger me-2"></i> Quartier Aitchédji Abomey-Calavi, Bénin</span>
                        <span><i class="bi bi-telephone-fill text-danger me-2"></i> +229 01 97 21 20 45</span>
                        <span><i class="bi bi-envelope-fill text-danger me-2"></i> cpegmariealain@gmail.com</span>
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
    
    <script>
        // Script pour la galerie modale
        document.addEventListener('DOMContentLoaded', function() {
            const galleryImages = document.querySelectorAll('.gallery-img');
            const modalImage = document.getElementById('modalImage');
            const galleryModal = document.getElementById('galleryModal');
            
            galleryImages.forEach(img => {
                img.addEventListener('click', function() {
                    const imgSrc = this.getAttribute('data-img-src');
                    modalImage.src = imgSrc;
                    modalImage.alt = this.alt;
                });
            });
            
            // Fermer la modal avec la touche Échap
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const modal = bootstrap.Modal.getInstance(galleryModal);
                    if (modal) {
                        modal.hide();
                    }
                }
            });
            
            // Gestion du bouton "Voir plus" des cours
            const toggleBtn = document.getElementById('toggleBtn');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    const expanded = this.getAttribute('aria-expanded') === 'true';
                    this.textContent = expanded ? 'Voir plus' : 'Voir moins';
                });
            }
        });
    </script>

    <!-- Modal Connexion Parent -->
<div class="modal fade" id="parentLoginModal" tabindex="-1" aria-labelledby="parentLoginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 overflow-hidden">
            <div class="modal-body p-0">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3 z-3" data-bs-dismiss="modal" aria-label="Close"></button>
                
                <div class="row g-0">
                    <!-- Partie gauche avec image et décoration -->
                    <div class="col-lg-5 d-none d-lg-block position-relative" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="h-100 d-flex flex-column justify-content-center align-items-center text-white p-4 position-relative">
                            <!-- Décoration animée -->
                            <div class="position-absolute top-0 start-0 w-100 h-100 overflow-hidden">
                                <div class="shape-1"></div>
                                <div class="shape-2"></div>
                                <div class="shape-3"></div>
                            </div>
                            
                            <!-- Contenu -->
                            <div class="text-center position-relative" style="z-index: 1;">
                                <img src="{{ asset('ursule/img/logo.png') }}" alt="Logo" style="height: 80px; filter: brightness(0) invert(1);" class="mb-4 animate__animated animate__fadeInDown">
                                <h3 class="fw-bold mb-3 animate__animated animate__fadeInUp">Bienvenue!</h3>
                                <p class="mb-4 animate__animated animate__fadeInUp animate__delay-1s">Connectez-vous pour suivre la scolarité de vos enfants</p>
                                <div class="mt-4 animate__animated animate__fadeInUp animate__delay-2s">
                                    <i class="fas fa-chart-line fa-2x mx-2"></i>
                                    <i class="fas fa-graduation-cap fa-2x mx-2"></i>
                                    <i class="fas fa-calendar-check fa-2x mx-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Partie droite avec formulaire -->
                    <div class="col-lg-7 p-4 p-lg-5 bg-white">
                        <div class="text-center mb-4">
                            <h4 class="fw-bold text-dark mb-2 animate__animated animate__fadeInDown">Espace Parent</h4>
                            <p class="text-muted small animate__animated animate__fadeIn">Accédez à votre espace sécurisé</p>
                        </div>

                        <!-- Messages d'erreur -->
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show animate__animated animate__shakeX" role="alert">
                                @foreach($errors->all() as $error)
                                    <i class="fas fa-exclamation-circle me-2"></i>{{ $error }}<br>
                                @endforeach
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show animate__animated animate__shakeX" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('parent.login') }}" class="animate__animated animate__fadeInUp" id="parentLoginForm">
                            @csrf
                            
                            <!-- Champ Téléphone -->
                            <div class="form-floating mb-3">
                                <input type="tel" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       placeholder="01XXXXXXXX" 
                                       value="{{ old('phone') }}"
                                       pattern="01[0-9]{8}"
                                       title="Format: 01XXXXXXXX"
                                       required>
                                <label for="phone"><i class="fas fa-phone-alt me-2 text-orange"></i>Numéro de téléphone</label>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Champ Mot de passe -->
                            <div class="form-floating mb-3 position-relative">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Mot de passe"
                                       required>
                                <label for="password"><i class="fas fa-lock me-2 text-orange"></i>Mot de passe</label>
                                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y" onclick="togglePassword()">
                                    <i class="far fa-eye" id="togglePasswordIcon"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Options supplémentaires -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                    <label class="form-check-label small" for="remember">
                                        Se souvenir de moi
                                    </label>
                                </div>
                                <a href="#" class="text-orange text-decoration-none small" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                                    Mot de passe oublié?
                                </a>
                            </div>

                            <!-- Bouton de connexion -->
                            <button type="submit" class="btn btn-orange w-100 py-3 mb-3 fw-bold" id="loginButton">
                                <span class="spinner-border spinner-border-sm d-none" id="loginSpinner" role="status"></span>
                                <span id="loginText">Se connecter</span>
                            </button>

                            <!-- Lien d'inscription -->
                            <p class="text-center small text-muted mb-0">
                                Pas encore inscrit? 
                                <a href="{{ route('students.create') }}" class="text-orange text-decoration-none fw-bold">Inscrivez votre enfant</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Mot de passe oublié -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 bg-orange text-white">
                <h5 class="modal-title"><i class="fas fa-key me-2"></i>Réinitialisation mot de passe</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-3">Entrez votre numéro de téléphone pour recevoir un lien de réinitialisation.</p>
                <div class="form-floating mb-3">
                    <input type="tel" class="form-control" id="resetPhone" placeholder="01XXXXXXXX">
                    <label for="resetPhone">Numéro de téléphone</label>
                </div>
                <button type="button" class="btn btn-orange w-100" onclick="sendResetLink()">
                    Envoyer le lien
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles personnalisés pour la modal */
.modal-content {
    animation: modalSlideIn 0.5s ease;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Formes décoratives animées */
.shape-1, .shape-2, .shape-3 {
    position: absolute;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    animation: float 6s infinite ease-in-out;
}

.shape-1 {
    width: 200px;
    height: 200px;
    top: -50px;
    left: -50px;
    animation-delay: 0s;
}

.shape-2 {
    width: 150px;
    height: 150px;
    bottom: -30px;
    right: -30px;
    animation-delay: 2s;
}

.shape-3 {
    width: 100px;
    height: 100px;
    bottom: 50px;
    left: 50px;
    animation-delay: 4s;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-20px) rotate(180deg);
    }
}

/* Effets de focus sur les inputs */
.form-floating > .form-control:focus {
    border-color: #ff6b35;
    box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
}

.form-floating > .form-control:focus + label {
    color: #ff6b35;
}

/* Bouton orange personnalisé */
.btn-orange {
    background: linear-gradient(45deg, #ff6b35, #ff8c5a);
    color: white;
    border: none;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-orange:hover {
    background: linear-gradient(45deg, #ff8c5a, #ff6b35);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4);
    color: white;
}

.btn-orange:active {
    transform: translateY(0);
}

/* Animation de chargement */
.spinner-border {
    vertical-align: middle;
    margin-right: 5px;
}

/* Effet de vague sur le bouton */
.btn-orange::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 5px;
    height: 5px;
    background: rgba(255, 255, 255, 0.5);
    opacity: 0;
    border-radius: 100%;
    transform: scale(1, 1) translate(-50%);
    transform-origin: 50% 50%;
}

.btn-orange:focus:not(:active)::after {
    animation: ripple 1s ease-out;
}

@keyframes ripple {
    0% {
        transform: scale(0, 0);
        opacity: 0.5;
    }
    20% {
        transform: scale(25, 25);
        opacity: 0.5;
    }
    100% {
        opacity: 0;
        transform: scale(40, 40);
    }
}

/* Adaptation mobile */
@media (max-width: 768px) {
    .modal-dialog {
        margin: 0.5rem;
    }
    
    .modal-content {
        border-radius: 1rem !important;
    }
}
</style>

<script>
// Fonction pour afficher/masquer le mot de passe
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const icon = document.getElementById('togglePasswordIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Animation du bouton de connexion
document.getElementById('parentLoginForm')?.addEventListener('submit', function(e) {
    const button = document.getElementById('loginButton');
    const spinner = document.getElementById('loginSpinner');
    const text = document.getElementById('loginText');
    
    button.disabled = true;
    spinner.classList.remove('d-none');
    text.textContent = 'Connexion en cours...';
});

// Remplir avec un compte démo
function fillDemoAccount() {
    document.getElementById('phone').value = '0197212045';
    document.getElementById('password').value = 'password123';
    
    // Animation de confirmation
    Swal.fire({
        icon: 'success',
        title: 'Compte démo rempli!',
        text: 'Cliquez sur Se connecter pour continuer',
        timer: 2000,
        showConfirmButton: false
    });
}

// Info mode démo
function showDemoInfo() {
    Swal.fire({
        title: 'Mode Démo',
        html: `
            <p><strong>Téléphone:</strong> 0197212045</p>
            <p><strong>Mot de passe:</strong> password123</p>
            <p class="text-muted small mt-2">Ceci est un compte de démonstration</p>
        `,
        icon: 'info',
        confirmButtonColor: '#ff6b35'
    });
}

// Simulation envoi lien de réinitialisation
function sendResetLink() {
    const phone = document.getElementById('resetPhone').value;
    if (!phone) {
        Swal.fire({
            icon: 'warning',
            title: 'Champ requis',
            text: 'Veuillez entrer votre numéro de téléphone',
            confirmButtonColor: '#ff6b35'
        });
        return;
    }
    
    Swal.fire({
        icon: 'success',
        title: 'Lien envoyé!',
        text: 'Un lien de réinitialisation a été envoyé à votre numéro',
        timer: 3000,
        showConfirmButton: false
    });
    
    setTimeout(() => {
        bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal')).hide();
    }, 2000);
}

document.addEventListener('DOMContentLoaded', function() {
    @if($errors->any() || session('error'))
        // Afficher le modal automatiquement s'il y a des erreurs
        var myModal = new bootstrap.Modal(document.getElementById('parentLoginModal'));
        myModal.show();
    @endif
});
</script>
</body>
</html>