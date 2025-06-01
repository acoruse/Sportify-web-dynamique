
<?php
include('connection_BDD.php');// afin de se connecter a la BDD
session_start();// afin de savoir si l'on est connecte ou non
// on verifie si l utilisateur est co
if (!isset($_SESSION['id'])) {
    header('Location: compte.php');
    exit();
}
$activites = ["Musculation", "Fitness", "Biking", "Cardio-Training", "Cours Collectifs"];// on liste les activites
$coachs = [];//on cree le tableau vide coach 

if (isset($_GET['activite'])) {
    $activite = $_GET['activite'];

    // on recupere toutes les lignes liees a l activite 
    $stmt = $conn->prepare("SELECT * FROM coach WHERE spe = ?");
    $stmt->bind_param("s", $activite);
    $stmt->execute();
    $result = $stmt->get_result();

    // on regroupe les coachs selon id_coach
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
    <title>Activités Sportives - Sportify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- barre de navigation -->
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
<!-- on passe au contenu -->
<div class="container mt-5">
    <h1 class="text-center mb-4">Activités Sportives</h1><!--d abord les activites sportives -->

    <!-- Bloc : Choix de l'activite -->
<div class="mb-4 text-center">
    
    <p>Choisissez une activité pour découvrir les coachs disponibles :</p>
    
    <!-- Boucle pour  chaque activites -->
    <?php foreach ($activites as $act): ?>
        <!-- on Crée un bouton pour chaque activité, menant a la mm page avec le nom de l activite en url -->
        <a href="activite_sportive.php?activite=<?= urlencode($act) ?>" class="btn btn-outline-primary m-1"><?= $act ?></a>
    <?php endforeach; ?>
</div>

<!-- Si la variable $coachs n'est pas vide ( soit au moins un coach trouve) -->
<?php if (!empty($coachs)): ?>
    
    <h2 class="text-center mb-4">Coach(s) pour l'activité : <?= htmlspecialchars($activite) ?></h2>

    <!-- on Crée une rangée Bootstrap -->
    <div class="row">

        <!-- Boucle : pour  chaque coach trouve -->
        <?php foreach ($coachs as $id => $coach): ?>
            <!-- Colonne Bootstrap de taille moyenne -->
            <div class="col-md-4 mb-4">
                <!-- Carte Bootstrap pour presenter le coach -->
                <div class="card text-center">

                    <!-- on affiche l image du coach suivant son nom -->
                    <img src="<?= htmlspecialchars($coach['image']) ?>" class="card-img-top" alt="Coach <?= htmlspecialchars($coach['nom']) ?>" style="max-height: 200px; object-fit: cover;">
