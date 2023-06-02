<?php

namespace Leeroy\Forms\Types;
use SailCMS\Collection;
use SailCMS\Contracts\Castable;
use SailCMS\Types\LocaleField;

class Settings implements Castable
{
    public string $from = '';
    public string $to = '';
    public LocaleField $subject;
    public array $cc = [];
    public array $bcc = [];

    public function __construct(Collection|array $data = [])
    {
        if (!is_array($data)) {
            $data = $data->unwrap();
        }

        foreach ($data as $key => $value) {
            if ($key === "subject") {
                $value = new LocaleField([
                    'en' => $data['subject']['en'],
                    'fr' => $data['subject']['fr']
                ]);
            }

            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function castFrom(): array
    {
        return [
            'from' => $this->from,
            'to' => $this->to,
            'subject' => $this->subject,
            'cc' => $this->cc,
            'bcc' => $this->bcc
        ];
    }

    public function castTo(mixed $value): self
    {
        return new self((array)$value);
    }
}