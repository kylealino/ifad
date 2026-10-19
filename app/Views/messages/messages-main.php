<?php echo view('templates/myheader.php', ['pageTitle' => 'Messages']); ?>
<head>
<link rel="stylesheet" href="<?=base_url('assets/css/fods-chat.css?v=1');?>">
</head>
<div class="container-fluid">
    <input type="hidden" id="__siteurl" data-mesiteurl="<?=site_url();?>" />
    <div class="row me-outp-msg mx-0"></div>

    <div class="fods-page-head">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?=site_url();?>"><i class="ti ti-home"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Messages</li>
                </ol>
            </nav>
            <h1 class="fods-page-title">Messages <span class="fods-count align-middle" id="chatsCount" hidden></span></h1>
            <p class="fods-page-sub">Chat with anyone in FODS. Need help from support? Use the Helpdesk <i class="ti ti-lifebuoy"></i> beside the messages icon.</p>
        </div>
        <div class="fods-page-actions d-flex">
            <button type="button" class="btn btn-fods btn-sm px-3 py-2" id="convAdd"><i class="ti ti-message-plus me-1"></i> New message</button>
        </div>
    </div>

    <div class="fods-card msg-shell" id="msgShell">
        <aside class="msg-side">
            <div class="msg-side-head">
                <div class="fods-search">
                    <i class="ti ti-search"></i>
                    <input type="search" class="form-control form-control-sm" id="convSearch" placeholder="Search chats...">
                </div>
                <div class="fods-filter msg-box" id="convBox">
                    <button type="button" class="active" data-box="inbox"><i class="ti ti-messages"></i> Chats</button>
                    <button type="button" data-box="archived"><i class="ti ti-archive"></i> Archived <span class="fods-count" id="archivedCount">0</span></button>
                </div>
            </div>
            <div class="msg-convs" id="convList"></div>
        </aside>

        <section class="msg-thread">
            <div class="msg-empty" id="msgEmpty">
                <div class="fods-stat-icon tone-teal"><i class="ti ti-messages"></i></div>
                <div class="fw-semibold text-dark">Pick a chat, or start a new one.</div>
                <div class="small">Messages go to people who have a FODS account.</div>
                <button type="button" class="btn btn-fods btn-sm px-3 mt-2" id="convAddEmpty"><i class="ti ti-message-plus me-1"></i> New message</button>
            </div>
            <div class="d-none flex-column h-100" id="msgOpen" style="min-height:0">
                <div class="msg-thread-head">
                    <button type="button" class="fods-icon-btn msg-back" id="msgBack" aria-label="Back to chats"><i class="ti ti-arrow-left"></i></button>
                    <div class="fods-avatar tone-teal" id="msgAvatar"></div>
                    <div style="min-width:0">
                        <div class="msg-thread-name" id="msgName"></div>
                        <div class="msg-thread-sub" id="msgSub"></div>
                    </div>
                    <div class="ms-auto d-flex gap-1 flex-shrink-0">
                        <button type="button" class="fods-icon-btn is-teal d-none msg-popout" id="msgPopout" title="Open as chat head: keep this chat open while you go to other pages"><i class="ti ti-picture-in-picture"></i></button>
                        <button type="button" class="fods-icon-btn is-teal d-none" id="msgMembers" title="Group members"><i class="ti ti-users"></i></button>
                        <button type="button" class="fods-icon-btn is-teal" id="msgArchive" title="Archive chat"><i class="ti ti-archive"></i></button>
                    </div>
                </div>
                <div class="msg-archived-bar d-none" id="msgArchivedBar"><i class="ti ti-archive"></i> This chat is archived. A new message brings it back to your chats.</div>
                <div class="msg-body" id="msgBody"></div>
                <div class="msg-typing d-none" id="msgTyping" aria-live="polite"><span class="msg-typing-dots"><i></i><i></i><i></i></span><span id="msgTypingText"></span></div>
                <form class="msg-compose" id="msgForm" autocomplete="off">
                    <div class="tk-thumbs" id="msgThumbs"></div>
                    <div class="msg-compose-row">
                        <label for="msgFiles" class="fods-icon-btn is-teal msg-attach" title="Attach images or files (up to 5, 10 MB each). You can also paste a screenshot with Ctrl+V."><i class="ti ti-paperclip"></i></label>
                        <input type="file" id="msgFiles" multiple hidden accept="image/png,image/jpeg,image/gif,image/webp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.csv,.txt,.zip">
                        <textarea class="form-control" id="msgText" rows="1" maxlength="2000" placeholder="Write a message... (Ctrl+V pastes a screenshot)" title="Enter to send, Shift+Enter for a new line. Paste a screenshot (Snipping Tool, Win+Shift+S) or drop files to attach them."></textarea>
                        <button type="submit" class="btn btn-fods px-3" id="msgSendBtn" style="height:38px;"><i class="ti ti-send"></i></button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<!-- New chat -->
<div class="modal fade fods-modal" id="convModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <form id="convForm" novalidate>
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <div class="fods-modal-icon tone-teal"><i class="ti ti-message-plus"></i></div>
                        <div>
                            <h5 class="modal-title">New message</h5>
                            <div class="fods-modal-sub">Pick one person, or several for a group chat.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="fods-section mt-0">To</div>
                    <div class="msg-picked mb-2" id="convPicked"></div>
                    <div class="fods-search mb-2">
                        <i class="ti ti-search"></i>
                        <input type="search" class="form-control form-control-sm w-100" id="convPeopleSearch" placeholder="Search name, username, division..." style="min-width:0">
                    </div>
                    <div class="msg-pick-list" id="convPeople"></div>

                    <div class="d-none" id="convTitleWrap">
                        <label class="form-label mt-3" for="convTitle">Group name <span class="text-muted">(optional)</span></label>
                        <input type="text" class="form-control" id="convTitle" maxlength="100" placeholder="e.g. ORS follow-ups">
                    </div>

                    <label class="form-label mt-3" for="convMessage">Message</label>
                    <textarea class="form-control" id="convMessage" rows="3" maxlength="2000" placeholder="Say hello, or get straight to it."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-fods-ghost px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-fods px-4" id="convSubmitBtn">Start chat</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Group members -->
<div class="modal fade fods-modal" id="membersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <div class="fods-modal-icon tone-indigo"><i class="ti ti-users"></i></div>
                    <div>
                        <h5 class="modal-title" id="membersTitle">Group members</h5>
                        <div class="fods-modal-sub" id="membersSub"></div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="msg-pick-list" id="membersList"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-fods-ghost px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Image preview -->
<div class="modal fade fods-modal" id="imgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imgModalName" style="overflow-wrap:anywhere"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <img src="" alt="" id="imgModalImg">
                <div class="text-end mt-2"><a href="#" target="_blank" rel="noopener" class="small" id="imgModalLink"><i class="ti ti-external-link"></i> Open full size</a></div>
            </div>
        </div>
    </div>
</div>

<script>
    var __msg_convs = <?= json_encode($convs, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __msg_users = <?= json_encode($users, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __msg_open = <?= json_encode($open, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __msg_me = <?= json_encode($me, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="<?=base_url('assets/js/messages/mymessages.js?v=12');?>"></script>
<?php echo view('templates/myfooter.php'); ?>
