<?php
namespace App\Models;
use CodeIgniter\Model;

// File uploads shared by Messages (chat attachments) and the Helpdesk
// (ticket screenshots): what may be uploaded, checked against the file's
// real content, and where it is stored (writable/uploads/<folder>/<year>/).
class MyUploadsModel extends Model
{

	protected $db;

	// Image attachments: extension => mime type (checked against the file's real content)
	public const IMAGE_TYPES = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp'];
	// Other files a chat may carry: extension => mime type sent back on download
	public const DOC_TYPES = [
		'pdf' => 'application/pdf',
		'doc' => 'application/msword',
		'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'xls' => 'application/vnd.ms-excel',
		'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'ppt' => 'application/vnd.ms-powerpoint',
		'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		'csv' => 'text/csv',
		'txt' => 'text/plain',
		'zip' => 'application/zip',
	];
	// Content that is never accepted, whatever the file is called
	public const BLOCKED_MIMES = [
		'text/html', 'text/x-php', 'application/x-php', 'application/x-httpd-php', 'text/javascript', 'application/javascript',
		'application/x-dosexec', 'application/x-msdownload', 'application/x-executable', 'application/x-sharedlib',
		'application/x-sh', 'text/x-shellscript', 'image/svg+xml', 'application/x-msi',
	];
	public const MAX_FILES = 5;

	public function __construct(){
		parent::__construct();
		$this->request = \Config\Services::request();
	}

	//UPLOADS - VALIDATE the 'attachments[]' files: [files, error]
	// Each file comes back as ['file' => UploadedFile, 'ext', 'mime', 'is_image'].
	// Types are checked against the file's content, not the name the browser sent.
	public function upload_files(bool $imagesOnly, int $maxBytes, int $maxTotal = 0) {
		$files = array_values(array_filter((array) ($this->request->getFileMultiple('attachments') ?? []), fn ($f) => $f !== null && $f->getError() !== UPLOAD_ERR_NO_FILE));
		$what = $imagesOnly ? 'images' : 'files';

		if (count($files) > self::MAX_FILES) {
			return [[], 'Attach up to ' . self::MAX_FILES . " {$what} at a time."];
		}

		$out = [];
		$total = 0;
		foreach ($files as $file) {
			$name = $file->getClientName();
			if (!$file->isValid()) {
				return [[], "{$name} did not upload. Please try again."];
			}
			if ($file->getSize() > $maxBytes) {
				return [[], "{$name} is over " . round($maxBytes / 1048576) . ' MB.'];
			}
			$total += $file->getSize();

			$ext = strtolower($file->getClientExtension());
			$mime = (string) $file->getMimeType();
			if (isset(self::IMAGE_TYPES[$ext])) {
				if (!in_array($mime, self::IMAGE_TYPES, true) || @getimagesize($file->getTempName()) === false) {
					return [[], "{$name} is not a valid image."];
				}
				$out[] = ['file' => $file, 'ext' => $ext, 'mime' => $mime, 'is_image' => 1];
				continue;
			}
			if ($imagesOnly) {
				return [[], "{$name} is not an image (JPG, PNG, GIF or WEBP only)."];
			}
			if (!isset(self::DOC_TYPES[$ext]) || in_array($mime, self::BLOCKED_MIMES, true) || ($ext === 'pdf' && $mime !== 'application/pdf')) {
				return [[], "{$name} can't be sent. Allowed: images, PDF, Word, Excel, PowerPoint, CSV, TXT and ZIP."];
			}
			$out[] = ['file' => $file, 'ext' => $ext, 'mime' => self::DOC_TYPES[$ext], 'is_image' => 0];
		}

		if ($maxTotal > 0 && $total > $maxTotal) {
			return [[], 'Files are too big together (' . round($maxTotal / 1048576) . ' MB per message).'];
		}
		return [$out, ''];
	}

	//UPLOADS - MOVE one validated file to writable/uploads/<folder>/<year>/
	// Returns the columns every attachment table shares.
	public function upload_store(string $folder, array $up) {
		$sub = date('Y');
		$dir = WRITEPATH . 'uploads/' . $folder . '/' . $sub;
		if (!is_dir($dir)) {
			mkdir($dir, 0755, true);
		}
		$file = $up['file'];
		$stored = bin2hex(random_bytes(16)) . '.' . $up['ext'];
		$row = [
			'file_name' => $sub . '/' . $stored,
			'orig_name' => mb_substr($file->getClientName(), 0, 255),
			'mime_type' => $up['mime'],
			'file_size' => (int) $file->getSize(),
			'is_image' => $up['is_image'],
		];
		$file->move($dir, $stored);
		return $row;
	}

	//UPLOADS - PATH of a stored file (null if the name is odd or the file is gone)
	public function upload_path(string $folder, string $fileName) {
		$path = WRITEPATH . 'uploads/' . $folder . '/' . $fileName;
		if (!preg_match('~^\d{4}/[\w.]+$~', $fileName) || !is_file($path)) {
			return null;
		}
		return $path;
	}

} //end main class
?>
