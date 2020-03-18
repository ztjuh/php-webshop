<?php
require_once 'include/config.php';
require_once 'include/session.php';
require_once 'include/header.php';
require_once 'include/navigation.php';


?>
    <div class="container">
<?php
if (isset($_GET['id']) && !empty($_GET['id']) && is_numeric($_GET['id'])) {
    $sql = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $sql->bindParam(':id', $_GET['id']);
    $sql->execute() or die("Unable to execute query!");
        
    if ($sql->rowCount() > 0) {
        while ($row = $sql->fetch(PDO::FETCH_BOTH)) {
            ?>
            <div class="row">
              <div class="sm-col-4">
                <div class="card bg-light border-dark mb-3" style="overflow: hidden">
            <?php
           echo "            <div class=\"card-header\">" . $row['product'] . "</div>" . PHP_EOL;
            if (empty($row['pictures'])) {
                echo "      <img class=\"card-img-top\" src=\"img/empty.png\" alt=\"" . $row['product'] . "\">" . PHP_EOL;
            }
            else {
                ?>
                <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
                  <ol class="carousel-indicators">
                <?php
                $pictures = unserialize($row['pictures']);
                $counter = count($pictures);
                $i = 0;
                
                while ($i < $counter) {
                    if ($i == 0) {
                        echo "        <li data-target=\"#carouselExampleIndicator\" data-slide-to=\"" . $i . "\" class=\"active\"></li>" . PHP_EOL;
                        $i++;
                    }
                    else {
                        echo "        <li data-target=\"#carouselExampleIndicator\" data-slide-to=\"" . $i . "\"></li>" . PHP_EOL;
                        $i++;
                    }
                }
                ?>
                  </ol>
                  <div class="carousel-inner">
                <?php
                $i = 0;
                foreach ($pictures as $picture) {
                    if ($i == 0) {
                        echo "        <div class=\"carousel-item active\">" . PHP_EOL;
                        echo "            <img class=\"d-block w-100\" src=\"" . $location . $picture . "\" alt=\"" . $row['product'] . "\">" . PHP_EOL;
                        echo "        </div>" . PHP_EOL;
                        $i++;
                    }
                    else {
                        echo "        <div class=\"carousel-item\">" . PHP_EOL;
                        echo "            <img class=\"d-block w-100\" src=\"" . $location . $picture . "\" alt=\"" . $row['product'] . "\">" . PHP_EOL;
                        echo "        </div>" . PHP_EOL;
                    }
                }
                ?>
                  </div>
                  <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                  </a>
                  <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                  </a>
                </div>
        <?php
            }
            echo "            <div class=\"card-header\">" . $row['product'] . "</div>" . PHP_EOL;
            echo "              <div class=\"card-body\">" . PHP_EOL;
            echo "                <p class=\"card-text\">" . PHP_EOL;
            echo "                  <span>" . nl2br($row['description']) . "</span><br>" . PHP_EOL;
            echo "                  <span>&euro; " . number_format($row['price'], 2, ',', '.') . "</span><br>" . PHP_EOL;
            echo "                  <span>Stock: " . $row['stock'] . "</span><br>" . PHP_EOL;
            echo "                </p>\n";
            echo "                <a href=\"cart.php?action=add&id=" . $row['id'] . "\" class=\"btn btn-primary\">Buy</a>\n";
            if (isset($_SESSION['admin']) && $_SESSION['admin'] == "1") {
                echo "            <a href=\"admin/action.php?action=modify&id=" . $row['id'] . "\" class=\"btn btn-warning pull-xs-right\">Modify</a>\n";
                echo "            <a href=\"admin/action.php?action=remove&id=" . $row['id'] . "\" class=\"btn btn-danger pull-xs-right\">Remove</a>\n";
            }
            echo "              </div>" . PHP_EOL;
            echo "            </div>" . PHP_EOL;
            echo "          </div>\n";
            echo "      </div>\n";
            echo "  </div>\n";
            echo "</div>\n";

            ?>
                </div>
              </div>
            </div>
        <?php
        }
    }
    else {
        echo "    <div class=\"alert alert-info\" role=\"alert\">" . PHP_EOL;
        echo "        This product doesn't exist!" . PHP_EOL;
        echo "    </div>" . PHP_EOL;
    }
}
else {
    echo "    <div class=\"alert alert-info\" role=\"alert\">" . PHP_EOL;
    echo "        This product doesn't exist!" . PHP_EOL;
    echo "    </div>" . PHP_EOL;
}
?>
    </div>
<?php
require_once 'include/footer.php';
