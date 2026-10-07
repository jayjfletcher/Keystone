<?php

declare(strict_types=1);

use Illuminate\Validation\ValidationException;
use JayI\Keystone\Domains\Attribute\Enums\AttributeType;

it('offers options only on select and multiselect', function (AttributeType $type): void {
    expect($type->hasOptions())->toBe(in_array($type, [AttributeType::Select, AttributeType::Multiselect], true));
})->with(AttributeType::cases());

it('allows uniqueness only on scalar identifier types', function (AttributeType $type): void {
    $unique = [AttributeType::Text, AttributeType::Number, AttributeType::Decimal, AttributeType::Date];

    expect($type->canBeUnique())->toBe(in_array($type, $unique, true));
})->with(AttributeType::cases());

it('keeps only the settings a type understands', function (): void {
    expect(AttributeType::Text->validateSettings(['max_length' => 50, 'decimals' => 2]))->toBe(['max_length' => 50])
        ->and(AttributeType::Boolean->validateSettings(['max_length' => 50]))->toBe([]);
});

it('reports invalid settings under the settings key', function (): void {
    AttributeType::Decimal->validateSettings(['decimals' => 42]);
})->throws(ValidationException::class, 'settings.decimals');

it('requires a metric family and default unit for metric attributes', function (): void {
    expect(fn () => AttributeType::Metric->validateSettings([]))->toThrow(ValidationException::class);

    expect(AttributeType::Metric->validateSettings(['metric_family' => 'weight', 'default_unit' => 'kilogram']))
        ->toBe(['metric_family' => 'weight', 'default_unit' => 'kilogram']);
});

it('accepts ISO currency codes for prices', function (): void {
    expect(AttributeType::Price->validateSettings(['currencies' => ['USD', 'EUR']]))->toBe(['currencies' => ['USD', 'EUR']])
        ->and(fn () => AttributeType::Price->validateSettings(['currencies' => ['usd']]))->toThrow(ValidationException::class);
});
