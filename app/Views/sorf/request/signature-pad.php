<?php
// Signature pad, driven by assets/js/drf-signature.js.
// 'current' = URL of the signature already on the request: it is shown and
// kept unless the person chooses "Sign again".
$current = $current ?? '';
?>
<div class="drf-sign" data-drf-sign>
    <?php if ($current !== ''): ?>
    <div class="drf-sign-current" data-sign-current>
        <img src="<?= esc($current, 'attr') ?>" alt="Your signature on this request">
        <button type="button" class="btn btn-sm btn-drf-ghost" data-sign-redo><i class="ti ti-pencil me-1"></i>Sign again</button>
    </div>
    <?php endif; ?>
    <div data-sign-new <?= $current !== '' ? 'hidden' : '' ?>>
        <div class="drf-sign-pad">
            <canvas data-sign-canvas aria-label="Signature pad. Draw your signature here."></canvas>
            <span class="drf-sign-hint" data-sign-hint>Sign here with your mouse, finger or stylus</span>
        </div>
        <div class="drf-sign-actions">
            <button type="button" class="btn btn-sm btn-drf-ghost" data-sign-upload><i class="ti ti-upload me-1"></i>Upload a picture instead</button>
            <button type="button" class="btn btn-sm btn-drf-ghost" data-sign-clear><i class="ti ti-eraser me-1"></i>Clear</button>
            <input type="file" accept="image/png,image/jpeg,image/webp" data-sign-file hidden>
        </div>
    </div>
</div>
