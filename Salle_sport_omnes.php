<?php
include('connection_BDD.php');
session_start();

// on verifie si l utilisateur est co
if (!isset($_SESSION['id'])) {
    header('Location: compte.php');
    exit();
}
// on verifie si l utilisateur est un admin (type = 3 dans la session)
$isAdmin = (isset($_SESSION['type']) && $_SESSION['type'] == 3);

// on initialise un message vide 
$message = '';

// Si l utilisateur est admin et  que la requête est un POST et qu'une action sur une salle est demandee
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_salle'])) {
    
    // on recupere l action a effectuer (ajouter modifier ou suppr)
    $action = $_POST['action_salle'];
    
    // on recupere et nettoie les champs saisis
    $nom = trim($_POST['nom']);
    $adresse = trim($_POST['adresse']);
    $img = trim($_POST['img']);

    // Si on veut ajouter une salle 
    if ($action === 'add') {
        // on prepare une requete SQL pour inserer une nouvelle salle
        $stmt = $conn->prepare("INSERT INTO salle (nom, adresse_salle, img) VALUES (?, ?, ?)");
        // on lie les parametres (nom, adresse, image) à la requete
        $stmt->bind_param("sss", $nom, $adresse, $img);
        // on execute la requete et on affiche un message selon le resultat
        $message = $stmt->execute() ? "✅ Salle ajoutée." : "❌ Erreur ajout salle.";
        // on ferme la requete préparee
        $stmt->close();

    // Si on veut modifier une salle 
    } elseif ($action === 'edit' && isset($_POST['id_salle'])) {
        // on recupere l id de la salle a modifier
        $id_salle = intval($_POST['id_salle']);
        // mm logique qu avant sauf que l on update avec l id
        $stmt = $conn->prepare("UPDATE salle SET nom=?, adresse_salle=?, img=? WHERE id_salle=?");
        
        $stmt->bind_param("sssi", $nom, $adresse, $img, $id_salle);
        
        $message = $stmt->execute() ? "✅ Salle modifiée." : "❌ Erreur modification.";
        
        $stmt->close();

    // Si on veut suppr une salle 
    } elseif ($action === 'delete' && isset($_POST['id_salle'])) {
        // on recupere l id de la salle a suppr
        $id_salle = intval($_POST['id_salle']);
        // mm logique sauf que l on delete avec l id 
        $stmt = $conn->prepare("DELETE FROM salle WHERE id_salle=?");
        
        $stmt->bind_param("i", $id_salle);
        
        $message = $stmt->execute() ? "✅ Salle supprimée." : "❌ Erreur suppression.";
        
        $stmt->close();
    }
}


// on Recupere toutes les salles
$salles = [];
$res = $conn->query("SELECT * FROM salle");
while ($row = $res->fetch_assoc()) {
    $salles[] = $row;
}
// pour l annulation d un RDV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_rdv'])) {
    $id_rdv = intval($_POST['cancel_rdv']);
    $stmtCancel = $conn->prepare(
        "DELETE FROM rdv WHERE id_rdv = ? AND index_client = ?"// on supprime selon l id
    );
    $stmtCancel->bind_param("ii", $id_rdv, $client_id);
    if ($stmtCancel->execute()) {//si la requete a bien ete execute
        $message = "✅ Votre rendez-vous a bien été annulé.";// mess positif
    } else {
        $message = "❌ Impossible d'annuler le rendez-vous.";// mess d erreur
    }
    $stmtCancel->close();
}

// pour la reservation d un nouveau creneau
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['coach_id']) && !isset($_POST['cancel_rdv'])) {
    list($id_coach, $jour, $heure_debut, $heure_fin) = explode('_', $_POST['coach_id']);

    // on check  la dispo
    $stmtCheck = $conn->prepare("
      SELECT * 
      FROM coach 
      WHERE id_coach = ? AND jour = ? AND heure_debut = ? AND heure_fin = ?
    ");// en prenant les infos dans la table coach
    $stmtCheck->bind_param("isss", $id_coach, $jour, $heure_debut, $heure_fin);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows === 1) {
        // on insere le RDV
        $stmtInsert = $conn->prepare("
          INSERT INTO rdv (index_client, index_coach, jour_rdv, heure_debut, heure_fin)
          VALUES (?, ?, ?, ?, ?)
        ");
        $stmtInsert->bind_param("iisss", $client_id, $id_coach, $jour, $heure_debut, $heure_fin);
        $stmtInsert->execute();
        $stmtInsert->close();

        // enfin on suppr le creneau dispo
        $stmtDelete = $conn->prepare("
          DELETE FROM coach 
          WHERE id_coach = ? AND jour = ? AND heure_debut = ? AND heure_fin = ?
        ");
        $stmtDelete->bind_param("isss", $id_coach, $jour, $heure_debut, $heure_fin);
        $stmtDelete->execute();
        $stmtDelete->close();

        $message = "✅ Votre rendez-vous a bien été réservé.";//message de confirmation
    } else {
        $message = "⚠️ Ce créneau n'est plus disponible.";// si probleme
    }
    $stmtCheck->close();
}

// pour les dispos encore valides , on n affiche pas les crenneaux 00:00:00 a 00:00:00
$sql = "
  SELECT id_coach, nom, spe, jour, heure_debut, heure_fin 
  FROM coach 
  WHERE heure_debut <> '00:00:00' 
    AND heure_fin   <> '00:00:00'
    AND LOWER(spe) = 'musculation'
  ORDER BY nom, jour, heure_debut
";
$result = $conn->query($sql);

// on recupere les RDV du client (gracce a  id_rdv)
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
// on passe au html
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Prendre un rendez-vous - Sportify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<!-- barre de navigation-->
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
  <div class="row">
    <!-- Colonne gauche : Cartes des salles -->
    <div class="col-md-8">
      <h2 class="mb-4">Nos Salles</h2>
      <!-- noutons  Nos services -->
<div class="mb-4">
  <button class="btn btn-info" onclick="document.getElementById('services-section').classList.toggle('d-none')">
    Nos services
  </button>
  <div id="services-section" class="mt-3 d-none">
    <div class="list-group">
      <button class="list-group-item list-group-item-action" onclick="showService('personnel')">👥 Personnels de la salle de sport</button>
      <button class="list-group-item list-group-item-action" onclick="showService('horaire')">🕒 Horaire de la gym</button>
      <button class="list-group-item list-group-item-action" onclick="showService('regles')">⚙️ Règles sur l’utilisation des machines</button>
      <button class="list-group-item list-group-item-action" onclick="showService('clients')">🆕 Nouveaux clients</button>
      <button class="list-group-item list-group-item-action" onclick="showService('nutrition')">🥗 Alimentation et nutrition</button>
    </div>
    <div id="service-content" class="mt-3 border p-3 bg-light rounded"></div>
  </div>
</div>
<!-- si l user est un admin-->
<?php if($isAdmin): ?>
    <h2>Administration des salles</h2><!-- il a acces a ce panneau de de configuration-->
    <?php if($message): ?><div class="alert alert-info"><?=htmlspecialchars($message)?></div><?php endif; ?>
    <form class="admin-form" method="post">
      <input type="hidden" name="action_salle" value="add">
      <div class="row g-2">
        <div class="col-md-4"><input name="nom" class="form-control" placeholder="Nom de la salle" required></div>
        <div class="col-md-4"><input name="adresse" class="form-control" placeholder="Adresse" required></div>
        <div class="col-md-3"><input name="img" class="form-control" placeholder="URL image"></div>
        <div class="col-md-1"><button type="submit" class="btn btn-success">Ajouter</button></div><!-- affichage du panneau de config-->
      </div>
    </form>
    <table class="table">
      <thead><tr><th>Nom</th><th>Adresse</th><th>Image</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($salles as $s): ?>
        <tr>
          <form method="post">
            <td><input name="nom" class="form-control" value="<?=htmlspecialchars($s['nom'])?>"></td>
            <td><input name="adresse" class="form-control" value="<?=htmlspecialchars($s['addresse_salle'])?>"></td>
            <td><input name="img" class="form-control" value="<?=htmlspecialchars($s['img'])?>"></td>
            <td>
              <!-- boutoons -->
              <input type="hidden" name="id_salle" value="<?=$s['id_salle']?>">
              <button name="action_salle" value="edit" class="btn btn-sm btn-primary">Modifier</button>
              <button name="action_salle" value="delete" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</button>
            </td>
          </form>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <hr>
  <?php endif; ?>
      <div class="row">
        <?php foreach($salles as $salle): ?>
          <div class="col-md-6 mb-4">
            <div class="card">
              <?php if($salle['img']): ?>
                <img src="<?= htmlspecialchars($salle['img']) ?>" class="card-img-top" alt="<?= htmlspecialchars($salle['nom']) ?>">
              <?php endif; ?>
              <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($salle['nom']) ?></h5>
                <p class="card-text"><?= htmlspecialchars($salle['addresse_salle']) ?></p>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Colonne droite : rdv -->
    <!-- on reprend la logique de rendezvous.php check si besoin-->
    <div class="col-md-4">
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
  </div>

  

<footer class="text-center py-4 mt-5 bg-light">
  <p class="mb-0">&copy; 2025 Sportify. Tous droits réservés.</p>
</footer>
<script>
  function showService(service) {
    const content = {
      personnel: "Nos coachs expérimentés vous accompagnent dans tous vos entraînements pour atteindre vos objectifs.",
      horaire: "Notre salle est ouverte de 6h00 à 23h00 du lundi au dimanche, jours fériés inclus.",
      regles: "Merci de nettoyer les machines après usage, porter une tenue adéquate, et respecter le matériel et les autres utilisateurs.",
      clients: "Bienvenue aux nouveaux ! Une séance d'introduction est offerte. Nos coachs sont là pour vous guider.",
      nutrition: "Nous offrons des conseils personnalisés en nutrition, avec des plans alimentaires adaptés à vos objectifs."
    };
    document.getElementById("service-content").innerText = content[service] || "Sélectionnez un service pour voir les détails.";
  }
</script>

</body>
</html>

<?php
$stmtRdv->close();
$conn->close();
?>
