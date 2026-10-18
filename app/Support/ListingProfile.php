<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Validation\Rule;

/**
 * What a listing in one category asks for and shows: labels of the basic fields, which of them apply,
 * and the extra "details" (see config/listing_types.php).
 */
class ListingProfile
{
    private function __construct(public readonly string $category, private readonly array $config)
    {
    }

    public static function forName(?string $name): self
    {
        $default = config('listing_types.default');
        $own = config('listing_types.profiles')[(string) $name] ?? [];

        return new self((string) $name, array_replace($default, $own));
    }

    public static function forCategory(Category|int|string|null $category): self
    {
        if (! $category instanceof Category) {
            $category = $category ? Category::find($category) : null;
        }

        return self::forName($category?->name);
    }

    public function breedLabel(): string
    {
        return $this->config['breed_label'];
    }

    public function usesGender(): bool
    {
        return (bool) $this->config['gender'];
    }

    public function usesAge(): bool
    {
        return (bool) $this->config['age'];
    }

    public function usesColor(): bool
    {
        return (bool) $this->config['color'];
    }

    /**
     * @return array<string, array{label: string, type: string, options?: list<string>, unit?: string, min?: float|int, max?: float|int, required?: bool}>
     */
    public function fields(): array
    {
        return $this->config['fields'];
    }

    /**
     * Basic-field labels and switches for the form's script, which re-labels the form when the category changes.
     */
    public function forForm(): array
    {
        return [
            'breed_label' => $this->config['breed_label'],
            'breed_placeholder' => $this->config['breed_placeholder'],
            'quantity_label' => $this->config['quantity_label'],
            'age_label' => $this->config['age_label'],
            'age_placeholder' => $this->config['age_placeholder'],
            'gender' => $this->usesGender(),
            'age' => $this->usesAge(),
            'color' => $this->usesColor(),
        ];
    }

    /**
     * Validation rules for the `details.*` inputs (nothing outside the profile's fields is accepted).
     */
    public function detailRules(): array
    {
        $rules = ['details' => ['nullable', 'array']];

        foreach ($this->fields() as $key => $field) {
            $rule = [$field['required'] ?? false ? 'required' : 'nullable'];

            $rule = array_merge($rule, match ($field['type']) {
                'select' => [Rule::in($field['options'])],
                'number' => array_filter(['numeric', isset($field['min']) ? 'min:' . $field['min'] : null, isset($field['max']) ? 'max:' . $field['max'] : null]),
                default => ['string', 'max:' . ($field['max'] ?? 255)],
            });

            $rules["details.{$key}"] = $rule;
        }

        return $rules;
    }

    public function detailMessages(): array
    {
        $messages = [];

        foreach ($this->fields() as $key => $field) {
            $messages["details.{$key}.required"] = "«{$field['label']}» maydonini to'ldiring.";
            $messages["details.{$key}.in"] = "«{$field['label']}»: ro'yxatdan tanlang.";
            $messages["details.{$key}.numeric"] = "«{$field['label']}» raqam bo'lishi kerak.";
            $messages["details.{$key}.min"] = "«{$field['label']}» juda kichik.";
            $messages["details.{$key}.max"] = "«{$field['label']}» juda katta.";
        }

        return $messages;
    }

    /**
     * Keep only this category's fields that were filled in.
     */
    public function cleanDetails(?array $input): array
    {
        $clean = [];

        foreach (array_keys($this->fields()) as $key) {
            $value = $input[$key] ?? null;

            if ($value !== null && $value !== '') {
                $clean[$key] = is_string($value) ? trim($value) : $value;
            }
        }

        return $clean;
    }

    /**
     * Saved details as label/value pairs, in the order the profile lists them.
     *
     * @return list<array{label: string, value: string}>
     */
    public function detailRows(?array $details): array
    {
        $rows = [];

        foreach ($this->fields() as $key => $field) {
            $value = $details[$key] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            $rows[] = [
                'label' => $field['label'],
                'value' => $field['type'] === 'number'
                    ? rtrim(rtrim(number_format((float) $value, 2, '.', ' '), '0'), '.') . ' ' . $field['unit']
                    : (string) $value,
            ];
        }

        return $rows;
    }
}
