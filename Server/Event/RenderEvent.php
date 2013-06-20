<?php
namespace Theapi\Lcdproc\Server\Event;

use Symfony\Component\EventDispatcher\Event;


class RenderEvent extends Event
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}