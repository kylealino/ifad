<?php
namespace App\Models;
use CodeIgniter\Model;

class SupportTicketModel extends Model
{
    protected $db;

    public function __construct(){
        parent::__construct();
        $this->session = session();
        $this->request = \Config\Services::request();
        $this->db = \Config\Database::connect();
        $this->cuser = $this->session->get('__xsys_myuserzicas__');
    }

    public function generateTicketNo() {
        $year = date('Y');
        $month = date('m');
        
        $query = $this->db->query("
            SELECT COUNT(*) as count 
            FROM tbl_support_tickets 
            WHERE YEAR(added_at) = '$year' AND MONTH(added_at) = '$month'
        ");
        $result = $query->getRowArray();
        $count = $result['count'] + 1;
        
        return 'SUP-' . $year . $month . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function getTicketList($isAdmin = false, $userId = null) {
        if ($isAdmin) {
            $query = $this->db->query("
                SELECT 
                    t.*,
                    u.full_name as user_name,
                    u.username as user_username,
                    a.full_name as admin_name,
                    a.username as admin_username
                FROM tbl_support_tickets t
                LEFT JOIN myua_user u ON u.recid = t.user_id
                LEFT JOIN myua_user a ON a.recid = t.admin_id
                ORDER BY t.updated_at DESC
            ");
        } else {
            $query = $this->db->query("
                SELECT 
                    t.*,
                    u.full_name as user_name,
                    u.username as user_username,
                    a.full_name as admin_name,
                    a.username as admin_username
                FROM tbl_support_tickets t
                LEFT JOIN myua_user u ON u.recid = t.user_id
                LEFT JOIN myua_user a ON a.recid = t.admin_id
                WHERE t.user_id = '$userId'
                ORDER BY t.updated_at DESC
            ");
        }
        
        $tickets = $query->getResultArray();
        
        // Get last message and unread count for each ticket
        foreach ($tickets as &$ticket) {
            // Get last message
            $msgQuery = $this->db->query("
                SELECT * 
                FROM tbl_support_messages 
                WHERE ticket_id = '" . $ticket['recid'] . "' 
                ORDER BY added_at DESC 
                LIMIT 1
            ");
            $lastMsg = $msgQuery->getRowArray();
            $ticket['last_message'] = $lastMsg ? $lastMsg['message'] : 'No messages yet';
            $ticket['last_message_time'] = $lastMsg ? $lastMsg['added_at'] : $ticket['added_at'];
            
            // Count unread messages
            if ($isAdmin) {
                $unreadQuery = $this->db->query("
                    SELECT COUNT(*) as count 
                    FROM tbl_support_messages 
                    WHERE ticket_id = '" . $ticket['recid'] . "' 
                    AND is_read = 0 
                    AND sender_type = 'user'
                ");
            } else {
                $unreadQuery = $this->db->query("
                    SELECT COUNT(*) as count 
                    FROM tbl_support_messages 
                    WHERE ticket_id = '" . $ticket['recid'] . "' 
                    AND is_read = 0 
                    AND sender_type = 'admin'
                ");
            }
            $unreadResult = $unreadQuery->getRowArray();
            $ticket['unread_count'] = $unreadResult['count'];
        }
        
        return $tickets;
    }

    public function getTicketMessages($ticketId) {
        $query = $this->db->query("
            SELECT 
                m.*,
                u.full_name as sender_name,
                u.username as sender_username
            FROM tbl_support_messages m
            LEFT JOIN myua_user u ON u.recid = m.sender_id
            WHERE m.ticket_id = '$ticketId'
            ORDER BY m.added_at ASC
        ");
        return $query->getResultArray();
    }

    public function createTicket($data) {
        $ticketNo = $this->generateTicketNo();
        
        $query = $this->db->query("
            INSERT INTO tbl_support_tickets (
                ticket_no,
                user_id,
                subject,
                status,
                added_at,
                updated_at,
                added_by
            )
            VALUES (
                '$ticketNo',
                '" . $data['user_id'] . "',
                '" . addslashes($data['subject']) . "',
                'open',
                NOW(),
                NOW(),
                '" . $this->cuser . "'
            )
        ");
        
        $ticketId = $this->db->insertID();
        return $ticketId;
    }

    public function addMessage($data) {
        $query = $this->db->query("
            INSERT INTO tbl_support_messages (
                ticket_id,
                sender_id,
                sender_type,
                message,
                is_read,
                added_at,
                added_by
            )
            VALUES (
                '" . $data['ticket_id'] . "',
                '" . $data['sender_id'] . "',
                '" . $data['sender_type'] . "',
                '" . addslashes($data['message']) . "',
                0,
                NOW(),
                '" . $this->cuser . "'
            )
        ");
        
        $messageId = $this->db->insertID();
        
        // Update ticket status if admin is responding
        if ($data['sender_type'] == 'admin') {
            $this->db->query("
                UPDATE tbl_support_tickets 
                SET 
                    status = 'in_progress',
                    admin_id = '" . $data['sender_id'] . "',
                    updated_at = NOW()
                WHERE recid = '" . $data['ticket_id'] . "'
            ");
        } else {
            $this->db->query("
                UPDATE tbl_support_tickets 
                SET 
                    status = 'open',
                    updated_at = NOW()
                WHERE recid = '" . $data['ticket_id'] . "'
            ");
        }
        
        return $messageId;
    }

    public function getNewMessages($ticketId, $lastMessageId = 0) {
        $query = $this->db->query("
            SELECT 
                m.*,
                u.full_name as sender_name,
                u.username as sender_username
            FROM tbl_support_messages m
            LEFT JOIN myua_user u ON u.recid = m.sender_id
            WHERE m.ticket_id = '$ticketId'
            AND m.recid > '$lastMessageId'
            ORDER BY m.added_at ASC
        ");
        return $query->getResultArray();
    }

    public function markMessagesAsRead($ticketId, $userId, $isAdmin = false) {
        if ($isAdmin) {
            $query = $this->db->query("
                UPDATE tbl_support_messages 
                SET is_read = 1 
                WHERE ticket_id = '$ticketId' 
                AND is_read = 0 
                AND sender_type = 'user'
            ");
        } else {
            $query = $this->db->query("
                UPDATE tbl_support_messages 
                SET is_read = 1 
                WHERE ticket_id = '$ticketId' 
                AND is_read = 0 
                AND sender_type = 'admin'
            ");
        }
        return $query;
    }

    public function closeTicket($ticketId) {
        $query = $this->db->query("
            UPDATE tbl_support_tickets 
            SET 
                status = 'closed',
                updated_at = NOW()
            WHERE recid = '$ticketId'
        ");
        return $query;
    }

    public function getUnreadCount($userId, $isAdmin = false) {
        if ($isAdmin) {
            $query = $this->db->query("
                SELECT COUNT(*) as count 
                FROM tbl_support_messages m
                LEFT JOIN tbl_support_tickets t ON t.recid = m.ticket_id
                WHERE m.is_read = 0 
                AND m.sender_type = 'user'
            ");
        } else {
            $query = $this->db->query("
                SELECT COUNT(*) as count 
                FROM tbl_support_messages m
                LEFT JOIN tbl_support_tickets t ON t.recid = m.ticket_id
                WHERE m.is_read = 0 
                AND m.sender_type = 'admin'
                AND t.user_id = '$userId'
            ");
        }
        $result = $query->getRowArray();
        return $result['count'];
    }

    public function getTicketById($ticketId) {
        $query = $this->db->query("
            SELECT 
                t.*,
                u.full_name as user_name,
                u.username as user_username
            FROM tbl_support_tickets t
            LEFT JOIN myua_user u ON u.recid = t.user_id
            WHERE t.recid = '$ticketId'
        ");
        return $query->getRowArray();
    }

    public function verifyUserTicket($ticketId, $userId) {
        $query = $this->db->query("
            SELECT recid 
            FROM tbl_support_tickets 
            WHERE recid = '$ticketId' 
            AND user_id = '$userId'
        ");
        return $query->getNumRows() > 0;
    }
}