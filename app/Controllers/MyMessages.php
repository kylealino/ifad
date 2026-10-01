<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class MyMessages extends BaseController
{
    public function __construct()
    {
        $this->request = \Config\Services::request();
        $this->mymessages = model('App\Models\MyMessagesModel');
        $this->session = session();
        $this->db = \Config\Database::connect();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
    }

    // Everyone signed in can chat and file/follow their own tickets. Support
    // staff (37xxx): View = the Support desk (everyone's tickets), Update =
    // reply as support and change status - checked in the model per ticket.
    public function index() {

        $meaction = $this->request->getPostGet('meaction');

        // PHP drops the whole POST when it is over post_max_size (big uploads)
        if ($meaction === null && strtolower($this->request->getMethod()) === 'post' && (int) $this->request->getServer('CONTENT_LENGTH') > 0) {
            return $this->response->setStatusCode(413)->setJSON(['error' => 'Files are too big to send. Try fewer or smaller files.']);
        }

        switch ($meaction) {
            //TOPBAR INBOX
            case 'INBOX':
                return $this->response->setJSON($this->mymessages->inbox());

            //CHATS
            case 'CONV-LIST':
                return $this->response->setJSON(['convs' => $this->mymessages->conv_list()]);

            case 'CONV-LOAD':
                $conv = $this->mymessages->conv_get($this->request->getGet('conv_id'));
                if ($conv === null) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'Chat not found.']);
                }
                return $this->response->setJSON([
                    'conv' => $conv,
                    'messages' => $this->mymessages->conv_messages($conv['recid'], (int) $this->request->getGet('after_id')),
                ]);

            case 'CONV-START':
                return $this->json($this->mymessages->conv_start());

            case 'MSG-SEND':
                return $this->json($this->mymessages->msg_send());

            case 'CONV-REMOVE':
                return $this->json($this->mymessages->conv_remove());

            case 'CONV-ARCHIVE':
                return $this->json($this->mymessages->conv_archive());

            case 'CHAT-FILE':
                return $this->attachment($this->mymessages->chat_attachment($this->request->getGet('id')));

            //TICKETS
            case 'TICKET-SAVE':
                $this->mymessages->ticket_save();
                return redirect()->to('mymessages?meaction=MAIN&tab=tickets');

            case 'TICKET-LIST':
                return $this->response->setJSON(['tickets' => $this->mymessages->ticket_list()]);

            case 'TICKET-LOAD':
                $data = $this->mymessages->ticket_load($this->request->getGet('recid'));
                if ($data === null) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'Ticket not found.']);
                }
                return $this->response->setJSON($data);

            case 'TICKET-REPLY':
                return $this->json($this->mymessages->ticket_reply());

            case 'TICKET-STATUS':
                return $this->json($this->mymessages->ticket_status());

            case 'ATTACHMENT':
                return $this->attachment($this->mymessages->ticket_attachment($this->request->getGet('id')));

            case 'MAIN':
            default:
                return $this->loadMainView();
        }
    }

    // Model results carry 'error' on failure -> 422 so the JS shows it
    private function json(array $result) {
        return $this->response->setStatusCode(isset($result['error']) ? 422 : 200)->setJSON($result);
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

    private function loadMainView() {

        // Modules the user can open, for "Which part of FODS?" on a ticket
        $modules = [];
        foreach (fods_nav() as $group) {
            foreach ($group['items'] as $item) {
                $modules[] = $item['label'];
            }
        }
        $modules[] = 'Messages';
        $modules[] = 'Log in / account';
        $modules[] = 'Other';

        $canDesk = $this->mymessages->desk_access();
        $tab = (string) $this->request->getGet('tab');
        if (!in_array($tab, $canDesk ? ['tickets', 'desk'] : ['tickets'], true)) {
            $tab = 'chats';
        }

        return view('messages/messages-main', [
            'convs' => $this->mymessages->conv_list(),
            'tickets' => $this->mymessages->ticket_list(),
            'users' => $this->mymessages->users_lookup(),
            'modules' => array_values(array_unique($modules)),
            'lookups' => [
                'categories' => \App\Models\MyMessagesModel::CATEGORIES,
                'priorities' => \App\Models\MyMessagesModel::PRIORITIES,
                'statuses' => \App\Models\MyMessagesModel::STATUSES,
            ],
            'perms' => [
                'desk' => $canDesk,
                'work' => fods_has_access(\Config\AccessCodes::TICKET_UPDATE),
            ],
            'open' => [
                'tab' => $tab,
                'conv' => (int) $this->request->getGet('conv'),
                'ticket' => (int) $this->request->getGet('ticket'),
                'to' => (string) $this->request->getGet('to'),
            ],
            'me' => (string) $this->cuser,
        ]);
    }

}
