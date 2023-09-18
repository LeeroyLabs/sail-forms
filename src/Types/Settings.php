<?php

namespace Leeroy\Forms\Types;
use SailCMS\Collection;
use SailCMS\Contracts\Castable;

class Settings implements Castable
{
    public string $to = '';
    public array $cc = [];
    public array $bcc = [];
    public string $success_email_handle = '';
    public string $action;
    public string $entry_title = '';

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
            'to' => $this->to,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            'success_email_handle' => $this->success_email_handle,
            'action' => $this->action,
            'entry_title' => $this->entry_title
        ];
    }

    public function castTo(mixed $value): self
    {
        return new self((array)$value);
    }
}