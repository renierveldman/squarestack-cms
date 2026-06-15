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

        case 'delete_user': {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid id']);
                exit;
            }
            // Prevent deleting yourself
            $sessionUser = Auth::currentUser();
            if ($id === (int)($sessionUser['id'] ?? 0)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'You cannot delete your own account.']);
                exit;
            }
            $db = Database::getInstance();
            $rows = $db->delete('users', 'id = ?', [$id]);
            echo json_encode(['success' => $rows > 0]);
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

        case 'delete_form': {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid id']);
                exit;
            }
            echo json_encode(['success' => CMS::deleteForm($id)]);
            break;
        }

        case 'delete_form_submission': {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid id']);
                exit;
            }
            echo json_encode(['success' => CMS::deleteFormSubmission($id)]);
            break;
        }

        case 'get_mailchimp_lists': {
            $apiKey = Settings::get('mailchimp_api_key', '');
            $dc     = Settings::get('mailchimp_dc',      '');
            if (!$apiKey || !$dc) {
                echo json_encode(['success' => false, 'error' => 'Mailchimp not connected.']);
                exit;
            }
            $url = "https://{$dc}.api.mailchimp.com/3.0/lists?count=100&fields=lists.id,lists.name,lists.stats";
            $ch  = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => ['Authorization: Basic ' . base64_encode('anystring:' . $apiKey)],
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpCode !== 200) {
                echo json_encode(['success' => false, 'error' => 'Failed to fetch audiences (HTTP ' . $httpCode . ').']);
                exit;
            }
            $data = json_decode($response, true);
            echo json_encode(['success' => true, 'lists' => $data['lists'] ?? []]);
            break;
        }

        case 'test_mailchimp': {
            $apiKey = trim($_POST['api_key'] ?? Settings::get('mailchimp_api_key', ''));
            if ($apiKey === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'No API key provided.']);
                exit;
            }
            if (!preg_match('/-([a-z0-9]+)$/', $apiKey, $m)) {
                echo json_encode(['success' => false, 'error' => 'Invalid key format.']);
                exit;
            }
            $dc  = $m[1];
            $url = "https://{$dc}.api.mailchimp.com/3.0/";
            $ch  = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => ['Authorization: Basic ' . base64_encode('anystring:' . $apiKey)],
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            $response  = curl_exec($ch);
            $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                echo json_encode(['success' => false, 'error' => 'Connection failed (HTTP ' . $httpCode . ').']);
                exit;
            }
            $data = json_decode($response, true);
            echo json_encode([
                'success'      => true,
                'dc'           => $dc,
                'account_name' => $data['account_name'] ?? '',
                'email'        => $data['email']        ?? '',
            ]);
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
