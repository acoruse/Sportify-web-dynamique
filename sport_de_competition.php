<?php
include('connection_BDD.php');// on inclut le fichier pour se connecter a la BDD
session_start();// pour savoir si l user est connecte
// ps ce code s inspire grandemende de "activite_sportive.php"
// s'y referer si besoin 
// on verifie si l utilisateur est co
if (!isset($_SESSION['id'])) {
    header('Location: compte.php');
    exit();
}
$activites = ["Basketball", "Football", "Rugby", "Tennis", "Natation", "Plongeon"];
$coachs = [];

if (isset($_GET['activite'])) {
    $activite = $_GET['activite'];

    // on recupere  toutes les lignes liees à l'activite
    $stmt = $conn->prepare("SELECT * FROM coach WHERE spe = ?");
    $stmt->bind_param("s", $activite);
    $stmt->execute();
    $result = $stmt->get_result();

    //  puis on regroupe les coachs selon  "id_coach"
    while ($row = $result->fetch_assoc()) {
        $id = $row['id_coach'];
        if (!isset($coachs[$id])) {
            $coachs[$id] = [
                'nom' => $row['nom'],
                'image' => $row['image'],
                'bureau' => $row['bureau'],
                'spe' => $row['spe'],
                'dispos' => []
            ];
        }
        $coachs[$id]['dispos'][] = $row['jour'] . ' de ' . $row['heure_debut'] . ' à ' . $row['heure_fin'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Sports - Sportify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><!-- Lien vers Bootstrap pour la mise en page dynamique -->
</head>
<body>

<!-- barre de nav-->
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
            <!-- Si l utilisateur est co, on affiche son nom et un bouton de deco -->

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

<div class="container mt-5">
    <h1 class="text-center mb-4">Sports</h1>

    <div class="mb-4 text-center">
        <p>Choisissez un sport pour découvrir les coachs disponibles :</p>
        <!-- on affiche un bouton pour chaque activite-->
        <?php foreach ($activites as $act): ?>
            <a href="sport_de_competition.php?activite=<?= urlencode($act) ?>" class="btn btn-outline-primary m-1"><?= $act ?></a>
        <?php endforeach; ?>
    </div>
    <!-- si des coachs sont trouves  -->
    <?php if (!empty($coachs)): ?>
        <h2 class="text-center mb-4">Coach(s) pour l'activité : <?= htmlspecialchars($activite) ?></h2>
        <div class="row">
            <!-- on affiche chaque coach dans une carte -->
            <?php foreach ($coachs as $id => $coach): ?>
                <div class="col-md-4 mb-4">
                    <div class="card text-center">
                        <img src="<?= htmlspecialchars($coach['image']) ?>" class="card-img-top" alt="Coach <?= htmlspecialchars($coach['nom']) ?>" style="max-height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($coach['nom']) ?></h5>
                            <p class="card-text">Bureau : <?= htmlspecialchars($coach['bureau']) ?></p>
                            <p class="card-text">Activité : <?= htmlspecialchars($coach['spe']) ?></p>
                            <p class="card-text">Disponibilités :</p>
                            <ul class="list-unstyled">
                                <?php foreach ($coach['dispos'] as $dispo): ?>
                                    <li>🕒 <?= htmlspecialchars($dispo) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <a href="cv.php?coach_id=<?= urlencode($id) ?>" class="btn btn-sm btn-outline-info">Voir le CV</a>
                            <a href="message.php?coach_id=<?= urlencode($id) ?>" class="btn btn-sm btn-outline-dark">Contacter</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
         <!-- si une activite est selec mais qu aucun coach n est trouve-->
    <?php elseif (isset($_GET['activite'])): ?>
        <p class="text-center text-muted">Aucun coach trouvé pour cette activité.</p>
    <?php endif; ?>
</div>

<footer class="text-center py-4 mt-5 bg-light">
    <p class="mb-0">&copy; 2025 Sportify. Tous droits réservés.</p>
</footer>

</body>
</html>
