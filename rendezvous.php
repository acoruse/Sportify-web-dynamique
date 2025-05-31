<?php
include('connection_BDD.php');
session_start();

// Redirection si non connecté
if (!isset($_SESSION['id'])) {
    header('Location: compte.php');
    exit();
}

$client_id = $_SESSION['id'];
$message = '';

// Annulation d’un RDV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_rdv'])) {
    $id_rdv = intval($_POST['cancel_rdv']);
    $stmtCancel = $conn->prepare(
        "DELETE FROM rdv WHERE id_rdv = ? AND index_client = ?"
    );
    $stmtCancel->bind_param("ii", $id_rdv, $client_id);
    if ($stmtCancel->execute()) {
        $message = "✅ Votre rendez-vous a bien été annulé.";
    } else {
        $message = "❌ Impossible d'annuler le rendez-vous.";
    }
    $stmtCancel->close();
}

// Réservation d’un nouveau créneau
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['coach_id']) && !isset($_POST['cancel_rdv'])) {
    list($id_coach, $jour, $heure_debut, $heure_fin) = explode('_', $_POST['coach_id']);

    // Vérifier la dispo
    $stmtCheck = $conn->prepare("
      SELECT * 
      FROM coach 
      WHERE id_coach = ? AND jour = ? AND heure_debut = ? AND heure_fin = ?
    ");
    $stmtCheck->bind_param("isss", $id_coach, $jour, $heure_debut, $heure_fin);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows === 1) {
        // Insérer le RDV
        $stmtInsert = $conn->prepare("
          INSERT INTO rdv (index_client, index_coach, jour_rdv, heure_debut, heure_fin)
          VALUES (?, ?, ?, ?, ?)
        ");
        $stmtInsert->bind_param("iisss", $client_id, $id_coach, $jour, $heure_debut, $heure_fin);
        $stmtInsert->execute();
        $stmtInsert->close();

        // Supprimer le créneau dispo
        $stmtDelete = $conn->prepare("
          DELETE FROM coach 
          WHERE id_coach = ? AND jour = ? AND heure_debut = ? AND heure_fin = ?
        ");
        $stmtDelete->bind_param("isss", $id_coach, $jour, $heure_debut, $heure_fin);
        $stmtDelete->execute();
        $stmtDelete->close();

        $message = "✅ Votre rendez-vous a bien été réservé.";
    } else {
        $message = "⚠️ Ce créneau n'est plus disponible.";
    }
    $stmtCheck->close();
}

// Dispos encore valides
$sql = "
  SELECT id_coach, nom, spe, jour, heure_debut, heure_fin 
  FROM coach 
  WHERE heure_debut <> '00:00:00' 
    AND heure_fin   <> '00:00:00'
  ORDER BY nom, jour, heure_debut
";
$result = $conn->query($sql);

// Récupérer les RDV du client (avec id_rdv)
$stmtRdv = $conn->prepare("
  SELECT
    r.id_rdv,
    r.jour_rdv,
    r.heure_debut,
    r.heure_fin,
    c.nom   AS nom_coach,
    c.spe
  FROM rdv r
  JOIN coach c ON r.index_coach = c.id_coach
  WHERE r.index_client = ?
  ORDER BY r.jour_rdv, r.heure_debut
");
$stmtRdv->bind_param("i", $client_id);
$stmtRdv->execute();
$rdvResult = $stmtRdv->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Prendre un rendez-vous - Sportify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Sportify</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="index_projet.php">Accueil</a></li>
        <li class="nav-item"><a class="nav-link" href="parcourir.php">Tout parcourir</a></li>
        <li class="nav-item"><a class="nav-link" href="recherche.php">Recherche</a></li>
        <li class="nav-item"><a class="nav-link active" href="rendezvous.php">Rendez-vous</a></li>
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
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="container mt-5">
  <h1 class="mb-4">Prendre un rendez-vous</h1>

  <?php if ($message): ?>
    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <form action="rendezvous.php" method="post" class="mb-5">
    <div class="mb-3">
      <label for="coach_id" class="form-label">Sélectionnez un créneau disponible :</label>
      <select class="form-select" id="coach_id" name="coach_id" required>
        <option value="">-- Choisissez un créneau --</option>
        <?php while ($row = $result->fetch_assoc()): ?>
            <option value="<?= "{$row['id_coach']}_{$row['jour']}_{$row['heure_debut']}_{$row['heure_fin']}" ?>">
              Coach <?= htmlspecialchars($row['nom']) ?> (<?= htmlspecialchars($row['spe']) ?>)
              — <?= htmlspecialchars($row['jour']) ?> de <?= htmlspecialchars($row['heure_debut']) ?> à <?= htmlspecialchars($row['heure_fin']) ?>
            </option>
        <?php endwhile; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Réserver</button>
  </form>

  <h2>Vos rendez-vous</h2>
  <?php if ($rdvResult->num_rows > 0): ?>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Coach</th>
          <th>Spécialité</th>
          <th>Jour</th>
          <th>Heure de début</th>
          <th>Heure de fin</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($rdv = $rdvResult->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($rdv['nom_coach']) ?></td>
            <td><?= htmlspecialchars($rdv['spe']) ?></td>
            <td><?= htmlspecialchars($rdv['jour_rdv']) ?></td>
            <td><?= htmlspecialchars($rdv['heure_debut']) ?></td>
            <td><?= htmlspecialchars($rdv['heure_fin']) ?></td>
            <td>
              <form method="post" action="rendezvous.php" onsubmit="return confirm('Confirmez-vous l\'annulation de ce rendez-vous ?');">
                <input type="hidden" name="cancel_rdv" value="<?= $rdv['id_rdv'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger">Annuler</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>Vous n'avez pas encore de rendez-vous.</p>
  <?php endif; ?>
</div>

<footer class="text-center py-4 mt-5 bg-light">
  <p class="mb-0">&copy; 2025 Sportify. Tous droits réservés.</p>
</footer>
</body>
</html>

<?php
$stmtRdv->close();
$conn->close();
?>
