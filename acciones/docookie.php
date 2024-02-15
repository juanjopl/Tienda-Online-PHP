<?php
    if(isset($_POST['añadirCarrito'])) {
        $idProducto = $_POST['añadirCarrito'];
        $oferta = $_POST['valorOriginal'];
        if(isset($_POST['oferta']) && $_POST['oferta'] !== "" && $_POST['oferta'] > 0) {
            $oferta = $_POST['oferta'];
        }
        if(isset($_COOKIE['carrito'])) {
            $carrito = json_decode($_COOKIE['carrito'], true);
        } else {
            $carrito = [];
        }
        $i = array_search($idProducto, array_column($carrito, 'id'));
        if($i !== false) {
            $carrito[$i]['oferta'] = $oferta;
        } else {
            $nuevoProducto = [
                'id' => $idProducto,
                'oferta' => $oferta
            ];
            $carrito[] = $nuevoProducto;
        }
        setcookie('carrito', json_encode($carrito), time() + 3600, '/');
        header("Location:..\\carrito.php");
        exit();
    }
?>