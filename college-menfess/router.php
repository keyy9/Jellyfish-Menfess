<?php
// Router for PHP's built-in server
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Handle static files
if (preg_match('/\.(css|js|svg|png|jpg|jpeg|gif)$/', $uri)) {
    $file = __DIR__ . $uri;
    if (file_exists($file)) {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $mime_types = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif'
        ];
        
        if (isset($mime_types[$extension])) {
            header('Content-Type: ' . $mime_types[$extension]);
        }
        readfile($file);
        return true;
    }
    return false;
}

// Route handling
switch ($uri) {
    case '/':
        require __DIR__ . '/index.php';
        break;
    
    case '/login':
        require __DIR__ . '/pages/login.php';
        break;
    
    case '/register':
        require __DIR__ . '/pages/register.php';
        break;
    
    case '/submit':
        require __DIR__ . '/pages/submit.php';
        break;
    
    case '/browse':
        require __DIR__ . '/pages/browse.php';
        break;
    
    case '/profile':
        require __DIR__ . '/pages/profile.php';
        break;
    
    case '/logout':
        require __DIR__ . '/pages/logout.php';
        break;
    
    default:
        // Check for batch routes
        if (preg_match('/^\/batch\/(\d{4})$/', $uri, $matches)) {
            $_GET['batch'] = $matches[1];
            require __DIR__ . '/pages/browse.php';
            break;
        }
        
        // 404 page
        header('HTTP/1.1 404 Not Found');
        echo '404 Page Not Found';
        break;
}
?>
