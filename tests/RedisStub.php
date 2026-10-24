<?php

class Redis
{
    public $result;

    public $exception;

    public function eval($script, $arguments, $numberOfKeys)
    {
        if ($this->exception !== null) {
            throw $this->exception;
        }

        return $this->result;
    }
}
