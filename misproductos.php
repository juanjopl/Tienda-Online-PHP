<?php
    require_once("p2/p2_lib.php");
    include_once("entity/usuarios.php");
    include_once("entity/productos.php");
    session_start();

    $objeto = $_SESSION['objeto'];

    if(isset($_GET['pagina'])) {
    $numpagina = $_GET['pagina'];
    if($numpagina<0 || !is_numeric($numpagina)) {
        header('Location:index.php');
    }
    }

    $con = get_connection();
    $sql = "SELECT idCategoria, descripcion FROM categoria";
    $statement = $con->prepare($sql);
    $statement->execute();
    $resultado = $statement->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>
    <link rel="stylesheet" href="estilos/misproductos.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body>
    <nav>
        <ul class="lista">
            <li><a href="index.php">Inicio</a></li>
            <?php
            if(isset($_SESSION['user'])) {
                if(comprobarAdmin($_SESSION['user']) == false) {
                    echo '<li><a href="carrito.php">Carrito</a></li>';
                    echo '<li><a href="ofertas.php">Ofertas</a></li>'; 
                }
            }
            ?>
            <?php
            if(!isset($_SESSION['objeto'])) {
                echo "<li>Bienvenido invitado!!</li>";
            }else {
                $objeto = $_SESSION['objeto'];
                ?>
                    <li>
                        <img src="<?php
                            if ($objeto->avatar == null) {
                                echo 'img-default/default.jpg';
                            } else {
                                echo 'data:image/jpeg;base64, ' . base64_encode($objeto->avatar);
                            }
                        ?>" id="img">
                    </li>
                <?php
            }
            ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 25px;">
                    ☰
                </a>
                <ul class="dropdown-menu" style="background-color: #1E1E1E">
                    <?php
                        if(!isset($_SESSION["user"])) {
                            ?>
                            <li class="dropdown-item"><a href="login.php">Iniciar Sesion</a></li>
                            <?php
                        }else {
                            if(comprobarAdmin($_SESSION["user"])) {
                            ?>
                                <li class="dropdown-item"><a href="#" onclick="mostrarPopup()">Modo admin</a><li>
    
                                <div class="overlay" id="overlay"></div>
                                <div class="popup" id="popup">
                                    <form action="admin/admin.php" method="POST">
                                    <p>Selecciona tabla</p>
                                    <button type="submit" name="seleccion" value="usuarios">Usuarios</button>
                                    <button type="submit" name="seleccion" value="productos">Productos</button>
                                    </form>
                                </div>

                                <li><hr class="dropdown-divider"></li>
                                <li class="dropdown-item"><a href="informacion.php">Cuenta</a></li>
                                <li class="dropdown-item"><a href="acciones/logout.php">Cerrar Sesion</a></li>
                            <?php
                            }else {
                            ?>
                                <li class="dropdown-item"><a href="subirproducto.php">Subir Producto</a></li>
                                <li class="dropdown-item"><a href="informacion.php">Cuenta</a></li>
                                <li class="dropdown-item"><a href="acciones/logout.php">Cerrar Sesion</a></li>
                            <?php
                            }
                        }
                    ?>
                </ul>
            </li>
        </ul>
    </nav>
    
    <div class="d-flex justify-content-center aling-items-center" id="misproductos">
        <h2>Mis Productos</h2>
    </div>

    <main>
        <?php
            define('REGISTROS_PAGINA', 9);
            $registros = Producto::contarProductos();
            $paginas = ceil($registros / REGISTROS_PAGINA);
            
            if(isset($_GET['pagina'])) {
                $pagina = $_GET['pagina'];
            }else {
                $pagina = 1;
            }

            $conn = get_connection();
            $limit = "LIMIT " . (($pagina - 1) * REGISTROS_PAGINA) . ', ' . REGISTROS_PAGINA;
            $productos = Producto::getPaginacion($pagina, REGISTROS_PAGINA);
            $productosUsuario = array();
            foreach ($productos as $producto) {
                if($producto->idVendedor == $objeto->idUsuario) {
                    $productosUsuario[] = $producto;
                }
            }
        ?>

        <?php
        if(empty($productosUsuario)) {
            echo '<h3 style="color: whitesmoke;">No has subido ningun producto todavía</h3>';
        }else {
            mostrarMisProductos($productosUsuario);
        }
        ?>
    </main>

    <div class="paginacion">
        <ul class="pagination">
            <li class="page-item">
                <a class="page-link bg-dark text-light" href="?pagina=<?php 
                    if($pagina > 1) {
                        echo $pagina - 1; 
                    }else {
                        echo 1;
                    }
                ?>">
                    <span class="sr-only">Anterior</span>
                </a>
            </li>
            <?php
            for ($i = 1; $i <= $paginas; $i++) {
                ?>
                <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>">
                    <a class="page-link bg-dark text-light" href="?pagina=<?php echo $i ?>"><?php echo $i ?></a>
                </li>
                <?php
            }
            ?>
            <li class="page-item">
                <a class="page-link bg-dark text-light" href="?pagina=<?php 
                        if($pagina < $paginas) {
                            echo $pagina + 1;
                        }else {
                            echo $paginas;
                        }
                ?>" aria-label="Siguiente">
                    <span class="sr-only">Siguiente</span>
                </a>
            </li>
        </ul>
    </div>

    <footer>
    <p>&copy; 2023 McSneakers. Todos los derechos reservados.</p>
    </footer>
    <script>
    const iconoMenu = document.getElementById('iconomenu');
    const opcionesMenu = document.getElementById('opciones');
    iconoMenu.onclick = function() {
        iconoMenu.classList.toggle('rotar');
        if (opcionesMenu.style.display === 'block') {
            opcionesMenu.style.display = 'none';
        } else {
            opcionesMenu.style.display = 'block';
        }
    };
    const productos = document.querySelectorAll('td');
    for (const producto of productos) {
        producto.addEventListener('click', function() {
            window.location.href = 'producto.php';
        })
    }

    document.getElementById("categoria").onchange = function() {
        let categoriaSeleccionada = this.value;

        let ajax = new XMLHttpRequest();
        ajax.open("GET", "acciones/dosubcategorias.php?categoria=" + categoriaSeleccionada, true);

        ajax.onload = function() {
            if (ajax.status == 200) {

                let selectSubcategorias = document.getElementById("subcategoria");

                selectSubcategorias.options.length = 0;

                let subcategorias = JSON.parse(ajax.responseText);

                subcategorias.forEach(function(subcategoria) {
                    let option = document.createElement("option");
                    option.value = subcategoria.idSubcategoria;
                    option.text = subcategoria.descripcion; 
                    selectSubcategorias.add(option);
                });
            }
        };

        ajax.send();
    };

    function mostrarPopup() {
        // Mostrar el fondo oscuro y el popup
        document.getElementById('overlay').style.display = 'block';
        document.getElementById('popup').style.display = 'block';
    }

    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
</body>
</html>