<?php
echo view('templates/myheader.php');
?>
<head>
<style>
/* Editor grid */
.ppmp-grid-card { border:1px solid var(--fods-border); background:#fff; margin-bottom:1rem; border-radius:12px; overflow:hidden; }
.ppmp-grid-head { display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; justify-content:space-between; padding:.65rem .9rem; border-bottom:1px solid var(--fods-border); background:#fafbfc; }
.ppmp-grid-title { font-weight:600; color:var(--fods-text); }
.ppmp-grid-sub { font-size:.75rem; color:var(--fods-muted); }
.ppmp-grid-wrap { overflow-x:auto; scrollbar-width:thin; }
/* Fixed, roomy columns: the grid scrolls sideways instead of squeezing the fields */
.ppmp-grid { width:max-content; min-width:100%; table-layout:fixed; border-collapse:separate; border-spacing:0; font-size:.85rem; }
.ppmp-grid col.c-act { width:84px; }
.ppmp-grid col.c-desc { width:320px; }
.ppmp-grid col.c-type { width:170px; }
.ppmp-grid col.c-qty { width:100px; }
.ppmp-grid col.c-unit { width:110px; }
.ppmp-grid col.c-amt { width:150px; }
.ppmp-grid col.c-mode { width:180px; }
.ppmp-grid col.c-yn { width:120px; }
.ppmp-grid col.c-month { width:165px; }
.ppmp-grid col.c-range { width:350px; }
.ppmp-grid col.c-fund { width:230px; }
.ppmp-grid col.c-text { width:190px; }
.ppmp-grid th { background:#f8fafc; color:var(--fods-muted); font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.04em; padding:.6rem .6rem; border-bottom:1px solid var(--fods-border); white-space:nowrap; text-align:left; }
.ppmp-grid th.num, .ppmp-grid td.num { text-align:right; }
.ppmp-grid td { padding:.45rem .6rem; border-bottom:1px solid #f1f4f8; vertical-align:middle; background:#fff; }
.ppmp-grid tbody tr:hover td { background:#fbfcfd; }
.ppmp-grid input { width:100%; height:36px; border:1px solid var(--fods-border); background:#fff; padding:.35rem .65rem; font-size:.85rem; color:var(--fods-text); border-radius:8px; text-overflow:ellipsis; }
.ppmp-grid input::placeholder { color:#a3adbb; }
.ppmp-grid input:hover:not(:disabled):not([readonly]) { border-color:#c7d0db; }
.ppmp-grid input:focus { outline:none; border-color:var(--fods-teal); background:#fff; box-shadow:0 0 0 3px var(--fods-teal-soft); }
.ppmp-grid input[type=number] { text-align:right; font-variant-numeric:tabular-nums; font-weight:600; }
.ppmp-grid input:disabled, .ppmp-grid input[readonly] { color:#475569; background:#f1f4f8; border-color:transparent; }
.ppmp-grid tfoot td { font-weight:600; background:#fafbfc; border-top:1px solid var(--fods-border); border-bottom:0; padding:.6rem .6rem; font-variant-numeric:tabular-nums; }
/* Row actions stay on the left while scrolling */
.ppmp-grid .sticky-l { position:sticky; left:0; z-index:1; }
.ppmp-grid th.sticky-l { background:#f8fafc; z-index:2; }
.ppmp-grid tfoot .sticky-l { background:#fafbfc; }
.ppmp-range { display:flex; align-items:center; gap:.4rem; color:var(--fods-muted); font-size:.78rem; }
.ppmp-grid-hint { font-size:.72rem; color:var(--fods-muted); }
.ppmp-row-actions { display:flex; gap:.1rem; }
.ppmp-row-actions .fods-icon-btn { width:28px; height:28px; font-size:.95rem; }
.ppmp-empty { padding:1.25rem; text-align:center; color:var(--fods-muted); font-size:.82rem; }

/* Item versions: the original, then one after each PR that used the PPMP */
.ppmp-versions { display:flex; flex-wrap:wrap; gap:.4rem; margin-bottom:.75rem; }
.ppmp-versions button { display:inline-flex; flex-direction:column; align-items:flex-start; gap:.05rem; border:1px solid var(--fods-border); background:#fff; border-radius:10px; padding:.4rem .75rem; font-size:.78rem; color:var(--fods-text); cursor:pointer; text-align:left; line-height:1.25; }
.ppmp-versions button small { font-size:.68rem; color:var(--fods-muted); }
.ppmp-versions button:hover { border-color:#c7d0db; }
.ppmp-versions button.active { border-color:var(--fods-teal); background:var(--fods-teal-soft); box-shadow:0 0 0 1px var(--fods-teal) inset; }
.ppmp-versions button.is-done small { color:#b42318; font-weight:600; }
.ppmp-version-table { width:100%; border-collapse:separate; border-spacing:0; font-size:.84rem; }
.ppmp-version-table th { background:#f8fafc; color:var(--fods-muted); font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.04em; padding:.6rem .7rem; border-bottom:1px solid var(--fods-border); text-align:left; white-space:nowrap; }
.ppmp-version-table td { padding:.55rem .7rem; border-bottom:1px solid #f1f4f8; vertical-align:top; }
.ppmp-version-table .num { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
.ppmp-version-table tr.is-zero td { color:#94a3b8; }
.ppmp-version-table td.took { color:#b54708; font-weight:600; }
.ppmp-version-table td.left { font-weight:700; }
.ppmp-version-table tfoot td { font-weight:600; background:#fafbfc; border-top:1px solid var(--fods-border); border-bottom:0; }
.ppmp-version-note { font-size:.75rem; color:#b54708; padding:.5rem .9rem 0; }
.ppmp-used-bar { height:4px; border-radius:2px; background:#eef2f6; overflow:hidden; margin-top:.3rem; width:90px; }
.ppmp-used-bar > span { display:block; height:100%; background:var(--fods-teal); }
/* Documents tab */
.ppmp-drop { border:1.5px dashed #c9d3df; border-radius:12px; padding:1.1rem 1rem; text-align:center; background:#fafbfc; color:var(--fods-muted); transition:background .15s, border-color .15s; }
.ppmp-drop > .ti { font-size:1.6rem; color:var(--fods-teal); }
.ppmp-drop-text { font-size:.86rem; color:var(--fods-text); margin-top:.2rem; }
.ppmp-drop-hint { font-size:.74rem; margin-top:.35rem; }
.ppmp-drop.is-over { background:var(--fods-teal-soft); border-color:var(--fods-teal); }
.ppmp-drop.is-busy { opacity:.6; pointer-events:none; }
.ppmp-docs { margin-top:.9rem; border:1px solid var(--fods-border); border-radius:12px; overflow:hidden; }
.ppmp-docs:empty { display:none; }
.ppmp-doc { display:flex; align-items:center; gap:.75rem; padding:.6rem .8rem; border-bottom:1px solid #f1f4f8; background:#fff; }
.ppmp-doc:last-child { border-bottom:0; }
.ppmp-doc-icon { flex:0 0 36px; height:36px; border-radius:9px; display:grid; place-items:center; font-size:1.15rem; }
.ppmp-doc-main { flex:1 1 auto; min-width:0; }
.ppmp-doc-name { font-weight:600; font-size:.86rem; color:var(--fods-text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:block; background:none; border:0; padding:0; text-align:left; max-width:100%; }
.ppmp-doc-name:hover { color:var(--fods-teal); text-decoration:underline; }
.ppmp-doc-meta { font-size:.74rem; color:var(--fods-muted); }
.ppmp-doc-acts { display:flex; gap:.25rem; flex-shrink:0; }
.ppmp-docs-empty { padding:1rem; text-align:center; font-size:.8rem; color:var(--fods-muted); }
.ppmp-docs-new { display:flex; align-items:center; gap:.75rem; padding:1rem; border:1px dashed var(--fods-border); border-radius:12px; color:var(--fods-muted); font-size:.86rem; }
.ppmp-docs-new .ti { font-size:1.4rem; }
.ppmp-doc-view { background:#525659; min-height:60vh; display:flex; align-items:center; justify-content:center; }
.ppmp-doc-view iframe { width:100%; height:80vh; border:0; background:#fff; }
.ppmp-doc-view img { max-width:100%; max-height:80vh; display:block; }

/* Editor footer totals */
.ppmp-totals { display:flex; flex-wrap:wrap; gap:1.25rem; align-items:center; font-size:.82rem; color:var(--fods-muted); }
.ppmp-totals b { color:var(--fods-text); font-size:.95rem; font-variant-numeric:tabular-nums; }
.ppmp-tab-amt { font-size:.7rem; color:var(--fods-muted); margin-left:.35rem; font-weight:500; }
.ppmp-dialog .fods-segment .nav-link { white-space:nowrap; }

/* Editor dialog: a bit wider than modal-xl on big screens so the grid fits */
@media (min-width: 1200px) {
    .ppmp-dialog { max-width: min(1320px, calc(100vw - 4rem)); }
}

/* jQuery UI autocomplete above the modal */
.ui-autocomplete { z-index:2000 !important; max-height:280px; overflow-y:auto; overflow-x:hidden; font-size:.82rem; }
.ui-autocomplete .ppmp-ac-sub { display:block; font-size:.72rem; color:var(--fods-muted); }

@media (max-width: 767.98px) {
    .ppmp-totals { gap:.5rem 1rem; width:100%; }
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
                    <li class="breadcrumb-item"><a href="#">Procurement</a></li>
                    <li class="breadcrumb-item active" aria-current="page">PPMP</li>
                </ol>
            </nav>
            <h1 class="fods-page-title">Project Procurement Management Plan</h1>
            <p class="fods-page-sub">Plan what each project will procure for the year, mark the plan indicative or final, then submit it for approval.</p>
        </div>
        <div class="fods-page-actions d-flex">
            <select class="form-select form-select-sm fods-year" id="ppmpYear" aria-label="Fiscal year"></select>
            <?php if ($perms['add']): ?>
                <button type="button" class="btn btn-fods btn-sm px-3 py-2" id="ppmpTableAdd"><i class="ti ti-plus me-1"></i> New PPMP</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stat cards (filled by myppmp.js for the selected year) -->
    <div class="row g-3 mb-4" id="ppmpTableCards">
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="ppmpTable" data-filter="pending">
                <div class="fods-stat-icon tone-amber"><i class="ti ti-hourglass"></i></div>
                <div><div class="fods-stat-value" data-card-value="pending">0</div><div class="fods-stat-label" data-card-label="pending">For approval</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="ppmpTable" data-filter="disapproved">
                <div class="fods-stat-icon tone-red"><i class="ti ti-arrow-back-up"></i></div>
                <div><div class="fods-stat-value" data-card-value="returned">0</div><div class="fods-stat-label" data-card-label="returned">Returned</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="ppmpTable" data-filter="approved">
                <div class="fods-stat-icon tone-green"><i class="ti ti-circle-check"></i></div>
                <div><div class="fods-stat-value" data-card-value="approved">0</div><div class="fods-stat-label" data-card-label="approved">Approved</div></div>
            </button>
        </div>
        <div class="col-6 col-xl-3">
            <button type="button" class="fods-stat" data-table="ppmpTable" data-filter="all">
                <div class="fods-stat-icon tone-teal"><i class="ti ti-cash"></i></div>
                <div><div class="fods-stat-value" data-card-value="total">₱0.00</div><div class="fods-stat-label" data-card-label="total">Estimated budget</div></div>
            </button>
        </div>
    </div>

    <div class="fods-card">
        <div class="fods-card-head">
            <div class="fods-filter" id="ppmpTableFilter">
                <button type="button" class="active" data-filter="all">All records</button>
                <button type="button" data-filter="pending">For approval</button>
                <button type="button" data-filter="disapproved">Returned</button>
                <button type="button" data-filter="approved">Approved</button>
                <button type="button" data-filter="unmarked">Not marked</button>
                <button type="button" data-filter="indicative">Indicative</button>
                <button type="button" data-filter="final">Final</button>
                <button type="button" data-filter="fully_used">Fully used</button>
            </div>
            <div class="fods-search">
                <i class="ti ti-search"></i>
                <input type="search" class="form-control form-control-sm" id="ppmpTableSearch" placeholder="Search PPMP no., end-user, project...">
            </div>
        </div>
        <div class="fods-card-body fods-dt">
            <table id="ppmpTable" class="table fods-table fods-table-stack w-100">
                <thead>
                    <tr>
                        <th>PPMP</th>
                        <th class="d-none d-md-table-cell">End-user</th>
                        <th class="d-none d-lg-table-cell">Fiscal year</th>
                        <th class="text-end">Estimated budget</th>
                        <th>Status</th>
                        <th class="d-none d-xl-table-cell">Encoded</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- ============================= EDITOR ============================= -->
<div class="modal fade fods-modal fods-flat" id="ppmpModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down ppmp-dialog">
        <div class="modal-content">
            <form id="ppmpForm" novalidate>
                <div class="modal-header">
                    <div class="d-flex align-items-center" style="min-width:0">
                        <div class="fods-modal-icon tone-teal"><i class="ti ti-calendar-stats"></i></div>
                        <div style="min-width:0">
                            <h5 class="modal-title text-truncate" id="ppmpModalTitle">New PPMP</h5>
                            <div class="fods-modal-sub" id="ppmpModalSub">Fill in the project details, then the items to procure.</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 ms-auto ps-3 flex-shrink-0 text-nowrap">
                        <button type="button" class="btn btn-sm btn-fods-ghost d-none" id="ppmpUploadBtn" title="Fill this PPMP from the PPMP spreadsheet (Excel or CSV)"><i class="ti ti-file-spreadsheet me-1"></i>Import from Excel</button>
                        <input type="file" id="ppmpUploadFile" accept=".xlsx,.xls,.csv" hidden>
                        <button type="button" class="btn btn-sm btn-fods-ghost d-none" id="ppmpPrintBtn"><i class="ti ti-printer me-1"></i>Print</button>
                        <button type="button" class="btn btn-sm btn-danger d-none" id="ppmpDisapproveBtn"><i class="ti ti-x me-1"></i>Disapprove</button>
                        <button type="button" class="btn btn-sm btn-success d-none" id="ppmpApproveBtn"><i class="ti ti-check me-1"></i>Approve</button>
                        <button type="button" class="btn btn-sm btn-fods-ghost d-none" data-history-module="ppmp" data-history-field="recid"><i class="ti ti-history me-1"></i>History</button>
                        <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <ul class="nav fods-tabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#ppmpPaneDetails"><i class="ti ti-file-description"></i>Details</button></li>
                    <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#ppmpPaneItems"><i class="ti ti-list-details"></i>Items<span class="ppmp-tab-amt" data-tab-total></span></button></li>
                    <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#ppmpPaneDocs"><i class="ti ti-paperclip"></i>Documents<span class="ppmp-tab-amt" id="ppmpDocCount"></span></button></li>
                </ul>

                <div class="modal-body">
                    <input type="hidden" id="recid" name="recid">

                    <div id="ppmpBanner" class="alert d-none mt-3 mb-0"></div>

                    <div class="tab-content">
                        <!-- Details -->
                        <div class="tab-pane fade show active" id="ppmpPaneDetails">
                            <section class="fods-fsec">
                                <div class="fods-fsec-head">
                                    <h6>Plan</h6>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label class="form-label req" for="ppmpno">PPMP no.</label>
                                        <input type="text" class="form-control" id="ppmpno" name="ppmpno" maxlength="100" required>
                                        <div class="invalid-feedback">PPMP no. is required.</div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <label class="form-label req" for="fiscal_year">Fiscal year</label>
                                        <select class="form-select" id="fiscal_year" name="fiscal_year" required></select>
                                        <div class="invalid-feedback">Pick the fiscal year.</div>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label req" for="end_user">End-user</label>
                                        <input type="text" class="form-control" id="end_user" name="end_user" maxlength="100" placeholder="Implementing unit or person" required>
                                        <div class="invalid-feedback">End-user is required.</div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label d-block">PPMP type</label>
                                        <div class="d-flex gap-4">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="is_indicative" value="1">
                                                <label class="form-check-label" for="is_indicative">Indicative</label>
                                            </div>
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="is_final" value="1">
                                                <label class="form-check-label" for="is_final">Final</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="fods-fsec">
                                <div class="fods-fsec-head">
                                    <h6>Project</h6>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label class="form-label req" for="responsibility_code">Responsibility code</label>
                                        <input type="text" class="form-control" id="responsibility_code" name="responsibility_code" maxlength="100" placeholder="Type or pick a code" autocomplete="off" required>
                                        <div class="invalid-feedback">Responsibility code is required.</div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="project_title">Project, programs and activities</label>
                                        <input type="text" class="form-control" id="project_title" name="project_title" placeholder="Set by the responsibility code" readonly>
                                    </div>
                                </div>
                            </section>

                            <section class="fods-fsec">
                                <div class="fods-fsec-head">
                                    <h6>Signatories</h6>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label req" for="prepared_by">Prepared by</label>
                                        <input type="text" class="form-control" id="prepared_by" name="prepared_by" maxlength="100" required>
                                        <div class="invalid-feedback">Prepared by is required.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label req" for="submitted_by">Submitted by</label>
                                        <input type="text" class="form-control" id="submitted_by" name="submitted_by" maxlength="100" placeholder="Pick an authorized signatory" autocomplete="off" required>
                                        <div class="invalid-feedback">Submitted by is required.</div>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <!-- Items -->
                        <div class="tab-pane fade" id="ppmpPaneItems">
                            <div class="fods-pane-pad">
                                <!-- the original items, then one version after each PR that used this PPMP -->
                                <div class="ppmp-versions d-none" id="ppmpVersions" role="tablist" aria-label="Item versions"></div>
                                <div class="ppmp-grid-card" id="ppmpGrid"></div>
                                <div class="ppmp-grid-card d-none" id="ppmpVersionView"></div>
                            </div>
                        </div>

                        <!-- Documents: supporting files kept with the PPMP -->
                        <div class="tab-pane fade" id="ppmpPaneDocs">
                            <div class="fods-pane-pad">
                                <div class="ppmp-docs-new d-none" id="ppmpDocsNew">
                                    <i class="ti ti-device-floppy"></i>
                                    <div>Save the PPMP first to add documents.</div>
                                </div>
                                <div id="ppmpDocsBody">
                                    <div class="ppmp-drop" id="ppmpDrop">
                                        <i class="ti ti-cloud-upload"></i>
                                        <div class="ppmp-drop-text">
                                            <b>Drop files here</b> or paste them, or
                                            <button type="button" class="btn btn-sm btn-fods-ghost ms-1" id="ppmpDocAdd"><i class="ti ti-paperclip me-1"></i>Add files</button>
                                        </div>
                                        <div class="ppmp-drop-hint">PDF, images, Word, Excel, PowerPoint, CSV, TXT or ZIP · up to <?= (int) $doc_max_mb ?> MB each, <?= (int) $doc_max_files ?> at a time</div>
                                        <input type="file" id="ppmpDocFile" multiple hidden
                                            accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.csv,.txt,.zip">
                                    </div>
                                    <div class="ppmp-docs" id="ppmpDocs" aria-live="polite"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <div class="ppmp-totals">
                        <span>Items <b id="ppmpFootItems">0</b></span>
                        <span>Estimated budget <b id="ppmpFootBudget">₱0.00</b></span>
                    </div>
                    <div class="fods-modal-actions d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-fods-ghost px-3" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-fods px-4" id="submitBtn">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Supporting document preview (PDF / image; other files download) -->
<div class="modal fade fods-modal" id="ppmpDocModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-truncate" id="ppmpDocTitle">Document</h5>
                <div class="d-flex align-items-center gap-2 ms-auto ps-3 flex-shrink-0">
                    <a class="btn btn-sm btn-fods-ghost" id="ppmpDocDownload" href="#"><i class="ti ti-download me-1"></i>Download</a>
                    <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0 ppmp-doc-view" id="ppmpDocView"></div>
        </div>
    </div>
</div>

<!-- PPMP print preview -->
<div class="modal fade fods-modal" id="ppmpPrintModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">PPMP — print preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="ppmpPrintFrame" src="about:blank" style="width:100%; height:80vh; border:0;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    var __ppmp_data = <?= json_encode($ppmphddata, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __ppmp_lookups = <?= json_encode($lookups, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __ppmp_perms = <?= json_encode($perms) ?>;
    var __ppmp_open = <?= json_encode($open, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var __ppmp_xlsx = <?= json_encode(base_url('assets/libs/xlsx/xlsx.full.min.js')) ?>;
    var __ppmp_doc_limits = <?= json_encode(['mb' => $doc_max_mb, 'files' => $doc_max_files]) ?>;
</script>
<script src="<?=base_url('assets/js/procurement/ppmp/myppmp.js?v=12');?>"></script>
<?php echo view('templates/myfooter.php'); ?>
