    </main>

    <footer class="fods-footer">&copy; <?= date('Y') ?> FODS &mdash; All rights reserved.</footer>
  </div>

  <script src="<?=base_url('assets/js/vendor.min.js')?>"></script>
  <script src="<?=base_url('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js')?>"></script>
  <script src="<?=base_url('assets/libs/simplebar/dist/simplebar.min.js')?>"></script>
  <script src="<?=base_url('assets/js/theme/theme.js')?>"></script>
  <script src="<?=base_url('assets/libs/owl.carousel/dist/owl.carousel.min.js');?>"></script>
  <script src="<?=base_url('assets/js/plugins/toastr-init.js')?>"></script>

  <!-- jQuery (needed for DataTables) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
  <script src="<?=base_url('assets/js/fods-layout.js?v=1')?>"></script>
  <script src="<?=base_url('assets/js/fods-inbox.js?v=3')?>"></script>
<?php
// FODS Assistant: floating button + chat panel in the lower right of every
// page, for everyone signed in (its lookups follow each person's own access)
$fods_ai = null;
if (session('__xsys_myuserzicas_is_logged__') && session('__xsys_myuserzicas__')) {
    try {
        $fods_ai_user = fods_current_user();
        $fods_ai = [
            'url' => site_url('myassistant'),
            'me' => (string) session('__xsys_myuserzicas__'),
            'firstName' => explode(' ', trim((string) $fods_ai_user['full_name']))[0],
            'areas' => model('App\Models\MyAssistantModel')->areas(),
            'configured' => \App\Models\MyAssistantModel::configured(),
        ];
    } catch (\Throwable $e) {
        log_message('error', 'Assistant panel: ' . $e->getMessage());
    }
}
?>
<?php if ($fods_ai): ?>
  <link rel="stylesheet" href="<?=base_url('assets/css/fods-assistant.css?v=1')?>">
  <div class="fods-ai-panel" id="fodsAiPanel" role="dialog" aria-label="FODS Assistant" aria-hidden="true" data-opts="<?= esc(json_encode($fods_ai), 'attr') ?>">
    <div class="fods-ai-head">
      <div class="as-avatar"><i class="ti ti-message-chatbot"></i></div>
      <div style="min-width:0">
        <div class="fods-ai-title">FODS Assistant</div>
        <div class="fods-ai-sub">Reads FODS for you · uses your access</div>
      </div>
      <div class="ms-auto d-flex gap-1 flex-shrink-0">
        <button type="button" class="fods-icon-btn" id="fodsAiNew" title="New chat" aria-label="New chat"><i class="ti ti-plus"></i></button>
        <button type="button" class="fods-icon-btn" id="fodsAiClose" title="Close" aria-label="Close"><i class="ti ti-x"></i></button>
      </div>
    </div>
    <div id="fodsAiChat"></div>
  </div>
  <button type="button" class="fods-ai-fab" id="fodsAiFab" aria-controls="fodsAiPanel" aria-expanded="false" aria-label="Ask the FODS Assistant" title="Ask the FODS Assistant"><i class="ti ti-message-chatbot"></i><i class="ti ti-x"></i></button>
  <script src="<?=base_url('assets/js/assistant/fods-assistant.js?v=2')?>"></script>
  <script src="<?=base_url('assets/js/assistant/fods-assistant-panel.js?v=1')?>"></script>
<?php endif; ?>
</body>
</html>
