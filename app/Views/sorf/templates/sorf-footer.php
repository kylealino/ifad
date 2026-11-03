<?php
// SORF page bottom inside the IFAD Portal: closes sorf-header.php's wrapper,
// adds SORF's confirm and print windows, then the portal footer, which loads
// Bootstrap and then drf-app.js and the page's own 'scripts' (under assets/sorf/js).
$scripts = $scripts ?? [];

ob_start();
?>
  <!-- Confirmation: __mysys_drf.confirm({...}) asks here before an action that matters (drf-app.js) -->
  <div class="modal fade drf-confirm" id="drfConfirmModal" tabindex="-1" aria-labelledby="drfConfirmTitle" aria-describedby="drfConfirmText" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body">
          <div class="drf-confirm-icon" id="drfConfirmIcon"><i class="ti ti-help"></i></div>
          <h5 class="modal-title" id="drfConfirmTitle">Are you sure?</h5>
          <p class="drf-confirm-text" id="drfConfirmText"></p>
          <p class="drf-confirm-note" id="drfConfirmNote" hidden></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-drf-ghost px-4 py-2" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-drf px-4 py-2" id="drfConfirmYes">Continue</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Print form: links marked data-drf-print open their PDF here (drf-app.js) -->
  <div class="modal fade drf-print" id="drfPrintModal" tabindex="-1" aria-labelledby="drfPrintTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-lg-down">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="drfPrintTitle">Print Form</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <iframe id="drfPrintFrame" title="Request form (PDF)"></iframe>
          <div class="drf-empty" id="drfPrintNoPreview" hidden>
            <i class="ti ti-file-text"></i>
            This device cannot preview a PDF here.<br>Download the form to view or print it.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-drf-ghost px-3 py-2" data-bs-dismiss="modal">Close</button>
          <a class="btn btn-drf-ghost px-3 py-2" id="drfPrintDownload" href="#" download><i class="ti ti-download me-1"></i> Download PDF</a>
          <button type="button" class="btn btn-drf px-4 py-2" id="drfPrintNow"><i class="ti ti-printer me-1"></i> Print</button>
        </div>
      </div>
    </div>
  </div>
<?php
$footHtml = ob_get_clean();

$footScripts = [base_url('assets/sorf/js/drf-app.js?v=9')];
foreach ($scripts as $script) {
    $footScripts[] = base_url('assets/sorf/js/' . $script);
}

// the wrapper sorf-header.php opened, inside the portal's <main>
echo '</div>';
echo view('templates/myfooter.php', ['footHtml' => $footHtml, 'footScripts' => $footScripts]);
