<?php
require_once('../auth_helper.php');
requireAdminAuth();
require_once("../../includes/db.php");

// Get product data
if (!isset($_GET['id'])) {
    echo "No product ID!";
    exit;
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM inventory WHERE id = $id";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    die("Product not found!");
}

$product = $result->fetch_assoc();
?>

<!-- Modal Overlay -->
<div class="modal-overlay" id="editModal">
  <div class="modal-popup">
    <h2>Edit Product</h2>
    <form action="inventory_update.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?= $product['id']; ?>">

      <table class="form-table">
        <tr>
          <td><label>Name:</label></td>
          <td><input type="text" name="product_name" value="<?= htmlspecialchars($product['product_name']); ?>" required></td>
        </tr>
        <tr>
          <td><label>Price (₱):</label></td>
          <td><input type="number" step="0.01" name="price" value="<?= $product['price']; ?>" required></td>
        </tr>
        <tr>
          <td><label>Stock:</label></td>
          <td><input type="number" name="stock" value="<?= $product['stock']; ?>" required></td>
        </tr>
        <tr>
          <td><label>Tag:</label></td>
          <td><input type="text" name="tag" value="<?= htmlspecialchars($product['tag']); ?>"></td>
        </tr>
        <tr>
          <td>Current Image:</td>
          <td>
            <?php if ($product['image']): ?>
              <img src="../<?= $product['image']; ?>" width="80"><br>
            <?php endif; ?>
            <input type="file" name="image" accept="image/*">
          </td>
        </tr>
      </table>

      <div class="modal-actions">
        <button type="submit" class="save-btn">Update</button>
        <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>
