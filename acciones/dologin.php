    <?php
    require_once("..\\p2\\p2_lib.php");
    require_once("..\\entity\\usuarios.php");

    $user = $_POST['user'];
    $pass = $_POST['password'];
    if(autenticarUsuario($user,$pass)) {
        if(isBlocked($user)) {
            header('Location:..\\login.php?err=USER_BLOCKED');
        }else {
            session_start();
            $_SESSION["user"] =  $user;
            $_SESSION["objeto"] = crearObjetoUsuario($user);
            header('Location:..\\index.php');
        }
    }else {
        header('Location:..\\login.php?err=NOT_EXIST');
    }
    ?>