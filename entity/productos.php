<?php
require_once(__DIR__ . '/../p2/p2_lib.php');
    class Producto {
        public $idProducto;
        public $titulo;
        public $descripcion;
        public $estado;
        public $precio;
        public $fechaCreacion;
        public $idVendedor;
        public $idComprador;
        public $idCategoria;
        public $idSubcategoria;
        public $imagenes;
        public $estadoProducto;
        public $oferta;

        public static function parse ($datos) {
            $obj = new Producto();
            $obj->idProducto = $datos['idProducto'];
            $obj->titulo = $datos['titulo'];
            $obj->descripcion = $datos['descripcion'];
            $obj->estado = $datos['estado'];
            $obj->precio = $datos['precio'];
            $obj->fechaCreacion = $datos['fechaCreacion'];
            $obj->idVendedor = $datos['idVendedor'];
            $obj->idComprador = $datos['idComprador'];
            $obj->idCategoria = $datos['idCategoria'];
            $obj->idSubcategoria = $datos['idSubcategoria'];
            $obj->estadoProducto = $datos['estadoProducto'];
            $obj->oferta = $datos['oferta'];

            $con = get_connection();
            $sql = "SELECT imagen FROM fotosproductos WHERE idProducto=:idProducto;";
            $statement = $con->prepare($sql);
            $statement->bindParam(':idProducto',$obj->idProducto);
            $statement->execute();
            
            $obj->imagenes = $statement->fetchAll(PDO::FETCH_COLUMN);

            return $obj;
        }
        public static function getPaginacion ($pagina,$registros, $idVendedor=null) {
            $productos = [];
            $con = get_connection();
            try {
                $offset = ($pagina - 1) * $registros;
                if ($idVendedor == null) {
                $sql = "SELECT * FROM productos LIMIT :offset, :registros";
                }else {
                $sql = "SELECT * FROM productos WHERE idVendedor <> :idVendedor LIMIT :offset, :registros";
                }
                $statement = $con->prepare($sql);
                if($idVendedor !== null) {
                    $statement->bindParam(':idVendedor', $idVendedor, PDO::PARAM_INT);
                }
                $statement->bindParam(':offset', $offset, PDO::PARAM_INT);
                $statement->bindParam(':registros', $registros, PDO::PARAM_INT);
                $statement->execute();
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $productos[] = Producto::parse ($row);
                }
                return $productos;
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            } finally {
                cerrarConexion($con);
            }
        }
        public static function contarProductos () {
            $con = get_connection();
            $sql = "SELECT COUNT(`idProducto`) AS total FROM productos";
            $statement = $con->prepare($sql);
            $statement->execute();
            $resultado = $statement->fetch(PDO::FETCH_ASSOC);
            if($resultado) {
                return $resultado['total'];
            }
        }
        public static function recogerProductos() {
            $con = get_connection();
            $sql = "SELECT * FROM productos";
            $statement = $con->prepare($sql);
            $statement->execute();
            while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $producto = new Producto();
                $productos[] = Producto::parse($row);
            }
            if(empty($productos)) {
                return null;
            }else {
                return $productos;
            }
        }
        public static function productosFiltrados($categoria,$subcategoria) {
            $productos = [];
            $con = get_connection();

            $sql = "SELECT * FROM productos WHERE idCategoria=:categoria AND idSubcategoria=:subcategoria";
            $statement = $con->prepare($sql);
            $statement->bindParam(':categoria', $categoria, PDO::PARAM_INT);
            $statement->bindParam(':subcategoria', $subcategoria, PDO::PARAM_INT);
            $statement->execute();
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $producto = new Producto();
                $productos[] = Producto::parse ($row);
            }
            if(empty($productos)) {
                return null;
            }else {
                return $productos;
            }
        }
    }
?>