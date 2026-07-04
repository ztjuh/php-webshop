<?php
require_once 'include/config.php';
require_once 'include/session.php';
require_once 'include/database.php';
require_once 'include/header.php';
require_once 'include/navigation.php';

$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($product_id) {
    // Single product view
    $product = getProductById($product_id);
    
    if (!$product) {
        header('Location: products.php');
        exit();
    }
    
    echo "<div class='container mt-5'>";
    echo "  <div class='row'>";
    echo "    <div class='col-md-4'>";
    if ($product['image_url']) {
        echo "      <img src='" . htmlspecialchars($product['image_url']) . "' class='img-fluid' alt='" . htmlspecialchars($product['product']) . "'>";
    } else {
        echo "      <div class='bg-light' style='height: 300px; display: flex; align-items: center; justify-content: center;'>";
        echo "        <span class='text-muted'>No image available</span>";
        echo "      </div>";
    }
    echo "    </div>";
    
    echo "    <div class='col-md-8'>";
    echo "      <h2>" . htmlspecialchars($product['product']) . "</h2>";
    echo "      <p class='text-muted'>" . htmlspecialchars($product['category']) . "</p>";
    echo "      <h3>€ " . number_format($product['price'], 2, ',', '.') . "</h3>";
    echo "      <p>" . nl2br(htmlspecialchars($product['description'])) . "</p>";
    
    if ($product['stock'] > 0) {
        echo "      <form action='cart.php' method='GET'>";
        echo "        <input type='hidden' name='id' value='" . $product['id'] . "'>";
        echo "        <input type='hidden' name='action' value='add'>";
        echo "        <div class='form-group'>";
        echo "          <label>Quantity:</label>";
        echo "          <input type='number' name='amount' value='1' min='1' max='" . $product['stock'] . "' class='form-control' style='width: 100px;'>";
        echo "        </div>";
        echo "        <button type='submit' class='btn btn-success btn-lg'>Add to Cart</button>";
        echo "      </form>";
        echo "      <p class='text-success mt-2'>" . $product['stock'] . " in stock</p>";
    } else {
        echo "      <p class='text-danger'><strong>Out of stock</strong></p>";
    }
    
    echo "      <a href='products.php' class='btn btn-outline-secondary mt-3'>Back to Products</a>";
    echo "    </div>";
    echo "  </div>";
    echo "</div>";
} 
else {
    // Products listing
    $products = getProducts();
    
    echo "<div class='container mt-5'>";
    echo "  <h2>Our Products</h2>";
    echo "  <div class='row mt-4'>";
    
    if (empty($products)) {
        echo "    <div class='col-12'>";
        echo "      <p>No products available.</p>";
        echo "    </div>";
    } else {
        foreach ($products as $product) {
            echo "    <div class='col-md-4 mb-4'>";
            echo "      <div class='card h-100'>";
            
            if ($product['image_url']) {
                echo "        <img src='" . htmlspecialchars($product['image_url']) . "' class='card-img-top' alt='" . htmlspecialchars($product['product']) . "'>";
            } else {
                echo "        <div class='card-img-top bg-light' style='height: 200px; display: flex; align-items: center; justify-content: center;'>";
                echo "          <span class='text-muted'>No image</span>";
                echo "        </div>";
            }
            
            echo "        <div class='card-body'>";
            echo "          <h5 class='card-title'><a href='products.php?id=" . $product['id'] . "'>" . htmlspecialchars($product['product']) . "</a></h5>";
            echo "          <p class='card-text'>" . htmlspecialchars(substr($product['description'], 0, 100)) . "...</p>";
            echo "          <p class='text-muted'>" . htmlspecialchars($product['category']) . "</p>";
            echo "          <h5>€ " . number_format($product['price'], 2, ',', '.') . "</h5>";
            
            if ($product['stock'] > 0) {
                echo "          <a href='products.php?id=" . $product['id'] . "' class='btn btn-primary'>View Details</a>";
                echo "          <a href='cart.php?id=" . $product['id'] . "&action=add' class='btn btn-success'>Add to Cart</a>";
            } else {
                echo "          <button class='btn btn-secondary' disabled>Out of Stock</button>";
            }
            
            echo "        </div>";
            echo "      </div>";
            echo "    </div>";
        }
    }
    
    echo "  </div>";
    echo "</div>";
}

require_once 'include/footer.php';
?>
