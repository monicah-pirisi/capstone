<?php
/**
 * Samburu EWS — Input Validation Helper
 *
 * Supports both:
 *   - Instance / fluent API: new Validator($_POST) → $v->required()->fails()
 *   - Static API: Validator::email($value)
 */

class Validator
{
    private array $data     = [];
    private array $errors   = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /* ── Fluent instance methods ─────────────────────────────── */

    public function required(string $field, string $label = ''): static
    {
        $value = $this->data[$field] ?? null;
        if (is_string($value)) {
            $ok = trim($value) !== '';
        } else {
            $ok = $value !== null;
        }
        if (!$ok) {
            $this->errors[$field][] = ($label ?: $field) . ' is required';
        }
        return $this;
    }

    public function email(string $field, string $label = ''): static
    {
        $value = trim($this->data[$field] ?? '');
        if ($value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[$field][] = ($label ?: $field) . ' must be a valid email address';
        }
        return $this;
    }

    public function minLen(string $field, int $length, string $label = ''): static
    {
        $value = trim($this->data[$field] ?? '');
        if ($value !== '' && strlen($value) < $length) {
            $this->errors[$field][] = ($label ?: $field) . " must be at least {$length} characters";
        }
        return $this;
    }

    public function maxLen(string $field, int $length, string $label = ''): static
    {
        $value = trim($this->data[$field] ?? '');
        if (strlen($value) > $length) {
            $this->errors[$field][] = ($label ?: $field) . " must not exceed {$length} characters";
        }
        return $this;
    }

    public function in(string $field, array $allowed, string $label = ''): static
    {
        $value = $this->data[$field] ?? '';
        if (!in_array($value, $allowed, true)) {
            $this->errors[$field][] = ($label ?: $field) . ' has an invalid value';
        }
        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /** Flat list of all error messages */
    public function messages(): array
    {
        $msgs = [];
        foreach ($this->errors as $fieldErrors) {
            foreach ($fieldErrors as $msg) {
                $msgs[] = $msg;
            }
        }
        return $msgs;
    }

    /** Get a trimmed value from the validated data */
    public function get(string $field): string
    {
        return trim($this->data[$field] ?? '');
    }

    /* ── Static helpers (kept for backwards compatibility) ───── */

    public static function email_static(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function required_static(mixed $value): bool
    {
        if (is_string($value)) {
            return trim($value) !== '';
        }
        return $value !== null;
    }

    public static function minLength(string $value, int $length): bool
    {
        return strlen(trim($value)) >= $length;
    }

    public static function maxLength(string $value, int $length): bool
    {
        return strlen(trim($value)) <= $length;
    }

    public static function numeric(mixed $value): bool
    {
        return is_numeric($value);
    }

    public static function integer(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    public static function url(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    public static function isArray(mixed $value): bool
    {
        return is_array($value);
    }

    public static function in_static(mixed $value, array $allowed): bool
    {
        return in_array($value, $allowed, true);
    }

    public static function phone(string $value): bool
    {
        $cleaned = preg_replace('/[^0-9]/', '', $value);
        return strlen($cleaned) >= 10 && strlen($cleaned) <= 15;
    }
}
