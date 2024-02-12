<?php
    if(isset($_POST['vaciar'])) {
        if(isset($_COOKIE['carrito'])) {
            setcookie('carrito','',time()-60,'/');
            header("Location:..\\carrito.php");
        }else {
            header("Location:..\\carrito.php");
        }
    }
?>