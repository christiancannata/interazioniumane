<?php

global $wpowp_fs;

$current_tab = str_replace( 'admin/', '', $tab ); // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable

$div = '<div class="wrap">';

$div .= '<nav class="nav-tab-wrapper">';

$div .= '<a class="nav-tab ' . ( 'settings' === $current_tab ? 'nav-tab-active' : '' ) . '"  href="' . admin_url( 'admin.php?page=' . WPOWP_PLUGIN_SLUG ) . '&tab=settings">' . __( 'Settings', WPOWP_TEXT_DOMAIN ) . '</a>';

if ( $wpowp_fs->is_paying() ) 
$div .= '<a class="nav-tab ' . ( 'quote-only' === $current_tab ? 'nav-tab-active' : '' ) . '"  href="' . admin_url( 'admin.php?page=' . WPOWP_PLUGIN_SLUG ) . '&tab=quote-only">' . __( 'Quote Only ', WPOWP_TEXT_DOMAIN ) . '</a>';
else
 
$div .= '<a class="nav-tab ' . ( 'quote-only' === $current_tab ? 'nav-tab-active' : '' ) . '"  href="' . admin_url( 'admin.php?page=' . WPOWP_PLUGIN_SLUG ) . '&tab=quote-only">' . __( 'Quote Only (PRO)', WPOWP_TEXT_DOMAIN ) . '</a>';
// $div .= '<a class="nav-tab ' . ( 'docs' === $current_tab ? 'nav-tab-active' : '' ) . '"  href="' . admin_url( 'admin.php?page=' . WPOWP_PLUGIN_SLUG ) . '&tab=docs">' . __( 'Documentation', WPOWP_TEXT_DOMAIN ) . '</a>';

$div .= '</nav>';

echo wp_kses_post( $div );

