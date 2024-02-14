<?php
require_once("..\\p2\\p2_lib.php");
session_start();
$user = $_SESSION['user'];
session_destroy();
if(isset($_COOKIE['carrito'])) {
    setcookie('carrito','',time()-60,'/');
}
header('Location:..\\index.php');
?>