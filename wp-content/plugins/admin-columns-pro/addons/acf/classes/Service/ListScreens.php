<?php

namespace ACA\ACF\Service;

use AC\Column;
use AC\Groups;
use AC\Registerable;
use ACA\ACF\ListScreen;

class ListScreens implements Registerable {

	public function register() {
		add_action( 'ac/list_screen_groups', [ $this, 'register_list_screen_groups' ] );
		add_filter( 'ac/export/value', [ $this, 'strip_tags_export_value' ], 10, 2 );
	}

	public function strip_tags_export_value( string $value, Column $column ) {
		if ( $column->get_list_screen() instanceof ListScreen\FieldGroup && $column->is_original() ) {
			$value = strip_tags( $value );
		}

		return $value;
	}

	public function register_list_screen_groups( Groups $groups ): void {
		$groups->add( 'acf', __( 'Advanced Custom Fields', 'codepress-admin-columns' ), 7 );
	}

}