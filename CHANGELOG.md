# WPHelper\DatabaseTable - Changelog

## 0.1 - Initial Commit

Release Date: 24 Oct 2020

### Initial Commit

Class `WPHelper\DatabaseTable` provides abstraction to interfacing with WordPress core `$wpdb` global.
- Static method `create_table`.
- Static method `create_meta_table`.
- Static method `drop_table`.
- Static method `add_row`.
- Static method `update_row`.
- Static method `truncate_table`.
- Static method `table_exists`.
- Static helper method `validate_table_name` - returns prefixed table name.
- All methods accept both prefixed and non-prefixed table name.
