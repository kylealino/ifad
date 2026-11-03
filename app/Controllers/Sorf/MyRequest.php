<?php

namespace App\Controllers\Sorf;

use Config\Sorf\Permissions;

class MyRequest extends SorfBaseController
{
    protected $myrequest;
    protected $mychat;
    protected $mydoc;
    protected $session;
    protected $cuser;

    public function __construct()
    {
        $this->request = \Config\Services::request();
        $this->myrequest = model('App\Models\Sorf\MyRequestModel');
        $this->mychat = model('App\Models\Sorf\MyRequestChatModel');
        $this->mydoc = model('App\Models\Sorf\MyRequestDocModel');
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
    }

    // Requesters file, sign and follow their own requests here. The request
    // page, its PDF and signatures are shared with the "Noted by" signatory and
    // HR - the model only hands a request to those three. The conversation is
    // between the requester and HR; the signatory gets the request's log.
    public function index() {

        $meaction = $this->request->getPostGet('meaction');
        $isPost = strtolower($this->request->getMethod()) === 'post';

        // PHP drops the whole POST when it is over post_max_size (big uploads)
        if ($meaction === null && $isPost && (int) $this->request->getServer('CONTENT_LENGTH') > 0) {
            return $this->response->setStatusCode(413)->setJSON(['error' => 'Files are too big to send. Try fewer or smaller files.']);
        }

        // saving and sending only ever come from a form post
        if (in_array($meaction, ['MAIN-SAVE', 'FORM-PREVIEW', 'CHAT-SEND'], true) && !$isPost) {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Not allowed.']);
        }

        switch ($meaction) {
            case 'FORM':
                return $this->loadFormView();

            case 'MAIN-SAVE':
                return $this->json($this->myrequest->request_save());

            //PREVIEW - the printed form of what is being typed (nothing is saved)
            case 'FORM-PREVIEW':
                return $this->previewForm();

            case 'VIEW':
                return $this->loadRequestView();

            case 'SIGN-FILE':
                return $this->signature();

            case 'PRINT':
                return $this->printRequest();

            //SPECIAL ORDER - the document HR prepared (requester: once released)
            case 'DOC':
                return $this->document(false);

            case 'DOC-WORD':
                return $this->document(true);

            //CONVERSATION
            case 'CHAT-LOAD':
                $hd = $this->myrequest->request_get($this->request->getGet('recid'));
                if ($hd === null) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'Request not found.']);
                }
                return $this->response->setJSON([
                    'status' => $hd['status'],
                    // the page reloads when this changes (HR shared or withdrew the S.O.)
                    'doc' => $this->docStamp($this->mydoc->doc_view($hd)),
                    'messages' => $this->mychat->thread_messages($hd['recid'], (int) $this->request->getGet('after_id'), !$this->myrequest->request_can_chat($hd)),
                ]);

            case 'CHAT-SEND':
                return $this->json($this->mychat->msg_send());

            case 'CHAT-FILE':
                return $this->attachment($this->mychat->attachment($this->request->getGet('id')));

            case 'MAIN':
            default:
                if (!drf_can(Permissions::REQUEST_FILE)) {
                    return $this->deny();
                }
                return view('sorf/request/request-main', [
                    'rows' => $this->myrequest->request_list('mine'),
                    'mode' => 'mine',
                ]);
        }
    }

    // Model results carry 'error' on failure -> 422 so the JS shows it
    private function json(array $result) {
        return $this->response->setStatusCode(isset($result['error']) ? 422 : 200)->setJSON($result);
    }

    // What the viewer can see of the Special Order, as one string: it changes
    // when HR starts, shares or withdraws it, not on every draft save
    private function docStamp(?array $doc): string {
        return $doc === null ? '' : $doc['doc_status'] . '|' . $doc['released_at'];
    }

    // Images open in the browser; other files always download
    private function attachment(?array $file) {
        if ($file === null) {
            return $this->response->setStatusCode(404)->setBody('Attachment not found.');
        }
        // plain-ASCII name for old browsers, the real (UTF-8) name for the rest
        $ascii = preg_replace('/[^\x20-\x7E]|["\\\\]/', '_', $file['orig_name']);
        return $this->response
            ->setHeader('Content-Type', $file['mime_type'])
            ->setHeader('Content-Disposition', ($file['is_image'] ? 'inline' : 'attachment') . '; filename="' . $ascii . '"; filename*=UTF-8\'\'' . rawurlencode($file['orig_name']))
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Cache-Control', 'private, max-age=86400')
            ->setBody(file_get_contents($file['path']));
    }

    // A signature on a request: ?recid= and who=requester|noted
    private function signature() {
        $hd = $this->myrequest->request_get($this->request->getGet('recid'));
        $file = $hd === null ? '' : $hd[$this->request->getGet('who') === 'noted' ? 'noted_sign' : 'requester_sign'];
        $path = (new \App\Libraries\Sorf\SignatureStore())->path($file);
        if ($path === null) {
            return $this->response->setStatusCode(404)->setBody('Signature not found.');
        }
        return $this->response
            ->setHeader('Content-Type', 'image/png')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Cache-Control', 'private, no-store')
            ->setBody(file_get_contents($path));
    }

    //REQUEST FORM - new, or ?recid= to change one that HR has not decided yet
    private function loadFormView() {

        if (!drf_can(Permissions::REQUEST_FILE)) {
            return $this->deny();
        }

        $record = null;
        $recid = (int) $this->request->getGet('recid');
        if ($recid > 0) {
            $record = $this->myrequest->request_load($recid);
            if ($record === null || !$this->myrequest->request_is_editable($record['hd'])) {
                return redirect()->to(sorf_url('myrequest?meaction=VIEW&recid=' . $recid));
            }
        }

        $config = config(\Config\Sorf\RequestForms::class);

        return view('sorf/request/request-form', [
            'user' => drf_current_user(),
            'lookups' => $this->myrequest->lookups(),
            'forms' => $config->forms,
            'ltoExpenses' => $config->ltoExpenses,
            'record' => $record,
        ]);
    }

    //REQUEST PAGE - details, status and the conversation with HR
    private function loadRequestView() {

        $record = $this->myrequest->request_load($this->request->getGet('recid'));
        if ($record === null) {
            $this->session->setFlashdata('drf_notice', 'That request was not found.');
            return redirect()->to(site_url(drf_home_path() ?? 'portal'));
        }

        $config = config(\Config\Sorf\RequestForms::class);

        return view('sorf/request/request-view', [
            'record' => $record,
            'form' => $config->forms[$record['hd']['document_id']] ?? null,
            'ltoExpenses' => $config->ltoExpenses,
            // HR parts of the page: only for a request that has reached HR
            'isHr' => drf_can(Permissions::HR_VIEW) && $this->myrequest->request_at_hr($record['hd']),
            'canStatus' => drf_can(Permissions::HR_STATUS) && $this->myrequest->request_at_hr($record['hd']),
            'canDoc' => drf_can(Permissions::HR_DOCUMENT) && $this->myrequest->request_at_hr($record['hd']),
            'isOwner' => $record['hd']['added_by'] === $this->cuser,
            'isNoted' => $record['hd']['noted_by_user'] !== '' && $record['hd']['noted_by_user'] === $this->cuser,
            'canEdit' => $this->myrequest->request_is_editable($record['hd']),
            'canApprove' => $this->myrequest->request_can_approve($record['hd']),
            'canChat' => $this->myrequest->request_can_chat($record['hd']),
            'doc' => $this->mydoc->doc_view($record['hd']),
            'docStamp' => $this->docStamp($this->mydoc->doc_view($record['hd'])),
            'me' => (string) $this->cuser,
        ]);
    }

    //SPECIAL ORDER - the page to read and print, or the same document as a Word file
    private function document(bool $asWord) {

        $hd = $this->myrequest->request_get($this->request->getGet('recid'));
        $doc = $hd === null ? null : $this->mydoc->doc_view($hd);
        if ($doc === null) {
            return $this->response->setStatusCode(404)->setBody('This Special Order is not available.');
        }

        if (!$asWord) {
            return view('sorf/docs/doc-print', ['hd' => $hd, 'doc' => $doc, 'frame' => $this->request->getGet('frame') === '1']);
        }

        // no final release yet: the Word copy is marked DRAFT too
        $draft = config(\Config\Sorf\DocumentTemplates::class)->draftOnly;
        $name = preg_replace('/[^A-Za-z0-9._-]/', '_', ($draft ? 'DRAFT Special Order ' : 'Special Order ') . $hd['request_no']) . '.doc';
        return $this->response
            ->setHeader('Content-Type', 'application/msword')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $name . '"')
            ->setHeader('Cache-Control', 'private, no-store')
            ->setBody((new \App\Libraries\Sorf\DocWord())->build(($draft ? 'DRAFT ' : '') . 'Special Order ' . $hd['request_no'], $doc['doc_html'], $draft));
    }

    //PREVIEW - the request form PDF from the unsaved form, shown beside it while
    // the requester types. A newly drawn signature is put on it from a temporary
    // copy that is removed again; otherwise the one already on the request.
    private function previewForm() {

        $result = $this->myrequest->request_preview();
        if (isset($result['error'])) {
            return $this->json($result);
        }

        $requester = null;
        $temp = null;
        $tempStore = new \App\Libraries\Sorf\SignatureStore(WRITEPATH . 'temp/sorf-sign-preview');
        $signature = (string) $this->request->getPost('signature');
        // (without GD the preview simply shows no new signature yet)
        if ($signature !== '' && function_exists('imagecreatefromstring')) {
            [$file, $signError] = $tempStore->store($signature);
            if ($signError === '') {
                $temp = $file;
                $requester = $tempStore->path($file);
            }
        } elseif ($signature === '' && $this->request->getPost('signature_state') === 'kept' && $result['kept_sign'] !== '') {
            $requester = (new \App\Libraries\Sorf\SignatureStore())->path($result['kept_sign']);
        }

        try {
            $pdf = new \App\Libraries\Sorf\RequestPdf($result['form'], config(\Config\Sorf\RequestForms::class)->ltoExpenses);
            $body = $pdf->render($result['record'], ['requester' => $requester, 'noted' => null]);
        } catch (\Throwable $e) {
            log_message('error', 'Request form preview failed: ' . $e->getMessage());
            return $this->json(['error' => 'The preview could not be made from what is typed so far.']);
        } finally {
            if ($temp !== null) {
                $tempStore->delete($temp);
            }
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="preview.pdf"')
            ->setHeader('Cache-Control', 'private, no-store')
            ->setBody($body);
    }

    //PRINT - the filled request form as a PDF
    private function printRequest() {

        $record = $this->myrequest->request_load($this->request->getGet('recid'));
        $config = config(\Config\Sorf\RequestForms::class);
        if ($record === null || !isset($config->forms[$record['hd']['document_id']])) {
            return $this->response->setStatusCode(404)->setBody('Request not found.');
        }

        $signs = new \App\Libraries\Sorf\SignatureStore();
        $pdf = new \App\Libraries\Sorf\RequestPdf($config->forms[$record['hd']['document_id']], $config->ltoExpenses);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $record['hd']['request_no'] . '.pdf"')
            ->setHeader('Cache-Control', 'private, no-store')
            ->setBody($pdf->render($record, [
                'requester' => $signs->path($record['hd']['requester_sign']),
                'noted' => $signs->path($record['hd']['noted_sign']),
            ]));
    }

}
