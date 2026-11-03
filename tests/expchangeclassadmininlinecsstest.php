<?php
/**
 * The admin templates of expchangeclass carry no inline CSS and use only the shared admin components: no <style>
 * block, no style="" attribute, and every a4-* class they use is defined in the kernel's components.css. The extension
 * ships no stylesheet of its own. Reads the files only, without the kernel.
 *
 * Run directly with (from the extension, inside an installation; IC-03 is skipped outside one):
 *   php tests/expchangeclassadmininlinecsstest.php
 *
 *  IC-01 - no <style> block and no style="" attribute in the templates of the admin designs (template comments left out)
 *  IC-02 - design.ini names no stylesheet of the extension, and the extension has none
 *  IC-03 - every a4-* class of the templates is defined in design/admin/stylesheets/components.css of the installation
 *  IC-04 - no class of the templates outside the shared components and the admin frame (context-block, box-*)
 */

$failures = 0;
function icCheck( $ok, $message )
{
    global $failures;
    echo ( $ok ? 'PASS' : 'FAIL' ), ": $message\n";
    if ( !$ok )
        $failures++;
}

$root = dirname( __DIR__ );
$templates = array();
foreach ( glob( $root . '/design/{admin,admin2,admin3,admin4,admin4l,adminui}', GLOB_BRACE | GLOB_ONLYDIR ) as $dir )
{
    $it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
    foreach ( $it as $file )
        if ( substr( (string)$file, -4 ) === '.tpl' )
            $templates[substr( (string)$file, strlen( $root ) + 1 )] = preg_replace( '/\{\*.*?\*\}/s', '', file_get_contents( (string)$file ) );
}
ksort( $templates );

// IC-01
$inline = array();
foreach ( $templates as $path => $source )
    if ( preg_match_all( '/<style\b/i', $source ) + preg_match_all( '/(?<![\w.\-])style\s*=\s*["\'{]/i', $source ) )
        $inline[] = $path;
icCheck( count( $templates ) > 0 && !$inline, 'IC-01 no inline CSS in ' . count( $templates ) . ' admin templates' . ( $inline ? ': ' . implode( ', ', $inline ) : '' ) );

// IC-02
$ini = (string)@file_get_contents( $root . '/settings/design.ini.append.php' );
$sheets = glob( $root . '/design/*/stylesheets/{*,*/*}.css', GLOB_BRACE );
icCheck( !preg_match( '/^(BackendCSSFileList|AdminComponentCSSFileList|FrontendCSSFileList)\[\]=/m', $ini ) && !$sheets,
         'IC-02 the extension ships no stylesheet' . ( $sheets ? ': ' . implode( ', ', $sheets ) : '' ) );

// the classes the templates use
$classes = array();
foreach ( $templates as $path => $source )
    if ( preg_match_all( '/class="([^"]*)"/', $source, $m ) )
        foreach ( $m[1] as $list )
            foreach ( preg_split( '/\s+/', trim( preg_replace( '/\{.*?\}/s', ' ', $list ) ) ) as $class )
                // "is-{$kind}" leaves "is-": a class made from a variable is not checked
                if ( $class !== '' && substr( $class, -1 ) !== '-' )
                    $classes[$class][$path] = true;

// IC-03
$components = dirname( $root, 2 ) . '/design/admin/stylesheets/components.css';
if ( is_file( $components ) )
{
    $css = file_get_contents( $components );
    $missing = array();
    foreach ( $classes as $class => $paths )
        if ( ( strpos( $class, 'a4-' ) === 0 || strpos( $class, 'is-' ) === 0 ) && !preg_match( '/\.' . preg_quote( $class, '/' ) . '(?![\w-])/', $css ) )
            $missing[] = $class;
    icCheck( !$missing, 'IC-03 every a4-* and is-* class is a shared component' . ( $missing ? ': ' . implode( ', ', $missing ) : '' ) );
}
else
    echo "SKIP: IC-03 no installation around the extension\n";

// IC-04
$frame = array( 'context-block', 'context-title', 'box-header', 'box-ml', 'box-bc', 'box-content' );
$other = array();
foreach ( $classes as $class => $paths )
    if ( strpos( $class, 'a4-' ) !== 0 && strpos( $class, 'is-' ) !== 0 && !in_array( $class, $frame, true ) )
        $other[] = $class . ' (' . implode( ', ', array_keys( $paths ) ) . ')';
icCheck( !$other, 'IC-04 only shared components and the admin frame' . ( $other ? ': ' . implode( '; ', $other ) : '' ) );

echo $failures ? "FAIL: $failures\n" : "PASS: all\n";
exit( $failures ? 1 : 0 );
