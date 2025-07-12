<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // default user for now
}

$user_id = (int) $_SESSION['user_id'];

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $type = $_POST['type'];
    $size = $_POST['size'];
    $condition = $_POST['condition'];
    $tags = array_map('trim', explode(',', $_POST['tags']));

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO items (user_id, title, description, category, type, size, `condition`) VALUES (:uid, :title, :desc, :cat, :type, :size, :cond)");
        $stmt->execute([
            'uid' => $user_id,
            'title' => $title,
            'desc' => $description,
            'cat' => $category,
            'type' => $type,
            'size' => $size,
            'cond' => $condition
        ]);

        $item_id = $pdo->lastInsertId();

        // Simulate placeholder images (since uploads are disabled)
        $placeholders = [
            "https://via.placeholder.com/150?text=Preview+1",
            "https://via.placeholder.com/150?text=Preview+2",
            "https://via.placeholder.com/150?text=Preview+3"
        ];

        foreach ($placeholders as $i => $url) {
            $stmt = $pdo->prepare("INSERT INTO item_images (item_id, image_url, is_primary) VALUES (?, ?, ?)");
            $stmt->execute([$item_id, $url, $i === 0 ? 1 : 0]);
        }

        foreach ($tags as $tag) {
            if ($tag === '') continue;
            $pdo->prepare("INSERT IGNORE INTO tags (name) VALUES (?)")->execute([$tag]);
            $tag_id = $pdo->query("SELECT id FROM tags WHERE name = " . $pdo->quote($tag))->fetchColumn();
            $pdo->prepare("INSERT INTO item_tags (item_id, tag_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE item_id = item_id")->execute([$item_id, $tag_id]);
        }

        $pdo->commit();
        $success = true;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add New Item</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Manrope', sans-serif;
      background-color: #f6f4fd;
    }
    .container {
      max-width: 800px;
      margin: 50px auto;
      background: #fff;
      padding: 2rem;
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.07);
    }
    .form-label {
      font-weight: 600;
    }
    .form-control, .form-select {
      border-radius: 10px;
    }
    .alert-success {
      background-color: #e7f9ed;
      color: #146c43;
    }
    .alert-danger {
      background-color: #fde8e8;
      color: #b02a37;
    }
  </style>
</head>
<body>
<div class="container">
  <h2 class="text-center text-primary mb-4">🧺 Add New Clothing Item</h2>

  <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      Item submitted successfully!
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($error) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Title</label>
      <input type="text" class="form-control" name="title" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea class="form-control" name="description" rows="3" required></textarea>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Category</label>
        <select name="category" class="form-select" required>
          <option value="">-- Select --</option>
          <option value="T-Shirts">T-Shirts</option>
          <option value="Jackets">Jackets</option>
          <option value="Jumpsuits">Jumpsuits</option>
          <option value="Ethnic Wear">Ethnic Wear</option>
        </select>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Type</label>
        <select name="type" class="form-select" required>
          <option value="Men">Men</option>
          <option value="Women">Women</option>
          <option value="Unisex">Unisex</option>
        </select>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Size</label>
        <input type="text" class="form-control" name="size" placeholder="e.g., M, L, XL" required>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Condition</label>
        <select name="condition" class="form-select" required>
          <option value="Like New">Like New</option>
          <option value="Gently Used">Gently Used</option>
          <option value="Used">Used</option>
        </select>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Tags (comma-separated)</label>
      <input type="text" class="form-control" name="tags" placeholder="e.g., casual, summer, cotton">
    </div>

    <div class="mb-3">
      <label class="form-label">Images (not functional now)</label>
      <input type="file" class="form-control" disabled>
      <div class="form-text">Uploads disabled — using placeholder images for now.</div>
    </div>

    <button type="submit" class="btn btn-primary w-100">Submit Item</button>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
