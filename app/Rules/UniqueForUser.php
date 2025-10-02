<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Translation\PotentiallyTranslatedString;

class UniqueForUser implements ValidationRule
{
    protected string $table;

    protected string $column;

    protected ?int $ignoreId;

    protected ?int $userId;

    public function __construct(string $table, string $column = 'name', ?int $ignoreId = null, ?int $userId = null)
    {
        $this->table = $table;
        $this->column = $column;
        $this->ignoreId = $ignoreId;
        $this->userId = $userId ?? auth()->id();
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = DB::table($this->table)
            ->where($this->column, $value)
            ->where('user_id', $this->userId);

        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            $fail("The $attribute has already been taken for your account.");
        }
    }
}
