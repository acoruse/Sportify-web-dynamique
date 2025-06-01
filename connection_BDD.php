<?php
$host = "localhost";
$user = "root"; //   identifiant
$pass = "";     //  mot de passe
$dbname = "BDD_projet";
// on se connecte a la BDD
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}
?>