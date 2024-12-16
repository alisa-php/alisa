<?php

namespace Alisa;

use Alisa\Http\Request;

class Context extends Request
{
    public function __construct(protected Request $request)
    {
        parent::__construct($request->toArray());
    }
}