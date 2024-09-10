<?php
namespace WPHelper;
use Exception;
use wpdb;
use function dbDelta;
/**
 * Database Table Utility/Helper Class
 * 
 * @since 0.1
 */
class DatabaseTable{


	/**
	 * Util: CREATE TABLE
	 * 
	 * @since 0.1
	 * 
	 * @global wpdb $
	 * 
	 * @uses dbDelta
	 * 
	 * @param string $table_name table
	 * @param string $schema SQL formatted string
	 * @return void
	 */
	public static function create_table( $table_name, $schema ){
		global $wpdb;

		if ( empty( $table_name ) ){
			throw new Exception( __METHOD__ . ': $table_name cannot be empty.');
		}
		if ( empty( $schema ) ){
			throw new Exception( __METHOD__ . ': $schema cannot be empty.');
		}
		
		$table_name = static::validate_table_name( $table_name );

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		if( ! static::table_exists( $table_name ) ) {
			$charset_collate = $wpdb->get_charset_collate();
			$engine = 'ENGINE = MyISAM';
			$before = "SET sql_notes = 0;";
			$after = "SET sql_notes = 1;";
				
			$sql = "CREATE TABLE $table_name ($schema) $engine $charset_collate;";
				
			$sql = $before . $sql . $after;
			dbDelta( $sql );
		}

	}


	/**
	 * create: META TABLE
	 *
	 * Create empty copy of wp-postmeta table
	 *
	 * @note {meta_key_prefix}_id prefix must be the same to get $wpdb->{meta_key_prefix}meta to work
	 * 
	 * @since 0.1
	 * 
	 * @global wpdb $wpdb
	 */
	public static function create_meta_table( $meta_key_prefix  ){
		global $wpdb;
		
		$max_index_length = 191; // utf8mb4 - 4 bytes per char
		
		$schema =
		"meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		{$meta_key_prefix}_id bigint(20) unsigned NOT NULL DEFAULT '0',
		meta_key varchar(255) COLLATE $wpdb->collate DEFAULT NULL,
		meta_value longtext COLLATE $wpdb->collate,
		PRIMARY KEY  (meta_id),
		KEY {$meta_key_prefix}_id ({$meta_key_prefix}_id),
		KEY meta_key (meta_key($max_index_length))";

		static::create_table( "{$meta_key_prefix}_meta", $schema );
	}


	/**
	 * Util: DROP TABLE / PURGE TABLE
	 * 
	 * Utility function to drop table completely from WPDB
	 * 
	 * @since 0.1
	 * 
	 * @global wpdb $wpdb
	 * 
	 * @param string table_name Prefixed or non-prefixed table name.
	 * 
	 * @return void
	 */
	public static function drop_table( $table_name ){
		global $wpdb;

		if ( empty( $table_name ) ){
			throw new Exception( __METHOD__ . ': $table_name cannot be empty.');
		}

		$table_name = static::validate_table_name( $table_name );

		$sql = "DROP TABLE IF EXISTS $table_name";
		$wpdb->query($sql);

	}


	/**
	 * Util: TRUNCATE TABLE / EMPTY TABLE
	 * 
	 * Utility function to empty table completely
	 * 
	 * @since 0.1
	 * 
	 * @global wpdb $wpdb
	 * 
	 * @param string $table_name Prefixed or non-prefixed table name.
	 * 
	 * @return void
	 */
	public static function truncate_table( $table_name ){
		global $wpdb;

		if ( empty( $table_name ) ){
			throw new Exception( __METHOD__ . ': $table_name cannot be empty.');
		}

		$table_name = static::validate_table_name( $table_name );

		$sql = "TRUNCATE TABLE $table_name";
		$wpdb->query($sql);
	}


	/**
	 * Util: TABLE NAME
	 * 
	 * Get fully-prefixed table_name
	 * 
	 * @since 0.1
	 * 
	 * @global wpdb $wpdb
	 * 
	 * @param string $table_name Prefixed or non-prefixed table name.
	 * 
	 * @return string Prefixed table name.
	 */
	public static function validate_table_name( $table_name ){
		global $wpdb;

		if ( empty( $table_name ) ){
			throw new Exception( __METHOD__ . ': $table_name cannot be empty.');
		}

		if ( strpos( $table_name, $wpdb->prefix ) !== 0 ){
			$table_name = $wpdb->prefix . $table_name;
		}

		return $table_name;
	}


	/**
	 * Util: TABLE EXISTS
	 * 
	 * Test if table exists in database
	 * 
	 * @since 0.1
	 * 
	 * @global wpdb $wpdb
	 * 
	 * @param string $table_name Prefixed or non-prefixed table name.
	 * 
	 * @return boolean Table exists (yes/no)
	 */
	public static function table_exists( $table_name ){
		global $wpdb;

		if ( empty( $table_name ) )
			return false;

		$table_name = static::validate_table_name( $table_name );
		// \QM::debug( $wpdb->get_var("SHOW TABLES LIKE '$table_name'" ) );	
		// \QM::debug( $table_name );	
		// \QM::debug( $wpdb->get_var("SHOW TABLES LIKE '$table_name'" ) == $table_name );	
		return ( $wpdb->get_var("SHOW TABLES LIKE '$table_name'" ) == $table_name );
	}


	/**
	 * Util: ADD ROW
	 * 
	 * @since 0.1
	 * 
	 * @global wpdb $wpdb
	 * 
	 * @param string $table_name Prefixed or non-prefixed table name.
	 * @param array $row_array   Data to insert (in column => value pairs). Both `$row_array` columns and `$row_array` values should be "raw" (neither should be SQL escaped). A primary key or unique index is required to perform a replace operation. Sending a null value will cause the column to be set to NULL - the corresponding format is ignored in this case.
	 * 
	 * @return (int|false) The number of rows inserted, or false on error.
	 */
	public static function add_row( $table_name, $row_array ){
		global $wpdb;

		// return $wpdb->insert(
		// 	static::validate_table_name( $table_name ),
		// 	$row_array
		// );
		
		return $wpdb->replace(
			static::validate_table_name( $table_name ),
			$row_array
		);

	}


	/**
	 * Util: UPDATE ROW
	 * 
	 * @global wpdb $wpdb
	 * 
	 * @param string $table_name Prefixed or non-prefixed table name.
	 * @param array  $row_array  Data to update (in column => value pairs). Both $row_array columns and $row_array values should be "raw" (neither should be SQL escaped). Sending a null value will cause the column to be set to NULL - the corresponding format is ignored in this case.
	 * @param array  $where      A named array of WHERE clauses (in column => value pairs). Multiple clauses will be joined with ANDs. Both $where columns and $where values should be "raw". Sending a null value will create an IS NULL comparison - the corresponding format will be ignored in this case. eg. [ 'ID'=>1 ]
	 * 
	 * @return int|false The number of rows updated, or false on error.
	 */
	public static function update_row( $table_name, $row_array, $where ){
		global $wpdb;

		return $wpdb->update(
			static::validate_table_name( $table_name ),
			$row_array,
			$where
		);
		
	}


	/**
	 * Util: Select All
	 * 
	 * SELECT * FROM $table_name
	 * 
	 * @since 0.2
	 * 
	 * @global wpdb $wpdb
	 * 
	 * @param string $table_name Prefixed or non-prefixed table name.
	 * @param string $output     (Optional) Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants.
	 * 
	 * @return array|object|null All table rows.
	 */
	public static function select_all( $table_name, $output = OBJECT ){
		global $wpdb;

		return $wpdb->get_results(
			'SELECT * FROM ' . static::validate_table_name( $table_name ),
			$output
		);
		
	}

}