<?php
require_once("../p2/p2_lib.php");
require_once("../entity/usuarios.php");
session_start();
$objeto = $_SESSION['objeto'];
$formatos = array("image/jpeg", "image/png");
var_dump($_FILES['images']);
if (isset($_FILES["images"]) && !empty($_FILES["images"]["name"][0])) {
    $con = get_connection();
    
    // Datos del producto (sin las imágenes)
    $datos = array(
        'titulo' => $_POST['titulo'],
        'descripcion' => $_POST['descripcion'],
        'precio' => $_POST['precio'],
        'estado' => $_POST['estado'],
        'fechaCreacion' => date("Y-m-d H:i:s"),
        'idVendedor' => $objeto->idUsuario,
        'idComprador' => null,
        'categoria' => $_POST['categoria'],
        'subcategoria' => $_POST['subcategoria'],
        'estadoProducto' => 'activo'
    );

    
    $sql = "INSERT INTO productos (titulo, descripcion, precio, estado, fechaCreacion, idVendedor, idComprador, idCategoria, idSubcategoria, estadoProducto) 
            VALUES (:titulo, :descripcion, :precio, :estado, :fechaCreacion, :idVendedor, :idComprador, :categoria, :subcategoria, :estadoProducto)";
    $statement = $con->prepare($sql);
    $statement->bindParam(":titulo", $datos['titulo']);
    $statement->bindParam(":descripcion", $datos['descripcion']);
    $statement->bindParam(":precio", $datos['precio']);
    $statement->bindParam(":estado", $datos['estado']);
    $statement->bindParam(":fechaCreacion", $datos['fechaCreacion']);
    $statement->bindParam(":idVendedor", $datos['idVendedor']);
    $statement->bindParam(":idComprador", $datos['idComprador']);
    $statement->bindParam(":categoria", $datos['categoria']);
    $statement->bindParam(":subcategoria", $datos['subcategoria']);
    $statement->bindParam(":estadoProducto", $datos['estadoProducto']);
    $result = $statement->execute();

    if ($result) {
        $idProducto = $con->lastInsertId();

        foreach ($_FILES["images"]["tmp_name"] as $key => $temp) {
            $info = @getimagesize($temp);
            if ($info !== false) {
                $tipo = $info['mime'];

                if ($_FILES["images"]["size"][$key] <= 1000000 && in_array($tipo, $formatos)) {
                    $imagen = file_get_contents($temp);

                    $sql = "INSERT INTO fotosproductos (imagen, idProducto) VALUES (:imagen, :idProducto)";
                    $statement = $con->prepare($sql);
                    $statement->bindParam(":imagen", $imagen);
                    $statement->bindParam(":idProducto", $idProducto);
                    $result = $statement->execute();

                    if (!$result) {
                        echo "Error al insertar imagen en la base de datos.";
                    }
                } else {
                    echo "Error: Tamaño o formato incorrecto de la imagen.";
                }
            } else {
                echo "Error: La imagen no es válida.";
            }
        }
        header("Location:../misproductos.php");
    } else {
        echo "Error al insertar producto en la base de datos.";
    }
    $con = null;
} else {
    header("Location:../subirproducto.php?err=NOFILE");
}
?>