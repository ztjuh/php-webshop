<?php
require_once 'include/config.php';
require_once 'include/session.php';
require_once 'include/header.php';
require_once 'include/navigation.php';

// Handle email verification
if (isset($_GET['email'], $_GET['hash']) && !empty($_GET['email']) && !empty($_GET['hash'])) {
    $email = strtolower($_GET['email']);
    $hash = $_GET['hash'];
    
    $sql = $pdo->prepare("SELECT email, hash, active FROM login WHERE email = :email AND hash = :hash AND active = 0");
    $sql->bindParam(':email', $email);
    $sql->bindParam(':hash', $hash);
    $result = $sql->execute();
    
    if ($result) {
        if ($row = $sql->fetch()) {
            // Match found - activate account
            $newhash = md5(rand(0, 100000));
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
            } else {
                echo "<div class=\"container\">";
                echo "  <div class=\"alert alert-danger\" role=\"alert\">";
                echo "    <strong>Fault!</strong> Something went wrong while activating your account!<br> Error: ";
                print_r($sql->errorInfo());
                echo "  </div>";
                echo "</div>";
            }
        } else {
            // No match found
            echo "<div class=\"container\">";
            echo "  <div class=\"alert alert-warning\" role=\"alert\">";
            echo "    <strong>Fault!</strong> Use the link in your e-mail!";
            echo "  </div>";
            echo "</div>";
        }
    } else {
        // Query error
        echo "<div class=\"container\">";
        echo "  <div class=\"alert alert-danger\" role=\"alert\">";
        echo "    <strong>Error:</strong> ";
        var_dump($sql->errorInfo());
        echo "  </div>";
        echo "</div>";
    }
}
// Handle registration
elseif (isset($_POST['email'], $_POST['password'], $_POST['password2']) && !empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['password2'])) {
    $email = strtolower($_POST['email']);
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<div class=\"container\">" . PHP_EOL;
        echo "  <div class=\"alert alert-warning\" role=\"alert\">" . PHP_EOL;
        echo "    <strong>Fault!</strong> Not a valid e-mail address!" . PHP_EOL;
        echo "  </div>" . PHP_EOL;
        echo "</div>" . PHP_EOL;
    } else {
        // Check if email already exists
        $sql = $pdo->prepare("SELECT email FROM login WHERE email = :email");
        $sql->bindParam(':email', $email);
        $result = $sql->execute();
        
        if ($result) {
            if ($row = $sql->fetch()) {
                // Email already registered
                echo "<div class=\"container\">" . PHP_EOL;
                echo "  <div class=\"alert alert-warning\" role=\"alert\">" . PHP_EOL;
                echo "    <strong>Fout!</strong> This e-mail address (" . htmlspecialchars($row['email']) . ") is already registered!" . PHP_EOL;
                echo "  </div>" . PHP_EOL;
                echo "</div>" . PHP_EOL;
            } else {
                // Check if passwords match
                if ($_POST['password'] === $_POST['password2']) {
                    // Generate hash and hash password
                    $hash = md5(rand(0, 100000));
                    $secret = password_hash($_POST['password'], PASSWORD_BCRYPT);
                    
                    // Insert new user
                    $sql = $pdo->prepare("INSERT INTO login (id, email, password, hash, active, code, admin) VALUES (NULL, :email, :secret, :hash, 0, '', 0)");
                    $sql->bindParam(':email', $email);
                    $sql->bindParam(':secret', $secret);
                    $sql->bindParam(':hash', $hash);
                    
                    $result = $sql->execute();
                    
                    if ($result) {
                        // Send verification email
                        $subject = $webshop_name . " e-mail verification";
                        $verifyUrl = "http" . (isset($_SERVER['HTTPS']) ? "s" : "") . "://" . $_SERVER['HTTP_HOST'] . "" . dirname($_SERVER["REQUEST_URI"]) . "/auth.php?email=" . urlencode($email) . "&hash=" . $hash;
                        
                        $message = "Dear " . $email . ",\n\n";
                        $message .= "Thanks for making an account at " . $webshop_name . ".\n\n";
                        $message .= "You can login with this e-mail address and the password after you have verified your e-mail address, you can do this by clicking on the link below.\n\n";
                        $message .= $verifyUrl . "\n\n";
                        $message .= "Kind regards,\n" . $from_name;
                        
                        $headers = "From: " . $van_email . "\r\n";
                        $result = mail($email, $subject, $message, $headers);
                        
                        if ($result) {
                            echo "<div class=\"container\">" . PHP_EOL;
                            echo "  <div class=\"alert alert-success\" role=\"alert\">" . PHP_EOL;
                            echo "    <strong>Success!</strong> There has been sent an e-mail to " . htmlspecialchars($email) . " with an activation link! Don't forget to look in your spam folder!" . PHP_EOL;
                            echo "  </div>" . PHP_EOL;
                            echo "</div>" . PHP_EOL;
                        } else {
                            echo "<div class=\"container\">" . PHP_EOL;
                            echo "  <div class=\"alert alert-warning\" role=\"alert\">" . PHP_EOL;
                            echo "    <strong>Fault!</strong> Something went wrong with sending the activation e-mail! " . error_get_last()['message'] . PHP_EOL;
                            echo "  </div>" . PHP_EOL;
                            echo "</div>" . PHP_EOL;
                        }
                    } else {
                        echo "<div class=\"container\">" . PHP_EOL;
                        echo "  <div class=\"alert alert-danger\" role=\"alert\">" . PHP_EOL;
                        echo "    <strong>Error:</strong> ";
                        var_dump($sql->errorInfo());
                        echo "  </div>" . PHP_EOL;
                        echo "</div>" . PHP_EOL;
                    }
                } else {
                    // Passwords don't match
                    echo "<div class=\"container\">" . PHP_EOL;
                    echo "  <div class=\"alert alert-warning\" role=\"alert\">" . PHP_EOL;
                    echo "    <strong>Fault!</strong> Passwords don't match, please try again!" . PHP_EOL;
                    echo "  </div>" . PHP_EOL;
                    echo "</div>" . PHP_EOL;
                }
            }
        } else {
            echo "<div class=\"container\">" . PHP_EOL;
            echo "  <div class=\"alert alert-danger\" role=\"alert\">" . PHP_EOL;
            echo "    <strong>Error:</strong> ";
            var_dump($sql->errorInfo());
            echo "  </div>" . PHP_EOL;
            echo "</div>" . PHP_EOL;
        }
    }
}
// No valid request
else {
    echo "<div class=\"container\">";
    echo "  <div class=\"alert alert-warning\" role=\"alert\">";
    echo "    <strong>Fault!</strong> Invalid request!";
    echo "  </div>";
    echo "</div>";
}

require_once "include/footer.php";
