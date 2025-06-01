<?php
session_start();
include('connection_BDD.php');

// Vérifier que l'utilisateur est bien un administrateur
if (!isset($_SESSION['type']) || $_SESSION['type'] != 3) {
    header("Location: compte.php");
    exit;
}

$message = "";
// formulaire pour supprimer un utilisateur 
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['supprimer_utilisateur'])) {
    $id_supprimer = intval($_POST['id_supprimer']);

    // Supprimer dans les tables secondaires d'abord pour éviter erreurs de clé étrangère
    $conn->query("DELETE FROM coach WHERE id_coach = $id_supprimer");
    $conn->query("DELETE FROM client WHERE id = $id_supprimer");
    $conn->query("DELETE FROM rdv WHERE index_client = $id_supprimer OR index_coach = $id_supprimer");
    $conn->query("DELETE FROM messages WHERE expediteur_id = $id_supprimer OR destinataire_id = $id_supprimer");

    // Supprimer l'utilisateur principal
    $stmt = $conn->prepare("DELETE FROM utilisateurs WHERE id = ?");
    $stmt->bind_param("i", $id_supprimer);

    if ($stmt->execute()) {
        $message = "✅ Utilisateur supprimé avec succès.";
    } else {
        $message = "❌ Une erreur est survenue lors de la suppression.";
    }

    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['ajout_utilisateur'])) {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];
    $activite_pref = trim($_POST['activite_pref']);
    $type = intval($_POST['type']); // 1 = client, 2 = coach
    $image = trim($_POST['image']); // chemin ou URL de l'image
    $bureau = trim($_POST['bureau']);

    // Vérification si l'email est déjà utilisé
    $check = $conn->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $message = "⚠️ L'email est déjà utilisé par un autre utilisateur.";
    } else {
        // Insertion dans la table utilisateurs
        $id = intval($_POST['id']); // récupère l'id soumis

        $stmt = $conn->prepare("INSERT INTO utilisateurs (id, nom, email, mdp, activite_pref, type, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssis", $id, $nom, $email, $mot_de_passe, $activite_pref, $type, $image);

    
        if ($stmt->execute()) {
            $message = "✅ Utilisateur ajouté avec succès.";

            // Si c'est un coach, on ajoute son nom dans la table coach
            if ($type === 2) {
              $stmt2 = $conn->prepare("INSERT INTO coach (id_coach, nom,spe,image,bureau) VALUES (?, ?,?,?,?)");
              $stmt2->bind_param("issss", $id, $nom,$activite_pref,$image,$bureau);
              $stmt2->execute();
              $stmt2->close();
            } elseif ($type == 1) {
              $stmt2 = $conn->prepare("INSERT INTO client (id, nom) VALUES (?, ?)");
              $stmt2->bind_param("is", $id, $nom);
              $stmt2->execute();
              $stmt2->close();
              }
        } else {
            $message = "❌ Une erreur est survenue lors de l'ajout.";
        }

        $stmt->close();
    }

    $check->close();
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // AJOUTER UN NOUVELLE DISPONIBILITÉ
    if (isset($_POST['ajout_dispo'])) {
        $coach_nom = trim($_POST['coach_nom_add']);
        $jour = $_POST['jour_add'];
        $heure_debut = $_POST['heure_debut_add'];
        $heure_fin = $_POST['heure_fin_add'];

        // Vérifier si le coach existe
        $stmtCheck = $conn->prepare("SELECT * FROM coach WHERE nom = ?");
        $stmtCheck->bind_param("s", $coach_nom);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();

        if ($resultCheck->num_rows === 0) {
            $message = "❌ Coach introuvable.";
        } else {
            // Ici, soit la table coach accepte plusieurs disponibilités, soit on refuse d'ajouter si existe déjà une ligne avec ce coach + jour
            // Pour cette version, on vérifie si dispo existe déjà pour ce coach et jour :
            $stmtExist = $conn->prepare("SELECT * FROM coach WHERE nom = ? AND jour = ?");
            $stmtExist->bind_param("ss", $coach_nom, $jour);
            $stmtExist->execute();
            $resultExist = $stmtExist->get_result();

            if ($resultExist->num_rows > 0) {
                $message = "⚠️ Une disponibilité existe déjà pour ce coach ce jour. Utilisez la mise à jour.";
            } else {
                // Ajouter la dispo
                $stmtInsert = $conn->prepare("INSERT INTO coach (nom, jour, heure_debut, heure_fin) VALUES (?, ?, ?, ?)");
                $stmtInsert->bind_param("ssss", $coach_nom, $jour, $heure_debut, $heure_fin);
                if ($stmtInsert->execute()) {
                    $message = "✅ Nouvelle disponibilité ajoutée pour $coach_nom.";
                } else {
                    $message = "❌ Erreur lors de l'ajout de la disponibilité.";
                }
                $stmtInsert->close();
            }
            $stmtExist->close();
        }
        $stmtCheck->close();
    }

    // MISE À JOUR D'UNE DISPONIBILITÉ EXISTANTE
    if (isset($_POST['maj_dispo'])) {
        $coach_nom = trim($_POST['coach_nom']);
        $jour = $_POST['jour'];
        $heure_debut = $_POST['heure_debut'];
        $heure_fin = $_POST['heure_fin'];

        // Mettre à jour la dispo en fonction du coach + jour
        $stmt = $conn->prepare("UPDATE coach SET heure_debut = ?, heure_fin = ? WHERE nom = ? AND jour = ?");
        $stmt->bind_param("ssss", $heure_debut, $heure_fin, $coach_nom, $jour);

        if ($stmt->execute()) {
            $message = "✅ Disponibilité mise à jour avec succès pour $coach_nom le $jour.";
        } else {
            $message = "❌ Erreur lors de la mise à jour.";
        }

        $stmt->close();
    }
}
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['maj_ids_spe'])) {
    // Récupérer tous les noms distincts de coachs
    $sql = "SELECT DISTINCT nom FROM coach WHERE nom IS NOT NULL AND nom <> ''";
    $result = $conn->query($sql);

    $maj_effectuee = false;
    $message = "";

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $nom = $row['nom'];

            // Récupère une ligne de référence complète
            $stmt_ref = $conn->prepare("SELECT id_coach, spe, image, bureau FROM coach WHERE nom = ? AND id_coach IS NOT NULL AND spe IS NOT NULL AND image IS NOT NULL AND bureau IS NOT NULL LIMIT 1");
            $stmt_ref->bind_param("s", $nom);
            $stmt_ref->execute();
            $result_ref = $stmt_ref->get_result();

            if ($result_ref->num_rows === 1) {
                $data = $result_ref->fetch_assoc();
                $id_coach = $data['id_coach'];
                $spe = $data['spe'];
                $image = $data['image'];
                $bureau = $data['bureau'];

                // Mise à jour des lignes incomplètes
                $stmt_update = $conn->prepare("
                    UPDATE coach 
                    SET id_coach = ?, spe = ?, image = ?, bureau = ?
                    WHERE nom = ? AND (
                        id_coach IS NULL OR id_coach = '' OR
                        spe IS NULL OR spe = '' OR
                        image IS NULL OR image = '' OR
                        bureau IS NULL OR bureau = ''
                    )
                ");
                $stmt_update->bind_param("sssss", $id_coach, $spe, $image, $bureau, $nom);
                $stmt_update->execute();

                if ($stmt_update->affected_rows > 0) {
                    $message .= "✅ Coach '$nom' mis à jour avec ID: $id_coach, SPE: $spe, Bureau: $bureau, Image: $image.<br>";
                    $maj_effectuee = true;
                }

                $stmt_update->close();
            }

            $stmt_ref->close();
        }

        if (!$maj_effectuee) {
            $message = "ℹ️ Aucun changement nécessaire. Toutes les données sont à jour.";
        }
    } else {
        $message = "❌ Aucun coach trouvé.";
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST'  && isset($_POST['ajouter_cv'])) {
    $id = htmlspecialchars($_POST['id']);
    $nom = htmlspecialchars($_POST['nom']);
    $cv = htmlspecialchars($_POST['cv']);

    // Charger le fichier XML
    $xml = simplexml_load_file('cv.xml');

    // Ajouter un nouvel élément <coach>
    $nouveau_coach = $xml->addChild('coach');
    $nouveau_coach->addChild('id', $id);
    $nouveau_coach->addChild('nom', $nom);
    $nouveau_coach->addChild('cv', $cv);

    // Sauvegarder le XML
    $xml->asXML('cv.xml');

    $message = "✅ CV ajouté avec succès.";
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Panneau Admin - Sportify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style_projet.css">
</head>
<body>

<!-- Barre de navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Sportify - Admin</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index_projet.php">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="parcourir.php">Tout parcourir</a></li>
                <li class="nav-item"><a class="nav-link" href="recherche.php">Recherche</a></li>
                <li class="nav-item"><a class="nav-link" href="rendezvous.php">Rendez-vous</a></li>
                <li class="nav-item"><a class="nav-link" href="compte.php">Votre Compte</a></li>
            </ul>
        </div>
        <div class="d-flex">
            <a href="deconnexion.php" class="btn btn-outline-light">Déconnexion</a>
        </div>
    </div>
</nav>
<div class="container mt-5">
    <h2>Panneau d'administration </h2>
    <h3>Ajouter un utilisateur</h3>

    <?php if (!empty($message)): ?>
        <div class="alert alert-info mt-3"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" action="admin.php" class="mt-4">
        <div class="mb-3">
            <label>Nom</label>
            <input type="text" name="nom" class="form-control" required>
        </div>
        <div class="mb-3">
             <label>ID personnalisé</label>
             <input type="number" name="id" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Mot de passe</label>
            <input type="text" name="mot_de_passe" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Activité préférée</label>
            <input type="text" name="activite_pref" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Type d'utilisateur</label>
            <select name="type" class="form-control" required>
                <option value="1">Client</option>
                <option value="2">Coach</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Chemin de l'image (ex: images/photo1.jpg)</label>
            <input type="text" name="image" class="form-control" >
        </div>
        <div class="mb-3">
    <label>Bureau (ex: B215)</label>
    <input type="text" name="bureau" class="form-control">
</div>

       <button type="submit" name="ajout_utilisateur" class="btn btn-primary">Ajouter</button>
    </form>
</div>
<hr class="my-5">
<div class="container mt-5">
    <h3>Supprimer un utilisateur</h3>
    <form method="POST" action="admin.php">
        <div class="mb-3">
            <label>Utilisateur à supprimer</label>
            <select name="id_supprimer" class="form-control" required>
                <option value="">-- Sélectionner --</option>
                <?php
                $res = $conn->query("SELECT id, nom, email FROM utilisateurs ORDER BY nom");
                while ($row = $res->fetch_assoc()) {
                    echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['nom']) . ' (' . htmlspecialchars($row['email']) . ')</option>';
                }
                ?>
            </select>
        </div>
        <button type="submit" name="supprimer_utilisateur" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')">Supprimer</button>
    </form>
</div>

<div class="container mt-5">
    <h3>Ajouter une nouvelle disponibilité pour un coach</h3>
    <form method="POST" action="admin.php">
        <div class="mb-3">
            <label>Nom du coach</label>
            <select name="coach_nom_add" class="form-control" required>
                <option value="">-- Sélectionner --</option>
                <?php
                $res = $conn->query("SELECT DISTINCT nom FROM coach ORDER BY nom");
                while ($row = $res->fetch_assoc()) {
                    echo '<option value="' . htmlspecialchars($row['nom']) . '">' . htmlspecialchars($row['nom']) . '</option>';
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Jour de disponibilité</label>
            <select name="jour_add" class="form-control" required>
                <option>Lundi</option>
                <option>Mardi</option>
                <option>Mercredi</option>
                <option>Jeudi</option>
                <option>Vendredi</option>
                <option>Samedi</option>
                <option>Dimanche</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Heure de début</label>
            <input type="time" name="heure_debut_add" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Heure de fin</label>
            <input type="time" name="heure_fin_add" class="form-control" required>
        </div>

        <button type="submit" name="ajout_dispo" class="btn btn-primary">Ajouter la disponibilité</button>
    </form>
</div>

<hr class="my-5">

<div class="container mt-5">
    <h3>Modifier la disponibilité d’un coach</h3>
    <form method="POST" action="admin.php">
        <div class="mb-3">
            <label>Nom du coach</label>
            <select name="coach_nom" class="form-control" required>
                <option value="">-- Sélectionner --</option>
                <?php
                $res = $conn->query("SELECT DISTINCT nom FROM coach ORDER BY nom");
                while ($row = $res->fetch_assoc()) {
                    echo '<option value="' . htmlspecialchars($row['nom']) . '">' . htmlspecialchars($row['nom']) . '</option>';
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Jour de disponibilité</label>
            <select name="jour" class="form-control" required>
                <option>Lundi</option>
                <option>Mardi</option>
                <option>Mercredi</option>
                <option>Jeudi</option>
                <option>Vendredi</option>
                <option>Samedi</option>
                <option>Dimanche</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Heure de début</label>
            <input type="time" name="heure_debut" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Heure de fin</label>
            <input type="time" name="heure_fin" class="form-control" required>
        </div>

        <button type="submit" name="maj_dispo" class="btn btn-success">Mettre à jour la disponibilité</button>
    </form>
</div>
<hr class="my-5">
<div class="container mt-5">
    <h3>Mise à jour automatique des ID et spécialités des coachs</h3>
    <form method="POST" action="admin.php">
        <button type="submit" name="maj_ids_spe" class="btn btn-warning">
            Mettre à jour les ID et spécialités
        </button>
    </form>
</div>

<?php if (!empty($message)): ?>
    <div class="container mt-3">
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    </div>
<?php endif; ?>
<section class="container mt-4">
    <h2>Ajouter un CV pour un coach</h2>

    <?php if (isset($message_cv)) echo "<p class='text-success fw-bold'>{$message_cv}</p>"; ?>

    <form method="post" action="admin.php" class="bg-light p-4 rounded shadow-sm">
        <div class="mb-3">
            <label for="id" class="form-label">ID du coach :</label>
            <input type="number" name="id" id="id" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="nom" class="form-label">Nom du coach :</label>
            <input type="text" name="nom" id="nom" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="cv" class="form-label">Contenu du CV :</label>
            <textarea name="cv" id="cv" rows="8" class="form-control" required></textarea>
        </div>

        <button type="submit" name="ajouter_cv" class="btn btn-primary">Ajouter CV</button>
    </form>
</section>

</body>
</html>
