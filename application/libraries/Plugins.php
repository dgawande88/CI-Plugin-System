<?php

/**
* @name Hooks plugin system for Codeigniter
* @author Dwayne Charrington - http://ilikekillnerds.com
* @coypright Dwayne Charrington 2011
* @licence http://ilikekillnerds.com
*/

class Plugins {
    
    // Codeigniter instance
    protected $CI;
    
    // Where are our plugins stored (with a trailing slash)
    protected $plugins_directory;
    
    // Array of all set hooks
    protected $hooks;
    
    // The current hook we're working with
    protected $current_hook;
    
    // Array of all plugins
    protected $plugins_array = array();
    
    public function __construct()
    {
    	// Store our Codeigniter instance
        $this->CI =& get_instance();
        
        // Load the directory helper so we can parse for plugins in the plugin directory
        $this->CI->load->helper('directory');
        
        // Set the plugins directory if not already set
        if ( empty($this->plugins_directory) )
        {
            $this->plugins_directory = APPPATH . "plugins/";   
        }
        
        // Load all plugins
        $this->load_plugins();
    }
    
    /**
     * Set the location of where our plugins are stored
	 */
    public function set_plugin_dir($directory)
    {
    	$this->plugins_directory = trim($directory);
    }
	
    /**
	 * Load plugins from the plugins directory and store them
	 */
    protected function load_plugins()
    {
    	// Because plugin folder names are the same as their respect main plugin file
    	// We only have to go in one level deep and not recurse sub folders as that
    	// Would be too intensive.
    	$plugins = directory_map($this->plugins_directory, 1);
    	
    	// Iterate through every plugin found
    	foreach ($plugins AS $key => $value)
    	{
    		$extensionless = str_replace('.php', '', $value);
    		
    		// If we already have this plugin added to our cache of plugin objects
    		if ( !$this->plugins_array[$extensionless] )
    		{
    			$this->plugins_array[$extensionless];
    		}
    		else
    		{
				return TRUE;	
    		}
    	}
    	
    	// Get plugin headers and store them
    	$this->get_plugin_headers();	
    }
    
    /**
	 * Get header information from plugins
	 *
	 *
	 */
    protected function get_plugin_headers()
    {
    	$plugin_data = "";
    	
		preg_match ( '|Plugin Name:(.*)$|mi', $plugin_data, $name );
		preg_match ( '|Plugin URI:(.*)$|mi', $plugin_data, $uri );
		preg_match ( '|Version:(.*)|i', $plugin_data, $version );
		preg_match ( '|Description:(.*)$|mi', $plugin_data, $description );
		preg_match ( '|Author:(.*)$|mi', $plugin_data, $author_name );
		preg_match ( '|Author URI:(.*)$|mi', $plugin_data, $author_uri );
    }
	
    /**
     * Register action registers a new action to listen out for.
     *
     * @param $name string
     * @param $function string
     * @param $priority integer
     * @return true
	 */
    public static function register_action($name, $function, $priority=10)
    {
        if( !empty($this->hooks[$name][$priority][$function]) && is_array($this->hooks[$name][$priority][$function]) )
        {
            return true;
        }

        $this->hooks[$name][$priority][$function] = array("function" => $function);
        return true;
    }
	
    /**
     * Triggers an action taking place somewhere
     *
     * @param $name string
     * @param $arguments array
	 */
    public static function run_action($name, $arguments="")
    {
        if (!is_array($this->hooks[$name]))
        {
            return $arguments;
        }
        
        $this->current_hook = $name;
        ksort($this->hooks[$name]);
        
        foreach($this->hooks[$name] AS $priority => $names)
        {
            if (is_array($names))
            {
                foreach($names AS $name)
                {                    
                    $returnargs = call_user_func_array($name['function'], array(&$arguments));
                    
                    if($returnargs)
                    {
                        $arguments = $returnargs;
                    }
                }
            }
        }
        $this->current_hook = '';
        return $arguments;
    }  
	
    /**
     * Removes an action a.k.a hook
     *
     * @param $name string
     * @param $function string
     * @param $priority integer
     * @return true
	 */
    public static function remove_action($name, $function, $priority=10)
    {
        if ( !isset($this->hooks[$name][$priority][$function]) )
        {
            return true;
        }
        
        unset( $this->hooks[$name][$priority][$function] );
    }
    
    /**
     * Get the currently running hook
	 */
    public static function current_hook()
    {
        return $this->current_hook;
    }
    
    /**
     * Sometimes it's good to know what plugins and hooks we have loaded.
	 */
    public static function debug_plugins()
    {
		echo "<p><strong>Plugins found</strong></p>";
		print_r($this->plugins_array);
		echo "<br />";
		echo "<br />";
		echo "<p><strong>Registered hooks</strong></p>";
		print_r($this->hooks);    	
    }
    
}
?>
