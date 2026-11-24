<?php
declare(strict_types=1);

namespace App;

use RuntimeException;

/**
 * Sicherer Bild-Upload: prüft MIME-Typ per finfo, vergibt zufällige Dateinamen
 * und erlaubt nur Rastergrafiken (kein SVG wegen eingebettetem Script-Risiko).
 */
final class Upload
{
    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    public function __construct(private readonly array $config) {}

    /** @return string|null Dateiname oder null, wenn keine Datei hochgeladen wurde */
    public function image(string $field, string $prefix): ?string
    {
        $file = $_FILES[$field] ?? null;
        if (!$file || is_array($file['error'] ?? null) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        return $this->store($file, $prefix);
    }

    /**
     * Mehrere Bilder aus einem Feld wie <input type="file" name="images[]" multiple>.
     * @return array{0: string[], 1: string[]} [gespeicherte Dateinamen, Fehlermeldungen]
     */
    public function images(string $field, string $prefix): array
    {
        $files = $_FILES[$field] ?? null;
        if (!$files || !is_array($files['name'] ?? null)) {
            return [[], []];
        }
        $saved = $errors = [];
        foreach (array_keys($files['name']) as $i) {
            if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $file = [
                'name' => $files['name'][$i], 'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i], 'size' => $files['size'][$i],
            ];
            try {
                $saved[] = $this->store($file, $prefix);
            } catch (RuntimeException $e) {
                $errors[] = $file['name'] . ': ' . $e->getMessage();
            }
        }
        return [$saved, $errors];
    }

    private function store(array $file, string $prefix): string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Der Upload ist fehlgeschlagen (Fehlercode ' . (int) $file['error'] . ').');
        }
        if ($file['size'] > $this->config['upload_max_bytes']) {
            throw new RuntimeException('Die Datei ist zu groß (max. ' . round($this->config['upload_max_bytes'] / 1048576) . ' MB).');
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('Ungültiger Upload.');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::ALLOWED[$mime]) || @getimagesize($file['tmp_name']) === false) {
            throw new RuntimeException('Nur Bilder im Format JPG, PNG, WebP oder GIF sind erlaubt.');
        }

        $dir = $this->config['upload_dir'];
        if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
            throw new RuntimeException('Upload-Verzeichnis konnte nicht angelegt werden.');
        }
        $name = $prefix . '-' . bin2hex(random_bytes(8)) . '.' . self::ALLOWED[$mime];
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
            throw new RuntimeException('Die Datei konnte nicht gespeichert werden.');
        }
        return $name;
    }

    public function delete(?string $name): void
    {
        if (!$name || basename($name) !== $name) {
            return;
        }
        $path = $this->config['upload_dir'] . '/' . $name;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
