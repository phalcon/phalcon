<?php

namespace Phalcon {
    /** 
     *  A global in a PHP script, is a top level variable, that lives
     * outside a function  or class method. An easy way to make
     * a "global" is declare something
     * in "index.php", outside a function or class method.
     *  They can be accessed inside a function or class method by declaring
     *  with keyword global. 
     *  This Globals class has just one static method, to be called once.
     *  It makes a few preconstructed object instances of functors,
     *  used by routing, and metaprogramming with names of classes and methods.
     *  Classname argument is created as global $gGlobals.  
     *  To reduce the use of actual global variables, a stdClass or other class
     * name is created as all purpose globals storage.
     *  
     *  Global namespace is the top stack layer of PHP script, recreated every
     *  php request handling. Int is not those persistent globals managed by
     * PHP interpreter and configuration files,
     * which are used as properties for extensions.
     *  
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



