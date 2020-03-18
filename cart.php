<?php
require_once 'include/config.php';
require_once 'include/session.php';

if (filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT)) {
    $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
}
if (filter_input(INPUT_GET, "amount", FILTER_VALIDATE_INT)) {
    $aantal = filter_input(INPUT_GET, "amount", FILTER_VALIDATE_INT);
}
if (isset($_GET['action']) && !empty($_GET['action'])) {
    if ($_GET['action'] == "add" && isset($id)) {
        //Making a cart, because it doesn't excist yet
        if (!isset($_SESSION['cart']) && isset($id) && is_numeric($id) && empty($_SESSION['cart'])) {
            $_SESSION['cart'][$id] = array('amount' => 1);
            header('Location: cart.php');
            exit();
        }
        //The cart exists and is empty (this should never happen)
        elseif (isset($_SESSION['cart'], $id) && is_numeric($id) && empty($_SESSION['cart'])) {
            //Add the product to the array
            $_SESSION['cart'][$id] = array('amount' => 1);
            header('Location: cart.php');
            exit();
        }
        //The cart exists and isn't empty
        elseif (isset($_SESSION['cart'], $id) && is_numeric($id) && !empty($_SESSION['cart'])) {
            if(array_key_exists($id, $_SESSION['cart'])) {
                //The id (product) exists, add 1 to the amount of the array
                $_SESSION['cart'][$id]['amount']++;
                header('Location: cart.php');
                exit();
            }
            else {
                //The id (product) doesn't exist, make a array with the product and set it to 1
                $_SESSION['cart'][$id] = array('amount' => 1);
                header('Location: cart.php');
                exit();
            }
        }
    }
    elseif ($_GET['action'] == "update" && isset($id) && isset($amount)) {
        //Change the amount of products in the cart to the amount set
        $_SESSION['cart'][$id]['amount'] = $amount;
        header('Location: cart.php');
        exit();
    }
    elseif ($_GET['action'] == "remove" && isset($id)) {
        if (isset($_SESSION['cart'], $id) && is_numeric($id) && !empty($_SESSION['cart'])) {
            //Remove product from cart
            if (isset($_SESSION['cart'][$id])) {
                unset($_SESSION['cart'][$id]);
                //Remove cart if it's empty
                if (count($_SESSION['cart']) == 0) {
                    unset($_SESSION['cart']);
                    header('Location: cart.php');
                    exit();
                }
                header('Location: cart.php');
                exit();
            }
        }
        if (!isset($_SESSION['cart']) && isset($id) && is_numeric($id)) {
            header('Location: cart.php');
            exit();
        }
    }
    elseif ($_GET['action'] == "empty") {
        //Empty cart (removes all products)
        unset($_SESSION["cart"]);
        header('Location: cart.php');
        exit();
    }
}
if (!isset($_SESSION['cart'])) {
    require_once 'include/header.php';
    require_once 'include/navigation.php';
    echo "<div class=\"container\">";
    echo "    <div class=\"alert alert-info\" role=\"alert\">" . PHP_EOL;
    echo "        The cart is empty!" . PHP_EOL;
    echo "    </div>" . PHP_EOL;
    echo "</div>";
}
elseif (isset($_SESSION['cart'])) { 
    //There is something in the cart or the cart is empty (which should never happen)
    require_once 'include/header.php';
    require_once 'include/navigation.php';
    echo "<div class=\"container\">";
    echo "  <h3>Cart</h3>";
    $totaal = 0;
    echo "  <table class=\"table table-striped\">\n";
    echo "    <thead>";
    echo "      <tr>\n";
    echo "        <th>Name</th>\n";
    echo "        <th>Amount</th>\n";
    echo "        <th>Price</th>\n";
    echo "        <th>Action</th>\n";
    echo "      </tr>\n";
    echo "    </thead>";
    echo "    <tbody>";
    foreach ($_SESSION['cart'] as $id => $value) {
        $query = "SELECT * FROM products WHERE id = '$id'";
        $sql = $pdo->prepare($query);
        $sql->execute() or die("Unable to execute query!");
        while ($row = $sql->fetch(PDO::FETCH_BOTH)) {
            if ($value['amount'] > $row['stock']) {
                $value['amount'] = $row['stock'];
            }
            $totaal = $totaal + ($row['price'] * $value['amount']);
            echo "      <tr>\n";
            echo "        <td><a href=\"products.php?id=" . $row['id'] . "\">" . $row['product'] . "</a></td>\n";
            echo "        <td><form name=\"update\" method=\"get\"><input type=\"hidden\" name=\"id\" value=\"" . $id . "\"><input type=\"hidden\" name=\"action\" value=\"update\"><select name=\"amount\" onchange=\"this.form.submit()\">";
            for ($i = 1; $i < $row['stock'] + 1; $i++) {
                if ($i == $value['amount']) {
                    echo "<option value=\"" . $i . "\" selected>". $i . "</option>\n";
                }
                else {
                    echo "<option value=\"" . $i . "\">". $i . "</option>\n";
                }
            }
            echo "</select><noscript><input type=\"submit\" value=\"Submit\"></noscript></form></td>\n";
            echo "        <td style=\"white-space: nowrap;\">&euro; " . number_format($row['price'] * $value['amount'], 2, ',', '.') . "<br>\n";
            echo "        <td><a href=\"cart.php?action=remove&id=" . $id . "\" class=\"btn btn-primary\">Remove</a>\n";
            echo "      </tr>\n";
        }
    }
    echo "      <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    echo "      <tr><td>Total: </td><td>&nbsp;</td><td style=\"white-space: nowrap;\">&euro; " . number_format($totaal, 2, ',', '.') . "</td><td>&nbsp;</td></tr>\n";
    echo "    </tbody>\n";
    echo "  </table>\n";
    echo "  <p>\n";
    echo "  Pay with:<br>\n";
    echo "  <form>";
    echo "      <input type=\"radio\" name=\"pay\" value=\"ideal\" id=\"ideal\"> <label for=\"ideal\"><img src=\"https://www.ideal.nl/img/statisch/mobiel/iDEAL_216x36_enkel.gif\" alt=\"iDeal\"></label><br>\n";
    echo "      <input type=\"radio\" name=\"pay\" value=\"paypal\" id=\"paypal\"> <label for=\"paypal\"><img src=\"img/checkout-logo-small-alt-nl.png\" alt=\"PayPal\"></label>\n";
    echo "  </form>";
    echo "  </p>\n";
    echo "  <!--<a href=\"checkout.php?methode=paypal\" id=\"methode\" class=\"btn btn-success\">Pay</a> --><button class=\"btn btn-success\" id=\"pay\" disabled>Pay</button> <button class=\"btn btn-warning\" data-toggle=\"modal\" data-target=\"#popUpEmptyCart\">Empty cart</button>\n";
    echo "</div>\n";
}
?>
    <script>
        $('input[type="radio"]').click(function() {
            var ideal = $("#ideal").is(":checked");
            var paypal = $("#paypal").is(":checked");

            var addr = "";
            
            if (ideal) {
                addr = "checkout.php?method=ideal";
            }
            else if (paypal) {
                addr = "checkout.php?method=paypal";
            }
            $('#pay').prop('disabled', false);
            $('#pay').click(function() {
                location.href=addr;
            });
        });
    </script>
   
    <div class="modal fade" id="popUpEmptyCart">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark">
                    <h3 class="modal-title text-secondary">Empty cart</h3>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    Are you sure you want to empty the cart?
                </div>
                <div class="modal-footer">
                    <a href="cart.php?action=empty" class="btn btn-success btn-block" role="button" aria-pressed="true">Yes</a><br>
                    <button type="button" class="btn btn-danger btn-block" data-dismiss="modal">No</button>
                </div>
            </div>
        </div>
    </div>
<?php
require_once 'include/footer.php';