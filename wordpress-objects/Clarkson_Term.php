<?php

class Clarkson_Term {

	public $_term;

	/**
	 * This is used to subclasses get set it, so setters and getters don;t need to be overriden to set the taxonomy arg
	 * @var null
	 */
	protected static $taxonomy = null;


	public static function get_by_name( $name, $taxonomy = null ) {
		$term = get_term_by( 'name', $name, $taxonomy ? $taxonomy : static::$taxonomy );
		$class = get_called_class();
		return new $class( $term->term_id, $taxonomy ? $taxonomy : static::$taxonomy );
	}

	public static function get_by_slug( $slug, $taxonomy = null ) {
		$term = get_term_by( 'slug', $slug, $taxonomy ? $taxonomy : static::$taxonomy );
		$class = get_called_class();
		return new $class( $term->term_id, $taxonomy ? $taxonomy : static::$taxonomy );
	}

	public static function get_by_id( $term_id, $taxonomy = null ) {
		$term = get_term_by( 'id', $term_id, $taxonomy ? $taxonomy : static::$taxonomy );
		$class = get_called_class();
		return new $class( $term->term_id, $taxonomy ? $taxonomy : static::$taxonomy );
	}

	public function __construct( $term_id, $taxonomy = null ) {
		$taxonomy = $taxonomy ? $taxonomy : static::$taxonomy;
		if ( empty( $term_id ) || ! $taxonomy ) {
			throw new Exception( '$term_id or $taxonomy empty' );
		}
		$this->_term = get_term( (int) $term_id, $taxonomy );
		if ( ! $this->_term ) {
			throw new Exception( 'Term not found' );
		}
	}

	public function __get( $name ) {
		if ( in_array( $name, array( 'term_id', 'name', 'slug', 'taxonomy' ) ) ) {
			throw new Exception( 'Trying to access wp_term object properties from Term object' );
		}
	}

	/**
	 * Check is this term was used in the global $wp_query
	 *
	 * @return bool
	 */
	public function is_queried_object() {
		global $wp_query;
		$term_or_taxonomy = $this->_term;
		// tax
		if ( is_string( $term_or_taxonomy ) ) {
			if ( $wp_query->tax_query ) {
				foreach ( $wp_query->tax_query->queries as $query ) {
					if ( $query['taxonomy'] == $term_or_taxonomy ) {
						return true;
					}
				}
			}
			if ( ! empty( $wp_query->_post_parent_query ) ) {
				foreach ( $wp_query->_post_parent_query->tax_query->queries as $query ) {
					if ( $query['taxonomy'] == $term_or_taxonomy ) {
						return true;
					}
				}
			}
		} elseif ( is_object( $term_or_taxonomy ) ) {
			foreach ( $wp_query->tax_query->queries as $query ) {
				if ( 'slug' == $query['field'] && in_array( $term_or_taxonomy->slug, $query['terms'] ) ) {
					return true;
				}
				if ( in_array( $term_or_taxonomy->term_id, $query['terms'] ) ) {
					return true;
				}
			}
		}
		return false;
	}

	public function get_id() {
		return $this->_term->term_id;
	}

	public function get_parent() {
		if ( $this->_term->parent ) {
			$class = get_called_class();
			return new $class( $this->_term->parent, $this->get_taxonomy() );
		}
		return null;
	}

	public function get_taxonomy() {
		return $this->_term->taxonomy;
	}

	public function get_meta( $key, $single = false ) {
		return get_term_meta( $this->get_id(), $key, $single );
	}

	public function update_meta( $key, $value ) {
		return update_term_meta( $this->get_id(), $key, $value );
	}

	public function add_meta( $key, $value ) {
		return add_term_meta( $this->get_id(), $key, $value );
	}

	public function delete_meta( $key, $value = null ) {
		return delete_term_meta( $this->get_id(), $key, $value );
	}

	public function get_slug() {
		return $this->_term->slug;
	}

	public function get_name() {
		return $this->_term->name;
	}

	public function get_description() {
		return $this->_term->description;
	}

	public function set_name( $name ) {
		wp_update_term( $this->get_id(), $this->get_taxonomy(), array(
			'name' => $name,
		) );
	}

	public function get_term() {
		return $this->_term;
	}

	public function get_term_taxonomy_id() {
		return $this->_term->term_taxonomy_id;
	}

	public function get_permalink() {
		return get_term_link( $this->get_term(), $this->get_taxonomy() );
	}
}
