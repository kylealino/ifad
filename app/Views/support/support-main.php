<?php
$this->request = \Config\Services::request();
$this->db = \Config\Database::connect();
$is_admin = isset($is_admin) ? $is_admin : false;
$user_id = isset($user_id) ? $user_id : null;
$current_user = isset($current_user) ? $current_user : '';

echo view('templates/myheader.php');
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .chat-container {
        max-width: 1400px;
        margin: 20px auto;
        padding: 0 15px;
    }
    
    .ticket-list {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        max-height: 70vh;
        overflow-y: auto;
    }
    
    .ticket-item {
        padding: 15px;
        border-bottom: 1px solid #e9ecef;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
    }
    
    .ticket-item:hover {
        background: #f8f9fa;
    }
    
    .ticket-item.active {
        background: #e3f2fd;
        border-left: 4px solid #0d6efd;
    }
    
    .ticket-item .subject {
        font-weight: 600;
        color: #1a1a2e;
    }
    
    .ticket-item .meta {
        font-size: 12px;
        color: #6c757d;
        margin-top: 5px;
    }
    
    .ticket-item .status-badge {
        font-size: 11px;
        padding: 2px 10px;
        border-radius: 12px;
        font-weight: 500;
    }
    
    .status-open { background: #ffebee; color: #c62828; }
    .status-in_progress { background: #fff3e0; color: #e65100; }
    .status-closed { background: #e8f5e9; color: #2e7d32; }
    
    .unread-badge {
        background: #dc3545;
        color: white;
        border-radius: 50%;
        padding: 2px 8px;
        font-size: 12px;
        font-weight: bold;
        position: absolute;
        right: 15px;
        top: 15px;
    }
    
    .chat-window {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        height: 70vh;
    }
    
    .chat-header {
        padding: 15px 20px;
        border-bottom: 1px solid #e9ecef;
        background: #f8f9fa;
        border-radius: 10px 10px 0 0;
    }
    
    .chat-header .ticket-subject {
        font-weight: 600;
        font-size: 16px;
    }
    
    .chat-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        background: #fafafa;
    }
    
    .message {
        margin-bottom: 15px;
        display: flex;
        flex-direction: column;
    }
    
    .message.user { align-items: flex-start; }
    .message.admin { align-items: flex-end; }
    
    .message .bubble {
        max-width: 70%;
        padding: 10px 15px;
        border-radius: 18px;
        word-wrap: break-word;
    }
    
    .message.user .bubble {
        background: #e3f2fd;
        border-bottom-left-radius: 5px;
    }
    
    .message.admin .bubble {
        background: #0d6efd;
        color: white;
        border-bottom-right-radius: 5px;
    }
    
    .message .sender-name {
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 3px;
        color: #6c757d;
    }
    
    .message .time {
        font-size: 10px;
        color: #adb5bd;
        margin-top: 3px;
    }
    
    .chat-input {
        padding: 15px 20px;
        border-top: 1px solid #e9ecef;
        background: white;
        border-radius: 0 0 10px 10px;
    }
    
    .chat-input textarea {
        resize: none;
        border-radius: 20px;
    }
    
    .chat-input .btn-send {
        border-radius: 20px;
        padding: 10px 25px;
    }
    
    .empty-state {
        padding: 40px;
        text-align: center;
        color: #adb5bd;
    }
    
    .empty-state i {
        font-size: 48px;
        margin-bottom: 15px;
    }
    
    .new-ticket-form {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 20px;
        margin-bottom: 20px;
    }
    
    @media (max-width: 768px) {
        .ticket-list {
            max-height: 200px;
            margin-bottom: 15px;
        }
        .chat-window {
            height: 60vh;
        }
    }
</style>

<div class="container-fluid chat-container">
    <!-- Page Header -->
    <div class="row mb-3">
        <div class="col-sm-12">
            <h4 class="fw-semibold mb-0">
                <i class="fas fa-headset me-2"></i>FODS Support Center
            </h4>
        </div>
    </div>

    <!-- New Ticket Form (For Users Only) -->
    <?php if (!$is_admin): ?>
    <div class="row mb-3">
        <div class="col-sm-12">
            <div class="new-ticket-form">
                <h5 class="mb-3"><i class="fas fa-plus-circle me-2"></i>Create New Support Ticket</h5>
                <div class="row">
                    <div class="col-sm-12 mb-3">
                        <input type="text" id="ticketSubject" class="form-control form-control-sm" placeholder="Subject" required>
                    </div>
                    <div class="col-sm-12 mb-3">
                        <textarea id="ticketMessage" class="form-control form-control-sm" rows="3" placeholder="Describe your issue in detail..." required></textarea>
                    </div>
                    <div class="col-sm-12">
                        <button onclick="createTicket()" class="btn btn-primary btn-sm">
                            <i class="fas fa-paper-plane me-1"></i>Submit Request
                        </button>
                        <span id="ticketStatus" class="ms-3"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Chat Area -->
    <div class="row">
        <!-- Ticket List (Left Sidebar) -->
        <div class="col-md-4 col-lg-3 mb-3 mb-md-0">
            <div class="ticket-list" id="ticketList">
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p class="mb-0">No tickets found</p>
                    <small>Loading...</small>
                </div>
            </div>
        </div>

        <!-- Chat Window (Right Side) -->
        <div class="col-md-8 col-lg-9">
            <div class="chat-window" id="chatWindow">
                <!-- Header -->
                <div class="chat-header" id="chatHeader">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="ticket-subject" id="ticketSubjectDisplay">Select a ticket</div>
                            <div class="ticket-status" id="ticketStatusDisplay">
                                <span class="text-muted">No ticket selected</span>
                            </div>
                        </div>
                        <div>
                            <?php if ($is_admin): ?>
                            <button onclick="closeTicket()" class="btn btn-sm btn-outline-danger" id="btnCloseTicket" style="display:none;">
                                <i class="fas fa-times"></i> Close Ticket
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <div class="chat-messages" id="chatMessages">
                    <div class="empty-state">
                        <i class="fas fa-comment-dots"></i>
                        <p class="mb-0">No messages yet</p>
                        <small>Select a ticket to start chatting</small>
                    </div>
                </div>

                <!-- Input -->
                <div class="chat-input" id="chatInput" style="display: none;">
                    <div class="row g-2">
                        <div class="col-10">
                            <textarea id="messageInput" class="form-control form-control-sm" rows="2" placeholder="Type your message..." 
                                onkeypress="if(event.keyCode==13 && !event.shiftKey){event.preventDefault();sendMessage();}"></textarea>
                        </div>
                        <div class="col-2">
                            <button onclick="sendMessage()" class="btn btn-primary btn-send w-100">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Global variables
let currentTicketId = null;
let lastMessageId = 0;
let pollingInterval = null;
let isAdmin = <?= $is_admin ? 'true' : 'false' ?>;
let userId = <?= $user_id ?? 0 ?>;

// ============================================
// TICKET LIST FUNCTIONS
// ============================================

function loadTickets() {
    $.ajax({
        url: '<?= site_url("support") ?>',
        method: 'GET',
        data: { meaction: 'GET-TICKETS' },
        success: function(response) {
            if (!response.success) {
                console.error('Error loading tickets:', response.message);
                return;
            }
            
            const list = $('#ticketList');
            list.empty();
            
            if (response.tickets.length === 0) {
                list.html(`
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p class="mb-0">No tickets found</p>
                        <small>${isAdmin ? 'No support requests yet' : 'Your support requests will appear here'}</small>
                    </div>
                `);
                return;
            }
            
            response.tickets.forEach(ticket => {
                const statusClass = ticket.status;
                const statusText = ticket.status.replace('_', ' ').toUpperCase();
                const unreadBadge = ticket.unread_count > 0 ? 
                    `<span class="unread-badge">${ticket.unread_count}</span>` : '';
                
                const item = $(`
                    <div class="ticket-item ${currentTicketId == ticket.recid ? 'active' : ''}" 
                         onclick="selectTicket(${ticket.recid})">
                        <div>
                            <span class="subject">${ticket.subject || 'No Subject'}</span>
                            <span class="status-badge status-${statusClass}">${statusText}</span>
                            ${unreadBadge}
                        </div>
                        <div class="meta">
                            ${ticket.last_message || 'No messages yet'}
                        </div>
                        <div class="meta">
                            ${ticket.user_name || ticket.user_username} • ${new Date(ticket.updated_at).toLocaleString()}
                        </div>
                    </div>
                `);
                list.append(item);
            });
            
            // Update unread count badge in sidebar
            updateUnreadBadge(response.tickets);
        }
    });
}

function updateUnreadBadge(tickets) {
    let totalUnread = 0;
    tickets.forEach(ticket => {
        totalUnread += ticket.unread_count || 0;
    });
    
    if (totalUnread > 0) {
        $('#unreadBadge').text(totalUnread).show();
    } else {
        $('#unreadBadge').hide();
    }
}

// ============================================
// TICKET SELECTION
// ============================================

function selectTicket(ticketId) {
    if (currentTicketId === ticketId) return;
    
    currentTicketId = ticketId;
    lastMessageId = 0;
    
    // Update UI
    loadTickets();
    showChat(true);
    
    // Load messages
    loadMessages(ticketId);
    
    // Start polling for new messages
    if (pollingInterval) clearInterval(pollingInterval);
    pollingInterval = setInterval(() => {
        checkNewMessages(ticketId);
    }, 3000);
}

function showChat(show) {
    if (show) {
        $('#chatInput').show();
        $('#btnCloseTicket').show();
    } else {
        $('#chatInput').hide();
        $('#btnCloseTicket').hide();
    }
}

// ============================================
// MESSAGE FUNCTIONS
// ============================================

function loadMessages(ticketId) {
    $.ajax({
        url: '<?= site_url("support") ?>',
        method: 'GET',
        data: { 
            meaction: 'GET-MESSAGES',
            ticket_id: ticketId,
            last_id: 0
        },
        success: function(response) {
            if (!response.success) {
                console.error('Error loading messages:', response.message);
                return;
            }
            
            // Update header
            const ticket = response.messages.length > 0 ? response.messages[0] : null;
            $('#ticketSubjectDisplay').text('Loading...');
            
            if (response.messages) {
                displayMessages(response.messages);
                if (response.messages.length > 0) {
                    lastMessageId = response.messages[response.messages.length - 1].recid;
                }
                scrollToBottom();
                
                // Get ticket info for header
                $.ajax({
                    url: '<?= site_url("support") ?>',
                    method: 'GET',
                    data: { 
                        meaction: 'GET-TICKETS'
                    },
                    success: function(ticketResponse) {
                        if (ticketResponse.success) {
                            const ticket = ticketResponse.tickets.find(t => t.recid == ticketId);
                            if (ticket) {
                                $('#ticketSubjectDisplay').text(ticket.subject);
                                $('#ticketStatusDisplay').html(`
                                    <span class="status-badge status-${ticket.status}">
                                        ${ticket.status.replace('_', ' ').toUpperCase()}
                                    </span>
                                    ${ticket.status != 'closed' ? 
                                        '<span class="text-success ms-2"><i class="fas fa-circle" style="font-size:8px;"></i> Active</span>' : 
                                        '<span class="text-danger ms-2"><i class="fas fa-circle" style="font-size:8px;"></i> Closed</span>'
                                    }
                                `);
                            }
                        }
                    }
                });
            }
        }
    });
}

function checkNewMessages(ticketId) {
    if (!ticketId) return;
    
    $.ajax({
        url: '<?= site_url("support") ?>',
        method: 'GET',
        data: { 
            meaction: 'GET-MESSAGES',
            ticket_id: ticketId,
            last_id: lastMessageId
        },
        success: function(response) {
            if (response.success && response.messages && response.messages.length > 0) {
                displayMessages(response.messages);
                lastMessageId = response.messages[response.messages.length - 1].recid;
                scrollToBottom();
                
                // Refresh ticket list to update unread counts
                loadTickets();
            }
        }
    });
}

function displayMessages(messages) {
    const container = $('#chatMessages');
    // Remove empty state if present
    container.find('.empty-state').remove();
    
    messages.forEach(msg => {
        const isMine = (msg.sender_type === 'user' && !isAdmin) || 
                      (msg.sender_type === 'admin' && isAdmin);
        const senderName = msg.sender_name || (msg.sender_type === 'admin' ? 'Admin' : 'User');
        
        const messageHtml = `
            <div class="message ${msg.sender_type}">
                <div class="sender-name">${senderName}</div>
                <div class="bubble">${escapeHtml(msg.message)}</div>
                <div class="time">${new Date(msg.added_at).toLocaleString()}</div>
            </div>
        `;
        container.append(messageHtml);
    });
}

function sendMessage() {
    const message = $('#messageInput').val().trim();
    if (!message || !currentTicketId) return;
    
    // Optimistic UI update
    const tempMessage = {
        recid: 'temp_' + Date.now(),
        message: message,
        sender_type: isAdmin ? 'admin' : 'user',
        sender_name: 'You',
        added_at: new Date().toISOString()
    };
    
    displayMessages([tempMessage]);
    $('#messageInput').val('');
    scrollToBottom();
    
    // Send to server
    $.ajax({
        url: '<?= site_url("support") ?>',
        method: 'POST',
        data: {
            meaction: 'SEND-MESSAGE',
            ticket_id: currentTicketId,
            message: message
        },
        success: function(response) {
            if (response.success) {
                // Message sent successfully
            } else {
                alert('Error sending message: ' + response.message);
            }
        }
    });
}

function closeTicket() {
    if (!currentTicketId) return;
    if (!confirm('Are you sure you want to close this ticket?')) return;
    
    $.ajax({
        url: '<?= site_url("support") ?>',
        method: 'POST',
        data: {
            meaction: 'CLOSE-TICKET',
            ticket_id: currentTicketId
        },
        success: function(response) {
            if (response.success) {
                alert('Ticket closed successfully!');
                loadTickets();
                $('#chatMessages').html(`
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <p class="mb-0">Ticket closed</p>
                        <small>This conversation has been closed</small>
                    </div>
                `);
                $('#chatInput').hide();
                $('#btnCloseTicket').hide();
                $('#ticketStatusDisplay').html('<span class="text-danger">Closed</span>');
            } else {
                alert('Error closing ticket: ' + response.message);
            }
        }
    });
}

// ============================================
// TICKET CREATION
// ============================================

function createTicket() {
    const subject = $('#ticketSubject').val().trim();
    const message = $('#ticketMessage').val().trim();
    
    if (!subject || !message) {
        alert('Please fill in subject and message.');
        return;
    }
    
    $('#ticketStatus').html('<span class="text-info">Submitting...</span>');
    
    $.ajax({
        url: '<?= site_url("support") ?>',
        method: 'POST',
        data: {
            meaction: 'CREATE-TICKET',
            subject: subject,
            message: message
        },
        success: function(response) {
            if (response.success) {
                $('#ticketStatus').html('<span class="text-success">Ticket created successfully!</span>');
                $('#ticketSubject').val('');
                $('#ticketMessage').val('');
                loadTickets();
                setTimeout(() => {
                    $('#ticketStatus').html('');
                }, 3000);
            } else {
                $('#ticketStatus').html('<span class="text-danger">Error: ' + response.message + '</span>');
            }
        }
    });
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function scrollToBottom() {
    const container = document.getElementById('chatMessages');
    setTimeout(() => {
        container.scrollTop = container.scrollHeight;
    }, 100);
}

// ============================================
// INITIALIZATION
// ============================================

$(document).ready(function() {
    loadTickets();
    
    // Auto-refresh tickets every 30 seconds
    setInterval(loadTickets, 30000);
});

// Clear polling interval when page unloads
$(window).on('beforeunload', function() {
    if (pollingInterval) clearInterval(pollingInterval);
});
</script>

<?php
echo view('templates/myfooter.php');
?>