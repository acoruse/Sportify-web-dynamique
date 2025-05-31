<?php
session_start();
include('connection_BDD.php');
//  Redirection automatique si déjà connecté
if (isset($_SESSION['id'], $_SESSION['type'])) {
    switch ($_SESSION['type']) {
        case 1:
            header("Location: clients.php");
            exit;
        case 2:
            header("Location: coach.php");
            exit;
        case 3:
            header("Location: admin.php");
            exit;
        default:
            header("Location: index_projet.php");
            exit;
    }
}
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];

    if (!empty($email) && !empty($mot_de_passe)) {
        $sql = "SELECT * FROM utilisateurs WHERE email = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Comparaison simple (en clair) — à remplacer par password_verify() si hashé
                if ($mot_de_passe === $user['mdp']) {
                    // Stocker les infos dans la session
                    $_SESSION['id']    = $user['ID'];
                    $_SESSION['nom']   = $user['nom'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['type']  = $user['type'];

                    // Redirection en fonction du type
                    switch ($user['type']) {
                        case 1: header("Location: clients.php"); break;
                        case 2: header("Location: coach.php"); break;
                        case 3: header("Location: admin.php"); break;
                        default: header("Location: index.php"); break;
                    }
                    exit;
                } else {
                    $erreur = "❌ Mot de passe incorrect.";
                }
            } else {
                $erreur = "❌ Aucun compte trouvé avec cet email.";
            }

            $stmt->close();
        } else {
            $erreur = "❌ Erreur de préparation de la requête.";
        }
    } else {
        $erreur = "❌ Veuillez remplir tous les champs.";
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Sportify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light"><!-- on deploie a partir de l ecran , avec un texte clair et l arriere plan est gris clair-->
        <div class="container-fluid"><!--conteneur fluide qui prend toute la largeur de l ecran , pour organiser le contenu de la barre -->
            <a class="navbar-brand" href="#">Sportify</a><!--lien pour afficher le nom de la "marque" , ne dirige nul part pour l instant adpter si besoin-->
            <div class="collapse navbar-collapse"><!-- regroupe les liens de navigation , peut etre repliee ou depliee-->
                <ul class="navbar-nav me-auto "><!-- liste non ordonné ,class bootstrap , pousse les elements a gauche-->
                    <li class="nav-item"><a class="nav-link" href="index_projet.php">Accueil</a></li><!-- on cree les liens-->
                    <li class="nav-item"><a class="nav-link" href="parcourir.php">Tout parcourir</a></li>
                    <li class="nav-item"><a class="nav-link" href="recherche.php">Recherche</a></li>
                    <li class="nav-item"><a class="nav-link" href="rendezvous.php">Rendez-vous</a></li>
                    <li class="nav-item"><a class="nav-link" href="compte.php">Votre Compte</a></li>
                </ul>
            </div>
        </div>
    </nav>

<div class="container mt-5">
    <h2>Connexion à Sportify</h2>

    <?php if ($erreur): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <form action="compte.php" method="POST" class="mt-4">
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Mot de passe</label>
            <input type="password" name="mot_de_passe" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Se connecter</button>
    </form>
</div>
</body>
</html>
