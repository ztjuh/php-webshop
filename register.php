<?php
require_once 'include/config.php';
require_once 'include/session.php';
require_once 'include/header.php';
require_once 'include/navigation.php';

if (isset($_POST['email'], $_POST['password'], $_POST['password2']) && !empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['password2'])) {
    $email = strtolower($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<div class=\"container\">" . PHP_EOL;
        echo "  <div class=\"alert alert-warning\" role=\"alert\">" . PHP_EOL;
        echo "    <strong>Fault!</strong> Not a valid e-mail address!" . PHP_EOL;
        echo "  </div>" . PHP_EOL;
        echo "</div>" . PHP_EOL;
    }
    else {
        //Check if e-mail is in database
        $sql = $pdo->prepare("SELECT email FROM login WHERE email = :email");
        $sql->bindParam(':email', $email);
        $result = $sql->execute();
        if ($result) {
            if ($row = $sql->fetch()) {
                //Found e-mail address
                echo "<div class=\"container\">" . PHP_EOL;
                echo "  <div class=\"alert alert-warning\" role=\"alert\">" . PHP_EOL;
                echo "    <strong>Fout!</strong> This e-mail address (" . $row['email'] . ") is already  registered!" . PHP_EOL;
                echo "  </div>" . PHP_EOL;
                echo "</div>" . PHP_EOL;
            }
            else {
                //No e-mail found, register!
                if ($_POST['password'] === $_POST['password2']) {
                    $hash = md5(rand(0,100000)); //Generate random 32 number hash
                    $secret = password_hash($_POST['password'], PASSWORD_BCRYPT);
                    
                    $sql = $pdo->prepare("INSERT INTO login (id, email, password, hash, active, code, admin) VALUES (NULL, :email, :secret, :hash, 0, '', 0)");
                    $sql->bindParam(':email', $email);
                    $sql->bindParam(':secret', $secret);
                    $sql->bindParam(':hash', $hash);
                    
                    $result = $sql->execute();
                    if ($result) {
                        //E-mail address added! Send activation e-mail!
                        
                        $subject = $webshop_name . " e-mail verification";
                        $message = "Dear " . $email . ",
        
Thanks for making a account at " . $webshop_name . ".

You can login with this e-mail address and the password after you have verified your e-mail address, you can do this by clicking on the link below.

http" . (isset($_SERVER['HTTPS']) ? "s" : "") . "://" . $_SERVER['HTTP_HOST'] . "" . dirname($_SERVER["REQUEST_URI"]) . "/verify.php?email=" . $email . "&hash=" . $hash . "

Kind regards,
" . $from_name;

                        $headers = "From:" . $van_email . "\r\n";
                        $result = mail($email, $subject, $message, $headers);
                        
                        if ($result) {
                            echo "<div class=\"container\">" . PHP_EOL;
                            echo "  <div class=\"alert alert-success\" role=\"alert\">" . PHP_EOL;
                            echo "    <strong>Success!</strong> There has been sended a e-mail to " . $email . " with a activation link! Don't forget to look in your span folder!" . PHP_EOL;
                            echo "  </div>" . PHP_EOL;
                            echo "</div>" . PHP_EOL;
                        }
                        elseif (!$result) {
                            echo "<div class=\"container\">" . PHP_EOL;
                            echo "  <div class=\"alert alert-warning\" role=\"alert\">" . PHP_EOL;
                            echo "    <strong>Fault!</strong> Something went wrong with sending the activation e-mail! " . error_get_last()['message'] . PHP_EOL;
                            echo "  </div>" . PHP_EOL;
                            echo "</div>" . PHP_EOL;
                        }
                    }
                    elseif(!$result) {
                        var_dump($sql->errorInfo());
                    }
                }
                else {
                    //Passwords don't match
                    echo "<div class=\"container\">" . PHP_EOL;
                    echo "  <div class=\"alert alert-warning\" role=\"alert\">" . PHP_EOL;
                    echo "    <strong>Fault!</strong> Passwords don't match, please try again!" . PHP_EOL;
                    echo "  </div>" . PHP_EOL;
                    echo "</div>" . PHP_EOL;
                }
            }
        } 
        elseif (!$result) { 
            var_dump($sql->errorInfo());
        }
    }
}
require_once 'include/footer.php';
