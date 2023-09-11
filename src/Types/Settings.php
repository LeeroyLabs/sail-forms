<?php

namespace Leeroy\Forms\Types;
use SailCMS\Collection;
use SailCMS\Contracts\Castable;
use SailCMS\Types\LocaleField;

class Settings implements Castable
{
    public string $title = '';
    public string $to = '';
    public array $cc = [];
    public array $bcc = [];
    public string $success_email_handle = '';

    public function __construct(Collection|array $data = [])
    {
        if (!is_array($data)) {
            $data = $data->unwrap();
        }

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function castFrom(): array
    {
        return [
            'title' => $this->title,
            'to' => $this->to,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            'success_email_handle' => $this->success_email_handle
        ];
    }

    public function castTo(mixed $value): self
    {
        return new self((array)$value);
    }
}