<?php

namespace Config\Sorf;

use CodeIgniter\Config\BaseConfig;

/**
 * The Special Order documents HR prepares in the document editor.
 *
 * Each template is a view in app/Views/docs/templates/<view>.php that fills
 * the official form from the request. 'documents' are the request document
 * types (tbl_document_type.document_id) the template is offered for; the
 * first template listed for a type is the one suggested.
 */
class DocumentTemplates extends BaseConfig
{
    public const STATUS_DRAFT = 'Draft';
    public const STATUS_RELEASED = 'Released';

    // There is no final release yet: "Released" means HR shared the S.O. with
    // the requester as a draft, and every copy (page, print, Word) is stamped
    // DRAFT. Set to false once a final release exists.
    public bool $draftOnly = true;

    // Signs every Special Order
    public string $signatoryName = 'JACUS S. NACIS';
    public string $signatoryPosition = 'Director IV';

    public array $templates = [
        'ATRH-INC-AMEND' => ['name' => 'ATRH - Authority to Receive Incentive (Amendment)', 'view' => 'atrh-amend', 'documents' => ['SO01'], 'kind' => 'Incentive'],
        'ATRH-HON-AMEND' => ['name' => 'ATRH - Authority to Receive Honorarium (Amendment)', 'view' => 'atrh-amend', 'documents' => ['SO01'], 'kind' => 'Honorarium'],
        'GSO' => ['name' => 'General Special Order (Committee)', 'view' => 'gso', 'documents' => ['SO02']],
        'SDO' => ['name' => 'Special Disbursing Officer (SDO)', 'view' => 'sdo', 'documents' => ['SO03']],
        'PCFC-AMEND' => ['name' => 'Petty Cash Fund Custodian (Amendment)', 'view' => 'pcfc-amend', 'documents' => ['SO04']],
        'ATAA' => ['name' => 'Authority to Attend Activity (ATAA)', 'view' => 'ataa', 'documents' => ['SO05']],
        'LTO' => ['name' => 'Local Travel Order (LTO)', 'view' => 'lto', 'documents' => ['SO06']],
        'PTC' => ['name' => 'Project Team Composition (PTC)', 'view' => 'ptc', 'documents' => ['SO07']],
    ];

    /**
     * The template suggested for a request's document type ('' when none is listed for it).
     */
    public function suggested(string $documentId): string
    {
        foreach ($this->templates as $code => $template) {
            if (in_array($documentId, $template['documents'], true)) {
                return $code;
            }
        }
        return '';
    }
}
