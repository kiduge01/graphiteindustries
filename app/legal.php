<?php
declare(strict_types=1);
function legal_pages(string $email): array
{
    return [
        'privacy' => [
            'title' => 'Privacy notice.',
            'summary' => 'How this website handles enquiries and optional visitor measurement.',
            'body' => "Information you choose to share\n\nOur enquiry form collects your name, email, organisation, enquiry category and message to review and respond to your request. Do not submit sensitive or confidential information.\n\nWebsite measurement\n\nOptional first-party analytics record page visits, referring hostnames and document clicks after you accept analytics. A random session identifier is used to count visits; the application does not store IP addresses or infer your country. Your preference is saved in this browser.\n\nAccess and questions\n\nContact $email to ask about your enquiry information or request correction or deletion. Information is accessible to authorised staff. The company must confirm its retention schedule and applicable privacy arrangements before public launch.",
        ],
        'terms' => [
            'title' => 'Website terms.',
            'summary' => 'Information about preliminary project materials and use of this website.',
            'body' => "Preliminary project information\n\nThis website introduces a proposed development. Information does not constitute an offer to invest, a resource or reserve statement, or a guarantee of project performance. Planning figures are subject to verification and change.\n\nIllustrations and documents\n\nSupporting imagery is illustrative unless expressly identified otherwise. Documents marked draft remain preliminary. Enquiries do not create a contractual commitment.\n\nContact\n\nQuestions about this website may be directed to $email.",
        ],
    ];
}
