<?php

namespace App\Libraries\Sorf;

/**
 * Signature images of a request (drawn or uploaded in the browser, sent as a
 * PNG data URL by drf-signature.js). Files live in
 * writable/uploads/sorf/signatures/<year>/ and are only served through
 * MyRequest (SIGN-FILE) to people who may open the request.
 */
class SignatureStore
{
    public const MAX_BYTES = 400000;    // the browser sends a trimmed PNG, usually 5-40 KB
    public const MAX_WIDTH = 1600;
    public const MAX_HEIGHT = 800;

    private string $dir;

    public function __construct(?string $dir = null)
    {
        $this->dir = rtrim($dir ?? WRITEPATH . 'uploads/sorf/signatures', '/\\') . '/';
    }

    /**
     * Check a posted signature and save it. The image is decoded and written
     * out again as a plain PNG, so nothing but pixels is ever stored.
     *
     * @return array{0: string, 1: string} [file name to keep in the table, error]
     */
    public function store(string $dataUrl): array
    {
        // GD re-encodes the image; without it every save would end in a fatal error
        if (!function_exists('imagecreatefromstring')) {
            log_message('critical', 'SignatureStore: the PHP GD extension is not enabled (php.ini: extension=gd).');
            return ['', 'Signatures cannot be saved on this server right now. Please tell the administrator.'];
        }

        $prefix = 'data:image/png;base64,';
        if (strncmp($dataUrl, $prefix, strlen($prefix)) !== 0) {
            return ['', 'The signature could not be read. Please sign again.'];
        }
        $bytes = base64_decode(substr($dataUrl, strlen($prefix)), true);
        if ($bytes === false || $bytes === '' || strlen($bytes) > self::MAX_BYTES) {
            return ['', 'The signature image is too large. Please sign again.'];
        }

        $info = @getimagesizefromstring($bytes);
        if ($info === false || $info[2] !== IMAGETYPE_PNG || $info[0] < 20 || $info[1] < 10 || $info[0] > self::MAX_WIDTH || $info[1] > self::MAX_HEIGHT) {
            return ['', 'The signature could not be read. Please sign again.'];
        }
        $image = @imagecreatefromstring($bytes);
        if ($image === false) {
            return ['', 'The signature could not be read. Please sign again.'];
        }

        // keep the transparent background, so the ink sits on the form's signature line
        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        $sub = date('Y');
        if (!is_dir($this->dir . $sub)) {
            mkdir($this->dir . $sub, 0755, true);
        }
        $file = $sub . '/' . bin2hex(random_bytes(16)) . '.png';
        $saved = imagepng($image, $this->dir . $file);
        imagedestroy($image);

        return $saved ? [$file, ''] : ['', 'The signature was not saved. Please try again.'];
    }

    /**
     * Full path of a stored signature (null if the name is odd or the file is gone).
     */
    public function path(string $file): ?string
    {
        if (!preg_match('~^\d{4}/[0-9a-f]{32}\.png$~', $file) || !is_file($this->dir . $file)) {
            return null;
        }
        return $this->dir . $file;
    }

    public function delete(string $file): void
    {
        $path = $this->path($file);
        if ($path !== null) {
            @unlink($path);
        }
    }
}
