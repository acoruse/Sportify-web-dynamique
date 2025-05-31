<?php
include('connection_BDD.php');
session_start();

// on recupere les coachs
$coachs = [];
$sql = "SELECT nom, activite_pref, image FROM utilisateurs WHERE type = 2";//on prend leur nom/spe/image de la table utilisateur quand c des coachs
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $coachs = $result->fetch_all(MYSQLI_ASSOC);
}

// si le user est un admin alors il peut modifier/ajouter l event de la semaine
if (isset($_SESSION['type']) && $_SESSION['type'] == 3 && $_SERVER["REQUEST_METHOD"] == "POST") {
    $titre = $_POST['titre'] ?? '';
    $date = $_POST['date'] ?? '';
    $img = $_POST['img'] ?? '';

    if (!empty($titre) && !empty($date) && !empty($img)) {
        // Remplace l'ancien event (1 seul event en base)
        $conn->query("DELETE FROM event_semaine");
        $stmt = $conn->prepare("INSERT INTO event_semaine (Titre, Date, img) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $titre, $date, $img);
        $stmt->execute();
    }
}

//On recupere les infos de l evenement de la semaine
$evenement = null;
$sqlEvent = "SELECT Titre, Date, img FROM event_semaine ORDER BY Date DESC LIMIT 1";
$resultEvent = $conn->query($sqlEvent);
if ($resultEvent && $resultEvent->num_rows > 0) {
    $evenement = $resultEvent->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Sportify - Accueil</title>
    <link rel="stylesheet" href="style_projet.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<header class="bg-dark text-white p-3 text-center">
    <h1>Bienvenue sur Sportify</h1>
    <p>La plateforme de consultation sportive de la communauté Omnes Education</p>
</header>

<!-- Barre de nav -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <!-- Titre/logo de la barre -->
        <a class="navbar-brand" href="#">Sportify</a>

        <!-- Contenu de la barre de navigation -->
        <div class="collapse navbar-collapse">
            <!-- On met les liens de nav a gauches , ils renvoient aux differentes pages du site -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index_projet.php">Accueil</a></li><!-- accueil du site -->
                <li class="nav-item"><a class="nav-link" href="parcourir.php">Tout parcourir</a></li><!-- tout parcourir -->
                <li class="nav-item"><a class="nav-link" href="recherche.php">Recherche</a></li><!-- recherche-->
                <li class="nav-item"><a class="nav-link" href="rendezvous.php">Rendez-vous</a></li><!-- RDV -->
                <li class="nav-item"><a class="nav-link" href="compte.php">Votre Compte</a></li><!-- compte de l user -->
            </ul>

            <!-- Si le user  est connecte-->
            <?php if (isset($_SESSION['nom'])): ?>
                <ul class="navbar-nav ms-auto d-flex align-items-center">
                    <!-- on affiche son nom avec une petite icone -->
                    <li class="nav-item">
                        <span class="nav-link">👤 <?= htmlspecialchars($_SESSION['nom']) ?></span>
                    </li>

                    <!-- Bouton de deco -->
                    <li class="nav-item">
                        <form action="deconnexion.php" method="post" class="d-inline">
                            <button type="submit" class="btn btn-outline-danger btn-sm ms-2">Déconnexion</button>
                        </form>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>
<!-- fin de la barre de nav-->
<!--section de l event de la semaine-->
<main class="container mt-4">
    <section class="mb-5">
        <h2>Évènement de la semaine</h2>
        <?php if ($evenement): ?>
            <div class="card mb-3">
                <img src="<?= htmlspecialchars($evenement['img']) ?>" class="card-img-top rounded w-25 mx-auto d-block" alt="Évènement de la semaine"><!-- on recup l image depuis la table event_semaine-->


                <div class="card-body text-center">
    <h5 class="card-title"><?= htmlspecialchars($evenement['Titre']) ?></h5> <!-- pareil pour le titre-->
    <p class="card-text">Date : <?= htmlspecialchars($evenement['Date']) ?></p> <!-- pareil pour la date-->
</div>

            </div>
        <?php else: ?>
            <p>Aucun évènement à afficher pour le moment.</p><!-- si aucun event on affiche -->
        <?php endif; ?>

        <?php if (isset($_SESSION['type']) && $_SESSION['type'] == 3): ?> <!-- si le user est un admin-->
            <!-- on affiche le formulaire pour l admin -->
            <h4>Modifier l’événement de la semaine</h4>
            <form method="post" class="border p-3 rounded bg-light">
                <div class="mb-3">
                    <label for="titre" class="form-label">Titre de l’événement</label>
                    <input type="text" name="titre" id="titre" class="form-control" required><!-- section pour modifier le titre-->
                </div>
                <div class="mb-3">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" name="date" id="date" class="form-control" required><!-- section pour modifier la date-->
                </div>
                <div class="mb-3">
                    <label for="img" class="form-label">URL de l’image</label>
                    <input type="text" name="img" id="img" class="form-control" required><!-- lien de l image-->
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button><!-- bouton pour enregistrer-->
            </form>
        <?php endif; ?>
    </section>
<!--  section sur les specialistes-->
    <section class="mb-5">
        <h2>Nos spécialistes</h2>
        <div id="carouselCoach" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($coachs as $index => $coach): ?><!-- pour chaque coach-->
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <img src="<?= htmlspecialchars($coach['image']) ?>" class="img-fluid w-40 rounded shadow mx-auto d-block" alt="Coach <?= $index + 1 ?>"><!-- on fait defiler les images en incrementant l indice-->
                        <div class="carousel-caption d-none d-md-block">
                            <h5><?= htmlspecialchars($coach['nom']) ?></h5><!--on affiche le nom correspondant-->
                            <p>Coach <?= htmlspecialchars($coach['activite_pref']) ?></p><!--et sa spe-->
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselCoach" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselCoach" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span><!--bouttons pour faire defiler le caroussel-->
            </button>
        </div>
    </section>
</main>
<!-- bas de la page-->
<footer class="bg-dark text-white text-center p-4">
    <p>Contactez-nous : contact@sportify.omnes | 01 23 45 67 89</p><!-- infos pour contacter la salle de sport-->
    <p>Adresse : 10 Rue Sextius Michel, Paris 75015</p>
    <div class="map-responsive">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2498.8896518569727!2d2.285962676128088!3d48.851108001219416!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e6701b486bb253%3A0x61e9cc6979f93fae!2s10%20Rue%20Sextius%20Michel%2C%2075015%20Paris!5e1!3m2!1sfr!2sfr!4v1748181860815!5m2!1sfr!2sfr" width="400" height="250" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" ></iframe> <!--lien pour le plan donnant l addresse , dispo sur google map -->
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
