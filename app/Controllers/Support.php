<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class Support extends BaseController
{
    public function __construct()
    {
        $this->request = \Config\Services::request();
        $this->supportTicket = model('App\Models\SupportTicketModel');
        $this->db = \Config\Database::connect();
        $this->session = session();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
    }

    public function index() {
        $meaction = $this->request->getPostGet('meaction');
        
        // Get user info from myua_user table
        $userQuery = $this->db->query("
            SELECT recid, full_name, username 
            FROM myua_user 
            WHERE username = '" . $this->cuser . "' 
            AND is_active = 1
        ");
        $userData = $userQuery->getRowArray();
        
        // Check if user is admin (username starts with 'ADMIN-')
        $isAdmin = (strpos($this->cuser, 'ADMIN-') === 0) ? true : false;
        $userId = $userData ? $userData['recid'] : null;
        
        switch ($meaction) {
            case 'MAIN':
                return $this->loadMainView($isAdmin, $userId);
                break;
                
            case 'CREATE-TICKET':
                return $this->createTicket();
                break;
                
            case 'SEND-MESSAGE':
                return $this->sendMessage();
                break;
                
            case 'GET-MESSAGES':
                return $this->getMessages();
                break;
                
            case 'GET-TICKETS':
                return $this->getTickets();
                break;
                
            case 'CLOSE-TICKET':
                return $this->closeTicket();
                break;
                
            case 'GET-UNREAD':
                return $this->getUnreadCount();
                break;
                
            default:
                return $this->loadMainView($isAdmin, $userId);
        }
    }

    private function loadMainView($isAdmin = false, $userId = null) {
        $data = [
            'is_admin' => $isAdmin,
            'user_id' => $userId,
            'current_user' => $this->cuser
        ];
        
        return view('support/support-main', $data);
    }

    private function createTicket() {
        // Get user info
        $userQuery = $this->db->query("
            SELECT recid 
            FROM myua_user 
            WHERE username = '" . $this->cuser . "' 
            AND is_active = 1
        ");
        $userData = $userQuery->getRowArray();
        $userId = $userData ? $userData['recid'] : null;
        
        if (!$userId) {
            echo "
            <script>
            toastr.error('User not found!', 'Oops!', {
                    progressBar: true,
                    closeButton: true,
                    timeOut:2000,
                });
            </script>
            ";
            die();
        }
        
        $subject = $this->request->getPostGet('subject');
        $message = $this->request->getPostGet('message');
        
        if (empty($subject) || empty($message)) {
            echo "
            <script>
            toastr.error('Subject and message are required!', 'Oops!', {
                    progressBar: true,
                    closeButton: true,
                    timeOut:2000,
                });
            </script>
            ";
            die();
        }
        
        // Create ticket
        $ticketId = $this->supportTicket->createTicket([
            'user_id' => $userId,
            'subject' => $subject
        ]);
        
        // Add first message
        $this->supportTicket->addMessage([
            'ticket_id' => $ticketId,
            'sender_id' => $userId,
            'sender_type' => 'user',
            'message' => $message
        ]);
        
        return $this->response->setJSON([
            'success' => true,
            'ticket_id' => $ticketId,
            'message' => 'Ticket created successfully'
        ]);
    }

    private function sendMessage() {
        // Get user info
        $userQuery = $this->db->query("
            SELECT recid 
            FROM myua_user 
            WHERE username = '" . $this->cuser . "' 
            AND is_active = 1
        ");
        $userData = $userQuery->getRowArray();
        $userId = $userData ? $userData['recid'] : null;
        $isAdmin = (strpos($this->cuser, 'ADMIN-') === 0) ? true : false;
        
        if (!$userId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ]);
        }
        
        $ticketId = $this->request->getPostGet('ticket_id');
        $message = $this->request->getPostGet('message');
        
        if (empty($ticketId) || empty($message)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ticket ID and message are required'
            ]);
        }
        
        // Verify access (for non-admin users)
        if (!$isAdmin) {
            $ticketQuery = $this->db->query("
                SELECT recid 
                FROM tbl_support_tickets 
                WHERE recid = '$ticketId' 
                AND user_id = '$userId'
            ");
            if ($ticketQuery->getNumRows() == 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ]);
            }
        }
        
        // Add message
        $messageId = $this->supportTicket->addMessage([
            'ticket_id' => $ticketId,
            'sender_id' => $userId,
            'sender_type' => $isAdmin ? 'admin' : 'user',
            'message' => $message
        ]);
        
        return $this->response->setJSON([
            'success' => true,
            'message_id' => $messageId,
            'ticket_id' => $ticketId
        ]);
    }

    private function getMessages() {
        // Get user info
        $userQuery = $this->db->query("
            SELECT recid 
            FROM myua_user 
            WHERE username = '" . $this->cuser . "' 
            AND is_active = 1
        ");
        $userData = $userQuery->getRowArray();
        $userId = $userData ? $userData['recid'] : null;
        $isAdmin = (strpos($this->cuser, 'ADMIN-') === 0) ? true : false;
        
        if (!$userId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ]);
        }
        
        $ticketId = $this->request->getGet('ticket_id');
        $lastId = $this->request->getGet('last_id') ?? 0;
        
        if (empty($ticketId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ticket ID is required'
            ]);
        }
        
        // Verify access (for non-admin users)
        if (!$isAdmin) {
            $ticketQuery = $this->db->query("
                SELECT recid 
                FROM tbl_support_tickets 
                WHERE recid = '$ticketId' 
                AND user_id = '$userId'
            ");
            if ($ticketQuery->getNumRows() == 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ]);
            }
        }
        
        // Get messages
        if ($lastId == 0) {
            // Get all messages
            $messages = $this->supportTicket->getTicketMessages($ticketId);
        } else {
            // Get new messages only
            $messages = $this->supportTicket->getNewMessages($ticketId, $lastId);
        }
        
        // Mark messages as read
        if (!empty($messages)) {
            $this->supportTicket->markMessagesAsRead($ticketId, $userId, $isAdmin);
        }
        
        return $this->response->setJSON([
            'success' => true,
            'messages' => $messages,
            'ticket_id' => $ticketId
        ]);
    }

    private function getTickets() {
        // Get user info
        $userQuery = $this->db->query("
            SELECT recid 
            FROM myua_user 
            WHERE username = '" . $this->cuser . "' 
            AND is_active = 1
        ");
        $userData = $userQuery->getRowArray();
        $userId = $userData ? $userData['recid'] : null;
        $isAdmin = (strpos($this->cuser, 'ADMIN-') === 0) ? true : false;
        
        if (!$userId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found'
            ]);
        }
        
        $tickets = $this->supportTicket->getTicketList($isAdmin, $userId);
        
        return $this->response->setJSON([
            'success' => true,
            'tickets' => $tickets,
            'is_admin' => $isAdmin
        ]);
    }

    private function closeTicket() {
        $isAdmin = (strpos($this->cuser, 'ADMIN-') === 0) ? true : false;
        
        if (!$isAdmin) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Only admins can close tickets'
            ]);
        }
        
        $ticketId = $this->request->getPostGet('ticket_id');
        
        if (empty($ticketId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ticket ID is required'
            ]);
        }
        
        $result = $this->supportTicket->closeTicket($ticketId);
        
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Ticket closed successfully'
        ]);
    }

    private function getUnreadCount() {
        // Get user info
        $userQuery = $this->db->query("
            SELECT recid 
            FROM myua_user 
            WHERE username = '" . $this->cuser . "' 
            AND is_active = 1
        ");
        $userData = $userQuery->getRowArray();
        $userId = $userData ? $userData['recid'] : null;
        $isAdmin = (strpos($this->cuser, 'ADMIN-') === 0) ? true : false;
        
        if (!$userId) {
            return $this->response->setJSON([
                'success' => false,
                'count' => 0
            ]);
        }
        
        $count = $this->supportTicket->getUnreadCount($userId, $isAdmin);
        
        return $this->response->setJSON([
            'success' => true,
            'count' => $count
        ]);
    }
}