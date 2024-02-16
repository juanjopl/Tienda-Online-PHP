<?php
require_once('../p2/p2_lib.php');
    $respuesta = $_POST['respuesta'];
    $idProducto = $_POST['idProducto'];
    $con = get_connection();
    if($respuesta == 'aceptada') {
        $sql = "UPDATE productos SET estadoProducto = 'comprado' WHERE idProducto = :idProducto;";
    }/* else {
        $sql = "UPDATE productos SET estadoProducto = 'comprado', oferta = NULL WHERE idProducto = :idProducto;";
    } */
    $statement = $con->prepare($sql);
    $statement->bindParam(":idProducto", $idProducto, PDO::PARAM_INT);
    $resultado = $statement->execute();
    if($resultado) {
        header('Location:..\\index.php');
    }
?>