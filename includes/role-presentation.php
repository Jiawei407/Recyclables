<?php
/** Role-only presentation of already-rendered PHP output. No request/data handling lives here. */
function render_role_layout(string $html, string $page, array $user): string
{
    if (!class_exists('DOMDocument') || !preg_match('~^(user|faculty|coordinator|admin)/[a-z0-9-]+\.php$~D', $page, $match) ||
        stripos($html, '<html') === false || stripos($html, '<main') === false) return $html;
    $role = $match[1];
    if (($user['role'] ?? '') !== $role) return $html;
    $previous = libxml_use_internal_errors(true);
    $doc = new DOMDocument('1.0', 'UTF-8');
    try {
        if (!$doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NONET)) return $html;
        $xp = new DOMXPath($doc);
        $main = $xp->query('//main')->item(0);
        $head = $xp->query('//head')->item(0);
        $body = $xp->query('//body')->item(0);
        if (!$main || !$head || !$body) return $html;
        $classes = static function (DOMElement $node, string $extra): void {
            $node->setAttribute('class', trim($node->getAttribute('class') . ' ' . $extra));
        };
        $nodes = static function (string $query, ?DOMNode $context = null) use ($xp): array {
            return iterator_to_array($xp->query($query, $context));
        };
        $has = static function (string $class): string {
            return 'contains(concat(" ",normalize-space(@class)," ")," ' . $class . ' ")';
        };
        $create = static function (string $tag, string $class = '', string $text = '') use ($doc): DOMElement {
            $node = $doc->createElement($tag);
            if ($class !== '') $node->setAttribute('class', $class);
            if ($text !== '') $node->appendChild($doc->createTextNode($text));
            return $node;
        };
        $route = str_replace('.php', '.html', $page);
        $file = basename($page);
        $home = $file === 'home.php';
        $bold = $home || in_array($page, ['user/profile-profile.php', 'user/profile-edit-details.php',
            'coordinator/profile-manage-account.php', 'coordinator/profile-change-password.php'], true);
        $dialog = $xp->query('//*[@role="dialog"]')->item(0);
        $isForm = !$home && !$dialog && $xp->query('.//*[' . $has('form-grid') . '] | .//input[@type="file"]', $main)->length > 0;
        $detail = (bool)preg_match('~(?:-details|-view-challenge|-faculty-report|-activity-report|-faculty-cleanup|-campus-cleanup)\.php$~', $file);
        $analytics = (bool)preg_match('~analytics|insights|completion-rates|ranking~', $file);
        $layout = $home ? 'dashboard' : ($isForm ? 'form' : ($detail ? 'read' : ($analytics ? 'analytics' : 'browse')));
        $family = $home ? 'dashboard' : (str_starts_with($file, 'profile-') ? 'profile' :
            (str_contains($file, 'announcements') ? 'announcements' :
            (preg_match('~submit-report|reports-~', $file) ? 'reports' :
            (preg_match('~submissions|submit-evidence~', $file) ? 'evidence' :
            (preg_match('~challenge|campaign~', $file) ? 'challenges' :
            (str_contains($file, 'achievement') ? 'achievements' :
            (preg_match('~leaderboard|ranking~', $file) ? 'rankings' :
            (str_starts_with($file, 'users-') ? 'accounts' : (str_contains($file, 'audit') ? 'audit' : 'analytics')))))))));
        $classes($doc->documentElement, 'clay-site');
        $doc->documentElement->setAttribute('data-palette', 'original');
        $classes($body, 'layout-redesign role-' . $role . ' intensity-' . ($bold ? 'expressive' : 'calm') .
            ' layout-' . $layout . ($home ? ' dashboard' : '') . ($bold && !$home ? ' expressive-profile' : ''));
        $body->setAttribute('data-page-family', $family);
        $body->setAttribute('data-surface', $home ? 'dashboard' : ($dialog ? 'dialog' : ($isForm ? 'form' : ($detail ? 'detail' : 'list'))));
        foreach (['theme', 'controls', 'layout', 'density', 'expressive', ...($home ? ['role-home'] : []), 'consistency', 'selections'] as $style) {
            $link = $create('link'); $link->setAttribute('rel', 'stylesheet');
            $link->setAttribute('href', '../assets/role-ui/' . $style . '.css'); $head->appendChild($link);
        }
        $intro = $xp->query('.//*[' . $has('page-header') . ']', $main)->item(0);
        if ($intro) $classes($intro, 'workspace-intro ' . ($bold ? 'poster-intro' : 'intro-plain'));

        // Reuse the real navigation and logout form, including its CSRF token and POST action.
        $sidebar = $xp->query('//*[' . $has('app-sidebar') . ']')->item(0);
        if ($sidebar) {
            $header = $create('header', 'clay-header');
            $top = $create('div', 'clay-header-top');
            $brand = $xp->query('.//a[' . $has('brand') . ']', $sidebar)->item(0);
            if ($brand) $top->appendChild($brand->cloneNode(true));
            $account = $create('div', 'clay-role');
            $labels = ['user' => 'Registered User', 'faculty' => 'Faculty Representative',
                'coordinator' => 'Sustainability Coordinator', 'admin' => 'Administrator'];
            $account->appendChild($create('strong', 'role-label', $labels[$role]));
            $account->appendChild($create('div', 'role-account-name', (string)($user['full_name'] ?? '')));
            $logout = $xp->query('.//form[.//input[@name="action" and @value="logout"]]', $sidebar)->item(0);
            if ($logout) $account->appendChild($logout->cloneNode(true));
            $top->appendChild($account); $header->appendChild($top);
            $nav = $xp->query('.//nav[' . $has('sidebar-nav') . ']', $sidebar)->item(0);
            if ($nav) {
                $desktop = $nav->cloneNode(true); $desktop->setAttribute('class', 'clay-nav'); $header->appendChild($desktop);
                $menu = $create('details', 'mobile-menu');
                $summary = $create('summary', '', 'Workspace menu');
                $icon = $create('svg', 'icon'); $icon->setAttribute('viewBox', '0 0 24 24'); $icon->setAttribute('aria-hidden', 'true');
                $path = $create('path'); $path->setAttribute('d', 'm6 9 6 6 6-6'); $icon->appendChild($path); $summary->appendChild($icon);
                $menu->appendChild($summary);
                $mobile = $nav->cloneNode(true); $mobile->setAttribute('class', 'clay-nav'); $mobile->setAttribute('aria-label', 'Mobile workspace navigation');
                $menu->appendChild($mobile);
                $menu->appendChild($create('p', 'mobile-account', $labels[$role] . ' · ' . (string)($user['full_name'] ?? '')));
                if ($logout) $menu->appendChild($logout->cloneNode(true));
                $header->appendChild($menu);
            }
            $sidebar->parentNode->replaceChild($header, $sidebar);
            if ($dialog) $header->setAttribute('inert', '');
        }
        foreach ($nodes('//*[' . $has('workspace-header') . ']') as $oldHeader) $oldHeader->parentNode->removeChild($oldHeader);
        if ($dialog) $main->setAttribute('inert', '');

        // Only redundant presentation is removed. Review records and menu destinations stay real.
        if ($page === 'coordinator/home.php') foreach ($nodes('.//section[@aria-labelledby="program-title"]//a[contains(@href,"submissions-pending.php")]', $main) as $link) {
            $wrapper = $link->parentNode; $wrapper->parentNode->removeChild($wrapper);
        }
        if ($page === 'admin/home.php') foreach ($nodes('.//*[' . $has('status-banner') . ']', $main) as $status) $status->parentNode->removeChild($status);
        $photos = ['user/home.php' => 'field-horizon.jpg', 'faculty/home.php' => 'shared-action.jpg',
            'user/profile-edit-details.php' => 'canopy.jpg', 'coordinator/profile-manage-account.php' => 'field-horizon.jpg'];
        if ($intro && isset($photos[$page])) {
            $figure = $create('figure', 'poster-photo'); $figure->setAttribute('aria-hidden', 'true');
            $image = $create('img'); $image->setAttribute('src', '../assets/nature/' . $photos[$page]);
            foreach (['alt' => '', 'width' => '1800', 'height' => '900'] as $key => $value) $image->setAttribute($key, $value);
            $figure->appendChild($image); $intro->appendChild($figure);
        }
        foreach ($nodes('//*[' . $has('form-actions') . ']') as $actions) {
            if (!$actions->getElementsByTagName('*')->length && trim($actions->textContent) === '') $classes($actions, 'is-empty');
        }
        // Search uses real record text, never the extra mobile column labels.
        foreach ($nodes('//*[@data-record]') as $recordNode) $recordNode->setAttribute('data-filter-text', $recordNode->textContent);
        foreach ($nodes('//table[' . $has('data-table') . ']') as $table) {
            $headers = $nodes('.//thead//th', $table);
            $table->setAttribute('data-columns', (string)count($headers)); $table->setAttribute('role', 'table');
            foreach ($nodes('.//thead | .//tbody', $table) as $part) $part->setAttribute('role', 'rowgroup');
            foreach ($nodes('.//tr', $table) as $row) $row->setAttribute('role', 'row');
            foreach ($headers as $column) $column->setAttribute('role', 'columnheader');
            foreach ($nodes('.//tbody/tr', $table) as $row) {
                foreach ($nodes('./td', $row) as $index => $cell) {
                    $cell->setAttribute('role', 'cell');
                    if ($cell->hasAttribute('colspan')) continue;
                    $value = $create('span', 'cell-value');
                    while ($cell->firstChild) $value->appendChild($cell->firstChild);
                    $label = $create('span', 'cell-label', trim($headers[$index]->textContent ?? ''));
                    $label->setAttribute('aria-hidden', 'true'); $cell->appendChild($label); $cell->appendChild($value);
                }
            }
        }
        $copy = json_decode(file_get_contents(__DIR__ . '/role-copy.json'), true, 512, JSON_THROW_ON_ERROR);
        $routeCopy = $copy['routes'][$route] ?? [];
        $replacements = array_merge($copy['shared'] ?? [], $routeCopy['replacements'] ?? []);
        foreach ($nodes('//main//h2 | //main//h3 | //main//p | //main//small') as $element) {
            // Never interpret participant content, database records or feedback as interface copy.
            if ($xp->query('ancestor::*[@data-record or @data-popup-message] | ancestor::td | ancestor::dd | ancestor::textarea | ancestor::select', $element)->length ||
                $xp->query('ancestor::section[parent::main] | ancestor::*[' . $has('profile-summary') . ']', $element)->length ||
                $xp->query('.//input | .//select | .//textarea | .//button | .//a', $element)->length) continue;
            $text = preg_replace('/\s+/u', ' ', trim(str_replace(["\r", "\n"], ' ', $element->textContent)));
            // DOM textContent concatenates lines separated by br; the map includes legacy aliases.
            if (isset($replacements[$text])) $element->textContent = $replacements[$text];
        }
        if ($intro && isset($routeCopy['description'])) {
            $description = $xp->query('.//*[' . $has('page-description') . ']', $intro)->item(0);
            if (!$description) { $description = $create('p', 'page-description'); ($xp->query('./div', $intro)->item(0) ?? $intro)->appendChild($description); }
            $description->textContent = $routeCopy['description'];
        }
        // Keep browser constraints aligned with the agreed server-side account policy.
        foreach ($nodes('//main//input[@type="email"] | //*[@role="dialog"]//input[@type="email"]') as $email) {
            $email->setAttribute('pattern', '[^\s@]+@[mM][aA][iI][lL]\.[aA][pP][uU]\.[eE][dD][uU]\.[mM][yY]');
            $email->setAttribute('title', 'Use your @mail.apu.edu.my university email.');
        }
        foreach ($nodes('//input[@pattern="TP[0-9]{6}"]') as $identity) $identity->setAttribute('pattern', '[Tt][Pp][0-9]{6}');
        foreach ($nodes('//input[@type="file" and contains(@accept,"image/")]') as $upload) {
            $accept = $upload->getAttribute('accept');
            $upload->setAttribute('accept', $accept . ',image/heic,image/heif,.heic,.heif');
        }
        foreach ($nodes('//p[' . $has('field-hint') . ']') as $hint) {
            if (trim($hint->textContent) === 'JPG or PNG.') $hint->textContent = 'JPG, PNG or HEIC/HEIF. Up to ' .
                (function_exists('upload_max_bytes') ? (int)floor(upload_max_bytes() / 1048576) : 256) . ' MB.';
            if (trim($hint->textContent) === 'Record a useful reason before requesting a revision or rejecting evidence.') $hint->textContent = 'Review notes are optional.';
        }
        // Suppress a stale empty-state note only when PHP has actually loaded a selected record.
        foreach ([$main, ...($dialog ? [$dialog] : [])] as $scope) {
            $record = $xp->query('.//input[@type="hidden" and @name="id" and number(@value)>0]', $scope)->item(0);
            if ($record) foreach ($nodes('.//p[' . $has('note') . ' and normalize-space(.)="No record selected."]', $scope) as $note) $note->parentNode->removeChild($note);
        }
        foreach (['role-controls', 'role-selections'] as $scriptName) {
            $script = $create('script'); $script->setAttribute('src', '../scripts/' . $scriptName . '.js');
            $script->setAttribute('defer', ''); $body->appendChild($script);
        }
        foreach (iterator_to_array($doc->childNodes) as $child) if ($child->nodeType === XML_PI_NODE) $doc->removeChild($child);
        return $doc->saveHTML();
    } finally {
        libxml_clear_errors(); libxml_use_internal_errors($previous);
    }
}
