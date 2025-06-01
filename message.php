<?php
include('connection_BDD.php');
session_start();


// Vérification si connecté
if (!isset($_SESSION['id']) ) {
    header("Location: compte.php");
    exit;
}

$client_id = $_SESSION['id'];
$message = "";

// Vérifier que l'ID du coach est passé
if (!isset($_GET['coach_id'])) {
    die("Coach introuvable.");
}
$coach_id = intval($_GET['coach_id']);

// Traitement de l'envoi du message
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['envoyer_message'])) {
    $texte = trim($_POST['text']);

    if (!empty($texte)) {
        $stmt = $conn->prepare("INSERT INTO messages (expediteur_id, destinataire_id, text, lu) VALUES (?, ?, ?, 0)");
        $stmt->bind_param("iis", $client_id, $coach_id, $texte);
        if ($stmt->execute()) {
            $message = "✅ Message envoyé.";
        } else {
            $message = "❌ Erreur lors de l'envoi.";
        }
        $stmt->close();
    } else {
        $message = "❌ Le message ne peut pas être vide.";
    }
}

// Récupérer le nom du coach pour l'affichage
$stmt = $conn->prepare("SELECT nom FROM coach WHERE id_coach = ?");
$stmt->bind_param("i", $coach_id);
$stmt->execute();
$result = $stmt->get_result();
$coach = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contacter Coach - Sportify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Contacter le coach <?= htmlspecialchars($coach['nom']) ?></h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" class="bg-white p-4 shadow rounded">
        <div class="mb-3">
            <label for="text" class="form-label">Votre message :</label>
            <textarea name="text" id="text" class="form-control" required></textarea>
        </div>
        <button type="submit" name="envoyer_message" class="btn btn-primary">Envoyer</button>
        <a href="activite_sportive.php" class="btn btn-secondary ms-2">Retour</a>
    </form>
</div>
</body>
</html>
