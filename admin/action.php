<?php
require_once '../include/config.php';
require_once '../include/session.php';

function rmdir_recursive($dir) {
    foreach(scandir($dir) as $file) {
        if ('.' === $file || '..' === $file) continue;
        if (is_dir("$dir/$file")) rmdir_recursive("$dir/$file");
        else unlink("$dir/$file");
    }
    rmdir($dir);
}

if ($_SESSION['admin'] == "1" && $_SERVER['REQUEST_METHOD'] == 'GET') {
    if (empty($_GET) && empty($_POST)) {
        header("Location: ../index.php");
        exit();
    }
    elseif (!empty($_GET)) {
        if (filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT)) {
            $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
        }
        if (filter_input(INPUT_GET, "amount", FILTER_VALIDATE_INT)) {
            $value = filter_input(INPUT_GET, "amount", FILTER_VALIDATE_INT);
        }
    }
    if (!isset($_GET['action']) && empty($_GET['action'])) {
        require_once "../include/header.php";
        require_once "../include/navigation.php";
        echo "  <div class=\"container\">" . PHP_EOL;
        echo "    <div class=\"alert alert-warning\" role=\"alert\">" . PHP_EOL;
        echo "        Nothing to see here!" . PHP_EOL;
        echo "    </div>" . PHP_EOL;
        echo "  </div>" . PHP_EOL;
        require_once "../include/footer.php";
        exit();
    }
    elseif ($_GET['action'] == "new") {
        require_once '../include/header.php';
        require_once '../include/navigation.php';
?>
    <script>
        function addMoreFiles() {
            $("#pictures").append('<input type="file" class="form-control-file" name="pictures[]" id="file" aria-describedby="productPictures">')
        }
    </script>

    <div class="container">
       <h3>Add Product</h3>
        <form role="form" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post" id="form" enctype="multipart/form-data">
            <div class="form-group">
                <label for="product">Product name</label>
                <input type="text" class="form-control" name="product" id="product" aria-describedby="productName" placeholder="Product name" required="true">
                <p id="productName" class="form-text text-muted">
                    <small>Required field!</small>
                </p>
            </div>
            <div class="form-group">
                <label for="description">Product description</label>
                <textarea class="form-control" name="description" id="description" rows="3" placeholder="Product description"></textarea>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" class="form-control" name="category" id="category" aria-describedby="productCategory" placeholder="Category" required="true">
                <p id="productCategory" class="form-text text-muted">
                    <small>Required field!</small>
                </p>
            </div>
            <div class="form-group">
                <label for="stock">Stock</label>
                <input type="number" class="form-control" name="stock" id="stock" aria-describedby="productStock" placeholder="1" required="true">
                <p id="productStock" class="form-text text-muted">
                    <small>Required field!</small>
                </p>
            </div>
            <div class="form-group">
                <label for="price">Price</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="validationPrice">&euro;</span>
                    </div>
                    <input type="number" class="form-control" name="price" id="price" aria-describedby="productPrice" placeholder="10,00" pattern="[0-9]+([\.,][0-9]+)?" step="0.01" required="true">
                </div>
                <p id="productPrice" class="form-text text-muted">
                    <small>Required field!</small>
                </p>
            </div>
            <div class="form-group" id="pictures">
                <label for="pictures">Picture(s)</label>
                <p><a href="javascript:addMoreFiles()" class="btn btn-primary">Add more pictures</a></p>
                <input type="file" class="form-control-file" name="pictures[]" id="file" aria-describedby="productPictures">
            </div>
            <div class="form-group">
                <button name="action" value="add" type="submit" class="btn btn-success">Add</button>
            </div>
        </form>
    </div>
            <?php
        require_once '../include/footer.php';
        exit();
    }// End GET new
    elseif ($_GET['action'] == "modify" && isset($id) && is_numeric($id)) {
            require_once "../include/header.php";
            require_once "../include/navigation.php";
            
            $sql = $pdo->prepare("SELECT * FROM products WHERE id = :id");
            $sql->bindParam(':id', $id);
            $result = $sql->execute();
            if (!$result) {
                echo "  <div class=\"container\">" . PHP_EOL;
                echo "    <div class=\"alert alert-info\" role=\"alert\">" . PHP_EOL;
                echo "        This product doesn't exist!" . PHP_EOL;
                echo "    </div>" . PHP_EOL;
                echo "  </div>" . PHP_EOL;
            }
            elseif ($result) {
                if ($row = $sql->fetch(PDO::FETCH_BOTH)) {
                ?>
    <script>
        function addMoreFiles() {
            $("#pictures").append('<input type="file" class="form-control-file" name="pictures[]" id="file" aria-describedby="productPictures">')
        }
    </script>

    <div class="container">
       <h3>Modify product</h3>
        <form role="form" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?id=<?php echo $row['id']; ?>" method="post" id="form" enctype="multipart/form-data">
            <div class="form-group">
                <label for="product">Product name</label>
                <input type="text" class="form-control" name="product" id="product" aria-describedby="productName" placeholder="Product name" required="true" value="<?php echo $row['product']; ?>">
                <p id="productName" class="form-text text-muted">
                    <small>Required field!</small>
                </p>
            </div>
            <div class="form-group">
                <label for="description">Product description</label>
                <textarea class="form-control" name="description" id="description" rows="3" placeholder="Product description"><?php echo $row['description']; ?></textarea>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" class="form-control" name="category" id="category" aria-describedby="productCategory" placeholder="Category" required="true" value="<?php echo $row['pparent_id']; ?>">
                <p id="productCategory" class="form-text text-muted">
                    <small>Required field!</small>
                </p>
            </div>
            <div class="form-group">
                <label for="stock">Stock</label>
                <input type="number" class="form-control" name="stock" id="stock" aria-describedby="productStock" placeholder="1" required="true" value="<?php echo $row['stock']; ?>">
                <p id="productStock" class="form-text text-muted">
                    <small>Required field!</small>
                </p>
            </div>
            <div class="form-group">
                <label for="price">Price</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="validatePrice">&euro;</span>
                    </div>
                    <input type="number" class="form-control" name="price" id="price" aria-describedby="productPrice" placeholder="10,00" pattern="[0-9]+([\.,][0-9]+)?" step="0.01" required="true" value="<?php echo $row['price']; ?>">
                </div>
                <p id="productPrice" class="form-text text-muted">
                    <small>Required field!</small>
                </p>
            </div>
            <div class="form-group" id="pictures">
                <label for="pictures">Picture(s)</label>
                <?php
                    $pictures = unserialize($row['pictures']);
                    if (!empty($pictures)) {
                        foreach ($pictures as $picture) {
                            echo "<p>" . PHP_EOL;
                            echo "  <img src=\"" . $location . $picture . "\" alt=\"" . $row['product'] . "\" class=\"img-rounded\" style=\"width: 100%;\"><br>" . PHP_EOL;
                            echo "  <a href=\"" . htmlentities($_SERVER['PHP_SELF']) . "?action=remove-picture&id=" . $row['id'] . "&picture=" . $picture . "\" class=\"btn btn-danger\">Remove picture</a>" . PHP_EOL;
                            echo "</p>" . PHP_EOL;
                        }
                    }
                ?>
                <p><a href="javascript:addMoreFiles()" class="btn btn-primary">Add more pictures</a></p>
                <input type="file" class="form-control-file" name="pictures[]" id="file" aria-describedby="productPicture">
            </div>
            <div class="form-group">
                <button name="actio" value="modify" type="submit" class="btn btn-success">Save</button>
            </div>
        </form>
    </div>

            <?php
            }
            else {
                echo "  <div class=\"container\">" . PHP_EOL;
                echo "    <div class=\"alert alert-warning\" role=\"alert\">" . PHP_EOL;
                echo "        <strong>Fault!</strong> This product doesn't exist!" . PHP_EOL;
                echo "    </div>" . PHP_EOL;
                echo "  </div>" . PHP_EOL;
            }
        }
        require_once '../include/footer.php';
        exit();
    } // End GET modify
    elseif ($_GET['action'] == "remove" && isset($id) && is_numeric($id)) {
        // Remove product, all pictures and the pictures directory!
        if (file_exists("../img/" . $id)) {
            rmdir_recursive("../img/" . $id);
        }
        $sql = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $sql->bindParam(':id', $id);
        $result = $sql->execute();
        if ($result) {
            require_once '../include/header.php';
            require_once '../include/navigation.php';
            echo "<div class=\"container\">" . PHP_EOL;
            echo "  <div class=\"alert alert-success\" role=\"alert\">" . PHP_EOL;
            echo "    <strong>Success!</strong> The product is successfully removed!" . PHP_EOL;
            echo "  </div>" . PHP_EOL;
            echo "</div>" . PHP_EOL;
            require_once '../include/footer.php';
            exit();
        }
        else {
            require_once '../include/header.php';
            require_once '../include/navigation.php';
            echo "  <div class=\"container\">" . PHP_EOL;
            echo "    <div class=\"alert alert-info\" role=\"alert\">" . PHP_EOL;
            echo "        This product doesn't exist!" . PHP_EOL;
            echo "    </div>" . PHP_EOL;
            echo "  </div>" . PHP_EOL;
            require_once '../include/footer.php';
            exit();
        }
    } // End GET remove
    elseif ($_GET['action'] == "remove-picture" && isset($id, $_GET['picture'])) {
        // Remove picture from the server and from the database
        if (file_exists($_GET['picture'])) {
            $delete = unlink($_GET['picture']);
            if (!$delete) { 
                die ("Something went wrong with deleting" . $_GET['picture'] . " with id: $id"); 
            }
        }
        $sql = $pdo->prepare("SELECT * FROM products WHERE id = :id");
        $sql->bindParam(':id', $id);
        $result = $sql->execute();
        if ($result) {
            if ($row = $sql->fetch(PDO::FETCH_BOTH)) {
                $pictures = unserialize($row['picture']);
                $key = array_search($_GET['picture'], $pictures);
                unset($pictures[$key]);
                if (!empty($pictures)) {
                    $pictures = serialize($pictures);
                    $sql = $pdo->prepare("UPDATE products SET pictures = :pictures WHERE id = :id");
                    $sql->bindParam(':pictures', $pictures);
                    $sql->bindParam(':id', $id);
                    $result = $sql->execute();
                    if ($result) {
                        header('Location: ' . $_SERVER['PHP_SELF'] . '?action=modify&id=' . $id);
                        exit();
                    }
                    elseif (!$result) {
                        die(var_dump($sql->errorInfo()));
                    }
                }
                elseif (empty($pictures)) {
                    $sql = $pdo->prepare("UPDATE products SET pictures = '' WHERE id = :id");
                    $sql->bindParam(':id', $id);
                    $result = $sql->execute();
                    if ($result) {
                        header('Location: ' . $_SERVER['PHP_SELF'] . '?action=modify&id=' . $id);
                        exit();
                    }
                    elseif (!$result) {
                        die(var_dump($sql->errorInfo()));
                    }
                }
            }
        }
        elseif (!$result) {
            vdie(var_dump($sql->errorInfo()));
        }
        else {
            die(var_dump($row));
        }
    } // End GET remove-picture
    else {
        require_once '../include/header.php';
        require_once '../include/navigation.php';
        echo "<div class=\"container\">" . PHP_EOL;
        echo "  <div class=\"alert alert-warning\" role=\"alert\">" . PHP_EOL;
        echo "    <strong>Fault!</strong> This action doesn't exist or wrong <strong>id</strong>!" . PHP_EOL;
        echo "  </div>" . PHP_EOL;
        echo "</div>" . PHP_EOL;
    }
}
elseif ($_SESSION['admin'] == "1" && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST)) {
        exit(var_dump($_REQUEST));
    }
    elseif ($_POST['action'] == "add") {
        if (isset($_POST['product']) && !empty($_POST['product'])) {
            $product = $_POST['product'];
        }
        else {
            require_once '../include/header.php';
            require_once '../include/navigation.php';
            echo "<div class=\"container\">" . PHP_EOL;
            echo "  <div class=\"alert alert-warning\" role=\"alert\">" . PHP_EOL;
            echo "    <strong>Fault!</strong> Name has not been entered!" . PHP_EOL;
            echo "  </div>" . PHP_EOL;
            echo "</div>" . PHP_EOL;
            require_once '../include/footer.php';
            exit();
        }
        if (isset($_POST['description']) && !empty($_POST['description'])) {
            $description = $_POST['description'];
        }
        else {
            $description = "";
        }
        if (isset($_POST['category']) && !empty($_POST['category']) && is_numeric($_POST['category'])) {
            $category = $_POST['category'];
        }
        else {
            require_once '../include/header.php';
            require_once '../include/navigation.php';
            echo "<div class=\"container\">" . PHP_EOL;
            echo "  <div class=\"alert alert-warning\" role=\"alert\">" . PHP_EOL;
            echo "    <strong>Fault!</strong> Category has not been entered!" . PHP_EOL;
            echo "  </div>" . PHP_EOL;
            echo "</div>" . PHP_EOL;
            require_once '../include/footer.php';
            exit();
        }
        if (isset($_POST['stock']) && !empty($_POST['stock']) && is_numeric($_POST['stock'])){
            $stock = $_POST['stock'];
        }
        else {
            require_once '../include/header.php';
            require_once '../include/navigation.php';
            echo "<div class=\"container\">" . PHP_EOL;
            echo "  <div class=\"alert alert-warning\" role=\"alert\">" . PHP_EOL;
            echo "    <strong>Fault!</strong> Stock has not been entered or is not a number!" . PHP_EOL;
            echo "  </div>" . PHP_EOL;
            echo "</div>" . PHP_EOL;
            require_once '../include/footer.php';
            exit();
        }
        if (isset($_POST['price']) && !empty($_POST['price']) && is_numeric($_POST['price'])){
            $price = $_POST['price'];
        }
        else {
            require_once 'include/header.php';
            require_once 'include/navigation.php';
            echo "<div class=\"container\">" . PHP_EOL;
            echo "  <div class=\"alert alert-warning\" role=\"alert\">" . PHP_EOL;
            echo "    <strong>Fault!</strong> Price has not been entered or is not a number!" . PHP_EOL;
            echo "  </div>" . PHP_EOL;
            echo "</div>" . PHP_EOL;
            require_once 'include/footer.php';
            exit();
        }
        $sql = $pdo->prepare("INSERT INTO products (id, product, description, pparent_id, stock, price, pictures) VALUES (NULL, :product, :description, :category, :stock, :price, '')");
        $sql->bindParam(':product', $product);
        $sql->bindParam(':description', $description);
        $sql->bindParam(':category', $category);
        $sql->bindParam(':stock', $stock);
        $sql->bindParam(':price', $price);
        $result = $sql->execute();
        if ($result) {
            $id = $pdo->lastInsertId();
            if (isset($_FILES['pictures']) && !empty($_FILES['pictures'])) {
                $pictures = [];
                foreach($_FILES['pictures']['name'] as $key => $value) {
                    if ($_FILES['pictures']['error'][$key] == 4) {
                        //No File
                    }
                    elseif ($_FILES['pictures']['error'][$key] != 0) {
                        die("Error while uploading. Error code: " . $_FILES['pictures']['error'][$key]);
                    }
                    else {
                        //Test if it's a real picture
                        $test = getimagesize($_FILES['pictures']['tmp_name'][$key]);
                        if (!$test) {
                            die($_FILES['pictures']['name'][$key] . " is not a picture!");
                        }
                        if (!file_exists("../img/" . $id)) {
                            mkdir("../img/" . $id);
                        }
                        array_push($pictures, "../img/" . $id . "/" . basename($_FILES["pictures"]["name"][$key]));
                        move_uploaded_file($_FILES['pictures']['tmp_name'][$key], "../img/" . $id . "/" . basename($_FILES["pictures"]["name"][$key]));
                    }
                }
                $pictures = serialize($pictures);
                $sql = $pdo->prepare("UPDATE products SET pictures = :pictures WHERE id = :id");
                $sql->bindParam(':id', $id);
                $sql->bindParam(':pictures', $pictures);
                $result = $sql->execute();
                if (!$result) {
                    die("Something went wrong!");
                }
            }
            header('Location: ../products.php?id=' . $id);
            exit();
        } // End $result
    } //End add
    elseif ($_POST['action'] == "modify" && isset($_GET['id']) && is_numeric($_GET['id'])) {
        //The form has been filled, we gonna modify the database
        $id = $_GET['id'];
        if (isset($_POST['product']) && !empty($_POST['product'])) {
            $product = $_POST['product'];
        }
        else {
            require_once '../include/header.php';
            require_once '../include/navigation.php';
            echo "<div class=\"container\">" . PHP_EOL;
            echo "  <div class=\"alert alert-warning\" role=\"alert\">" . PHP_EOL;
            echo "    <strong>Fault!</strong> Name has not been entered!" . PHP_EOL;
            echo "  </div>" . PHP_EOL;
            echo "</div>" . PHP_EOL;
            require_once '../include/footer.php';
            exit();
        }
        if (isset($_POST['description']) && !empty($_POST['description'])) {
            $description = $_POST['description'];
        }
        else {
            $description = "";
        }
        if (isset($_POST['category']) && !empty($_POST['category'])){
            $category = $_POST['category'];
        }
        else {
            require_once '../include/header.php';
            require_once '../include/navigation.php';
            echo "<div class=\"container\">" . PHP_EOL;
            echo "  <div class=\"alert alert-warning\" role=\"alert\">" . PHP_EOL;
            echo "    <strong>Fault!</strong> Category has not been entered!" . PHP_EOL;
            echo "  </div>" . PHP_EOL;
            echo "</div>" . PHP_EOL;
            require_once '../include/footer.php';
            exit();
        }
        if (isset($_POST['stock']) && !empty($_POST['stock']) && is_numeric($_POST['stock'])){
            $stock = $_POST['stock'];
        }
        else {
            require_once '../include/header.php';
            require_once '../include/navigation.php';
            echo "<div class=\"container\">" . PHP_EOL;
            echo "  <div class=\"alert alert-warning\" role=\"alert\">" . PHP_EOL;
            echo "    <strong>Fault!</strong> Stock has not been entered or is not a number!" . PHP_EOL;
            echo "  </div>" . PHP_EOL;
            echo "</div>" . PHP_EOL;
            require_once '../include/footer.php';
            exit();
        }
        if (isset($_POST['price']) && !empty($_POST['price']) && is_numeric($_POST['price'])){
            $price = $_POST['price'];
        }
        else {
            require_once '../include/header.php';
            require_once '../include/navigation.php';
            echo "<div class=\"container\">" . PHP_EOL;
            echo "  <div class=\"alert alert-warning\" role=\"alert\">" . PHP_EOL;
            echo "    <strong>Fault!</strong> Price has not been entered or is not a number!" . PHP_EOL;
            echo "  </div>" . PHP_EOL;
            echo "</div>" . PHP_EOL;
            require_once '../include/footer.php';
            exit();
        }
        $sql = $pdo->prepare("UPDATE products SET product = :product, description = :description, pparent_id = :category, stock = :stock, price = :price WHERE id = :id");
        $sql->bindParam(':product', $product);
        $sql->bindParam(':description', $description);
        $sql->bindParam(':category', $category);
        $sql->bindParam(':stock', $stock);
        $sql->bindParam(':price', $price);
        $sql->bindParam(':id', $id);
        $result = $sql->execute();
        if ($result) {
            if (isset($_FILES['pictures']) && !empty($_FILES['pictures']) && $_FILES['pictures']['error']['0'] != 4) {
                $sql = $pdo->prepare("SELECT * FROM products WHERE id = :id");
                $sql->bindParam(':id', $id);
                $result = $sql->execute();
                if ($result) {
                    if ($row = $sql->fetch()) {
                        if (empty($row['pictures'])) {
                            $pictures = [];
                        }
                        else {
                            $pictures = unserialize($row['pictures']);
                        }
                    }
                }
                elseif (!$result) {
                    die(var_dump($sql->errorInfo()));
                }
                foreach($_FILES['pictures']['name'] as $key => $value) {
                    if ($_FILES['pictures']['error'][$key] == 4) {
                        //No file
                    }
                    elseif ($_FILES['pictures']['error'][$key] != 0) {
                        die("Error while uploading. File: " . $_FILES['pictures']['name'][$key] .  " Error code: " . $_FILES['pictures']['error'][$key]);
                    }
                    else {
                        //Test if it's a real picture
                        $test = getimagesize($_FILES['pictures']['tmp_name'][$key]);
                        if (!$test) {
                            die($_FILES['pictures']['name'][$key] . " is not a picture!");
                        }
                        if (!file_exists("../img/" . $id)) {
                            mkdir("../img/" . $id);
                        }
                        array_push($pictures, "../img/" . $id . "/" . basename($_FILES["pictures"]["name"][$key]));
                        move_uploaded_file($_FILES['pictures']['tmp_name'][$key], "../img/" . $id . "/" . basename($_FILES["pictures"]["name"][$key]));
                    }
                }
                $pictures = serialize($pictures);
                $sql = $pdo->prepare("UPDATE products SET pictures = :pictures WHERE id = :id");
                $sql->bindParam(':id', $id);
                $sql->bindParam(':pictures', $pictures);
                $result = $sql->execute();
                if (!$result) {
                    die(var_dump($sql->errorInfo()));
                }
            }
        }
        elseif (!$result) {
            die(var_dump($sql->errorInfo()));
        }
        header('Location: ../products.php?id=' . $id);
        exit();
    } //Einde wijzigen
    else {
        var_dump($_GET);
        var_dump($_POST);
        //header("Location: ../index.php");
        exit();
    }
}
else {
    header("Location: ../index.php");
    exit();
}