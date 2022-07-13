<?php

namespace App;

class StandardOutput
{
    public int $status = 400;
    public array $errors = [];
    public array $data = [];

    public function asArray(): array
    {
        return [
            'status' => $this->status,
            'errors' => $this->errors,
            'data' => $this->data,
        ];
    }
}
