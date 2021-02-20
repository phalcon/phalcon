<?php

namespace Phalcon {
    /** This is typical PHP suspect code.
     *  
     *  Globals has just one static method.
     *  Classname argument is created as global $gGlobals.  
     *  Purpose of having this globals file and class is to
     *  ensure that these odd helper classes and functions are
     *  created and available in global namespace.
     *  Global namespace is access to first layer of PHP interpreter stack.
     */
    class Globals  {
        static public function init(string $globals_class_name = null) : object {
            if ($globals_class_name === null) {
                $globals_class_name = "stdClass";
            }
            return \phalcon_globals_init($globals_class_name);
        }
    }
}
namespace {

    use Phalcon\Support\HelperFactory;
    use Phalcon\Globals;
    
    
    $gGlobals = null;  
    // $gUnCamelize is referenced in Route
    $gUnCamelize = null;
    // $gCamelize is referenced in AbstractDispatcher
    $gCamelize = null;
    // May be used in services setup.
    $gHelperFactory = null;

    function phalcon_globals_init(string $globals_class_name) : object {
        global $gUnCamelize;
        global $gCamelize;
        global $gHelperFactory;
        global $gGlobals;
        
        $gGlobals =  new $globals_class_name();
        $gHelperFactory = new HelperFactory();
        $gUnCamelize = $gHelperFactory->newInstance("uncamelize");
        $gCamelize = $gHelperFactory->newInstance("camelize");
        
        return $gGlobals;
    }
    /**
     * Return class path of object in all lower case.
     * 
     */
    function get_class_lower(object $instance): string {
        return strtolower(get_class($instance));
    }

    /**
     * @param object $instance
     * @return string Class name without namespace, as database table name
     */
    function get_class_ns(object $instance): string {
        $full = get_class($instance);
        $ix = strrpos($full, "\\");
        if ($ix === false) {
            return $full;
        }
        return substr($full, $ix + 1);
    }

    /**
     * @return A generator object
     *   $id = $generator->current();
     *   $generator->next();
     * 
     */
    function newIdGenerator(): \Generator {
        $i = 0;
        while (true) {
            yield $i;
            $i++;
        }
    }

} // end global namespace features



