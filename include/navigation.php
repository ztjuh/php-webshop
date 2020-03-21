<nav class="navbar navbar-dark bg-dark fixed-top navbar-expand-sm">
    <span class="navbar-brand">Webshop</span>
    <button class="navbar-toggler" style="background: #000000" type="button" data-toggle="collapse" data-target="#navbar-header" aria-controls="navbar-header">
        &#9776;
    </button>
    <div class="navbar-collapse collapse show" id="navbar-header">
        <ul class="navbar-nav mr-auto">

<?php

function menu_builder($pdo, $parent_id) {
    $sql = $pdo->prepare("SELECT id, menu_parent, href, name, is_dropdown FROM menu WHERE status = 1 ORDER BY position ASC");
    if($sql->execute()) {
        while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
            $array[$row['menu_parent']][] = $row;
        }
        main_menu($array);
    }
}
function main_menu($array, $parent_id = false) {
    if(!empty($array[$parent_id])) {
        foreach ($array[$parent_id] as $item) {
            if ($item['is_dropdown'] == false) {
                echo '<li class="nav-item"><a class="nav-link" href="' . $item['href'] . '">' . $item['name'] . '</a></li>' . PHP_EOL;
            }
            elseif ($item['is_dropdown'] == true) {
                echo '<li class="nav-item dropdown"><a class="nav-link dropdown-toggle" id="dropdown2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . $item['name'] . '</a>' . PHP_EOL;
                sub_menu($array, $item['id']);
                echo '</li>' . PHP_EOL;
            }
        }
    }
}
function sub_menu($array = array(), $parent_id = false) {
    if(!empty($array[$parent_id])) {
        echo '<ul class="dropdown-menu" aria-labelledby="dropdown2">' . PHP_EOL;
        foreach ($array[$parent_id] as $item) {
            if ($item['is_dropdown'] == false) {
                echo '<li class="dropdown-item"><a href="' . $item['href'] . '">' . $item['name'] . '</a></li>' . PHP_EOL;
            }
            elseif ($item['is_dropdown'] == true) {
                echo '<li class="dropdown-item dropdown"><a class="dropdown-toggle" id="dropdown2-1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . $item['name'] . '</a>' . PHP_EOL;
                sub_sub_menu($array, $item['id']);
                echo '</li>' . PHP_EOL;
            }
        }
        echo "</ul>";
    }
}
function sub_sub_menu($array = array(), $parent_id = false) {
    if(!empty($array[$parent_id])) {
        echo '<ul class="dropdown-menu" aria-labelledby="dropdown2-1">' . PHP_EOL;
        foreach ($array[$parent_id] as $item) {
            if ($item['is_dropdown'] == false) {
                echo '<li class="dropdown-item"><a href="' . $item['href'] . '">' . $item['name'] . '</a></li>' . PHP_EOL;
            }
        }
        echo "</ul>" . PHP_EOL;
    }
}

menu_builder($pdo, 0)

?>
        </ul>
 
        <div class="btn-group">
<?php
if (isset($_SESSION['email'])) {
?>
            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Menu</button>
            <div class="dropdown-menu dropdown-menu-right shadow">
                <a href="#" class="dropdown-item">Profile</a>
                <div class="dropdown-divider"></div>
                <a href="logout.php" class="dropdown-item">Logout</a>
<?php 
    if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1) {
?>
                <div class="dropdown-divider"></div>
                <a href="admin/action.php?action=new" class="dropdown-item">Add product</a>
<?php
  }
?>
            </div>
                                

<?php
}
else {
?>
        <ul class="nav navbar-nav">
            <li class="nav-item">
                <a href="#" class="nav-link" data-toggle="modal" data-target="#popUpLogin"><span class="fas fa-sign-in-alt"></span> Login</a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" data-toggle="modal" data-target="#popUpRegister"><span class="fas fa-file"></span> Register</a>
            </li>
            <li class="nav-item">&nbsp;</li>
        </ul>
<?php
}
?>
        </div>        
    </div>
</nav>

<div class="modal fade" id="popUpLogin">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark">
                <h3 class="modal-title text-secondary">Login</h3>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form role="form" action="login.php" method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <input type="email" name="email" class="form-control" placeholder="E-mail" required="true">
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" class="form-control" placeholder="Password" required="true">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-success btn-block">Login</button><br>
                    <a href="#" data-dismiss="modal" data-toggle="modal" data-target="#popUpForgot">Forgot password?</a>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="popUpRegister">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark">
                <h3 class="modal-title text-secondary">Register</h3>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form role="form" action="register.php" method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <input type="email" name="email" class="form-control" placeholder="E-mail" required="true">
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" class="form-control" placeholder="Password" required="true">
                    </div>
                    <div class="form-group">
                        <input type="password" name="password2" class="form-control" placeholder="Repeat password" required="true">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-success btn-block">Register</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="popUpForgot">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark">
                <h3 class="modal-title text-secondary">Reset password</h3>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form role="form" action="resetpassword.php" method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <input type="email" name="email" class="form-control" placeholder="E-mail" required="true">
                    </div>
                </div>
                <div class="modal-footer">
                    <button name="submit" value="submit" class="btn btn-outline-success btn-block">Send e-mail</button>
                </div>
            </form>
        </div>
    </div>
</div>

