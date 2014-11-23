<?php

namespace Anax\DI;


interface IInjectionAware
{
    /**
     * Set the http context to use
     * @param class $di a service container
     *
     * @return $this
     */
    public function setDi($di);
}