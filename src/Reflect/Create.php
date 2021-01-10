<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Phiz\Reflect;

/**
 * Description of Reflect
 *
 * @author michael
 */
class Create {
    static public function instance($class_path) {
         $reflect = new \ReflectionClass($class_path);
        return $reflect->newInstance();
   }
   static public function instance_params($class_path, $args) {
         $reflect = new \ReflectionClass($class_path);
        return $reflect->newInstanceArgs($args);
   }
}
