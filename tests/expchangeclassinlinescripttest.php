<?php
/**
 * The templates of expchangeclass carry no inline JavaScript: no <script> element with code, no event handler
 * attribute (onclick=, onmouseover= ...) and no javascript: link. A Content-Security-Policy without 'unsafe-inline' for
 * script-src blocks those. Reads the files only, without the kernel.
 *
 * Run directly with:
 *   php tests/expchangeclassinlinescripttest.php
 *
 *  IS-01 - no <script> element with code in any template (a src= file or a JSON data block is fine; comments left out)
 *  IS-02 - no event handler attribute and no javascript: link in any template
 *  IS-03 - each "Change content class" menu entry declares its form in data-menu-form and the template prints
 *          expchangeclass_menu.js, which submits that form with an older ezpopupmenu.js
 */

$failures = 0;
function isCheck( $ok, $message )
{
    global $failures;
    echo ( $ok ? 'PASS' : 'FAIL' ), ": $message\n";
    if ( !$ok )
        $failures++;
}

$root = dirname( __DIR__ );
$templates = array();
$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root . '/design', FilesystemIterator::SKIP_DOTS ) );
foreach ( $it as $file )
    if ( substr( (string)$file, -4 ) === '.tpl' )
        $templates[substr( (string)$file, strlen( $root ) + 1 )] = preg_replace( '/\{\*.*?\*\}/s', '', file_get_contents( (string)$file ) );
ksort( $templates );

// IS-01
$scripts = array();
foreach ( $templates as $path => $source )
    if ( preg_match( '/<script\b(?![^>]*\bsrc\s*=)(?![^>]*type\s*=\s*["\']application\/(?:ld\+)?json["\'])[^>]*>/i', $source ) )
        $scripts[] = $path;
isCheck( count( $templates ) > 0 && !$scripts, 'IS-01 no inline script in ' . count( $templates ) . ' templates' . ( $scripts ? ': ' . implode( ', ', $scripts ) : '' ) );

// IS-02
$handlers = array();
foreach ( $templates as $path => $source )
    if ( preg_match( '/<[a-z][^>]*\son[a-z]+\s*=/i', $source ) || preg_match( '/=\s*["\']?\s*javascript:/i', $source ) )
        $handlers[] = $path;
isCheck( !$handlers, 'IS-02 no event handler attribute or javascript: link' . ( $handlers ? ': ' . implode( ', ', $handlers ) : '' ) );

// IS-03
$js = (string)@file_get_contents( $root . '/design/admin/javascript/expchangeclass_menu.js' );
$ok = strpos( $js, 'window.ezpopmenu_submitForm(link.getAttribute(\'data-menu-form\'))' ) !== false;
foreach ( array( 'classcontextmenu.tpl' => 'menu-class-change', 'classsubitemscontextmenu.tpl' => 'menu-class-change-sub' ) as $name => $id )
{
    $source = isset( $templates['design/admin/templates/popupmenu/' . $name] ) ? $templates['design/admin/templates/popupmenu/' . $name] : '';
    $form = str_replace( 'menu-', 'menu-form-', $id );
    $ok = $ok && strpos( $source, '<a id="' . $id . '" href="#" data-menu-form="' . $form . '">' ) !== false
              && strpos( $source, '<form id="' . $form . '"' ) !== false
              && strpos( $source, "{ezscript( array( 'expchangeclass_menu.js' ) )}" ) !== false
              && strpos( $js, "'" . $id . "'" ) !== false;
}
isCheck( $ok, 'IS-03 the menu entries declare their form, and expchangeclass_menu.js submits it with an older ezpopupmenu.js' );

echo $failures ? "FAIL $failures\n" : "PASS\n";
exit( $failures ? 1 : 0 );
