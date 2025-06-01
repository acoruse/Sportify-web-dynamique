<?php
include('connection_BDD.php');
session_start();

$coachs = [];
$specialites = ["Musculation", "Fitness", "Biking", "Cardio-Training", "Cours Collectifs", "Basketball", "Football", "Rugby", "Tennis", "Natation", "Plongeon"];

if (isset($_GET['specialite'])) {
    $specialite = $_GET['specialite'];

    // Récupérer tous les coachs de cette spécialité
    $stmt = $conn->prepare("SELECT * FROM coach WHERE spe = ?");
    $stmt->bind_param("s", $specialite);
    $stmt->execute();
    $result = $stmt->get_result();

    // Regrouper par coach pour afficher toutes ses dispos
    while ($row = $result->fetch_assoc()) {
        $id = $row['id_coach'];
        if (!isset($coachs[$id])) {
            $coachs[$id] = [
                'nom'    => $row['nom'],
                'image'  => $row['image'],
                'bureau' => $row['bureau'],
                'spe'    => $row['spe'],
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
    <title>Recherche de Coachs - Sportify</title>
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
        <li class="nav-item"><a class="nav-link active" href="recherche.php">Recherche</a></li>
        <li class="nav-item"><a class="nav-link" href="rendezvous.php">Rendez-vous</a></li>
        <li class="nav-item"><a class="nav-link" href="compte.php">Votre Compte</a></li>
      </ul>
      <?php if (isset($_SESSION['nom'])): ?>
      <ul class="navbar-nav ms-auto d-flex align-items-center">
        <li class="nav-item"><span class="nav-link">👤 <?= htmlspecialchars($_SESSION['nom']) ?></span></li>
        <li class="nav-item">
          <form action="deconnexion.php" method="post" class="d-inline">
            <button type="submit" class="btn btn-outline-danger btn-sm ms-2">Déconnexion</button>
          </form>
        </li>
      </ul>
      <?php else: ?>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a href="compte.php" class="btn btn-outline-primary">Connexion</a></li>
      </ul>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="container mt-5">
  <h1 class="text-center mb-4">Recherche de Coachs</h1>

  <!-- Formulaire de recherche -->
  <form method="GET" action="recherche.php" class="mb-5">
    <div class="row g-3 justify-content-center">
      <div class="col-md-6">
        <select name="specialite" class="form-select" required>
          <option value="">Sélectionnez une spécialité</option>
          <?php foreach ($specialites as $spe): ?>
            <option value="<?= htmlspecialchars($spe) ?>"
              <?= (isset($specialite) && $specialite == $spe) ? 'selected' : '' ?>>
              <?= htmlspecialchars($spe) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">Rechercher</button>
      </div>
    </div>
  </form>

  <?php if (isset($specialite)): ?>
    <h2 class="mb-4 text-center">Résultats pour « <?= htmlspecialchars($specialite) ?> »</h2>
    <?php if (!empty($coachs)): ?>
      <div class="row">
        <?php foreach ($coachs as $id => $coach): ?>
          <div class="col-md-4 mb-4">
            <div class="card text-center">
              <img src="<?= htmlspecialchars($coach['image']) ?>"
                   class="card-img-top"
                   alt="Photo de <?= htmlspecialchars($coach['nom']) ?>"
                   style="max-height:200px; object-fit:cover;">
              <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($coach['nom']) ?></h5>
                <p class="card-text"><strong>Bureau :</strong> <?= htmlspecialchars($coach['bureau']) ?></p>
                <p class="card-text"><strong>Spécialité :</strong> <?= htmlspecialchars($coach['spe']) ?></p>
                <p class="card-text"><strong>Disponibilités :</strong></p>
                <ul class="list-unstyled mb-3">
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
    <?php else: ?>
      <p class="text-center text-muted">Aucun coach trouvé pour cette spécialité.</p>
    <?php endif; ?>
  <?php endif; ?>
</div>

<footer class="text-center py-4 mt-5 bg-light">
  <p class="mb-0">&copy; 2025 Sportify. Tous droits réservés.</p>
</footer>
</body>
</html>
