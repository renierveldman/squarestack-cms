<?php
require_once __DIR__ . '/../config.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Cache.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/CMS.php';
require_once CORE_PATH . '/Media.php';
require_once CORE_PATH . '/Settings.php';
require_once CORE_PATH . '/Slug.php';
require_once CORE_PATH . '/Router.php';

header('Content-Type: application/json');

Auth::require();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

$isGet = $_SERVER['REQUEST_METHOD'] === 'GET';

if (!$isGet) {
    $csrf = $_POST['csrf'] ?? $_POST['csrf_token'] ?? '';
    if (!Auth::verifyCsrf($csrf)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }
}

try {
    switch ($action) {

        case 'delete_page': {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid id']);
                exit;
            }
            $result = CMS::deletePage($id);
            Cache::flush();
            echo json_encode(['success' => $result]);
            break;
        }

        case 'delete_post': {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid id']);
                exit;
            }
            $result = CMS::deletePost($id);
            Cache::flush();
            echo json_encode(['success' => $result]);
            break;
        }

        case 'save_menu': {
            $location = trim($_POST['location'] ?? '');
            $name     = trim($_POST['name'] ?? '');
            $itemsRaw = $_POST['items'] ?? '[]';

            if ($location === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'location is required']);
                exit;
            }

            $items = json_decode($itemsRaw, true);
            if (!is_array($items)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'items must be a valid JSON array']);
                exit;
            }

            CMS::saveMenu($location, $name, $items);
            echo json_encode(['success' => true]);
            break;
        }

        case 'upload_media': {
            if (!isset($_FILES['file'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'No file uploaded']);
                exit;
            }
            $result = Media::upload($_FILES['file']);
            echo json_encode([
                'success'  => true,
                'url'      => $result['url'],
                'id'       => $result['id'],
                'filename' => $result['filename'],
            ]);
            break;
        }

        case 'delete_media': {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid id']);
                exit;
            }
            $result = Media::delete($id);
            echo json_encode(['success' => $result]);
            break;
        }

        case 'get_media': {
            $page   = max(1, (int) ($_GET['page'] ?? 1));
            $result = Media::getLibrary($page);
            echo json_encode([
                'items' => $result['items'],
                'total' => $result['total'],
                'pages' => $result['pages'],
            ]);
            break;
        }

        case 'generate_slug': {
            $text = $_GET['text'] ?? '';
            $slug = Slug::generate($text);
            echo json_encode(['slug' => $slug]);
            break;
        }

        case 'flush_cache': {
            Cache::flush();
            echo json_encode(['success' => true]);
            break;
        }

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
