<?php
/**
 * Request.php
 * Copyright (c) 2019 james@firefly-iii.org
 *
 * This file is part of Firefly III CSV Importer.
 *
 * Firefly III CSV Importer is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Firefly III CSV Importer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Firefly III CSV Importer.If not, see
 * <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace App\Http\Request;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidDateException;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Log;

/**
 * Class Request.
 *
 * @codeCoverageIgnore
 *
 */
class Request extends FormRequest
{
    /**
     * @param $array
     *
     * @return array|null
     */
    public function arrayFromValue($array): ?array
    {
        if (is_array($array)) {
            return $array;
        }
        if (null === $array) {
            return null;
        }
        if (is_string($array)) {
            return explode(',', $array);
        }

        return null;
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public function convertBoolean(?string $value): bool
    {
        if (null === $value) {
            return false;
        }
        if ('true' === $value) {
            return true;
        }
        if ('yes' === $value) {
            return true;
        }
        if (1 === $value) {
            return true;
        }
        if ('1' === $value) {
            return true;
        }
        if (true === $value) {
            return true;
        }

        return false;
    }

    /**
     * @param string|null $string
     *
     * @return Carbon|null
     */
    public function dateFromValue(?string $string): ?Carbon
    {
        if (null === $string) {
            return null;
        }
        if ('' === $string) {
            return null;
        }
        try {
            $carbon = new Carbon($string);
        } catch (Exception $e) {
            Log::debug(sprintf('Invalid date: %s: %s', $string, $e->getMessage()));

            return null;
        }

        return $carbon;
    }

    /**
     * Return floating value.
     *
     * @param string $field
     *
     * @return float|null
     */
    public function float(string $field): ?float
    {
        $res = $this->get($field);
        if (null === $res) {
            return null;
        }

        return (float)$res;
    }

    /**
     * Return integer value.
     *
     * @param string $field
     *
     * @return int
     */
    public function integer(string $field): int
    {
        return (int)$this->get($field);
    }

    /**
     * Parse to integer
     *
     * @param string|null $string
     *
     * @return int|null
     */
    public function integerFromValue(?string $string): ?int
    {
        if (null === $string) {
            return null;
        }
        if ('' === $string) {
            return null;
        }

        return (int)$string;
    }

    /**
     * Return integer value, or NULL when it's not set.
     *
     * @param string $field
     *
     * @return int|null
     */
    public function nullableInteger(string $field): ?int
    {
        if (!$this->has($field)) {
            return null;
        }

        $value = (string)$this->get($field);
        if ('' === $value) {
            return null;
        }

        return (int)$value;
    }

    /**
     * Return string value, or NULL if empty.
     *
     * @param string $field
     *
     * @return string|null
     */
    public function nullableString(string $field): ?string
    {
        if (!$this->has($field)) {
            return null;
        }

        return $this->cleanString((string)($this->get($field) ?? ''));
    }

    /**
     * Return string value.
     *
     * @param string $field
     *
     * @return string
     */
    public function string(string $field): string
    {
        return $this->cleanString((string)($this->get($field) ?? ''));
    }

    /**
     * Parse and clean a string.
     *
     * @param string|null $string
     *
     * @return string|null
     */
    public function stringFromValue(?string $string): ?string
    {
        if (null === $string) {
            return null;
        }
        $result = $this->cleanString($string);

        return '' === $result ? null : $result;

    }

    /**
     * Return date or NULL.
     *
     * @param string $field
     *
     * @return Carbon|null
     */
    protected function date(string $field): ?Carbon
    {
        $result = null;
        try {
            $result = $this->get($field) ? new Carbon($this->get($field)) : null;
        } catch (Exception $e) {
            Log::debug(sprintf('Exception when parsing date. Not interesting: %s', $e->getMessage()));
        }

        return $result;
    }

    /**
     * Return date time or NULL.
     *
     * @param string $field
     *
     * @return Carbon|null
     */
    protected function dateTime(string $field): ?Carbon
    {
        if (null === $this->get($field)) {
            return null;
        }
        $value = (string)$this->get($field);
        if (10 === strlen($value)) {
            // probably a date format.
            try {
                $result = Carbon::createFromFormat('Y-m-d', $value);
            } catch (InvalidDateException $e) {
                Log::error(sprintf('"%s" is not a valid date: %s', $value, $e->getMessage()));

                return null;
            }

            return $result;
        }
        // is an atom string, I hope?
        try {
            $result = Carbon::parse($value);
        } catch (InvalidDateException $e) {
            Log::error(sprintf('"%s" is not a valid date or time: %s', $value, $e->getMessage()));

            return null;
        }

        return $result;
    }

    /**
     * Remove weird chars from strings.
     *
     * @param string $string
     *
     * @return string
     */
    private function cleanString(string $string): string
    {
        $search  = [
            "\u{0001}", // start of heading
            "\u{0002}", // start of text
            "\u{0003}", // end of text
            "\u{0004}", // end of transmission
            "\u{0005}", // enquiry
            "\u{0006}", // ACK
            "\u{0007}", // BEL
            "\u{0008}", // backspace
            "\u{000E}", // shift out
            "\u{000F}", // shift in
            "\u{0010}", // data link escape
            "\u{0011}", // DC1
            "\u{0012}", // DC2
            "\u{0013}", // DC3
            "\u{0014}", // DC4
            "\u{0015}", // NAK
            "\u{0016}", // SYN
            "\u{0017}", // ETB
            "\u{0018}", // CAN
            "\u{0019}", // EM
            "\u{001A}", // SUB
            "\u{001B}", // escape
            "\u{001C}", // file separator
            "\u{001D}", // group separator
            "\u{001E}", // record separator
            "\u{001F}", // unit separator
            "\u{007F}", // DEL
            "\u{00A0}", // non-breaking space
            "\u{1680}", // ogham space mark
            "\u{180E}", // mongolian vowel separator
            "\u{2000}", // en quad
            "\u{2001}", // em quad
            "\u{2002}", // en space
            "\u{2003}", // em space
            "\u{2004}", // three-per-em space
            "\u{2005}", // four-per-em space
            "\u{2006}", // six-per-em space
            "\u{2007}", // figure space
            "\u{2008}", // punctuation space
            "\u{2009}", // thin space
            "\u{200A}", // hair space
            "\u{200B}", // zero width space
            "\u{202F}", // narrow no-break space
            "\u{3000}", // ideographic space
            "\u{FEFF}", // zero width no -break space
        ];
        $replace = "\x20"; // plain old normal space
        $string  = str_replace($search, $replace, $string);
        $string  = str_replace(["\n", "\t", "\r"], "\x20", $string);

        return trim($string);
    }
}
