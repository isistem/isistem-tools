<?php
if ($_POST["key"] != "") {
	$key = $_POST["key"];
	$keyAccess = "/root/token.txt";
	$accessToken = trim(file_get_contents($keyAccess));

	if ($key == $accessToken) {
		include_once("inc/conexao.php");
		$keyFile = "keyfile.itk";
		file_put_contents($keyFile, $accessToken);
		$datetime = date('Y-m-d H:i:s');
		$insertKey = "INSERT INTO token (code, created) VALUES ('$key', '$datetime')";
		$mysqli->query($insertKey);
		$mysqli->close();

		header('Location: index.php');
		exit;
	}
	header('Location: key.php');
	exit;

} else {
	header('Location: key.php');
	exit;
}
