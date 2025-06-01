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
