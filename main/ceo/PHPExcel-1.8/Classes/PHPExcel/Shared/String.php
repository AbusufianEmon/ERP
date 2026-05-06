<?php

/**
 * PHPExcel_Shared_String
 *
 * Copyright (c) 2006 - 2015 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel_Shared
 * @copyright  Copyright (c) 2006 - 2015 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 * @version    ##VERSION##, ##DATE##
 */
class PHPExcel_Shared_String
{
    /**    Constants                */
    /**    Regular Expressions        */
    //    Fraction
    const STRING_REGEXP_FRACTION    = '(-?)(\d+)\s+(\d+\/\d+)';


    /**
     * Control characters array
     *
     * @var string[]
     */
    private static $controlCharacters = array();

    /**
     * SYLK Characters array
     *
     * $var array
     */
    private static $SYLKCharacters = array();

    /**
     * Decimal separator
     *
     * @var string
     */
    private static $decimalSeparator;

    /**
     * Thousands separator
     *
     * @var string
     */
    private static $thousandsSeparator;

    /**
     * Currency code
     *
     * @var string
     */
    private static $currencyCode;

    /**
     * Is mbstring extension available?
     *
     * @var boolean
     */
    private static $isMbstringEnabled;

    /**
     * Is iconv extension available?
     *
     * @var boolean
     */
    private static $isIconvEnabled;

    /**
     * Build control characters array
     */
    private static function buildControlCharacters()
    {
        for ($i = 0; $i <= 31; ++$i) {
            if ($i != 9 && $i != 10 && $i != 13) {
                $find = '_x' . sprintf('%04s', strtoupper(dechex($i))) . '_';
                $replace = chr($i);
                self::$controlCharacters[$find] = $replace;
            }
        }
    }

    /**
     * Build SYLK characters array
     */
    private static function buildSYLKCharacters()
    {
        self::$SYLKCharacters = array(
            "\x1B 0"  => chr(0),
            "\x1B 1"  => chr(1),
            "\x1B 2"  => chr(2),
            "\x1B 3"  => chr(3),
            "\x1B 4"  => chr(4),
            "\x1B 5"  => chr(5),
            "\x1B 6"  => chr(6),
            "\x1B 7"  => chr(7),
            "\x1B 8"  => chr(8),
            "\x1B 9"  => chr(9),
            "\x1B :"  => chr(10),
            "\x1B ;"  => chr(11),
            "\x1B <"  => chr(12),
            "\x1B :"  => chr(13),
            "\x1B >"  => chr(14),
            "\x1B ?"  => chr(15),
            "\x1B!0"  => chr(16),
            "\x1B!1"  => chr(17),
            "\x1B!2"  => chr(18),
            "\x1B!3"  => chr(19),
            "\x1B!4"  => chr(20),
            "\x1B!5"  => chr(21),
            "\x1B!6"  => chr(22),
            "\x1B!7"  => chr(23),
            "\x1B!8"  => chr(24),
            "\x1B!9"  => chr(25),
            "\x1B!:"  => chr(26),
            "\x1B!;"  => chr(27),
            "\x1B!<"  => chr(28),
            "\x1B!="  => chr(29),
            "\x1B!>"  => chr(30),
            "\x1B!?"  => chr(31),
            "\x1B'?"  => chr(127),
            "\x1B(0"  => '€', // 128 in CP1252
            "\x1B(2"  => '‚', // 130 in CP1252
            "\x1B(3"  => 'ƒ', // 131 in CP1252
            "\x1B(4"  => '„', // 132 in CP1252
            "\x1B(5"  => '…', // 133 in CP1252
            "\x1B(6"  => '†', // 134 in CP1252
            "\x1B(7"  => '‡', // 135 in CP1252
            "\x1B(8"  => 'ˆ', // 136 in CP1252
            "\x1B(9"  => '‰', // 137 in CP1252
            "\x1B(:"  => 'Š', // 138 in CP1252
            "\x1B(;"  => '‹', // 139 in CP1252
            "\x1BNj"  => 'Œ', // 140 in CP1252
            "\x1B(>"  => 'Ž', // 142 in CP1252
            "\x1B)1"  => '‘', // 145 in CP1252
            "\x1B)2"  => '’', // 146 in CP1252
            "\x1B)3"  => '“', // 147 in CP1252
            "\x1B)4"  => '”', // 148 in CP1252
            "\x1B)5"  => '•', // 149 in CP1252
            "\x1B)6"  => '–', // 150 in CP1252
            "\x1B)7"  => '—', // 151 in CP1252
            "\x1B)8"  => '˜', // 152 in CP1252
            "\x1B)9"  => '™', // 153 in CP1252
            "\x1B):"  => 'š', // 154 in CP1252
            "\x1B);"  => '›', // 155 in CP1252
            "\x1BNz"  => 'œ', // 156 in CP1252
            "\x1B)>"  => 'ž', // 158 in CP1252
            "\x1B)?"  => 'Ÿ', // 159 in CP1252
            "\x1B*0"  => ' ', // 160 in CP1252
            "\x1BN!"  => '¡', // 161 in CP1252
            "\x1BN\"" => '¢', // 162 in CP1252
            "\x1BN#"  => '£', // 163 in CP1252
            "\x1BN("  => '¤', // 164 in CP1252
            "\x1BN%"  => '¥', // 165 in CP1252
            "\x1B*6"  => '¦', // 166 in CP1252
            "\x1BN'"  => '§', // 167 in CP1252
            "\x1BNH " => '¨', // 168 in CP1252
            "\x1BNS"  => '©', // 169 in CP1252
            "\x1BNc"  => 'ª', // 170 in CP1252
            "\x1BN+"  => '«', // 171 in CP1252
            "\x1B*<"  => '¬', // 172 in CP1252
            "\x1B*="  => '­', // 173 in CP1252
            "\x1BNR"  => '®', // 174 in CP1252
            "\x1B*?"  => '¯', // 175 in CP1252
            "\x1BN0"  => '°', // 176 in CP1252
            "\x1BN1"  => '±', // 177 in CP1252
            "\x1BN2"  => '²', // 178 in CP1252
            "\x1BN3"  => '³', // 179 in CP1252
            "\x1BN4"  => '´', // 180 in CP1252
            "\x1BN5"  => 'µ', // 181 in CP1252
            "\x1BN6"  => '¶', // 182 in CP1252
            "\x1BN7"  => '·', // 183 in CP1252
            "\x1BN8"  => '¸', // 184 in CP1252
            "\x1BN9"  => '¹', // 185 in CP1252
            "\x1BNa"  => 'º', // 186 in CP1252
            "\x1BN="  => '»', // 187 in CP1252
            "\x1BN?"  => '¿', // 191 in CP1252
            "\x1BNF"  => '×', // 215 in CP1252
            "\x1BNI"  => '÷', // 247 in CP1252
        );
    }

    /**
     * Convert a UTF-8 string into BIFF8 Unicode string data (8-bit string length)
     * Writes the string using uncompressed notation, no rich text, no Asian phonetics
     * @param string $value UTF-8 encoded string
     * @return string
     */
    public static function UTF8toBIFF8UnicodeShort($value)
    {
        // character count
        $ln = self::CountCharacters($value);
        $data = pack('CC', $ln, 0x01);

        // uncompressed data
        $data .= self::UTF8toUTF16($value, true);

        return $data;
    }

    /**
     * Convert a UTF-8 string into BIFF8 Unicode string data (16-bit string length)
     * Writes the string using uncompressed notation, no rich text, no Asian phonetics
     * @param string $value UTF-8 encoded string
     * @param mixed[] $arr
     * @return string
     */
    public static function UTF8toBIFF8UnicodeLong($value, &$arr)
    {
        // character count
        $ln = self::CountCharacters($value);

        $data = pack('vC', $ln, 0x01);

        // uncompressed data
        $arr[] = $data . self::UTF8toUTF16($value, true);

        return $arr;
    }

    /**
     * Get character count
     *
     * @param string $value UTF-8 encoded string
     * @return int Character count
     */
    public static function CountCharacters($value)
    {
        if (self::getIsMbstringEnabled()) {
            return mb_strlen($value, 'UTF-8');
        }

        return strlen($value);
    }

    /**
     * Convert a UTF-8 string into UTF-16 string
     *
     * @param string $value UTF-8 encoded string
     * @param bool $nullTerminated Whether the output should be null-terminated
     * @return string UTF-16 string
     */
    public static function UTF8toUTF16($value, $nullTerminated = false)
    {
        if (self::getIsIconvEnabled()) {
            $result = iconv('UTF-8', 'UTF-16LE', $value);
            return $nullTerminated ? $result . "\0" : $result;
        }

        //    Fallback if iconv isn't available
        return self::BuildUnicodeString($value, $nullTerminated);
    }

    /**
     * Get whether mbstring extension is available
     *
     * @return bool
     */
    public static function getIsMbstringEnabled()
    {
        if (self::$isMbstringEnabled === null) {
            self::$isMbstringEnabled = function_exists('mb_strlen');
        }
        return self::$isMbstringEnabled;
    }

    /**
     * Get whether iconv extension is available
     *
     * @return bool
     */
    public static function getIsIconvEnabled()
    {
        if (self::$isIconvEnabled === null) {
            self::$isIconvEnabled = function_exists('iconv');
        }
        return self::$isIconvEnabled;
    }

    /**
     * Build Unicode string
     *
     * @param string $value UTF-8 encoded string
     * @param bool $nullTerminated Whether the output should be null-terminated
     * @return string
     */
    public static function BuildUnicodeString($value, $nullTerminated)
    {
        $length = strlen($value);
        $unicode = '';
        for ($i = 0; $i < $length; ++$i) {
            $char = ord($value[$i]);
            if ($char < 0x80) {
                $unicode .= chr($char) . "\0";
            } elseif ($char < 0xE0) {
                $char2 = ord($value[++$i]);
                $unicode .= chr((($char & 0x1F) << 6) | ($char2 & 0x3F)) . "\0";
            } else {
                $char2 = ord($value[++$i]);
                $char3 = ord($value[++$i]);
                $unicode .= chr((($char & 0x0F) << 12) | (($char2 & 0x3F) << 6) | ($char3 & 0x3F)) . "\0";
            }
        }
        return $nullTerminated ? $unicode . "\0" : $unicode;
    }
}
?>
