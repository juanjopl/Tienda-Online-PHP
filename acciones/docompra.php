<?php
require_once('../p2/p2_lib.php');
include_once('../entity/usuarios.php');
session_start();
    $carrito = json_decode($_COOKIE['carrito']);
    $idComprador = $_SESSION['objeto']->idUsuario;
    foreach ($carrito as $producto) {
        $idProducto = $producto->id;
        $oferta = $producto->oferta;
        $con = get_connection();
        $sql = "UPDATE productos SET estadoProducto='reservado', oferta=:oferta, idComprador=:idComprador WHERE idProducto=:idProducto";
        $statement = $con->prepare($sql);
        $statement->bindParam(':idProducto',$idProducto, PDO::PARAM_INT);
        $statement->bindParam(':oferta',$oferta, PDO::PARAM_INT);
        $statement->bindParam(':idComprador',$idComprador, PDO::PARAM_INT);
        $resultado = $statement->execute();
        if($resultado) {
            header("Location:..\\ofertas.php");
        }
    }
?>