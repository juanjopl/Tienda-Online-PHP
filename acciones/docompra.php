<?php
require_once('../p2/p2_lib.php');
    $carrito = json_decode($_COOKIE['carrito']);
    foreach ($carrito as $producto) {
        $idProducto = $producto->id;
        $con = get_connection();
        $sql = "UPDATE productos SET estadoProducto='negociacion' WHERE idProducto=:idProducto";
        $statement = $con->prepare($sql);
        $statement->bindParam(':idProducto',$idProducto, PDO::PARAM_INT);
        $resultado = $statement->execute();
        if($resultado) {
            header("Location:..\\ofertas.php");
        }
    }
?>