<?php

namespace Leeroy\Forms\Types;

class FormDateSearch
{
    public function __construct(public readonly int $date, public readonly string $operator) { }
}