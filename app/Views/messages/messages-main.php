<?php echo view('templates/myheader.php', ['pageTitle' => 'Messages']); ?>
<head>
<style>
/* Chats: conversation list + thread */
.msg-shell { display:grid; grid-template-columns:320px minmax(0,1fr); height:calc(100vh - 300px); min-height:500px; overflow:hidden; }
.msg-side { border-right:1px solid var(--fods-border); display:flex; flex-direction:column; min-height:0; }
.msg-side-head { padding:1rem; border-bottom:1px solid var(--fods-border); }
.msg-side-head .fods-search input { min-width:0; width:100%; }
.msg-convs { flex:1; overflow-y:auto; }
.msg-conv { display:flex; gap:.7rem; align-items:center; width:100%; padding:.75rem 1rem; border:0; border-bottom:1px solid #f1f4f8; background:transparent; text-align:left; }
.msg-conv:hover { background:#f8fafc; }
.msg-conv.active { background:var(--fods-teal-soft); }
.msg-conv-main { flex:1; min-width:0; }
.msg-conv-top { display:flex; justify-content:space-between; gap:.5rem; }
.msg-conv-name { font-size:.86rem; font-weight:500; color:var(--fods-text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.msg-conv-time { font-size:.7rem; color:var(--fods-muted); flex-shrink:0; }
.msg-conv-text { font-size:.78rem; color:var(--fods-muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.msg-conv.is-unread .msg-conv-name, .msg-conv.is-unread .msg-conv-text { font-weight:600; color:var(--fods-text); }
.msg-thread { display:flex; flex-direction:column; min-height:0; min-width:0; }
.msg-thread-head { display:flex; align-items:center; gap:.75rem; padding:.85rem 1.25rem; border-bottom:1px solid var(--fods-border); }
.msg-thread-name { font-weight:600; color:var(--fods-text); line-height:1.2; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.msg-thread-sub { font-size:.76rem; color:var(--fods-muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.msg-body { flex:1; overflow-y:auto; padding:1.25rem; background:#fafbfc; }
.msg-day { text-align:center; margin:.75rem 0; font-size:.7rem; color:var(--fods-muted); text-transform:uppercase; letter-spacing:.06em; }
.msg-row { display:flex; flex-direction:column; align-items:flex-start; margin-bottom:.5rem; }
.msg-row.mine { align-items:flex-end; }
.msg-sender { font-size:.72rem; color:var(--fods-muted); margin:0 .6rem .15rem; }
.msg-bubble { max-width:min(75%, 560px); padding:.55rem .85rem; border-radius:16px; background:#fff; border:1px solid var(--fods-border); color:var(--fods-text); font-size:.88rem; white-space:pre-wrap; overflow-wrap:anywhere; }
.msg-row.mine .msg-bubble { background:var(--fods-teal); border-color:var(--fods-teal); color:#fff; }
.msg-time { font-size:.66rem; color:var(--fods-muted); margin:.15rem .6rem 0; }
.msg-compose { display:flex; gap:.5rem; align-items:flex-end; padding:.85rem 1.25rem; border-top:1px solid var(--fods-border); }
.msg-compose textarea { resize:none; border-radius:12px; border-color:var(--fods-border); max-height:140px; }
.msg-compose textarea:focus { border-color:var(--fods-teal); box-shadow:0 0 0 3px var(--fods-teal-soft); }
.msg-empty { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:.5rem; padding:2rem; text-align:center; color:var(--fods-muted); }
.msg-empty .fods-stat-icon { width:56px; height:56px; font-size:1.6rem; border-radius:16px; }
.msg-back { display:none; }
.msg-box { margin-top:.75rem; }
.msg-box .fods-count { background:#dfe5ee; color:var(--fods-muted); border-radius:999px; padding:0 .4rem; font-size:.7rem; margin-left:.2rem; }
.msg-box button.active .fods-count { background:rgba(255,255,255,.2); color:#fff; }
.msg-archived-bar { display:flex; align-items:center; gap:.5rem; padding:.5rem 1.25rem; background:#fef4e2; color:#b45309; font-size:.78rem; border-bottom:1px solid var(--fods-border); }

/* Chat attachments: in the composer and in the bubbles */
.msg-compose { flex-direction:column; align-items:stretch; }
.msg-compose-row { display:flex; gap:.5rem; align-items:flex-end; }
.msg-compose .tk-thumbs { margin:0 0 .6rem; }
.msg-compose .tk-thumbs:empty { display:none; }
.msg-attach { width:38px; height:38px; flex-shrink:0; cursor:pointer; margin:0; }
.msg-thread.is-over .msg-body { outline:2px dashed var(--fods-teal); outline-offset:-10px; background:var(--fods-teal-soft); }
.msg-bubble .msg-text + .msg-atts { margin-top:.45rem; }
.msg-atts { display:flex; flex-wrap:wrap; gap:.4rem; }
.msg-atts .tk-thumb { width:150px; height:150px; cursor:zoom-in; }
.msg-file { display:flex; align-items:center; gap:.6rem; padding:.5rem .7rem; border-radius:10px; background:#f1f5f9; color:var(--fods-text); text-decoration:none; min-width:200px; max-width:280px; }
.msg-file:hover { background:#e8edf3; color:var(--fods-text); }
.msg-file i { font-size:1.5rem; color:var(--fods-teal); flex-shrink:0; }
.msg-file-name { font-size:.8rem; font-weight:500; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.msg-file-size { font-size:.7rem; color:var(--fods-muted); }
.msg-row.mine .msg-file { background:rgba(255,255,255,.16); color:#fff; }
.msg-row.mine .msg-file:hover { background:rgba(255,255,255,.26); }
.msg-row.mine .msg-file i, .msg-row.mine .msg-file-size { color:rgba(255,255,255,.85); }
.tk-thumb.is-file { width:150px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:.25rem; padding:.5rem; text-align:center; }
.tk-thumb.is-file > i { font-size:1.7rem; color:var(--fods-teal); }
.tk-thumb.is-file > span { font-size:.72rem; color:var(--fods-text); width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

/* New chat: people picker */
.msg-pick-list { max-height:280px; overflow-y:auto; border:1px solid var(--fods-border); border-radius:12px; }
.msg-pick { display:flex; align-items:center; gap:.7rem; padding:.55rem .85rem; margin:0; border-bottom:1px solid #f1f4f8; cursor:pointer; }
.msg-pick:last-child { border-bottom:0; }
.msg-pick:hover { background:#f8fafc; }
.msg-pick .fods-avatar { width:32px; height:32px; font-size:.7rem; }
.msg-picked { display:flex; flex-wrap:wrap; gap:.35rem; min-height:1.5rem; }
.msg-picked .fods-chip { padding-right:.25rem; }
.msg-unpick { width:20px; height:20px; border-radius:50%; border:0; display:inline-grid; place-items:center; background:rgba(15,111,111,.14); color:var(--fods-teal); font-size:.8rem; padding:0; }
.msg-unpick:hover { background:#fdecec; color:#b91c1c; }
.msg-member { cursor:default; }
.msg-member .fods-icon-btn { margin-left:auto; flex-shrink:0; }
.msg-member .fods-icon-btn.is-confirm { width:auto; padding:0 .7rem; background:#fdecec; color:#b91c1c; border-color:#f8d4d4; font-size:.78rem; font-weight:600; }
.msg-note { text-align:center; margin:.6rem 0; font-size:.72rem; color:var(--fods-muted); }
.msg-note span { display:inline-block; padding:.2rem .7rem; border-radius:999px; background:#eef1f5; }

/* Tickets as conversations: support label, notes, status chip in the list */
.msg-sender .tk-support { display:inline-block; margin-left:.3rem; padding:0 .4rem; border-radius:999px; background:var(--fods-teal-soft); color:var(--fods-teal); font-size:.64rem; font-weight:600; }
.tk-sys { text-align:center; margin:.6rem 0; font-size:.72rem; color:var(--fods-muted); }
.tk-sys span { display:inline-block; padding:.2rem .7rem; border-radius:999px; background:#eef1f5; }
.msg-conv .fods-chip { font-size:.66rem; padding:.1rem .45rem; }
.msg-bubble .msg-atts .tk-thumb { width:130px; height:130px; }
#tvThread { background:#fafbfc; border:1px solid var(--fods-border); border-radius:12px; padding:1rem; max-height:none; }

/* Support desk: breakdown bars */
.tk-bars { display:flex; flex-direction:column; gap:.55rem; }
.tk-bar { display:block; width:100%; border:0; background:transparent; padding:.15rem 0; text-align:left; }
.tk-bar-top { display:flex; justify-content:space-between; font-size:.8rem; color:var(--fods-text); margin-bottom:.2rem; }
.tk-bar-top span:last-child { color:var(--fods-muted); font-weight:600; }
.tk-bar-track { height:7px; border-radius:999px; background:#eef1f5; overflow:hidden; }
.tk-bar-fill { height:100%; border-radius:999px; background:var(--fods-teal); transition:width .3s ease; }
.tk-bar:hover .tk-bar-top span:first-child { color:var(--fods-teal); }
.tk-bar.is-active .tk-bar-top span:first-child { color:var(--fods-teal); font-weight:600; }

/* Tickets: type toggle (radio buttons styled as a segment) */
.fods-segment .btn-check:checked + .nav-link { background:#fff; color:var(--fods-text); box-shadow:0 1px 3px rgba(16,24,40,.1); }
.fods-segment .btn-check:focus-visible + .nav-link { box-shadow:0 0 0 3px var(--fods-teal-soft); }

/* Tickets: thread, attachments */
.tk-meta { display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:.75rem 1.25rem; font-size:.82rem; }
.tk-meta span { display:block; color:var(--fods-muted); font-size:.72rem; }
.tk-post { display:flex; gap:.75rem; padding:.9rem 0; border-bottom:1px solid #f1f4f8; }
.tk-post:last-child { border-bottom:0; }
.tk-post .fods-avatar { width:34px; height:34px; font-size:.72rem; }
.tk-post-head { font-size:.8rem; color:var(--fods-muted); }
.tk-post-head strong { color:var(--fods-text); font-weight:600; }
.tk-post-text { font-size:.88rem; color:var(--fods-text); white-space:pre-wrap; overflow-wrap:anywhere; margin-top:.2rem; }
.tk-note { text-align:center; font-size:.76rem; color:var(--fods-muted); padding:.5rem 0; border-bottom:1px solid #f1f4f8; }
.tk-thumbs { display:flex; flex-wrap:wrap; gap:.5rem; margin-top:.5rem; }
.tk-thumb { position:relative; width:96px; height:96px; border-radius:10px; overflow:hidden; border:1px solid var(--fods-border); background:#f8fafc; padding:0; }
.tk-thumb img { width:100%; height:100%; object-fit:cover; }
.tk-thumb .tk-thumb-x { position:absolute; top:4px; right:4px; width:22px; height:22px; border-radius:50%; border:0; background:rgba(15,23,42,.7); color:#fff; font-size:.8rem; display:grid; place-items:center; }
.tk-drop { display:flex; align-items:center; gap:.75rem; padding:.85rem 1rem; border:1.5px dashed #cbd5e1; border-radius:12px; color:var(--fods-muted); font-size:.82rem; cursor:pointer; transition:all .15s ease; }
.tk-drop:hover, .tk-drop.is-over { border-color:var(--fods-teal); background:var(--fods-teal-soft); color:var(--fods-teal); }
.tk-drop i { font-size:1.4rem; }
.tk-reply { border-top:1px solid var(--fods-border); padding-top:1rem; margin-top:.5rem; }
.tk-unread { width:8px; height:8px; border-radius:50%; background:#dc2626; display:inline-block; flex-shrink:0; }
#imgModal { z-index:1070; }
#imgModal img { max-width:100%; max-height:80vh; display:block; margin:0 auto; border-radius:10px; }

@media (max-width: 991.98px) {
  .msg-shell { grid-template-columns:1fr; height:calc(100vh - 240px); }
  .msg-shell .msg-thread { display:none; }
  .msg-shell.is-thread .msg-side { display:none; }
  .msg-shell.is-thread .msg-thread { display:flex; }
  .msg-side { border-right:0; }
  .msg-back { display:inline-grid; }
  #tkOpen .msg-thread-head { flex-wrap:wrap; }
  #tkActions { flex-basis:100%; margin-left:0 !important; }
  .msg-bubble { max-width:85%; }
}
</style>
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
            <h1 class="fods-page-title">Messages</h1>
            <p class="fods-page-sub">Chat with anyone in FODS, or send a ticket to ask for something or report a bug.</p>
        </div>
        <div class="fods-page-actions d-flex">
            <button type="button" class="btn btn-fods-ghost btn-sm px-3 py-2 js-new-ticket" id="ticketAdd"><i class="ti ti-lifebuoy me-1"></i> New ticket</button>
            <button type="button" class="btn btn-fods btn-sm px-3 py-2" id="convAdd"><i class="ti ti-message-plus me-1"></i> New message</button>
        </div>
    </div>

    <ul class="nav fods-segment mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $open['tab'] === 'chats' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#paneChats" type="button" role="tab" id="tabChats">
                <i class="ti ti-messages"></i> Chats <span class="fods-count" id="chatsCount"></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $open['tab'] === 'tickets' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#paneTickets" type="button" role="tab" id="tabTickets">
                <i class="ti ti-lifebuoy"></i> My tickets <span class="fods-count" id="ticketsCount"></span>
            </button>
        </li>
        <?php if ($perms['desk']): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $open['tab'] === 'desk' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#paneDesk" type="button" role="tab" id="tabDesk">
                <i class="ti ti-headset"></i> Support desk <span class="fods-count" id="deskCount"></span>
            </button>
        </li>
        <?php endif; ?>
    </ul>

    <div class="tab-content">
        <!-- Chats -->
        <div class="tab-pane fade <?= $open['tab'] === 'chats' ? 'show active' : '' ?>" id="paneChats" role="tabpanel">
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
                                <button type="button" class="fods-icon-btn is-teal d-none" id="msgMembers" title="Group members"><i class="ti ti-users"></i></button>
                                <button type="button" class="fods-icon-btn is-teal" id="msgArchive" title="Archive chat"><i class="ti ti-archive"></i></button>
                            </div>
                        </div>
                        <div class="msg-archived-bar d-none" id="msgArchivedBar"><i class="ti ti-archive"></i> This chat is archived. A new message brings it back to your chats.</div>
                        <div class="msg-body" id="msgBody"></div>
                        <form class="msg-compose" id="msgForm" autocomplete="off">
                            <div class="tk-thumbs" id="msgThumbs"></div>
                            <div class="msg-compose-row">
                                <label for="msgFiles" class="fods-icon-btn is-teal msg-attach" title="Attach images or files (up to 5, 10 MB each)"><i class="ti ti-paperclip"></i></label>
                                <input type="file" id="msgFiles" multiple hidden accept="image/png,image/jpeg,image/gif,image/webp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.csv,.txt,.zip">
                                <textarea class="form-control" id="msgText" rows="1" maxlength="2000" placeholder="Write a message..." title="Enter to send, Shift+Enter for a new line. Paste or drop files to attach them."></textarea>
                                <button type="submit" class="btn btn-fods px-3" id="msgSendBtn" style="height:38px;"><i class="ti ti-send"></i></button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>

        <!-- My tickets: each ticket is a conversation with support -->
        <div class="tab-pane fade <?= $open['tab'] === 'tickets' ? 'show active' : '' ?>" id="paneTickets" role="tabpanel">
            <div class="fods-card msg-shell" id="tkShell">
                <aside class="msg-side">
                    <div class="msg-side-head">
                        <div class="fods-search">
                            <i class="ti ti-search"></i>
                            <input type="search" class="form-control form-control-sm" id="tkSearch" placeholder="Search your tickets...">
                        </div>
                        <div class="fods-filter msg-box" id="tkBox">
                            <button type="button" class="active" data-box="active"><i class="ti ti-inbox"></i> Open <span class="fods-count" id="tkActiveCount">0</span></button>
                            <button type="button" data-box="done"><i class="ti ti-circle-check"></i> Resolved <span class="fods-count" id="tkDoneCount">0</span></button>
                        </div>
                    </div>
                    <div class="msg-convs" id="tkList"></div>
                </aside>

                <section class="msg-thread">
                    <div class="msg-empty" id="tkEmpty">
                        <div class="fods-stat-icon tone-amber"><i class="ti ti-lifebuoy"></i></div>
                        <div class="fw-semibold text-dark">Need help? Start a ticket.</div>
                        <div class="small">Ask for something or report a bug. Support answers you here, like a chat.</div>
                        <button type="button" class="btn btn-fods btn-sm px-3 mt-2 js-new-ticket"><i class="ti ti-plus me-1"></i> New ticket</button>
                    </div>
                    <div class="d-none flex-column h-100" id="tkOpen" style="min-height:0">
                        <div class="msg-thread-head">
                            <button type="button" class="fods-icon-btn msg-back" id="tkBack" aria-label="Back to tickets"><i class="ti ti-arrow-left"></i></button>
                            <div class="fods-avatar tone-teal" id="tkAvatar"></div>
                            <div style="min-width:0">
                                <div class="msg-thread-name" id="tkName"></div>
                                <div class="msg-thread-sub" id="tkSub"></div>
                            </div>
                            <div class="ms-auto d-flex align-items-center gap-2 flex-shrink-0" id="tkActions"></div>
                        </div>
                        <div class="msg-archived-bar d-none" id="tkBar"></div>
                        <div class="msg-body" id="tkBody"></div>
                        <form class="msg-compose" id="tkForm" autocomplete="off">
                            <div class="tk-thumbs" id="tkReplyThumbs"></div>
                            <div class="msg-compose-row">
                                <label for="tkReplyFiles" class="fods-icon-btn is-teal msg-attach" title="Attach screenshots (up to 5 images, 5 MB each)"><i class="ti ti-photo-plus"></i></label>
                                <input type="file" id="tkReplyFiles" multiple hidden accept="image/png,image/jpeg,image/gif,image/webp">
                                <textarea class="form-control" id="tkReplyText" rows="1" maxlength="2000" placeholder="Write a reply..." title="Enter to send, Shift+Enter for a new line. Paste a screenshot to attach it."></textarea>
                                <button type="submit" class="btn btn-fods px-3" id="tkSendBtn" style="height:38px;"><i class="ti ti-send"></i></button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>

        <?php if ($perms['desk']): ?>
        <!-- Support desk: every ticket, for support staff -->
        <div class="tab-pane fade <?= $open['tab'] === 'desk' ? 'show active' : '' ?>" id="paneDesk" role="tabpanel">
            <div class="row g-3 mb-3">
                <div class="col-6 col-xl-3">
                    <button type="button" class="fods-stat" data-table="ticketTable" data-filter="Open">
                        <div class="fods-stat-icon tone-amber"><i class="ti ti-inbox"></i></div>
                        <div><div class="fods-stat-value" data-card-value="Open">0</div><div class="fods-stat-label" data-card-label="Open">Open</div></div>
                    </button>
                </div>
                <div class="col-6 col-xl-3">
                    <button type="button" class="fods-stat" data-table="ticketTable" data-filter="waiting">
                        <div class="fods-stat-icon tone-red"><i class="ti ti-message-dots"></i></div>
                        <div><div class="fods-stat-value" data-card-value="waiting">0</div><div class="fods-stat-label">Waiting for a reply</div></div>
                    </button>
                </div>
                <div class="col-6 col-xl-3">
                    <button type="button" class="fods-stat" data-table="ticketTable" data-filter="In progress">
                        <div class="fods-stat-icon tone-indigo"><i class="ti ti-clock-play"></i></div>
                        <div><div class="fods-stat-value" data-card-value="In progress">0</div><div class="fods-stat-label">In progress</div></div>
                    </button>
                </div>
                <div class="col-6 col-xl-3">
                    <button type="button" class="fods-stat" data-table="ticketTable" data-filter="done">
                        <div class="fods-stat-icon tone-green"><i class="ti ti-circle-check"></i></div>
                        <div><div class="fods-stat-value" data-card-value="done">0</div><div class="fods-stat-label" data-card-label="done">Resolved / closed</div></div>
                    </button>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-lg-4">
                    <div class="fods-card h-100">
                        <div class="fods-card-head"><h6 class="fods-card-title">By type</h6><span class="small text-muted" id="deskWeek"></span></div>
                        <div class="fods-card-body tk-bars" id="deskByType"></div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="fods-card h-100">
                        <div class="fods-card-head"><h6 class="fods-card-title">By priority</h6><span class="small text-muted">open tickets</span></div>
                        <div class="fods-card-body tk-bars" id="deskByPriority"></div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="fods-card h-100">
                        <div class="fods-card-head"><h6 class="fods-card-title">Where in FODS</h6><span class="small text-muted">top 5</span></div>
                        <div class="fods-card-body tk-bars" id="deskByModule"></div>
                    </div>
                </div>
            </div>

            <div class="fods-card">
                <div class="fods-card-head">
                    <div class="fods-filter" id="ticketTableFilter">
                        <button type="button" class="active" data-filter="all">All</button>
                        <button type="button" data-filter="Open">Open</button>
                        <button type="button" data-filter="waiting">Waiting for a reply</button>
                        <button type="button" data-filter="In progress">In progress</button>
                        <button type="button" data-filter="Resolved">Resolved</button>
                        <button type="button" data-filter="Closed">Closed</button>
                    </div>
                    <div class="fods-search">
                        <i class="ti ti-search"></i>
                        <input type="search" class="form-control form-control-sm" id="ticketTableSearch" placeholder="Search ticket no., subject, name...">
                    </div>
                </div>
                <div class="fods-card-body fods-dt">
                    <table id="ticketTable" class="table fods-table fods-table-stack w-100">
                        <thead>
                            <tr>
                                <th>Ticket</th>
                                <th class="d-none d-md-table-cell">Requested by</th>
                                <th class="d-none d-lg-table-cell">Type</th>
                                <th class="d-none d-xl-table-cell">Updated</th>
                                <th>Status</th>
                                <th class="text-end"></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
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

<!-- New ticket -->
<div class="modal fade fods-modal" id="ticketModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <form id="ticketForm" novalidate>
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <div class="fods-modal-icon tone-amber"><i class="ti ti-lifebuoy"></i></div>
                        <div>
                            <h5 class="modal-title">New ticket</h5>
                            <div class="fods-modal-sub">Ask for something, or tell us what went wrong.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="fods-section mt-0">Ticket</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label d-block">Type</label>
                            <div class="fods-segment w-100">
                                <?php foreach ($lookups['categories'] as $i => $c): ?>
                                    <input type="radio" class="btn-check" name="category" id="tkCat<?= $i ?>" value="<?= esc($c, 'attr') ?>" <?= $i === 0 ? 'checked' : '' ?>>
                                    <label class="nav-link flex-fill justify-content-center mb-0" for="tkCat<?= $i ?>" style="cursor:pointer"><i class="ti <?= $c === 'Bug report' ? 'ti-bug' : 'ti-hand-finger' ?>"></i> <?= esc($c) ?></label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label" for="tkPriority">Priority</label>
                            <select class="form-select" id="tkPriority" name="priority">
                                <?php foreach ($lookups['priorities'] as $p): ?>
                                    <option value="<?= esc($p, 'attr') ?>" <?= $p === 'Normal' ? 'selected' : '' ?>><?= esc($p) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label" for="tkModule">Where in FODS?</label>
                            <select class="form-select" id="tkModule" name="module">
                                <option value="">—</option>
                                <?php foreach ($modules as $m): ?>
                                    <option value="<?= esc($m, 'attr') ?>"><?= esc($m) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="tkSubject">Subject</label>
                            <input type="text" class="form-control" id="tkSubject" name="subject" maxlength="150" required placeholder="e.g. Can't print ORS for 2026">
                            <div class="invalid-feedback">Subject is required.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="tkDescription">Details</label>
                            <textarea class="form-control" id="tkDescription" name="description" rows="5" maxlength="2000" required placeholder="What do you need? For a bug: what you did, what you expected, and what happened instead."></textarea>
                            <div class="invalid-feedback">Please describe what you need.</div>
                        </div>
                    </div>

                    <div class="fods-section">Screenshots</div>
                    <label class="tk-drop" id="tkDrop" for="tkFiles">
                        <i class="ti ti-photo-plus"></i>
                        <span>Add images — click, drop them here, or paste a screenshot into Details. Up to 5 images, 5 MB each.</span>
                    </label>
                    <input type="file" id="tkFiles" accept="image/png,image/jpeg,image/gif,image/webp" multiple hidden>
                    <div class="tk-thumbs" id="tkThumbs"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-fods-ghost px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-fods px-4" id="submitBtn">Send ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($perms['desk']): ?>
<!-- Ticket review (Support desk) -->
<div class="modal fade fods-modal" id="ticketViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center" style="min-width:0">
                    <div class="fods-modal-icon tone-amber" id="tvIcon"><i class="ti ti-lifebuoy"></i></div>
                    <div style="min-width:0">
                        <h5 class="modal-title" id="tvTitle" style="overflow-wrap:anywhere"></h5>
                        <div class="fods-modal-sub" id="tvSub"></div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="tk-meta mb-2" id="tvMeta"></div>
                <div class="fods-section">Conversation</div>
                <div id="tvThread"></div>

                <form class="tk-reply d-none" id="tvReplyForm" novalidate>
                    <textarea class="form-control" id="tvReplyText" rows="3" maxlength="2000" placeholder="Write a reply..."></textarea>
                    <div class="tk-thumbs" id="tvThumbs"></div>
                    <input type="file" id="tvFiles" accept="image/png,image/jpeg,image/gif,image/webp" multiple hidden>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <label for="tvFiles" class="btn btn-sm btn-fods-ghost px-3 mb-0"><i class="ti ti-paperclip me-1"></i> Attach images</label>
                        <button type="submit" class="btn btn-sm btn-fods px-4" id="tvReplyBtn"><i class="ti ti-send me-1"></i> Reply</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <div class="d-flex flex-wrap gap-2" id="tvActions"></div>
                <button type="button" class="btn btn-sm btn-fods-ghost px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

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
    var __msg_tickets = <?= json_encode($tickets, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __msg_users = <?= json_encode($users, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __msg_perms = <?= json_encode($perms) ?>;
    var __msg_open = <?= json_encode($open, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __msg_me = <?= json_encode($me, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="<?=base_url('assets/js/messages/mymessages.js?v=9');?>"></script>
<?php echo view('templates/myfooter.php'); ?>
