<?php

namespace App\Responses;
/**
 * ResponseBuilder is a utility class for building structured API responses.
 * It allows you to create responses with a status, message, data, errors, and meta information.
 * 
 * Usage:
 * return ResponseBuilder::ok()
 *  ->withMessage("Loaded successfully")
*  ->withData($study)
*  ->build();
 */

class ResponseBuilder
{
    protected array $response = [
        'status' => 'ok',
        'message' => null,
        'data' => null,
        'errors' => null,
        'meta' => null,
    ];

    public static function ok(): self
    {
        $builder = new self();
        $builder->response['status'] = 'ok';
        return $builder;
    }

    public static function error(string $message = 'An error occurred'): self
    {
        $builder = new self();
        $builder->response['status'] = 'error';
        $builder->response['message'] = $message;
        return $builder;
    }

    public function withMessage(string $message): self
    {
        $this->response['message'] = $message;
        return $this;
    }

    public function withData($data): self
    {
        $this->response['data'] = $data;
        return $this;
    }

    public function withErrors(array $errors): self
    {
        $this->response['errors'] = $errors;
        return $this;
    }

    public function withMeta(array $meta): self
    {
        $this->response['meta'] = $meta;
        return $this;
    }

    public function build(): array
    {
        // Optionally filter out nulls
        return array_filter($this->response, fn($val) => $val !== null);
    }

    public function json(): void
    {
        header('Content-Type: application/json');
        echo json_encode($this->build());
    }
}
