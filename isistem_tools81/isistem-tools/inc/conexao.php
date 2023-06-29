<?php
$host = "localhost";
$user = "isistemtools_admin";
$pass = "OpEe5Sh3GSI523d";
$bd = "isistemtools";

$mysqli = mysqli_connect($host, $user, $pass, $bd);
if (!$mysqli) {
    die("<center>Erro na conexão com o banco de dados!</center>");
}