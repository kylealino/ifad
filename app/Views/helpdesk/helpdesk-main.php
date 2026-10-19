<?php echo view('templates/myheader.php', ['pageTitle' => 'Helpdesk']); ?>
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
                    <li class="breadcrumb-item active" aria-current="page">Helpdesk</li>
                </ol>
            </nav>
            <h1 class="fods-page-title">Helpdesk</h1>
            <p class="fods-page-sub">Ask the support team for something or report a bug, then follow your ticket here.</p>
        </div>
        <div class="fods-page-actions d-flex">
            <button type="button" class="btn btn-fods btn-sm px-3 py-2 js-new-ticket" id="ticketAdd"><i class="ti ti-plus me-1"></i> New ticket</button>
        </div>
    </div>

    <ul class="nav fods-segment mb-3" role="tablist">
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
                                <label for="tkReplyFiles" class="fods-icon-btn is-teal msg-attach" title="Attach screenshots (up to 5 images, 5 MB each). You can also paste one with Ctrl+V."><i class="ti ti-photo-plus"></i></label>
                                <input type="file" id="tkReplyFiles" multiple hidden accept="image/png,image/jpeg,image/gif,image/webp">
                                <textarea class="form-control" id="tkReplyText" rows="1" maxlength="2000" placeholder="Write a reply... (Ctrl+V pastes a screenshot)" title="Enter to send, Shift+Enter for a new line. Paste a screenshot (Snipping Tool, Win+Shift+S) to attach it."></textarea>
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
                        <span>Add images — click, drop them here, or paste a screenshot (Ctrl+V). Up to 5 images, 5 MB each.</span>
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
                        <span><label for="tvFiles" class="btn btn-sm btn-fods-ghost px-3 mb-0"><i class="ti ti-paperclip me-1"></i> Attach images</label><small class="text-muted ms-2 d-none d-sm-inline">or paste a screenshot (Ctrl+V)</small></span>
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
    var __hd_tickets = <?= json_encode($tickets, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __hd_perms = <?= json_encode($perms) ?>;
    var __hd_open = <?= json_encode($open, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __hd_me = <?= json_encode($me, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="<?=base_url('assets/js/helpdesk/myhelpdesk.js?v=2');?>"></script>
<?php echo view('templates/myfooter.php'); ?>
