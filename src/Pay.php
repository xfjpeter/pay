<?php
namespace johnxu\pay;

class Pay
{
    /**
     * call pay method
     *
     * @access public
     *
     * @param  string $name      pay method
     * @param  array  $arguments pay param
     * @return johnxu\pay\{$name}
     */
    public static function __callStatic($name, $arguments)
    {
        $class = __NAMESPACE__ . '\\' . strtolower($name) . '\\' . ucfirst($name);

        if (!class_exists($class)) {
            throw new \Exception('not found ' . $class);
        }

        return new $class($arguments[0]);
    }
}
