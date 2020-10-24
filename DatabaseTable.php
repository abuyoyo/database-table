<?php
namespace WPHelper;
use Exception;
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
	 * @global WPDB $wpdb
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
	 * @global WPDB
	 * @uses dbDelta
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
	 * @global WPDB $wpdb
	 * 
	 * @param string table_name (fully prefixed)
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
	 * @global WPDB $wpdb
	 * 
	 * @param string $table_name table
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
	 * @global WPDB $wpdb
	 * 
	 * @param string $table_name (prefix-)table
	 * 
	 * @return string table 
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
	 * @global WPDB $wpdb
	 * 
	 * @param string $table_name table
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
	 * @global WPDB $wpdb
	 * 
	 * @param string $table_name table
	 * @param array $row_array data
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
	 * @global WPDB $wpdb
	 * 
	 * @param string $table_name table
	 * @param array $row_array data
	 * @param array $where eg. [ 'ID'=>1 ]
	 * 
	 * @return (int|false) The number of rows updated, or false on error.
	 */
	public static function update_row( $table_name, $row_array, $where ){
		global $wpdb;

		return $wpdb->update(
			static::validate_table_name( $table_name ),
			$row_array,
			$where
		);
		
	}

}