<?php
if (!isset($_GET['coach_id'])) {
    echo "Aucun coach sélectionné.";
    exit;
}

$coachId = $_GET['coach_id'];
$xmlFile = 'cv.xml';

if (!file_exists($xmlFile)) {
    echo "Fichier XML introuvable.";
    exit;
}

$xml = simplexml_load_file($xmlFile);
$cvTrouve = false;

foreach ($xml->coach as $coach) {
    if ((string)$coach->id === $coachId) {
        $nom = $coach->nom;
        $cv = $coach->cv;
        $cvTrouve = true;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CV du Coach</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <?php if ($cvTrouve): ?>
        <h1 class="text-center">CV de <?= htmlspecialchars($nom) ?></h1>
        <div class="card mt-4">
            <div class="card-body">
                <p><?= nl2br(htmlspecialchars($cv)) ?></p>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger text-center">CV introuvable pour ce coach.</div>
    <?php endif; ?>
    <div class="text-center mt-3">
        <a href="javascript:history.back()" class="btn btn-secondary">Retour</a>
    </div>
</div>

</body>
</html>
