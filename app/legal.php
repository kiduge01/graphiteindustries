<?php
declare(strict_types=1);
// Draft legal copy. Have the company's legal adviser confirm it before public launch.
function legal_pages(string $email): array
{
    return [
        'privacy' => [
            'title' => 'Privacy notice.',
            'summary' => 'How this website handles enquiries, update subscriptions, cookies and optional visitor measurement.',
            'body' => "Who we are\n\nGraphite Industries Limited is responsible for personal data collected through this website. We handle personal data in accordance with the Personal Data Protection Act, 2022 of the United Republic of Tanzania and its regulations.\n\nInformation you choose to share\n\nOur enquiry form collects your name, email, organisation, enquiry category and message so we can review and respond to your request. Do not submit sensitive or confidential information. New enquiries may also be forwarded by email to authorised staff.\n\nProject updates\n\nIf you subscribe to project updates, we store your email address and the date of your consent and use it only to send company announcements. You can unsubscribe at any time by contacting $email.\n\nCookies and browser storage\n\nThe website sets one essential session cookie that protects forms against misuse. It contains no personal profile and expires when you close your browser. Your analytics choice is saved in your browser's local storage. We do not use advertising or third-party tracking cookies.\n\nWebsite measurement\n\nOptional first-party analytics record page visits, referring hostnames and document clicks only after you accept analytics. A random session identifier is used to count visits; the application does not store IP addresses or infer your country. Analytics records are deleted after 90 days.\n\nYour rights\n\nYou may ask to access, correct or delete the personal data we hold about you, object to its use, or withdraw consent at any time. Contact $email. Information is accessible only to authorised staff. You may also lodge a complaint with the Personal Data Protection Commission of Tanzania.\n\nRetention\n\nEnquiries and subscriptions are kept only for as long as needed to respond, maintain the relationship or meet legal obligations, and are then deleted.",
        ],
        'terms' => [
            'title' => 'Website terms.',
            'summary' => 'Information about preliminary project materials and use of this website.',
            'body' => "Preliminary project information\n\nThis website introduces a proposed development. Information does not constitute an offer to invest, a resource or reserve statement, or a guarantee of project performance. Planning figures are subject to verification and change.\n\nIllustrations and documents\n\nSupporting imagery is illustrative unless expressly identified otherwise. Documents marked draft remain preliminary. Enquiries do not create a contractual commitment.\n\nIntellectual property\n\nThe Graphite Industries Limited name, logo and website content belong to the company or its licensors and may not be reused without permission.\n\nGoverning law\n\nThese terms are governed by the laws of the United Republic of Tanzania.\n\nContact\n\nQuestions about this website may be directed to $email.",
        ],
    ];
}
