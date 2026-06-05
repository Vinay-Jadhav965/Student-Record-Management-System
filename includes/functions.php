<?php
// Include database connection
include_once 'db.php';

// Function to sanitize input
function sanitize($data) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($data))));
}

// Function to check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Function to display success message
function displaySuccess($message) {
    return "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

// Function to display error message
function displayError($message) {
    return "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

// Function to generate pagination
function generatePagination($totalPages, $currentPage, $baseUrl) {
    $pagination = '<nav><ul class="pagination justify-content-center">';
    
    if ($currentPage > 1) {
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage - 1) . '">Previous</a></li>';
    }
    
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = ($i == $currentPage) ? 'active' : '';
        $pagination .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a></li>';
    }
    
    if ($currentPage < $totalPages) {
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage + 1) . '">Next</a></li>';
    }
    
    $pagination .= '</ul></nav>';
    return $pagination;
}
?>
