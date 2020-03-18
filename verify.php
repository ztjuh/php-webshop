<?php
require_once 'include/config.php';
require_once 'include/session.php';
require_once 'include/header.php';
require_once 'include/navigation.php';

if (isset($_GET['email'], $_GET['hash']) && !empty($_GET['email']) && !empty($_GET['hash'])) {
    $email = strtolower($_GET['email']);
    $hash = $_GET['hash'];
    
    $sql = $pdo->prepare("SELECT email, hash, active FROM login WHERE email = :email AND hash = :hash AND active = 0");
    $sql->bindParam(':email', $email);
    $sql->bindParam(':hash', $hash);
    $result = $sql->execute();
    
    if ($result) {
        if ($row = $sql->fetch()) {
            //Match found!
            $newhash = md5(rand(0,100000));
            $sql = $pdo->prepare("UPDATE login SET active = 1, hash = :newhash WHERE email = :email AND hash = :hash AND active = 0");
            $sql->bindParam(':newhash', $newhash);
            $sql->bindParam(':email', $email);
            $sql->bindParam(':hash', $hash);
            $result = $sql->execute();
            if ($result) {
                echo "<div class=\"container\">";
                echo "  <div class=\"alert alert-success\" role=\"alert\">";
                echo "    <strong>Success!</strong> Your account is activated, you can now <a class=\"alert-link\" href=\"#\" data-toggle=\"modal\" data-target=\"#popUpLogin\">login</a>!";
                echo "  </div>";
                echo "</div>";
            }
            elseif (!$result) {
                echo "<div class=\"container\">";
                echo "  <div class=\"alert alert-danger\" role=\"alert\">";
                echo "    <strong>Fault!</strong> Something went wrong while activating your account!<br> Error: ";
                print_r($sql->errorInfo());
                echo "  </div>";
                echo "</div>";                
                
            }
        }
        else {
            //Geen match gevonden!
            echo "No match!";
        }
    }
    elseif (!$result){
        //There is something wrong with the query
        var_dump($sql->errorInfo());
    }
}
else {
    //Verkeerde aanpak
    echo "<div class=\"container\">";
    echo "  <div class=\"alert alert-warning\" role=\"alert\">\n";
    echo "    <strong>Fault!</strong> Use the link in your e-mail!\n";
    echo "  </div>\n";
    echo "</div>";
}
require_once "include/footer.php";
