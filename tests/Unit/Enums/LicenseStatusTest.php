<?php

/**
 * Test untuk LicenseStatus enum
 */

use App\Enums\LicenseStatus;

describe('LicenseStatus', function () {

    it('setiap case memiliki label yang tidak kosong', function () {
        foreach (LicenseStatus::cases() as $case) {
            expect($case->label())->not->toBeEmpty();
        }
    });

    it('isOperational mengembalikan true hanya untuk Valid dan Unchecked', function () {
        expect(LicenseStatus::Valid->isOperational())->toBeTrue();
        expect(LicenseStatus::Unchecked->isOperational())->toBeTrue();

        expect(LicenseStatus::Invalid->isOperational())->toBeFalse();
        expect(LicenseStatus::Expired->isOperational())->toBeFalse();
        expect(LicenseStatus::Unregistered->isOperational())->toBeFalse();
    });

    it('fromString mengembalikan case yang tepat untuk string valid', function () {
        expect(LicenseStatus::fromString('valid'))->toBe(LicenseStatus::Valid);
        expect(LicenseStatus::fromString('invalid'))->toBe(LicenseStatus::Invalid);
        expect(LicenseStatus::fromString('expired'))->toBe(LicenseStatus::Expired);
        expect(LicenseStatus::fromString('unregistered'))->toBe(LicenseStatus::Unregistered);
        expect(LicenseStatus::fromString('unchecked'))->toBe(LicenseStatus::Unchecked);
    });

    it('fromString fallback ke Unchecked untuk string tidak dikenal', function () {
        expect(LicenseStatus::fromString('tidak_dikenal'))->toBe(LicenseStatus::Unchecked);
        expect(LicenseStatus::fromString(''))->toBe(LicenseStatus::Unchecked);
        expect(LicenseStatus::fromString('VALID'))->toBe(LicenseStatus::Valid); // case-insensitive via strtolower
    });

});
