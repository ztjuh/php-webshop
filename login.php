<?php
require_once 'include/config.php';
require_once 'include/session.php';

if (isset($_POST['email'], $_POST['password']) && !empty($_POST['email']) && !empty($_POST['password'])) {
    $email = strtolower($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        require_once 'header.php';
        require_once 'navigation.php';
        echo "<div class=\"container\">";
        echo "  <div class=\"alert alert-warning\" role=\"alert\">\n";
        echo "    <strong>Fault!</strong> Not a valid e-mail address!\n";
        echo "  </div>\n";
        echo "</div>";
        require_once 'include/footer.php';
        exit();
    }
    else {
        $password_entered = $_POST['password'];

        $sql = $pdo->prepare("SELECT * FROM login WHERE email = :email AND active= 1");
        $sql->bindParam(':email', $email);
        $result = $sql->execute();
        
        if ($result) {
            while ($row = $sql->fetch()) {
                $stored_secret = $row['password'];
                if (password_verify($password_entered, $stored_secret)) {
                    // Check if a newer hashing algorithm is available
                    // or the cost has changed
                    if (password_needs_rehash($stored_secret, PASSWORD_DEFAULT)) {
                        // If so, create a new hash, and replace the old one
                        $new_secret = password_hash($password_entered, PASSWORD_DEFAULT);
                        $sql = $pdo->prepare("UPDATE * FROM login SET password = :new_secret WHERE email = :email");
                        $sql->bindParam(':new_secret', $new_secret);
                        $sql->bindParam(':email', $email);
                        $result = $sql->execute();
                        if (!$result) {
                            var_dump($sql->errorInfo());
                        }
                    }
                    require_once 'include/session.php';
                    $_SESSION['email'] = $email;
                    if ($row['admin'] == 1) {
                        $_SESSION['admin'] = $row['admin'];
                    }
                    exit(header('Location: index.php'));
                }
                else {
                    require_once 'include/header.php';
                    require_once 'include/navigation.php';
                    echo "<div class=\"container\">";
                    echo "  <div class=\"alert alert-warning\" role=\"alert\">\n";
                    echo "    <strong>Fault!</strong> Wrong e-mail address and/or password entered, or account hasn't been activated yet!\n";
                    echo "  </div>\n";
                    echo "</div>";
                    require_once "include/footer.php";
                    exit();
                }
            }
        }        
    }
}
else {
    header('Location: index.php');
}
