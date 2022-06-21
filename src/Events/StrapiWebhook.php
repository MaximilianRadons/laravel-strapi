<?php

namespace MaximilianRadons\LaravelStrapi\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class StrapiWebhook
{
    use Dispatchable, SerializesModels;

    /**
     * The request object.
     *
     * @var Request
     */
    public $request;

    /**
     * Create a new event instance.
     * @param Request $request
     * @return void
     */
    public function __construct($request)
    {
        $this->request = $request;
    }
}