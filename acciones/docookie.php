<?php
    if(isset($_POST['añadirCarrito'])) {
        $idProducto = $_POST['añadirCarrito'];
        if(isset($_COOKIE['carrito'])) {
            $carrito = json_decode($_COOKIE['carrito'], true);
        }else {
            $carrito = [];
        }
        $i = array_search($idProducto, array_column($carrito,'id'));
        if($i !== false) {
            $carrito[$i]['cantidad'] += 1;
        }else {
            $nuevoProducto = [
                'id' => $idProducto
            ];
            $carrito[] = $nuevoProducto;
        }
        setcookie('carrito',json_encode($carrito),time()+3600,'/');
        header("Location:..\\carrito.php");
        exit();
    }
?>