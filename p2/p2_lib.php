<?php
    //FUNCIONES DE CONEXION
    function get_connection() {
        $dsn = 'mysql:host=localhost;dbname=db_tienda_segunda_mano';
        $user = 'gestorSegundaMano';
        $pass = 'juanjose1@';
        $opciones = [];
        try {
            $con = new PDO($dsn,$user,$pass);
            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch (PDOException $e) {
            echo "Fallo la conexion: ".$e->getMessage();
        };
        return $con;
    }
    function cambiarBD($con,$bd) {
        try {
        $con->exec("USE $bd");
        } catch (PDOException $e) {
            echo "Error al cambiar la base de datos: " . $e->getMessage();
        }
    }
    function cerrarConexion($con) {
        $con = null;
    }
    //FUNCION AUTENTICAR
    function autenticarUsuario($user,$pass) {
            $con = get_connection();
            $sql = "SELECT * FROM usuarios WHERE username='$user' OR email='$user'";
            $statement = $con->prepare($sql);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            cerrarConexion($con);
            if ($result && password_verify($pass, $result['pass'])) {
                cerrarConexion($con);
                return true;
            }else {
                cerrarConexion($con);
                return false;
            }
    }
    //FUNCION PARA SABER SI UN USUARIO ESTA BLOQUEADO
    function isBlocked($user) {
        $con = get_connection();
        $sql = "SELECT estado FROM usuarios WHERE username='$user' OR email='$user'";
        $statement = $con->prepare($sql);
        $statement->execute();
        $resultado = $statement->fetch(PDO::FETCH_ASSOC);
        if($resultado['estado'] == 'Bloqueado') {
            return true;
        }else {
            return false;
        }
    }

    //FUNCION DE MUESTRA DE DATOS
    function mostrarDatos($id) {
        $con = get_connection();
        $sql = "SELECT * FROM usuarios WHERE idUsuario='$id';";
        $statement = $con->prepare($sql);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        cerrarConexion($con);
        return $result;
    }
    //VALIDACIONES REGISTRO
    function validacionesRegistro($name,$username,$password,$email,$fechaNac,$direccion) {

        if(!$name ||!$username ||!$password ||!$email || !$fechaNac ||!$direccion ) {
            header("Location:..\\registro.php?err=EMPTY_FIELDS");
            exit();
        }

        $con = get_connection();
        $sql = "SELECT * FROM usuarios WHERE username='$username';";
        $statement = $con->prepare($sql);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if($result) {
            cerrarConexion($con);
            header("Location:..\\registro.php?err=AUTH_USERNAME_EXIST");
            exit();
        }
        

        $con = get_connection();
        $sql = "SELECT * FROM usuarios WHERE email='$email';";
        $statement = $con->prepare($sql);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if($result) {
            cerrarConexion($con);
            header("Location:..\\registro.php?err=AUTH_EMAIL_EXIST");
            exit();
        }

        $patron = '/^[a-zA-Z0-9._-]+@gmail\.com$/';
        if(!preg_match($patron,$email)) {
            header("Location:..\\registro.php?err=EMAIL_FORMAT");
            exit();
        }

        $fechaActual = date( 'Y-m-d' );
        $fechaActualNum = strtotime($fechaActual);
        $fechanacimientoNum = strtotime( $fechaNac);
        $edad = $fechaActualNum - $fechanacimientoNum;
        $edad = $edad / (365*60*60*24);
        $edad = round($edad);
        if ($edad < 18) {
            header("Location:..\\registro.php?err=AUTH_AGE_INVALID");
            exit();
        }

        $patron = '/^.{5,}$/';
        if (!preg_match($patron, $password)) {
            header("Location:..\\registro.php?err=PASSWORD_INVALID");
            exit();
        }

        $patron = '/^[a-z0-9]{5,}+$/';
        if (!preg_match($patron, $username)) {
            header("Location:..\\registro.php?err=USERNAME_FORMAT");
            exit();
        }

        return true;
    }
    //VALIDACIONES MODIFICACION
    function validacionesModificar($name,$username,$pass,$email,$direccion,$oldname) {

        if(!$name ||!$username ||!$email ||!$direccion ) {
            header("Location:..\\informacion.php?err=EMPTY_FIELDS");
            exit();
        }

        if($oldname !== $username) {
            $con = get_connection();
            $sql = "SELECT * FROM usuarios WHERE username='$username';";
            $statement = $con->prepare($sql);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            if($result) {
                cerrarConexion($con);
                header("Location:..\\informacion.php?err=AUTH_USERNAME_EXIST");
                exit();
            }
        }


        $patron = '/^[a-zA-Z0-9._-]+@gmail\.com$/';
        if(!preg_match($patron,$email)) {
            header("Location:..\\informacion.php?err=EMAIL_FORMAT");
            exit();
        }

        if($pass!="") {
            $patron = '/^.{5,}$/';
            if (!preg_match($patron, $pass)) {
                header("Location:..\\informacion.php?err=PASSWORD_INVALID");
                exit();
            }
        }

        $patron = '/^[a-z0-9]{5,}+$/';
        if (!preg_match($patron, $username)) {
            header("Location:..\\informacion.php?err=USERNAME_FORMAT");
            exit();
        }

        return true;
    }
    //COMPROBACION DE TIPO DE USUARIO
    function comprobarAdmin($usuario) {
        try {
            $con = get_connection();
            $sql="SELECT perfil FROM usuarios WHERE username='$usuario'";
            $statement = $con ->prepare($sql);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            if($result && $result['perfil'] == 1) {
                cerrarConexion($con);
                return true;
            }else {
                cerrarConexion($con);
                return false;
            }
        }catch(PDOException $e) {
            echo "Error de comprobacion: " . $e->getMessage();
        }
    }
    //FUNCION PARA CREAR OBJETO USUARIO EN LOGIN
    function crearObjetoUsuario($user) {
        $con = get_connection();
        $sql = "SELECT * FROM usuarios WHERE username='$user' OR email='$user';";
        $statement = $con->prepare($sql);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if($result) {
         $datos = array(
            'idUsuario' => $result['idUsuario'],
            'username' => $result['username'],
            'email' => $result['email'],
            'pass' => $result['pass'],
            'nombre' => $result['nombre'],
            'apellido1' => $result['apellido1'],
            'apellido2' => $result['apellido2'],
            'direccion' => $result['direccion'],
            'fechaNac' => $result['fechaNac'],
            'fechaCreacion' => $result['fechaCreacion'],
            'fechaModificacion' => null,
            'estado' => $result['estado'],
            'perfil' => $result['perfil'],
            'avatar' => $result['avatar']
         );
         $usuario = Usuario::parse($datos);
         return $usuario;
        }else {
            return false;
        }
    }
    //FUNCION PARA RECOGER ID
    function recogerIdUsuario($user) {
        $con = get_connection();
        $sql = "SELECT idUsuario FROM usuarios WHERE username='$user';";
        $statement = $con ->prepare($sql);
        $statement->execute();
        $resultado = $statement->fetch(PDO::FETCH_ASSOC);
        if($resultado) {
            cerrarConexion($con);
            return $resultado['idUsuario'];
        }
    }
    //FUNCION PARA COMPROBAR CONTRASEÑA
    function comprobarPass($pass,$idUsuario) {
            $con = get_connection();
            $sql = "SELECT pass FROM usuarios WHERE idUsuario=$idUsuario;";
            $statement = $con ->prepare($sql);
            $statement->execute();
            $resultado = $statement->fetch(PDO::FETCH_ASSOC);
            if($resultado) {
                if(password_verify($pass,$resultado['pass'])) {
                    cerrarConexion($con);
                    return true;
                }else {
                    cerrarConexion($con);
                    return false;
                }
            }
    }
    //FUNCION PARA MOSTRAR PRODUCTOS
    function mostrarProductos($productos) {
        ?>
        <div class="container m-5">
            <div class="row row-cols-1 row-cols-md-3">
        <?php
        foreach ($productos as $producto) {
        ?>
                <div class="col mb-4">
                    <div class="card mx-auto text-bg-dark" style="max-width: 23rem;">
                        <div class="card-body bg-dark producto">
                        <?php
                        $imagenes = $producto->imagenes;
                        if(count($imagenes) > 1) {
                            ?>
                            <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                            <?php
                                for ($i=0; $i < count($imagenes); $i++) { 
                                    if($i == 0) {
                                        $activo = "active";
                                    } else {
                                        $activo = '';
                                    }
                                    ?>
                                    <div class="carousel-item <?php echo $activo ?>" data-bs-interval="5000">
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($imagenes[$i]) ?>" class="d-block w-100" style="height:15rem;object-fit: cover;">
                                    </div>
                                    <?php
                                }
                                    ?>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                </div>
                            </div>
                                <?php   
                                }else {
                                    ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($imagenes[0]) ?>" class="card-img img-fluid" style="width: 100%; height:15rem;object-fit: cover;">
                                    <?php
                                }
                                ?>
                            <h5 class="card-title bg-custom mt-2"><?php echo $producto->titulo ?></h5>
                            <h6 class="bg-custom mt-2"><?php echo $producto->precio ?>€</h6>
                            <form action="producto.php" method="GET">
                                <button type="submit" class="btn btn-success" name="idProducto" value="<?php echo $producto->idProducto ?>">Ver Producto</button>
                            </form>
                        </div>
                    </div>
                </div>
        <?php
        }
        ?>
            </div>
        </div>
        <?php
    }
    //FUNCION PARA MOSTRAR PRODUCTO DESEADO
    function recogerProductoDeseado($idProducto) {
        $con = get_connection();
        $sql = "SELECT * FROM productos WHERE idProducto=:idProducto";
        $statement = $con->prepare($sql);
        $statement->bindParam(":idProducto",$idProducto);
        $statement->execute();
        while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $producto = new Producto();
            $producto = Producto::parse($row);
        }
        return $producto;
    }
    function mostrarImagenesProducto($producto) {
        $imagenes = $producto->imagenes;
        if(count($imagenes) > 1) {
            ?>
            <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
            <?php
                for ($i=0; $i < count($imagenes); $i++) { 
                    if($i == 0) {
                        $activo = "active";
                    } else {
                        $activo = '';
                    }
                    ?>
                    <div class="carousel-item <?php echo $activo ?>" data-bs-interval="5000">
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($imagenes[$i]) ?>" class="d-block w-100" style="height:30rem;object-fit: cover;">
                    </div>
                    <?php
                }
                    ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
                <?php   
                }else {
                    ?>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($imagenes[0]) ?>" class="card-img img-fluid" style="width: 100%; height:30rem;object-fit: cover;">
                    <?php
                }
                                
    }
    
    //mostrar mis productos
    function mostrarMisProductos($productos) {
        ?>
        <div class="container m-5">
            <div class="row row-cols-1 row-cols-md-3">
        <?php
        foreach ($productos as $producto) {
        ?>
                <div class="col mb-4">
                    <div class="card mx-auto text-bg-dark" style="max-width: 23rem;">
                        <div class="card-body bg-dark producto">
                        <?php
                        $imagenes = $producto->imagenes;
                        if(count($imagenes) > 1) {
                            ?>
                            <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                            <?php
                                for ($i=0; $i < count($imagenes); $i++) { 
                                    if($i == 0) {
                                        $activo = "active";
                                    } else {
                                        $activo = '';
                                    }
                                    ?>
                                    <div class="carousel-item <?php echo $activo ?>" data-bs-interval="5000">
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($imagenes[$i]) ?>" class="d-block w-100" style="height:15rem;object-fit: cover;">
                                    </div>
                                    <?php
                                }
                                    ?>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                </div>
                            </div>
                                <?php   
                                }else {
                                    ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($imagenes[0]) ?>" class="card-img img-fluid" style="width: 100%; height:15rem;object-fit: cover;">
                                    <?php
                                }
                                ?>
                            <h5 class="card-title bg-custom mt-2"><?php echo $producto->titulo ?></h5>

                            <div class="d-flex" style="justify-content: space-between;">
                                <form action="producto.php" method="GET">
                                <button type="submit" class="btn btn-outline-light" name="idProducto" value="<?php echo $producto->idProducto ?>"><?php echo $producto->precio ?>€</button>
                                </form>

                                <form action="acciones/delproducto.php" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas borrar este producto?');">
                                <button type="submit" class="btn btn-outline-danger" name="idProducto" value="<?php echo $producto->idProducto ?>">Borrar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
        <?php
        }
        ?>
            </div>
        </div>
        <?php
    }

    //FUNCION VALIDACION PARA SUBIR PRODUCTOS
    function validarSubirProducto($titulo, $descripcion, $categoria, $subcategoria, $precio) {
        if(!$titulo || !$descripcion || !$precio) {
            header("Location:subirproducto.php?err=EMPTY_FIELDS");
            exit();
        }
        if($precio <= 0) {
            header("Location:subirproducto.php?err=INVALID_PRICE");
            exit();
        }
        

    }
    //FUNCTION DE MOSTRAR CARRITO
    function mostrarCarrito($productos) {
        ?>
        <div class="container m-5 d-flex justify-content-center" style="flex-direction:column;">
        <?php
        foreach ($productos as $producto) {
            $carrito = json_decode($_COOKIE['carrito']);
            foreach ($carrito as $ofertas) {
        ?>
        <div class="row row-cols-1 d-flex justify-content-center">
            <div class="card mb-3 p-0 text-bg-dark" style="width: 50%;">
                <div class="row g-0">
                    <div class="col">
                        <?php
                        $imagenes = $producto->imagenes;
                        if(count($imagenes) > 1) {
                        ?>
                        <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                        <?php
                            for ($i=0; $i < count($imagenes); $i++) { 
                                if($i == 0) {
                                    $activo = "active";
                                } else {
                                    $activo = '';
                                }
                                ?>
                                <div class="carousel-item <?php echo $activo ?>" data-bs-interval="5000">
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($imagenes[$i]) ?>" class="card-img img-fluid" style="width: 100%; height:22rem;object-fit: cover;">
                                </div>
                                <?php
                            }
                                ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                        </div>
                            <?php 
                            }else {
                                ?>
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($imagenes[0]) ?>" class="card-img img-fluid" style="width: 100%; height:22rem;object-fit: cover;">
                                <?php
                            }
                            ?>
                    </div>
                    <div class="col-md-8">
                    <div class="card-body bg-dark">
                        <h5 class="card-title"><a href="../producto.php?idProducto=<?php echo $producto->idProducto ?>"><?php echo $producto->titulo ?></a></h5>
                        <p class="card-text"><?php echo $producto->descripcion ?></p>
                        <p class="card-text"><?php echo $producto->precio ?>€</p>
                        <p class="card-text">Oferta: <?php echo $ofertas->oferta ?>€</p>
                        <form action="../acciones/deletecookie.php" method="POST">
                        <button type="submit" class="btn btn-danger" name="idProducto" value="<?php echo $producto->idProducto ?>">Borrar</button>
                        </form>
                    </div>
                    </div>
                </div>
                </div>
            </div>
        <?php
            }
        }
        ?>
        </div>
        </div>
        
        <?php
    }
    function ofertasRecibidas($idUsuario) {
        //RECOGER PRODUCTOS OFERTADOS
        $con = get_connection();
        $sql = "SELECT * FROM productos WHERE idVendedor = :idUsuario AND estadoProducto = 'reservado';";
        $statement = $con->prepare($sql);
        $statement->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $statement->execute();
        while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $productos[] = Producto::parse($row);
        }
        //MOSTRAR OFERTAS
        if(!empty($productos)) {
            foreach ($productos as $producto) {
                ?>
        <div class="container m-5 d-flex justify-content-center" style="flex-direction:column;">
            <div class="row row-cols-1 d-flex justify-content-center">
                <div class="card mb-3 p-0 text-bg-dark" style="width: 50%;">
                    <div class="row g-0">
                        <div class="col">
                            <?php
                            $imagenes = $producto->imagenes;
                            if(count($imagenes) > 1) {
                            ?>
                            <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                            <?php
                                for ($i=0; $i < count($imagenes); $i++) { 
                                    if($i == 0) {
                                        $activo = "active";
                                    } else {
                                        $activo = '';
                                    }
                                    ?>
                                    <div class="carousel-item <?php echo $activo ?>" data-bs-interval="5000">
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($imagenes[$i]) ?>" class="card-img img-fluid" style="width: 100%; height:16rem;object-fit: cover;">
                                    </div>
                                    <?php
                                }
                                    ?>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                </div>
                            </div>
                                <?php 
                                }else {
                                    ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($imagenes[0]) ?>" class="card-img img-fluid" style="width: 100%; height:16rem;object-fit: cover;">
                                    <?php
                                }
                                ?>
                        </div>
                        <div class="col-md-8">
                        <div class="card-body bg-dark">
                            <h5 class="card-title"><a href="../producto.php?idProducto=<?php echo $producto->idProducto ?>"><?php echo $producto->titulo ?></a></h5>
                            <p class="card-text"><?php echo $producto->precio ?>€</p>
                            <p class="card-text">Oferta: <?php echo $producto->oferta ?>€</p>
                            <form action="../acciones/confirmproduct.php" method="POST">
                            <input type="hidden" name="idProducto" value="<?php echo $producto->idProducto ?>">
                            <button type="submit" class="btn btn-success" name="respuesta" value="aceptada">Aceptar</button>
                            <button type="button" class="btn btn-danger" onclick="mostrarContraoferta()">Rechazar</button>
                            </form>
                        </div>
                        </div>
                    </div>
                    </div>
                </div>

                <div class="overlay" id="contraoferta"></div>
                <div class="popup" id="contraoferta2">
                    <form action="acciones/contraoferta.php" method="GET">
                    <p>Contraoferta:</p>
                    <input type="hidden" name="idProducto" value="<?php echo $producto->idProducto ?>">
                    <input type="number" name="contraoferta"><br>
                    <button type="submit" class="btn btn-success mt-2">Enviar</button>
                    <button type="button" class="btn btn-danger mt-2" onclick="cerrarContraoferta()">Salir</button>
                    </form>
                </div>

            <?php
            }
            ?>
            </div>
            </form>
            </div>
            <?php
            }else {
                echo "<h5 style='color:whitesmoke;'>No tienes ofertas recibidas en este momento</h5>";
            }
        }
        function ofertasEnviadas($idUsuario) {
            //RECOGER PRODUCTOS OFERTADOS
            $con = get_connection();
            $sql = "SELECT * FROM productos WHERE idComprador = :idUsuario AND estadoProducto IN ('reservado','negociacion','comprado');";
            $statement = $con->prepare($sql);
            $statement->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
            $statement->execute();
            while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $productos[] = Producto::parse($row);
            }
            //MOSTRAR OFERTAS
            if(!empty($productos)) {
                foreach ($productos as $producto) {
                    ?>
            <div class="container m-5 d-flex justify-content-center" style="flex-direction:column;">
                <div class="row row-cols-1 d-flex justify-content-center">
                    <div class="card mb-3 p-0 text-bg-dark" style="width: 50%;">
                        <div class="row g-0">
                            <div class="col">
                                <?php
                                $imagenes = $producto->imagenes;
                                if(count($imagenes) > 1) {
                                ?>
                                <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                <?php
                                    for ($i=0; $i < count($imagenes); $i++) { 
                                        if($i == 0) {
                                            $activo = "active";
                                        } else {
                                            $activo = '';
                                        }
                                        ?>
                                        <div class="carousel-item <?php echo $activo ?>" data-bs-interval="5000">
                                            <img src="data:image/jpeg;base64,<?php echo base64_encode($imagenes[$i]) ?>" class="card-img img-fluid" style="width: 100%; height:16rem;object-fit: cover;">
                                        </div>
                                        <?php
                                    }
                                        ?>
                                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    </div>
                                </div>
                                    <?php 
                                    }else {
                                        ?>
                                            <img src="data:image/jpeg;base64,<?php echo base64_encode($imagenes[0]) ?>" class="card-img img-fluid" style="width: 100%; height:16rem;object-fit: cover;">
                                        <?php
                                    }
                                    ?>
                            </div>
                            <div class="col-md-8">
                            <div class="card-body bg-dark">
                                <h5 class="card-title"><a href="../producto.php?idProducto=<?php echo $producto->idProducto ?>"><?php echo $producto->titulo ?></a></h5>
                                <p class="card-text"><?php echo $producto->precio ?>€</p>
                                <p class="card-text">Oferta: <?php echo $producto->oferta ?>€</p>
                                <?php
                                switch ($producto->estadoProducto) {
                                    case 'reservado':
                                        ?>
                                        <p class="card-text">En espera...</p>
                                        <?php
                                        break;
                                    case 'negociacion':
                                        ?>
                                        <form action="">
                                            <input type="number" name="respContraoferta">
                                            <button type="submit" class="btn btn-success">Enviar</button>
                                        </form>
                                        <?php
                                        break;
                                    case 'comprado':
                                        ?>
                                        <p class="card-text">Oferta aceptada!!</p>
                                        <?php
                                        break;
                                }
                                ?>
                            </div>
                            </div>
                        </div>
                        </div>
                    </div>
                <?php
                }
                ?>
                </div>
                </form>
                </div>
                <?php
                }else {
                    echo "<h5 style='color:whitesmoke;'>No tienes ofertas enviadas en este momento</h5>";
                }
            }
?>