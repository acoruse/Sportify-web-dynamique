<?php
session_start(); // Nécessaire pour accéder aux variables de session
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tout parcourir - Sportify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Sportify</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index_projet.php">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="parcourir.php">Tout parcourir</a></li>
                <li class="nav-item"><a class="nav-link" href="recherche.php">Recherche</a></li>
                <li class="nav-item"><a class="nav-link" href="rendezvous.php">Rendez-vous</a></li>
                <li class="nav-item"><a class="nav-link" href="compte.php">Votre Compte</a></li>
            </ul>

            <?php if (isset($_SESSION['nom'])): ?>
                <ul class="navbar-nav ms-auto d-flex align-items-center">
                    <li class="nav-item">
                        <span class="nav-link">👤 <?= htmlspecialchars($_SESSION['nom']) ?></span>
                    </li>
                    <li class="nav-item">
                        <form action="deconnexion.php" method="post" class="d-inline">
                            <button type="submit" class="btn btn-outline-danger btn-sm ms-2">Déconnexion</button>
                        </form>
                    </li>
                </ul>
            <?php else: ?>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a href="compte.php" class="btn btn-outline-primary">Connexion</a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>
<!-- CONTENU PRINCIPAL -->
<div class="container mt-5">
    <h1 class="mb-4">Parcourir les activités sportives</h1>

    <div class="row">
        <!-- Carte Activité 1 -->
        <div class="col-md-4">
            <div class="card mb-4">
                <img src="images_projet/activite.jpg" class="card-img-top" alt="Football">
                <div class="card-body">
                    <h5 class="card-title">Activités Sportive</h5>
                    <p class="card-text">Venez decouvrir les activites sportives que l'on propose !</p>
                    <a href="activite_sportive.php" class="btn btn-primary">Voir plus</a>
                </div>
            </div>
        </div>

        <!-- Carte Activité 2 -->
        <div class="col-md-4">
            <div class="card mb-4">
                <img src="images_projet/compet.jpg" class="card-img-top" alt="Natation">
                <div class="card-body">
                    <h5 class="card-title">Sport de compétition</h5>
                    <p class="card-text">Venez pratiquer du sport à haut niveau près de chez vous !</p>
                    <a href="sport_de_competition.php" class="btn btn-primary">Voir plus</a>
                </div>
            </div>
        </div>

        <!-- Carte Activité 3 -->
        <div class="col-md-4">
            <div class="card mb-4">
                <img src="images_projet/musculation.jpg" class="card-img-top" alt="Musculation">
                <div class="card-body">
                    <h5 class="card-title">Musculation</h5>
                    <p class="card-text">Accédez à des salles de sport ou trouvez un coach personnel.</p>
                    <a href="Salle_sport_omnes.php" class="btn btn-primary">Voir plus</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FOOTER -->
<footer class="text-center py-4 mt-5 bg-light">
    <p class="mb-0">&copy; 2025 Sportify. Tous droits réservés.</p>
</footer>

</body>
</html>
