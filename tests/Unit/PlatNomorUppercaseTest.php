<?php

namespace Tests\Unit;

use App\Helpers\FormatHelper;
use PHPUnit\Framework\TestCase;

class PlatNomorUppercaseTest extends TestCase
{
    public function test_format_plat_nomor_separates_letters_and_numbers_with_spaces_and_uppercase(): void
    {
        $this->assertEquals('B 8976 UIY', FormatHelper::formatPlatNomor('B8976UIY'));
        $this->assertEquals('B 8976 UIY', FormatHelper::formatPlatNomor('b8976uiy'));
        $this->assertEquals('B 8976 UIY', FormatHelper::formatPlatNomor('b 8976 uiy'));
        $this->assertEquals('B 8976 UIY', FormatHelper::formatPlatNomor('B 8976UIY'));
        $this->assertEquals('B 8976 UIY', FormatHelper::formatPlatNomor('B8976 UIY'));
        $this->assertEquals('B 8976 UIY', FormatHelper::formatPlatNomor('B   8976   UIY'));
        $this->assertEquals('AB 1234 CD', FormatHelper::formatPlatNomor('ab1234cd'));
        $this->assertEquals('B 123', FormatHelper::formatPlatNomor('b123'));
        $this->assertEquals('RI 1', FormatHelper::formatPlatNomor('ri1'));
        $this->assertEquals('KT 8888 AA', FormatHelper::formatPlatNomor('kt8888aa'));
        $this->assertEquals('D 1234 A', FormatHelper::formatPlatNomor('d1234a'));
        $this->assertEquals('', FormatHelper::formatPlatNomor(''));
        $this->assertEquals('', FormatHelper::formatPlatNomor(null));
    }

    public function test_to_upper_converts_text_to_uppercase(): void
    {
        $this->assertEquals('BUDI SANTOSO', FormatHelper::toUpper('budi santoso'));
        $this->assertEquals('PELABUHAN TANJUNG PRIOK, JAKARTA', FormatHelper::toUpper('pelabuhan tanjung priok, jakarta'));
        $this->assertEquals('PT SAMUDERA LOGISTIK', FormatHelper::toUpper('pt samudera logistik'));
        $this->assertEquals('', FormatHelper::toUpper(''));
        $this->assertNull(FormatHelper::toUpper(null));
    }
}
