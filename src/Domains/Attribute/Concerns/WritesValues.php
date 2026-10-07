<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;
use JayI\Keystone\Domains\Attribute\Services\Values;
use JayI\Keystone\Domains\Attribute\Services\ValueValidator;
use JayI\Keystone\Domains\Family\Models\FamilyVariantModel;

/**
 * Value and variant-axis rules shared by the product and product model Actions.
 */
trait WritesValues
{
    /**
     * Validate a values patch and apply it to stored values.
     *
     * @param  array<string, array<string, array<string, mixed>>>  $stored
     * @param  Collection<int, AttributeModel>|null  $settable
     * @return array<string, array<string, array<string, mixed>>>
     *
     * @throws ValidationException
     */
    private function patchValues(array $stored, mixed $input, ?Collection $settable, string $why): array
    {
        $patch = app(ValueValidator::class)->validate($input, $settable, $why);

        return Values::apply($stored, $patch);
    }

    /**
     * Every axis of the level must be filled, and no sibling may share the
     * same combination: the axes are what tells siblings apart.
     *
     * @param  array<string, array<string, array<string, mixed>>>  $values
     * @param  iterable<int, array<string, array<string, array<string, mixed>>>>  $siblings  The siblings' own values.
     *
     * @throws ValidationException
     */
    private function checkAxes(FamilyVariantModel $variant, int $level, array $values, iterable $siblings): void
    {
        $axes = $variant->axesAt($level);
        $errors = [];

        foreach ($axes as $axis) {
            if (Values::get($values, $axis->code) === null) {
                $errors['values.'.$axis->code] = sprintf('Axis "%s" of family variant "%s" needs a value.', $axis->code, $variant->code);
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        $signature = $this->axisSignature($axes, $values);

        foreach ($siblings as $sibling) {
            if ($this->axisSignature($axes, $sibling) === $signature) {
                throw ValidationException::withMessages([
                    'values' => sprintf(
                        'A sibling already has the axis values %s. Each variant needs its own combination.',
                        $signature,
                    ),
                ]);
            }
        }
    }

    /**
     * @param  Collection<int, AttributeModel>  $axes
     * @param  array<string, array<string, array<string, mixed>>>  $values
     */
    private function axisSignature(Collection $axes, array $values): string
    {
        $signature = [];

        foreach ($axes as $axis) {
            $signature[$axis->code] = Values::get($values, $axis->code);
        }

        return json_encode($signature, JSON_THROW_ON_ERROR);
    }
}
