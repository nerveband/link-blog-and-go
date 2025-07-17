<?php
/**
 * Autoloader for Link Blog and Go plugin
 * 
 * @package LinkBlogAndGo
 * @since 1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Autoloader for Link Blog and Go plugin
 */
class Link_Blog_Autoloader {
    
    /**
     * File extension
     */
    const FILE_EXTENSION = '.php';
    
    /**
     * Namespace
     */
    const NAMESPACE_PREFIX = 'Link_Blog_';
    
    /**
     * Base directory for class files
     */
    private $base_directory;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->base_directory = plugin_dir_path(__FILE__);
    }
    
    /**
     * Register autoloader
     */
    public function register() {
        spl_autoload_register(array($this, 'autoload'));
    }
    
    /**
     * Autoload classes
     *
     * @param string $class_name Class name
     * @return void
     */
    public function autoload($class_name) {
        // Check if class starts with our namespace
        if (strpos($class_name, self::NAMESPACE_PREFIX) !== 0) {
            return;
        }
        
        // Convert class name to file path
        $class_file = $this->get_class_file_path($class_name);
        
        if (file_exists($class_file)) {
            require_once $class_file;
        }
    }
    
    /**
     * Get class file path
     *
     * @param string $class_name Class name
     * @return string File path
     */
    private function get_class_file_path($class_name) {
        // Remove namespace prefix
        $class_name = substr($class_name, strlen(self::NAMESPACE_PREFIX));
        
        // Convert to lowercase and replace underscores with hyphens
        $class_name = strtolower(str_replace('_', '-', $class_name));
        
        // Add class prefix
        $filename = 'class-' . $class_name . self::FILE_EXTENSION;
        
        // Determine subdirectory based on class name
        $subdirectory = $this->get_subdirectory($class_name);
        
        return $this->base_directory . $subdirectory . '/' . $filename;
    }
    
    /**
     * Get subdirectory for class
     *
     * @param string $class_name Class name
     * @return string Subdirectory
     */
    private function get_subdirectory($class_name) {
        if (strpos($class_name, 'admin') !== false) {
            return 'admin';
        }
        
        if (strpos($class_name, 'public') !== false) {
            return 'public';
        }
        
        if (strpos($class_name, 'updater') !== false) {
            return 'updater';
        }
        
        return 'core';
    }
}