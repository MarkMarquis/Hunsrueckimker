<?php
declare(strict_types=1);

use App\ContactForm;
use App\Csrf;
use App\Flash;
use App\Format;
use App\Mailer;
use App\MenuRepository;
use App\OpeningHours;
use App\Settings;
use App\SlideRepository;

/** @var App\App $app */
$app = require dirname(__DIR__) . '/boot.php';

$page   = preg_replace('/[^a-z_]/', '', (string) ($_GET['page'] ?? 'dashboard'));
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
$ip     = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

header('X-Frame-Options: DENY');
header('Cache-Control: no-store');

function redirect(string $page, array $params = []): never
{
    header('Location: index.php?' . http_build_query(['page' => $page] + $params));
    exit;
}

function json(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Preisvarianten aus den Formularzeilen (Label + Preis) zusammensetzen. */
function parseVariants(mixed $labels, mixed $prices): array
{
    $out = [];
    foreach ((array) $labels as $i => $label) {
        $label = trim((string) $label);
        $price = trim((string) (((array) $prices)[$i] ?? ''));
        if ($label !== '' && $price !== '') {
            $out[] = ['label' => $label, 'price_cents' => Format::parsePrice($price)];
        }
    }
    return $out;
}

function post(string $key, string $default = ''): string
{
    $v = $_POST[$key] ?? $default;
    return is_string($v) ? trim($v) : $default;
}

// CSRF-Prüfung für alle POST-Anfragen
if ($isPost && !Csrf::check()) {
    if ($isAjax) {
        json(['ok' => false, 'message' => 'Sitzung abgelaufen. Bitte Seite neu laden.'], 419);
    }
    Flash::add('error', 'Ihre Sitzung ist abgelaufen. Bitte versuchen Sie es erneut.');
    redirect($page);
}

// ---------------------------------------------------------------------------
// Erstinstallation: Es existiert noch kein Benutzer -> Admin-Konto anlegen
// ---------------------------------------------------------------------------
if (!$app->auth->hasUsers()) {
    $errors = [];
    if ($isPost) {
        $username = post('username');
        $password = (string) ($_POST['password'] ?? '');
        if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
            $errors[] = 'Benutzername: 3–50 Zeichen (Buchstaben, Ziffern, . _ -).';
        }
        if (mb_strlen($password) < 10) {
            $errors[] = 'Das Passwort muss mindestens 10 Zeichen lang sein.';
        }
        if ($password !== ($_POST['password_confirm'] ?? '')) {
            $errors[] = 'Die Passwörter stimmen nicht überein.';
        }
        if (!$errors) {
            $id = $app->auth->createUser($username, $password);
            $app->auth->loginById($id);
            Flash::add('success', 'Willkommen! Ihr Administrator-Konto wurde angelegt.');
            redirect('dashboard');
        }
    }
    $app->render('admin/setup.tpl', ['errors' => $errors, 'username' => post('username')]);
    exit;
}

// ---------------------------------------------------------------------------
// Login / Logout
// ---------------------------------------------------------------------------
$user = $app->auth->user();

if ($page === 'logout' && $isPost) {
    $app->auth->logout();
    session_start();
    Flash::add('success', 'Sie wurden abgemeldet.');
    redirect('login');
}

if (!$user) {
    $error = null;
    if ($isPost && $page === 'login') {
        if ($app->auth->isLockedOut($ip)) {
            $error = 'Zu viele Fehlversuche. Bitte warten Sie einige Minuten.';
        } elseif ($app->auth->attempt(post('username'), (string) ($_POST['password'] ?? ''), $ip)) {
            redirect('dashboard');
        } else {
            $error = 'Benutzername oder Passwort ist falsch.';
        }
    }
    if ($isAjax) {
        json(['ok' => false, 'message' => 'Nicht angemeldet.'], 401);
    }
    $app->render('admin/login.tpl', ['error' => $error, 'username' => post('username')]);
    exit;
}

$app->view->assign([
    'user'         => $user,
    'page'         => $page,
    'site'         => $app->settings->all(),
    'unread_count' => $app->messages->unreadCount(),
]);

// ---------------------------------------------------------------------------
// Geschützter Bereich
// ---------------------------------------------------------------------------
try {
    switch ($page) {

        case 'dashboard':
            $app->render('admin/dashboard.tpl', [
                'news_count'  => count($app->news->all()),
                'cat_count'   => count($app->menu->categories()),
                'item_count'  => array_sum(array_map(fn ($c) => count($c['items']), $app->menu->tree(false))),
                'latest_msgs' => array_slice($app->messages->all(), 0, 5),
            ]);
            break;

        // ----- Allgemeine Einstellungen, Logo & Banner ------------------------
        case 'settings':
            if ($isPost) {
                foreach (Settings::TEXT_FIELDS as $field) {
                    $app->settings->set($field, post($field));
                }
                $app->settings->set('hero_news', isset($_POST['hero_news']) ? '1' : '0');
                if (!empty($_POST['smtp_pass_clear'])) {
                    $app->settings->set('smtp_pass', '');
                } elseif (($pass = (string) ($_POST['smtp_pass'] ?? '')) !== '') {
                    $app->settings->set('smtp_pass', $pass);
                }
                if (!array_key_exists(post('smtp_secure'), Mailer::SECURE_OPTIONS)) {
                    $app->settings->set('smtp_secure', 'tls');
                }
                if (post('smtp_port') !== '' && !ctype_digit(post('smtp_port'))) {
                    $app->settings->set('smtp_port', '');
                }
                foreach (['contact_email_to', 'contact_email_from'] as $mailField) {
                    $value = post($mailField);
                    if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $app->settings->set($mailField, '');
                        Flash::add('error', 'Die E-Mail-Adresse „' . $value . '“ ist ungültig und wurde nicht übernommen.');
                    }
                }
                foreach (array_keys(Settings::IMAGE_FIELDS) as $img) {
                    $old = $app->settings->get($img);
                    if (!empty($_POST['remove_' . $img])) {
                        $app->upload->delete($old);
                        $app->settings->set($img, '');
                    }
                    if ($new = $app->upload->image($img, $img)) {
                        $app->upload->delete($old);
                        $app->settings->set($img, $new);
                    }
                }
                Flash::add('success', 'Die Einstellungen wurden gespeichert.');
                redirect('settings');
            }
            $app->render('admin/settings.tpl', [
                'hero_news'      => $app->settings->heroNewsEnabled(),
                'mail_recipient' => ContactForm::recipient($app),
                'smtp_active'    => (new Mailer($app))->usesSmtp(),
                'smtp_has_pass'  => $app->settings->get('smtp_pass') !== '',
                'smtp_secure_options' => Mailer::SECURE_OPTIONS,
                'smtp_secure'    => array_key_exists('smtp_secure', $app->settings->all()) ? $app->settings->get('smtp_secure') : 'tls',
                'mail_sender'    => ((new Mailer($app))->usesSmtp() && $app->settings->get('contact_email_from') === ''
                                     && filter_var($app->settings->get('smtp_user'), FILTER_VALIDATE_EMAIL))
                                    ? $app->settings->get('smtp_user') : ContactForm::sender($app),
                'images' => [
                    'logo'         => ['label' => 'Logo', 'url' => $app->imageUrl('logo', '../'),
                                       'hint' => 'PNG/JPG mit weißem oder transparentem Hintergrund, ca. 900 px breit.'],
                    'banner'       => ['label' => 'Ersatz-Bannergrafik', 'url' => $app->imageUrl('banner', '../'),
                                       'hint' => 'Wird nur gezeigt, wenn unter „Bannerbilder“ kein Bild aktiv ist.'],
                    'about_image'  => ['label' => 'Bild neben dem Willkommenstext', 'url' => $app->imageUrl('about_image', '../'),
                                       'hint' => 'Querformat, ca. 1600 px breit, JPG.'],
                    'highlight_image' => ['label' => 'Bild Highlight-Bereich', 'url' => $app->imageUrl('highlight_image', '../'),
                                       'hint' => 'Hochformat oder quadratisch, ca. 1200 px, JPG.'],
                ],
            ]);
            break;

        case 'mail_test':
            if ($isPost) {
                $to = ContactForm::recipient($app);
                try {
                    (new Mailer($app))->send(
                        $to,
                        'Testmail von der Webseite ' . $app->settings->get('site_name'),
                        "Diese Testmail bestätigt, dass Anfragen aus dem Kontaktformular der Webseite per E-Mail ankommen.\n\n"
                        . 'Gesendet am ' . date('d.m.Y \u\m H:i') . " Uhr.\n"
                    );
                    Flash::add('success', 'Die Testmail wurde an ' . $to . ' gesendet. Bitte das Postfach (auch den Spam-Ordner) prüfen.');
                } catch (RuntimeException $e) {
                    Flash::add('error', $e->getMessage());
                }
            }
            header('Location: index.php?page=settings#kontaktformular');
            exit;

        // ----- News -----------------------------------------------------------
        case 'news':
            $app->render('admin/news_list.tpl', ['items' => $app->news->all()]);
            break;

        case 'news_edit':
            $id   = (int) ($_GET['id'] ?? 0);
            $item = $id ? $app->news->find($id) : null;
            if ($id && !$item) {
                Flash::add('error', 'Eintrag nicht gefunden.');
                redirect('news');
            }
            $errors = [];
            if ($isPost) {
                $data = [
                    'title'        => post('title'),
                    'content'      => post('content'),
                    'published'    => isset($_POST['published']) ? 1 : 0,
                    'published_at' => post('published_at') ?: date('Y-m-d'),
                    'image'        => $item['image'] ?? '',
                ];
                if ($data['title'] === '') {
                    $errors[] = 'Bitte geben Sie einen Titel ein.';
                }
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['published_at'])) {
                    $errors[] = 'Bitte geben Sie ein gültiges Datum ein.';
                }
                if (!$errors) {
                    if (!empty($_POST['remove_image'])) {
                        $app->upload->delete($data['image']);
                        $data['image'] = '';
                    }
                    if ($new = $app->upload->image('image', 'news')) {
                        $app->upload->delete($data['image']);
                        $data['image'] = $new;
                    }
                    $app->news->save($data, $id ?: null);
                    Flash::add('success', 'Die Neuigkeit wurde gespeichert.');
                    redirect('news');
                }
                $item = array_merge($item ?? [], $data);
            }
            $app->render('admin/news_edit.tpl', [
                'item'   => $item ?? ['title' => '', 'content' => '', 'image' => '', 'published' => 1, 'published_at' => date('Y-m-d')],
                'id'     => $id,
                'errors' => $errors,
            ]);
            break;

        case 'news_delete':
            if ($isPost && ($item = $app->news->find((int) ($_POST['id'] ?? 0)))) {
                $app->upload->delete($item['image']);
                $app->news->delete((int) $item['id']);
                Flash::add('success', 'Die Neuigkeit wurde gelöscht.');
            }
            redirect('news');

        // ----- Speisekarte ----------------------------------------------------
        case 'menu':
            $app->render('admin/menu.tpl', [
                'sections'   => MenuRepository::SECTIONS,
                'categories' => $app->menu->tree(false),
            ]);
            break;

        case 'category_edit':
            $id  = (int) ($_GET['id'] ?? 0);
            $cat = $id ? $app->menu->findCategory($id) : null;
            if ($id && !$cat) {
                redirect('menu');
            }
            $errors = [];
            if ($isPost) {
                $data = [
                    'name'        => post('name'),
                    'description' => post('description'),
                    'section'     => array_key_exists(post('section'), MenuRepository::SECTIONS) ? post('section') : 'menu',
                    'sort_order'  => $cat['sort_order'] ?? $app->menu->nextSort('categories'),
                ];
                if ($data['name'] === '') {
                    $errors[] = 'Bitte geben Sie einen Namen ein.';
                } else {
                    $app->menu->saveCategory($data, $id ?: null);
                    Flash::add('success', 'Die Kategorie wurde gespeichert.');
                    redirect('menu');
                }
                $cat = array_merge($cat ?? [], $data);
            }
            $app->render('admin/category_edit.tpl', [
                'cat'      => $cat ?? ['name' => '', 'description' => '', 'section' => ($_GET['section'] ?? '') === 'highlight' ? 'highlight' : 'menu'],
                'id'       => $id,
                'errors'   => $errors,
                'sections' => MenuRepository::SECTIONS,
            ]);
            break;

        case 'category_delete':
            if ($isPost) {
                $app->menu->deleteCategory((int) ($_POST['id'] ?? 0));
                Flash::add('success', 'Die Kategorie und ihre Gerichte wurden gelöscht.');
            }
            redirect('menu');

        case 'item_edit':
            $id   = (int) ($_GET['id'] ?? 0);
            $item = $id ? $app->menu->findItem($id) : null;
            if ($id && !$item) {
                redirect('menu');
            }
            $categories = $app->menu->categories();
            if (!$categories) {
                Flash::add('error', 'Bitte legen Sie zuerst eine Kategorie an.');
                redirect('category_edit');
            }
            $errors = [];
            if ($isPost) {
                $catId = (int) ($_POST['category_id'] ?? 0);
                $data  = [
                    'category_id' => $catId,
                    'name'        => post('name'),
                    'description' => post('description'),
                    'price_cents' => Format::parsePrice(post('price')),
                    'portion'     => post('portion'),
                    'variants'    => parseVariants($_POST['variant_label'] ?? [], $_POST['variant_price'] ?? []),
                    'allergens'   => post('allergens'),
                    'tags'        => post('tags'),
                    'active'      => isset($_POST['active']) ? 1 : 0,
                    'sort_order'  => ($item && (int) $item['category_id'] === $catId)
                        ? (int) $item['sort_order'] : $app->menu->nextSort('items', $catId),
                ];
                if ($data['name'] === '') {
                    $errors[] = 'Bitte geben Sie einen Namen ein.';
                }
                if (!$app->menu->findCategory($catId)) {
                    $errors[] = 'Bitte wählen Sie eine Kategorie.';
                }
                if ($data['price_cents'] < 0) {
                    $errors[] = 'Der Preis darf nicht negativ sein.';
                }
                if (!$errors) {
                    $app->menu->saveItem($data, $id ?: null);
                    Flash::add('success', 'Das Gericht wurde gespeichert.');
                    redirect('menu');
                }
                $item = array_merge($item ?? [], $data);
                $item['variants'] = json_encode($data['variants']);
            }
            if ($item) {
                $item['variant_list'] = MenuRepository::decodeVariants($item['variants'] ?? '[]');
            }
            $app->render('admin/item_edit.tpl', [
                'item'       => $item ?? [
                    'category_id' => (int) ($_GET['category'] ?? 0), 'name' => '', 'description' => '',
                    'price_cents' => 0, 'portion' => '', 'variant_list' => [], 'allergens' => '', 'tags' => '', 'active' => 1,
                ],
                'id'         => $id,
                'categories' => $categories,
                'errors'     => $errors,
            ]);
            break;

        case 'item_delete':
            if ($isPost) {
                $app->menu->deleteItem((int) ($_POST['id'] ?? 0));
                if ($isAjax) {
                    json(['ok' => true]);
                }
                Flash::add('success', 'Das Gericht wurde gelöscht.');
            }
            redirect('menu');

        case 'item_toggle':
            if (!$isPost) {
                redirect('menu');
            }
            $active = $app->menu->toggleItem((int) ($_POST['id'] ?? 0));
            json(['ok' => $active !== null, 'active' => $active]);

        case 'reorder':
            if (!$isPost) {
                redirect('menu');
            }
            $ids = array_filter((array) ($_POST['ids'] ?? []), 'is_numeric');
            if (post('type') === 'slides') {
                $app->slides->reorder($ids);
            } else {
                $app->menu->reorder(post('type') === 'categories' ? 'categories' : 'items', $ids);
            }
            json(['ok' => true]);

        // ----- Öffnungszeiten & Ausnahmen --------------------------------------
        case 'hours':
            if ($isPost) {
                $app->hours->saveWeek((array) ($_POST['week'] ?? []));
                $app->settings->set('opening_note', post('opening_note'));
                Flash::add('success', 'Die Öffnungszeiten wurden gespeichert.');
                redirect('hours');
            }
            $app->render('admin/hours.tpl', [
                'week'       => $app->hours->week(),
                'days'       => OpeningHours::DAYS,
                'exceptions' => $app->hours->exceptions(),
                'today'      => date('Y-m-d'),
                'types'      => OpeningHours::TYPES,
            ]);
            break;

        case 'exception_edit':
            $id  = (int) ($_GET['id'] ?? 0);
            $exc = $id ? $app->hours->findException($id) : null;
            if ($id && !$exc) {
                redirect('hours');
            }
            $errors = [];
            if ($isPost) {
                $data = [
                    'title'         => post('title'),
                    'date_from'     => post('date_from'),
                    'date_to'       => post('date_to'),
                    'type'          => array_key_exists(post('type'), OpeningHours::TYPES) ? post('type') : 'closed',
                    'open_time'     => OpeningHours::time(post('open_time')),
                    'close_time'    => OpeningHours::time(post('close_time')),
                    'note'          => post('note'),
                    'announce_days' => max(0, min(120, (int) post('announce_days', (string) OpeningHours::DEFAULT_ANNOUNCE_DAYS))),
                ];
                $isDate = fn ($v) => ($d = DateTimeImmutable::createFromFormat('!Y-m-d', $v)) && $d->format('Y-m-d') === $v;
                if (!$isDate($data['date_from'])) {
                    $errors[] = 'Bitte ein gültiges Datum (von) angeben.';
                }
                if ($data['date_to'] !== '' && (!$isDate($data['date_to']) || $data['date_to'] < $data['date_from'])) {
                    $errors[] = 'Das Datum „bis“ muss leer sein oder nach dem Datum „von“ liegen.';
                }
                if ($data['date_to'] === $data['date_from']) {
                    $data['date_to'] = '';
                }
                if ($data['type'] !== 'closed' && $data['open_time'] === '') {
                    $errors[] = 'Bitte die Uhrzeit angeben, ab der geöffnet ist.';
                }
                if ($data['type'] === 'closed') {
                    $data['open_time'] = $data['close_time'] = '';
                }
                if (!$errors) {
                    $app->hours->saveException($data, $id ?: null);
                    Flash::add('success', 'Die Ausnahme wurde gespeichert.');
                    redirect('hours');
                }
                $exc = array_merge($exc ?? [], $data);
            }
            $app->render('admin/exception_edit.tpl', [
                'exc'    => $exc ?? ['title' => '', 'date_from' => '', 'date_to' => '', 'type' => 'closed',
                                     'open_time' => '11:00', 'close_time' => '', 'note' => '',
                                     'announce_days' => OpeningHours::DEFAULT_ANNOUNCE_DAYS],
                'id'     => $id,
                'types'  => OpeningHours::TYPES,
                'errors' => $errors,
            ]);
            break;

        case 'exception_delete':
            if ($isPost) {
                $app->hours->deleteException((int) ($_POST['id'] ?? 0));
                Flash::add('success', 'Die Ausnahme wurde gelöscht.');
            }
            redirect('hours');

        // ----- Bannerbilder (Bildwechsler) -------------------------------------
        case 'slides':
            if ($isPost) {
                [$saved, $uploadErrors] = $app->upload->images('images', 'slide');
                foreach ($saved as $file) {
                    $app->slides->create($app->config['upload_url'] . '/' . $file);
                }
                foreach ($uploadErrors as $err) {
                    Flash::add('error', $err);
                }
                if ($saved) {
                    Flash::add('success', count($saved) === 1 ? 'Das Bild wurde hinzugefügt.' : count($saved) . ' Bilder wurden hinzugefügt.');
                } elseif (!$uploadErrors) {
                    Flash::add('error', 'Bitte wählen Sie mindestens ein Bild aus.');
                }
                redirect('slides');
            }
            $app->render('admin/slides.tpl', [
                'slides'    => $app->slides->all(),
                'positions' => SlideRepository::POSITIONS,
            ]);
            break;

        case 'slide_update':
            if ($isPost) {
                $app->slides->update((int) ($_POST['id'] ?? 0), post('alt'), post('position'));
                if ($isAjax) {
                    json(['ok' => true]);
                }
                Flash::add('success', 'Das Bild wurde gespeichert.');
            }
            redirect('slides');

        case 'slide_toggle':
            if (!$isPost) {
                redirect('slides');
            }
            $active = $app->slides->toggle((int) ($_POST['id'] ?? 0));
            json(['ok' => $active !== null, 'active' => $active]);

        case 'slide_delete':
            if ($isPost && ($slide = $app->slides->find((int) ($_POST['id'] ?? 0)))) {
                $prefix = $app->config['upload_url'] . '/';
                if (str_starts_with($slide['image'], $prefix)) {
                    $app->upload->delete(substr($slide['image'], strlen($prefix)));
                }
                $app->slides->delete((int) $slide['id']);
                Flash::add('success', 'Das Bild wurde entfernt.');
            }
            redirect('slides');

        // ----- Kontaktanfragen ------------------------------------------------
        case 'messages':
            $app->render('admin/messages.tpl', ['messages' => $app->messages->all()]);
            break;

        case 'message':
            $msg = $app->messages->find((int) ($_GET['id'] ?? 0));
            if (!$msg) {
                redirect('messages');
            }
            if (!$msg['is_read']) {
                $app->messages->markRead((int) $msg['id']);
                $app->view->assign('unread_count', $app->messages->unreadCount());
            }
            $app->render('admin/message.tpl', ['msg' => $msg]);
            break;

        case 'message_unread':
            if ($isPost) {
                $app->messages->markRead((int) ($_POST['id'] ?? 0), false);
            }
            redirect('messages');

        case 'message_delete':
            if ($isPost) {
                $app->messages->delete((int) ($_POST['id'] ?? 0));
                Flash::add('success', 'Die Nachricht wurde gelöscht.');
            }
            redirect('messages');

        // ----- Passwort ändern ------------------------------------------------
        case 'password':
            $errors = [];
            if ($isPost) {
                $new = (string) ($_POST['new_password'] ?? '');
                if (!$app->auth->verifyPassword((int) $user['id'], (string) ($_POST['current_password'] ?? ''))) {
                    $errors[] = 'Das aktuelle Passwort ist falsch.';
                }
                if (mb_strlen($new) < 10) {
                    $errors[] = 'Das neue Passwort muss mindestens 10 Zeichen lang sein.';
                }
                if ($new !== ($_POST['new_password_confirm'] ?? '')) {
                    $errors[] = 'Die neuen Passwörter stimmen nicht überein.';
                }
                if (!$errors) {
                    $app->auth->changePassword((int) $user['id'], $new);
                    session_regenerate_id(true);
                    Flash::add('success', 'Ihr Passwort wurde geändert.');
                    redirect('dashboard');
                }
            }
            $app->render('admin/password.tpl', ['errors' => $errors]);
            break;

        default:
            redirect('dashboard');
    }
} catch (RuntimeException $e) {
    // z. B. Upload-Fehler: Meldung anzeigen und zur Seite zurück
    Flash::add('error', $e->getMessage());
    redirect($page, isset($_GET['id']) ? ['id' => (int) $_GET['id']] : []);
}
