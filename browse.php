<?php
// File: browse.php
require 'db.php'; // Assumes db.php exists and establishes $pdo connection
session_start();

// Default user ID if not set in session
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}

// --- Configuration for pagination ---
$items_per_page = 12; // Number of items to display per load
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

// --- Filtering and Sorting Parameters ---
$selected_category = isset($_GET['category']) ? $_GET['category'] : 'All';
$search_query = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'created_at'; // Default sort by creation date
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC'; // Default sort order

// Validate sort_by to prevent SQL injection
$allowed_sort_by = ['created_at', 'title', 'price', 'condition'];
if (!in_array($sort_by, $allowed_sort_by)) {
    $sort_by = 'created_at';
}

// Validate sort_order
$allowed_sort_order = ['ASC', 'DESC'];
if (!in_array(strtoupper($sort_order), $allowed_sort_order)) {
    $sort_order = 'DESC';
}

// --- Fetch Categories ---
try {
    $stmt_categories = $pdo->query("SELECT DISTINCT category FROM items ORDER BY category ASC");
    $categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    $categories = []; // Fallback to empty array on error
}


// --- Build SQL Query for Items ---
$sql = "SELECT i.*, ii.image_url FROM items i
        LEFT JOIN item_images ii ON i.id = ii.item_id AND ii.is_primary = 1";
$conditions = [];
$params = [];

// Category filter
if ($selected_category !== 'All' && !empty($selected_category)) {
    $conditions[] = "i.category = :category";
    $params[':category'] = $selected_category;
}

// Search query filter
if (!empty($search_query)) {
    $conditions[] = "(i.title LIKE :search_query OR i.description LIKE :search_query)";
    $params[':search_query'] = '%' . $search_query . '%';
}

// Append conditions to SQL
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

// Add sorting
$sql .= " ORDER BY " . $sort_by . " " . $sort_order;

// Add pagination limit and offset
$sql .= " LIMIT :limit OFFSET :offset";
$params[':limit'] = $items_per_page;
$params[':offset'] = $offset;


// --- Fetch Items ---
try {
    $stmt_items = $pdo->prepare($sql);
    foreach ($params as $key => &$val) {
        // Bind parameters dynamically, ensuring integer types are bound correctly
        if (in_array($key, [':limit', ':offset'])) {
            $stmt_items->bindParam($key, $val, PDO::PARAM_INT);
        } else {
            $stmt_items->bindParam($key, $val);
        }
    }
    $stmt_items->execute();
    $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching items: " . $e->getMessage());
    $items = []; // Fallback to empty array on error
}

// --- Check if it's an AJAX request ---
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($items);
    exit(); // Terminate script after sending JSON response
}

// --- HTML Output for initial page load ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Browse Items | Rewear</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* Base styles from original */
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f4f5f9;
      color: #333;
    }
    .header {
      background: #7c3aed;
      color: white;
      padding: 1rem 2rem;
      border-radius: 0 0 20px 20px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.1);
      margin-bottom: 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }
    .header h2 {
      margin: 0;
      font-size: 1.6rem;
    }
    .header .user-info {
        font-size: 0.9rem;
        opacity: 0.8;
    }

    /* Category Filter */
    .category-filter {
      overflow-x: auto;
      white-space: nowrap;
      padding: 1rem 0;
      -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
      scrollbar-width: none; /* Firefox */
      -ms-overflow-style: none;  /* IE and Edge */
      margin-bottom: 1rem;
    }
    .category-filter::-webkit-scrollbar { /* Chrome, Safari, Opera */
        display: none;
    }
    .category-btn {
      display: inline-flex; /* Use flex for icon and text alignment */
      align-items: center;
      gap: 5px; /* Space between icon and text */
      margin-right: 10px;
      background-color: #fff;
      border: 1px solid #ddd;
      padding: 8px 20px;
      border-radius: 50px;
      font-size: 0.9rem;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none; /* Ensure it looks like a button, not a link */
      color: #333;
    }
    .category-btn:hover, .category-btn.active {
      background-color: #e6e6f7;
      border-color: #7c3aed;
      color: #7c3aed;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .category-btn.active {
        font-weight: bold;
    }

    /* Item Card */
    .item-card {
      border: none;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      background-color: #fff;
      display: flex;
      flex-direction: column;
      height: 100%; /* Ensure cards in a row have equal height */
    }
    .item-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    .item-img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-bottom: 1px solid #eee;
    }
    .item-details {
      padding: 1rem;
      flex-grow: 1; /* Allow details section to expand */
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .item-details h5 {
      font-size: 1.2rem;
      margin-bottom: 0.5rem;
      color: #444;
    }
    .item-details p {
      font-size: 0.9rem;
      color: #666;
      margin-bottom: 0.5rem;
    }
    .item-details .price {
        font-size: 1.1rem;
        font-weight: bold;
        color: #7c3aed;
        margin-top: 0.5rem;
    }
    .item-actions {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-top: 1rem;
    }
    .btn-swap, .btn-cart {
      flex: 1; /* Distribute space evenly */
      padding: 10px 15px;
      border-radius: 8px;
      font-size: 0.9rem;
      font-weight: 500;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
    }
    .btn-cart {
      background-color: #fff;
      color: #7c3aed;
      border: 1px solid #7c3aed;
    }
    .btn-cart:hover {
      background-color: #7c3aed;
      color: #fff;
      box-shadow: 0 4px 10px rgba(124, 58, 237, 0.3);
    }
    .btn-swap {
      background-color: #7c3aed;
      color: #fff;
      border: 1px solid #7c3aed;
    }
    .btn-swap:hover {
      background-color: #6d28d9;
      color: #fff;
      box-shadow: 0 4px 10px rgba(124, 58, 237, 0.5);
    }
    .favorite-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: rgba(255,255,255,0.8);
        border: none;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        color: #ccc;
        cursor: pointer;
        transition: all 0.2s ease;
        z-index: 10;
    }
    .favorite-btn:hover {
        background-color: rgba(255,255,255,1);
        color: #ff6347; /* Tomato red */
        transform: scale(1.1);
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    .favorite-btn.active {
        color: #ff6347;
    }

    /* Search and Sort Controls */
    .controls-bar {
        background-color: #fff;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: center;
    }
    .controls-bar .form-control, .controls-bar .form-select {
        border-radius: 8px;
        border: 1px solid #ddd;
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
    }
    .controls-bar .form-control:focus, .controls-bar .form-select:focus {
        border-color: #7c3aed;
        box-shadow: 0 0 0 0.25rem rgba(124, 58, 237, 0.25);
    }
    .search-input-group {
        flex-grow: 1;
        min-width: 200px;
    }
    .sort-dropdown-group {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    /* Load More Button */
    .load-more-container {
        text-align: center;
        margin: 3rem 0;
    }
    .btn-load-more {
        background-color: #7c3aed;
        color: #fff;
        border: none;
        padding: 12px 30px;
        border-radius: 50px;
        font-size: 1.1rem;
        font-weight: 600;
        transition: background-color 0.3s ease, transform 0.2s ease;
        box-shadow: 0 4px 15px rgba(124, 58, 237, 0.4);
    }
    .btn-load-more:hover {
        background-color: #6d28d9;
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(124, 58, 237, 0.6);
    }
    .btn-load-more:disabled {
        background-color: #ccc;
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }

    /* Modals (Item Details & Message Box) */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    .modal-overlay.show {
        opacity: 1;
        visibility: visible;
    }
    .modal-content {
        background-color: #fff;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        width: 90%;
        max-width: 700px;
        transform: translateY(-50px);
        transition: transform 0.3s ease;
        position: relative;
        max-height: 90vh; /* Limit height for scrollable content */
        overflow-y: auto; /* Enable scrolling for long content */
    }
    .modal-overlay.show .modal-content {
        transform: translateY(0);
    }
    .modal-close-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #888;
        cursor: pointer;
        transition: color 0.2s ease;
    }
    .modal-close-btn:hover {
        color: #333;
    }
    .modal-img {
        width: 100%;
        max-height: 400px;
        object-fit: contain; /* Use contain to show full image, not cover */
        border-radius: 8px;
        margin-bottom: 1.5rem;
        background-color: #f0f0f0; /* Placeholder background */
    }
    .modal-title {
        font-size: 2rem;
        color: #333;
        margin-bottom: 0.5rem;
    }
    .modal-meta {
        font-size: 1rem;
        color: #777;
        margin-bottom: 1rem;
    }
    .modal-description {
        font-size: 1.1rem;
        line-height: 1.6;
        color: #555;
        margin-bottom: 1.5rem;
    }
    .modal-price {
        font-size: 1.5rem;
        font-weight: bold;
        color: #7c3aed;
        margin-bottom: 1.5rem;
    }
    .modal-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
    }
    .modal-actions .btn {
        flex: 1;
        max-width: 200px;
    }

    /* Custom Message Box */
    .message-box-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1001; /* Higher than item modal */
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease, visibility 0.2s ease;
    }
    .message-box-overlay.show {
        opacity: 1;
        visibility: visible;
    }
    .message-box-content {
        background-color: #fff;
        padding: 2rem;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        text-align: center;
        max-width: 400px;
        transform: scale(0.8);
        transition: transform 0.2s ease;
    }
    .message-box-overlay.show .message-box-content {
        transform: scale(1);
    }
    .message-box-content h4 {
        margin-bottom: 1rem;
        color: #333;
    }
    .message-box-content p {
        margin-bottom: 1.5rem;
        color: #555;
    }
    .message-box-content .btn {
        background-color: #7c3aed;
        color: #fff;
        border: none;
        padding: 10px 25px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    .message-box-content .btn:hover {
        background-color: #6d28d9;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .header {
            flex-direction: column;
            padding: 1rem;
            text-align: center;
        }
        .header h2 {
            margin-bottom: 0.5rem;
        }
        .controls-bar {
            flex-direction: column;
            align-items: stretch;
            padding: 1rem;
        }
        .search-input-group, .sort-dropdown-group {
            width: 100%;
            flex-direction: column;
            gap: 0.5rem;
        }
        .sort-dropdown-group select {
            width: 100%;
        }
        .item-actions {
            flex-direction: column;
            gap: 8px;
        }
        .btn-swap, .btn-cart {
            width: 100%;
        }
        .modal-content {
            padding: 1.5rem;
        }
        .modal-title {
            font-size: 1.5rem;
        }
        .modal-meta {
            font-size: 0.9rem;
        }
        .modal-description {
            font-size: 1rem;
        }
        .modal-price {
            font-size: 1.2rem;
        }
        .modal-actions {
            flex-direction: column;
            gap: 10px;
        }
    }
  </style>
</head>
<body>
  <div class="header">
    <h2>🔄 Browse Items to Swap or Buy</h2>
    <div class="user-info">
        Logged in as User ID: <?php echo htmlspecialchars($_SESSION['user_id']); ?>
    </div>
  </div>

  <div class="container mt-4">
    <!-- Search and Sort Controls -->
    <div class="controls-bar">
      <div class="input-group search-input-group">
        <input type="text" id="searchQuery" class="form-control" placeholder="Search by title or description..." value="<?php echo htmlspecialchars($search_query); ?>">
        <button class="btn btn-primary" type="button" onclick="performSearch()">
            <i class="fas fa-search"></i> Search
        </button>
      </div>

      <div class="sort-dropdown-group">
        <label for="sortBy" class="form-label mb-0">Sort By:</label>
        <select id="sortBy" class="form-select" onchange="applySort()">
          <option value="created_at" <?php echo ($sort_by === 'created_at' ? 'selected' : ''); ?>>Date Posted</option>
          <option value="price" <?php echo ($sort_by === 'price' ? 'selected' : ''); ?>>Price</option>
          <option value="title" <?php echo ($sort_by === 'title' ? 'selected' : ''); ?>>Title</option>
          <option value="condition" <?php echo ($sort_by === 'condition' ? 'selected' : ''); ?>>Condition</option>
        </select>

        <label for="sortOrder" class="form-label mb-0">Order:</label>
        <select id="sortOrder" class="form-select" onchange="applySort()">
          <option value="DESC" <?php echo ($sort_order === 'DESC' ? 'selected' : ''); ?>>Descending</option>
          <option value="ASC" <?php echo ($sort_order === 'ASC' ? 'selected' : ''); ?>>Ascending</option>
        </select>
      </div>
    </div>

    <!-- Category Filter -->
    <div class="category-filter">
      <button class="category-btn <?php echo ($selected_category === 'All' ? 'active' : ''); ?>" onclick="filterCategory('All')">
        <i class="fas fa-th-large"></i> All
      </button>
      <?php foreach ($categories as $cat): ?>
        <button class="category-btn <?php echo ($selected_category === $cat['category'] ? 'active' : ''); ?>" onclick="filterCategory('<?php echo htmlspecialchars($cat['category']); ?>')">
          <i class="fas fa-tag"></i> <?php echo htmlspecialchars($cat['category']); ?>
        </button>
      <?php endforeach; ?>
    </div>

    <!-- Item Grid -->
    <div class="row" id="itemGrid">
      <?php if (empty($items)): ?>
        <div class="col-12 text-center py-5">
          <p class="text-muted fs-4">No items found matching your criteria.</p>
          <i class="fas fa-box-open fa-3x text-muted"></i>
        </div>
      <?php else: ?>
        <?php foreach ($items as $item): ?>
          <div class="col-md-6 col-lg-4 mb-4 item" data-category="<?php echo htmlspecialchars($item['category']); ?>" data-item-id="<?php echo htmlspecialchars($item['id']); ?>">
            <div class="card item-card h-100 position-relative">
              <button class="favorite-btn" data-item-id="<?php echo htmlspecialchars($item['id']); ?>" onclick="toggleFavorite(event, <?php echo htmlspecialchars($item['id']); ?>)">
                  <i class="far fa-heart"></i>
              </button>
              <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'https://placehold.co/400x300/e0e0e0/555555?text=No+Image'); ?>" class="item-img" alt="<?php echo htmlspecialchars($item['title']); ?>" onerror="this.onerror=null;this.src='https://placehold.co/400x300/e0e0e0/555555?text=No+Image';">
              <div class="item-details">
                <div>
                    <h5 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                    <p class="text-muted mb-1">Size: <?php echo htmlspecialchars($item['size']); ?> • Condition: <?php echo htmlspecialchars($item['condition']); ?></p>
                    <p class="card-text text-truncate"><?php echo htmlspecialchars($item['description']); ?></p>
                    <p class="price">$<?php echo number_format($item['price'], 2); ?></p>
                </div>
                <div class="item-actions">
                  <button class="btn btn-outline-primary btn-cart" onclick="addToCart(<?php echo htmlspecialchars($item['id']); ?>)">
                    <i class="fas fa-shopping-cart"></i> Add to Cart
                  </button>
                  <button class="btn btn-primary btn-swap" onclick="buyNow(<?php echo htmlspecialchars($item['id']); ?>)">
                    <i class="fas fa-bolt"></i> Swap / Buy Now
                  </button>
                </div>
              </div>
              <a href="#" class="stretched-link" onclick="showItemDetails(event, <?php echo htmlspecialchars(json_encode($item)); ?>)"></a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Load More Button -->
    <div class="load-more-container" id="loadMoreContainer">
      <button class="btn btn-load-more" id="loadMoreBtn" <?php echo (count($items) < $items_per_page ? 'disabled' : ''); ?>>
        <i class="fas fa-sync-alt"></i> Load More
      </button>
    </div>
  </div>

  <!-- Item Detail Modal -->
  <div class="modal-overlay" id="itemDetailModal">
    <div class="modal-content">
      <button class="modal-close-btn" onclick="closeModal('itemDetailModal')">&times;</button>
      <img src="" class="modal-img" id="modalItemImage" alt="Item Image">
      <h3 class="modal-title" id="modalItemTitle"></h3>
      <p class="modal-meta">Size: <span id="modalItemSize"></span> • Condition: <span id="modalItemCondition"></span></p>
      <p class="modal-description" id="modalItemDescription"></p>
      <p class="modal-price" id="modalItemPrice"></p>
      <div class="modal-actions">
        <button class="btn btn-outline-primary btn-cart" id="modalAddToCartBtn">
          <i class="fas fa-shopping-cart"></i> Add to Cart
        </button>
        <button class="btn btn-primary btn-swap" id="modalBuyNowBtn">
          <i class="fas fa-bolt"></i> Swap / Buy Now
        </button>
      </div>
    </div>
  </div>

  <!-- Custom Message Box Modal -->
  <div class="message-box-overlay" id="messageBoxModal">
      <div class="message-box-content">
          <h4 id="messageBoxTitle"></h4>
          <p id="messageBoxText"></p>
          <button class="btn" onclick="closeModal('messageBoxModal')">OK</button>
      </div>
  </div>

  <script>
    // Global state variables
    let currentOffset = <?php echo $offset + count($items); ?>;
    const itemsPerPage = <?php echo $items_per_page; ?>;
    let isLoading = false;
    let allItemsLoaded = <?php echo (count($items) < $items_per_page ? 'true' : 'false'); ?>;

    // Get DOM elements
    const itemGrid = document.getElementById('itemGrid');
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const loadMoreContainer = document.getElementById('loadMoreContainer');
    const searchQueryInput = document.getElementById('searchQuery');
    const sortBySelect = document.getElementById('sortBy');
    const sortOrderSelect = document.getElementById('sortOrder');
    const categoryButtons = document.querySelectorAll('.category-btn');

    // Item Detail Modal elements
    const itemDetailModal = document.getElementById('itemDetailModal');
    const modalItemImage = document.getElementById('modalItemImage');
    const modalItemTitle = document.getElementById('modalItemTitle');
    const modalItemSize = document.getElementById('modalItemSize');
    const modalItemCondition = document.getElementById('modalItemCondition');
    const modalItemDescription = document.getElementById('modalItemDescription');
    const modalItemPrice = document.getElementById('modalItemPrice');
    const modalAddToCartBtn = document.getElementById('modalAddToCartBtn');
    const modalBuyNowBtn = document.getElementById('modalBuyNowBtn');

    // Message Box Modal elements
    const messageBoxModal = document.getElementById('messageBoxModal');
    const messageBoxTitle = document.getElementById('messageBoxTitle');
    const messageBoxText = document.getElementById('messageBoxText');

    // --- Utility Functions ---

    /**
     * Shows a custom message box modal.
     * @param {string} title - The title of the message box.
     * @param {string} message - The message to display.
     */
    function showMessageBox(title, message) {
        messageBoxTitle.textContent = title;
        messageBoxText.textContent = message;
        messageBoxModal.classList.add('show');
    }

    /**
     * Closes any specified modal.
     * @param {string} modalId - The ID of the modal to close.
     */
    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('show');
    }

    /**
     * Fetches items from the server based on current filters, search, and sort.
     * @param {boolean} append - True to append new items, false to replace existing.
     */
    async function fetchItems(append = false) {
      if (isLoading || (append && allItemsLoaded)) {
        return;
      }
      isLoading = true;
      loadMoreBtn.disabled = true; // Disable button while loading
      loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

      const category = document.querySelector('.category-btn.active')?.dataset.category || 'All';
      const searchQuery = searchQueryInput.value;
      const sortBy = sortBySelect.value;
      const sortOrder = sortOrderSelect.value;

      const params = new URLSearchParams({
        category: category,
        search_query: searchQuery,
        sort_by: sortBy,
        sort_order: sortOrder,
        limit: itemsPerPage,
        offset: append ? currentOffset : 0 // Use currentOffset for appending, 0 for new search/filter
      });

      try {
        const response = await fetch(`browse.php?${params.toString()}`, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest' // Indicate AJAX request
          }
        });
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        const newItems = await response.json();

        if (!append) {
          itemGrid.innerHTML = ''; // Clear existing items if not appending
          currentOffset = 0; // Reset offset for new search/filter
        }

        if (newItems.length > 0) {
          renderItems(newItems, append);
          currentOffset += newItems.length;
          allItemsLoaded = newItems.length < itemsPerPage; // Check if fewer items than requested, indicating end
        } else if (!append) {
          // No items found for a new search/filter
          itemGrid.innerHTML = `
            <div class="col-12 text-center py-5">
              <p class="text-muted fs-4">No items found matching your criteria.</p>
              <i class="fas fa-box-open fa-3x text-muted"></i>
            </div>
          `;
          allItemsLoaded = true;
        } else {
            // No more items to load
            allItemsLoaded = true;
        }

      } catch (error) {
        console.error("Error fetching items:", error);
        showMessageBox("Error", "Failed to load items. Please try again later.");
        allItemsLoaded = true; // Prevent further load attempts on error
      } finally {
        isLoading = false;
        updateLoadMoreButtonState();
      }
    }

    /**
     * Renders items into the item grid.
     * @param {Array} items - Array of item objects to render.
     * @param {boolean} append - True to append, false to replace.
     */
    function renderItems(items, append) {
      if (!append) {
        itemGrid.innerHTML = ''; // Clear existing items if not appending
      }

      items.forEach(item => {
        const itemCardHtml = `
          <div class="col-md-6 col-lg-4 mb-4 item" data-category="${htmlspecialchars(item.category)}" data-item-id="${htmlspecialchars(item.id)}">
            <div class="card item-card h-100 position-relative">
              <button class="favorite-btn ${isFavorite(item.id) ? 'active' : ''}" data-item-id="${htmlspecialchars(item.id)}" onclick="toggleFavorite(event, ${htmlspecialchars(item.id)})">
                  <i class="${isFavorite(item.id) ? 'fas' : 'far'} fa-heart"></i>
              </button>
              <img src="${htmlspecialchars(item.image_url || 'https://placehold.co/400x300/e0e0e0/555555?text=No+Image')}" class="item-img" alt="${htmlspecialchars(item.title)}" onerror="this.onerror=null;this.src='https://placehold.co/400x300/e0e0e0/555555?text=No+Image';">
              <div class="item-details">
                <div>
                    <h5 class="card-title">${htmlspecialchars(item.title)}</h5>
                    <p class="text-muted mb-1">Size: ${htmlspecialchars(item.size)} • Condition: ${htmlspecialchars(item.condition)}</p>
                    <p class="card-text text-truncate">${htmlspecialchars(item.description)}</p>
                    <p class="price">$${numberFormat(item.price, 2)}</p>
                </div>
                <div class="item-actions">
                  <button class="btn btn-outline-primary btn-cart" onclick="addToCart(${htmlspecialchars(item.id)})">
                    <i class="fas fa-shopping-cart"></i> Add to Cart
                  </button>
                  <button class="btn btn-primary btn-swap" onclick="buyNow(${htmlspecialchars(item.id)})">
                    <i class="fas fa-bolt"></i> Swap / Buy Now
                  </button>
                </div>
              </div>
              <a href="#" class="stretched-link" onclick="showItemDetails(event, ${htmlspecialchars(JSON.stringify(item))})"></a>
            </div>
          </div>
        `;
        itemGrid.insertAdjacentHTML('beforeend', itemCardHtml);
      });
    }

    /**
     * Updates the state of the "Load More" button.
     */
    function updateLoadMoreButtonState() {
      if (allItemsLoaded) {
        loadMoreBtn.disabled = true;
        loadMoreBtn.textContent = 'No More Items';
        loadMoreContainer.style.display = 'none'; // Hide container if no more items
      } else {
        loadMoreBtn.disabled = false;
        loadMoreBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Load More';
        loadMoreContainer.style.display = 'block'; // Show container
      }
    }

    /**
     * Filters items by category.
     * @param {string} category - The category to filter by.
     */
    function filterCategory(category) {
      categoryButtons.forEach(btn => {
        btn.classList.remove('active');
      });
      document.querySelector(`.category-btn[data-category="${category}"]`).classList.add('active');
      allItemsLoaded = false; // Reset for new filter
      fetchItems(false); // Fetch new set of items
    }

    /**
     * Performs a search based on the input query.
     */
    function performSearch() {
      allItemsLoaded = false; // Reset for new search
      fetchItems(false); // Fetch new set of items
    }

    /**
     * Applies sorting based on selected criteria.
     */
    function applySort() {
      allItemsLoaded = false; // Reset for new sort
      fetchItems(false); // Fetch new set of items
    }

    /**
     * Handles adding an item to the cart.
     * @param {number} itemId - The ID of the item to add.
     */
    function addToCart(itemId) {
      showMessageBox("Cart Update", `Item ${itemId} added to cart (demo only).`);
      // In a real application, this would send an AJAX request to a backend cart handler.
    }

    /**
     * Handles the buy/swap action for an item.
     * @param {number} itemId - The ID of the item to buy/swap.
     */
    function buyNow(itemId) {
      showMessageBox("Proceed to Checkout", `Proceeding to swap or buy for item ${itemId} (demo only).`);
      // In a real application, this would redirect to a checkout page or initiate a swap process.
    }

    /**
     * Shows the item details in a modal.
     * @param {Event} event - The click event.
     * @param {object} item - The item object to display.
     */
    function showItemDetails(event, item) {
        event.preventDefault(); // Prevent default link behavior

        modalItemImage.src = htmlspecialchars(item.image_url || 'https://placehold.co/400x300/e0e0e0/555555?text=No+Image');
        modalItemTitle.textContent = htmlspecialchars(item.title);
        modalItemSize.textContent = htmlspecialchars(item.size);
        modalItemCondition.textContent = htmlspecialchars(item.condition);
        modalItemDescription.textContent = htmlspecialchars(item.description);
        modalItemPrice.textContent = `$${numberFormat(item.price, 2)}`;

        // Set up buttons in the modal
        modalAddToCartBtn.onclick = () => {
            addToCart(item.id);
            closeModal('itemDetailModal');
        };
        modalBuyNowBtn.onclick = () => {
            buyNow(item.id);
            closeModal('itemDetailModal');
        };

        itemDetailModal.classList.add('show');
    }

    // --- Favorite Items Logic ---

    /**
     * Gets favorite items from local storage.
     * @returns {Set<number>} A Set of favorite item IDs.
     */
    function getFavorites() {
        try {
            const favoritesJson = localStorage.getItem('rewear_favorites');
            return favoritesJson ? new Set(JSON.parse(favoritesJson)) : new Set();
        } catch (e) {
            console.error("Error reading favorites from localStorage:", e);
            return new Set();
        }
    }

    /**
     * Saves favorite items to local storage.
     * @param {Set<number>} favoritesSet - The Set of favorite item IDs to save.
     */
    function saveFavorites(favoritesSet) {
        try {
            localStorage.setItem('rewear_favorites', JSON.stringify(Array.from(favoritesSet)));
        } catch (e) {
            console.error("Error saving favorites to localStorage:", e);
        }
    }

    /**
     * Checks if an item is favorited.
     * @param {number} itemId - The ID of the item.
     * @returns {boolean} True if the item is a favorite, false otherwise.
     */
    function isFavorite(itemId) {
        const favorites = getFavorites();
        return favorites.has(itemId);
    }

    /**
     * Toggles an item's favorite status.
     * @param {Event} event - The click event.
     * @param {number} itemId - The ID of the item to toggle.
     */
    function toggleFavorite(event, itemId) {
        event.stopPropagation(); // Prevent card click from triggering modal
        const favorites = getFavorites();
        const button = event.currentTarget;
        const icon = button.querySelector('i');

        if (favorites.has(itemId)) {
            favorites.delete(itemId);
            icon.classList.remove('fas');
            icon.classList.add('far');
            button.classList.remove('active');
            showMessageBox("Favorites", "Item removed from favorites.");
        } else {
            favorites.add(itemId);
            icon.classList.remove('far');
            icon.classList.add('fas');
            button.classList.add('active');
            showMessageBox("Favorites", "Item added to favorites!");
        }
        saveFavorites(favorites);
    }

    // --- Helper for PHP htmlspecialchars equivalent ---
    function htmlspecialchars(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    // --- Helper for number formatting ---
    function numberFormat(value, decimals) {
        return parseFloat(value).toFixed(decimals);
    }

    // --- Event Listeners and Initial Load ---
    document.addEventListener('DOMContentLoaded', () => {
      // Initial fetch of items
      fetchItems(false); // Fetch initial set of items

      // Load More button click listener
      loadMoreBtn.addEventListener('click', () => fetchItems(true));

      // Search input keyup listener for live search (optional, or use button)
      searchQueryInput.addEventListener('keyup', (event) => {
          if (event.key === 'Enter') {
              performSearch();
          }
      });

      // Close modal on overlay click
      itemDetailModal.addEventListener('click', (event) => {
          if (event.target === itemDetailModal) {
              closeModal('itemDetailModal');
          }
      });
      messageBoxModal.addEventListener('click', (event) => {
          if (event.target === messageBoxModal) {
              closeModal('messageBoxModal');
          }
      });

      // Initialize favorite buttons based on local storage
      document.querySelectorAll('.favorite-btn').forEach(button => {
          const itemId = parseInt(button.dataset.itemId);
          const icon = button.querySelector('i');
          if (isFavorite(itemId)) {
              icon.classList.remove('far');
              icon.classList.add('fas');
              button.classList.add('active');
          } else {
              icon.classList.remove('fas');
              icon.classList.add('far');
              button.classList.remove('active');
          }
      });

      updateLoadMoreButtonState(); // Initial state for load more button
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
