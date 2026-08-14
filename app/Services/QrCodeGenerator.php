<?php

namespace App\Services;

/**
 * Minimal pure-PHP QR Code generator (byte mode, ECC level L) that renders an SVG.
 * Supports versions 1-10, which is more than enough for short payloads such as otpauth URIs.
 */
class QrCodeGenerator
{
    private const ECC_PER_BLOCK = [
        1 => 7, 2 => 10, 3 => 15, 4 => 20, 5 => 26,
        6 => 18, 7 => 20, 8 => 24, 9 => 30, 10 => 18,
    ];

    private const BLOCKS = [
        1 => [1, 19], 2 => [1, 34], 3 => [1, 55], 4 => [1, 80], 5 => [1, 108],
        6 => [2, 68], 7 => [2, 78], 8 => [2, 97], 9 => [2, 116], 10 => [2, 137],
    ];

    private const DATA_BYTE_CAPACITY = [17, 32, 53, 78, 106, 134, 154, 192, 230, 271];

    private const ALIGNMENT_POSITIONS = [
        1 => [], 2 => [6, 18], 3 => [6, 22], 4 => [6, 26], 5 => [6, 30],
        6 => [6, 34], 7 => [6, 22, 38], 8 => [6, 24, 42], 9 => [6, 26, 46], 10 => [6, 28, 50],
    ];

    private const FORMAT_LEVEL_L = 0b01;

    private array $gfExp = [];

    private array $gfLog = [];

    private array $matrix = [];

    private int $size = 0;

    public function svg(string $data, int $scale = 6): string
    {
        $this->initGaloisField();
        $version = $this->selectVersion($data);
        $this->size = $version * 4 + 17;
        $this->matrix = array_fill(0, $this->size, array_fill(0, $this->size, 0));

        $blocks = $this->encodeData($data, $version);
        $this->placeFunctionPatterns($version);
        $this->placeData($blocks, $version);

        $best = $this->applyMask($version);

        return $this->renderSvg($best, $scale);
    }

    private function selectVersion(string $data): int
    {
        $length = strlen($data);

        foreach (self::DATA_BYTE_CAPACITY as $version => $capacity) {
            if ($length <= $capacity) {
                return $version + 1;
            }
        }

        throw new \InvalidArgumentException('Data too long for QR generation (max 271 bytes).');
    }

    private function initGaloisField(): void
    {
        $this->gfExp = [];
        $this->gfLog = [];
        $x = 1;

        for ($i = 0; $i < 255; $i++) {
            $this->gfExp[$i] = $x;
            $this->gfLog[$x] = $i;
            $x <<= 1;

            if ($x & 0x100) {
                $x ^= 0x11D;
            }
        }

        $this->gfExp[255] = $this->gfExp[0];
    }

    private function gfMul(int $a, int $b): int
    {
        if ($a === 0 || $b === 0) {
            return 0;
        }

        return $this->gfExp[($this->gfLog[$a] + $this->gfLog[$b]) % 255];
    }

    private function encodeData(string $data, int $version): array
    {
        $bits = $this->toBitString($data, $version);
        [$numBlocks, $dataPerBlock] = self::BLOCKS[$version];

        $capacityBits = $numBlocks * $dataPerBlock * 8;
        $bits = str_pad($bits, $capacityBits, '0');

        $dataCodewords = [];
        $byte = '';

        foreach (str_split($bits) as $i => $bit) {
            $byte .= $bit;

            if (($i + 1) % 8 === 0) {
                $dataCodewords[] = bindec($byte);
                $byte = '';
            }
        }

        $blocks = [];
        $eccPerBlock = self::ECC_PER_BLOCK[$version];

        for ($b = 0; $b < $numBlocks; $b++) {
            $chunk = array_slice($dataCodewords, $b * $dataPerBlock, $dataPerBlock);
            $blocks[] = ['data' => $chunk, 'ecc' => $this->reedSolomon($chunk, $eccPerBlock)];
        }

        return $blocks;
    }

    private function toBitString(string $data, int $version): string
    {
        $mode = 0b0100;
        $countBits = $version <= 9 ? 8 : 16;

        $result = str_pad(decbin($mode), 4, '0', STR_PAD_LEFT);
        $result .= str_pad(decbin(strlen($data)), $countBits, '0', STR_PAD_LEFT);

        foreach (str_split($data) as $char) {
            $result .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        return $result;
    }

    private function reedSolomon(array $data, int $eccCount): array
    {
        $generator = [1];

        for ($i = 0; $i < $eccCount; $i++) {
            $next = array_fill(0, count($generator) + 1, 0);

            foreach ($generator as $index => $coefficient) {
                $next[$index] ^= $coefficient;
                $next[$index + 1] ^= $this->gfMul($coefficient, $this->gfExp[$i]);
            }

            $generator = $next;
        }

        $remainder = array_fill(0, $eccCount, 0);

        foreach ($data as $byte) {
            $factor = $byte ^ array_shift($remainder);
            $remainder[] = 0;

            foreach ($generator as $index => $coefficient) {
                if ($index === 0) {
                    continue;
                }

                $remainder[$index - 1] ^= $this->gfMul($coefficient, $factor);
            }
        }

        return $remainder;
    }

    private function placeFunctionPatterns(int $version): void
    {
        $this->placeFinder(0, 0);
        $this->placeFinder($this->size - 7, 0);
        $this->placeFinder(0, $this->size - 7);

        for ($i = 8; $i < $this->size - 8; $i++) {
            $this->matrix[$i][6] = ($i % 2 === 0) ? 1 : 0;
            $this->matrix[6][$i] = ($i % 2 === 0) ? 1 : 0;
        }

        $this->placeAlignmentPatterns($version);

        $this->matrix[$this->size - 8][8] = 1; // dark module
    }

    private function placeFinder(int $row, int $col): void
    {
        for ($r = -1; $r <= 7; $r++) {
            for ($c = -1; $c <= 7; $c++) {
                if ($row + $r < 0 || $row + $r >= $this->size || $col + $c < 0 || $col + $c >= $this->size) {
                    continue;
                }

                $isDark = ($r === 0 || $r === 6 || $c === 0 || $c === 6)
                    || (($r >= 2 && $r <= 4) && ($c >= 2 && $c <= 4));

                $this->matrix[$row + $r][$col + $c] = $isDark ? 1 : 0;
            }
        }
    }

    private function placeAlignmentPatterns(int $version): void
    {
        if ($version === 1) {
            return;
        }

        $positions = self::ALIGNMENT_POSITIONS[$version];

        foreach ($positions as $row) {
            foreach ($positions as $col) {
                if ($this->isFunctionArea($row, $col)) {
                    continue;
                }

                for ($r = -2; $r <= 2; $r++) {
                    for ($c = -2; $c <= 2; $c++) {
                        $isDark = abs($r) === 2 || abs($c) === 2 || ($r === 0 && $c === 0);
                        $this->matrix[$row + $r][$col + $c] = $isDark ? 1 : 0;
                    }
                }
            }
        }
    }

    private function isFunctionArea(int $row, int $col): bool
    {
        if (($row === 6 || $col === 6)) {
            return true;
        }

        // Overlaps with the three finder patterns.
        if (($row < 9 && $col < 9) || ($row < 9 && $col >= $this->size - 8) || ($row >= $this->size - 8 && $col < 9)) {
            return true;
        }

        return false;
    }

    private function placeData(array $blocks, int $version): void
    {
        $numBlocks = count($blocks);
        $eccPerBlock = self::ECC_PER_BLOCK[$version];
        $dataPerBlock = self::BLOCKS[$version][1];

        $codewords = [];

        for ($i = 0; $i < $dataPerBlock; $i++) {
            foreach ($blocks as $block) {
                if (isset($block['data'][$i])) {
                    $codewords[] = $block['data'][$i];
                }
            }
        }

        for ($i = 0; $i < $eccPerBlock; $i++) {
            foreach ($blocks as $block) {
                if (isset($block['ecc'][$i])) {
                    $codewords[] = $block['ecc'][$i];
                }
            }
        }

        $this->placeCodewords($codewords);
    }

    private function placeCodewords(array $codewords): void
    {
        $bitIndex = 0;
        $totalBits = $this->size * $this->size;

        for ($right = $this->size - 1; $right >= 1; $right -= 2) {
            if ($right === 6) {
                $right--;
            }

            for ($vertical = 0; $vertical < $this->size; $vertical++) {
                for ($horizontal = 0; $horizontal < 2; $horizontal++) {
                    $col = $right - $horizontal;
                    $row = (($right + 1) & 2) === 0 ? $this->size - 1 - $vertical : $vertical;

                    if ($this->matrix[$row][$col] !== 0) {
                        continue;
                    }

                    $bit = 0;

                    if (isset($codewords[intdiv($bitIndex, 8)])) {
                        $bit = ($codewords[intdiv($bitIndex, 8)] >> (7 - ($bitIndex % 8))) & 1;
                    }

                    $this->matrix[$row][$col] = $bit;
                    $bitIndex++;
                }
            }
        }
    }

    private function applyMask(int $version): array
    {
        $bestMatrix = $this->matrix;
        $bestPenalty = PHP_INT_MAX;

        for ($mask = 0; $mask < 8; $mask++) {
            $candidate = $this->matrix;

            for ($row = 0; $row < $this->size; $row++) {
                for ($col = 0; $col < $this->size; $col++) {
                    if ($this->isFunctionModule($row, $col)) {
                        continue;
                    }

                    if ($this->maskCondition($mask, $row, $col)) {
                        $candidate[$row][$col] ^= 1;
                    }
                }
            }

            $format = (self::FORMAT_LEVEL_L << 3) | $mask;
            $this->overwriteFormat($candidate, $this->formatBits($format));

            $penalty = $this->penalty($candidate);

            if ($penalty < $bestPenalty) {
                $bestPenalty = $penalty;
                $bestMatrix = $candidate;
            }
        }

        $this->matrix = $bestMatrix;

        return $bestMatrix;
    }

    private function isFunctionModule(int $row, int $col): bool
    {
        if ($row === 6 || $col === 6) {
            return true;
        }

        if (($row < 9 && $col < 9) || ($row < 9 && $col >= $this->size - 8) || ($row >= $this->size - 8 && $col < 9)) {
            return true;
        }

        return false;
    }

    private function maskCondition(int $mask, int $row, int $col): bool
    {
        return match ($mask) {
            0 => ($row + $col) % 2 === 0,
            1 => $row % 2 === 0,
            2 => $col % 3 === 0,
            3 => ($row + $col) % 3 === 0,
            4 => (intdiv($row, 2) + intdiv($col, 3)) % 2 === 0,
            5 => (($row * $col) % 2) + (($row * $col) % 3) === 0,
            6 => ((($row * $col) % 2) + (($row * $col) % 3)) % 2 === 0,
            7 => ((($row + $col) % 2) + (($row * $col) % 3)) % 2 === 0,
            default => false,
        };
    }

    private function formatBits(int $data): string
    {
        $generator = 0x537; // BCH generator polynomial for format info
        $value = $data << 10;

        for ($i = 14; $i >= 0; $i--) {
            if (($value >> ($i + 10)) & 1) {
                $value ^= $generator << $i;
            }
        }

        $format = (($data << 10) | $value) ^ 0x5412;

        return str_pad(decbin($format), 15, '0', STR_PAD_LEFT);
    }

    private function overwriteFormat(array &$candidate, string $bits): void
    {
        // Copy 1 (top-left): vertical along column 8, horizontal along row 8.
        $positions = [
            [8, 0], [8, 1], [8, 2], [8, 3], [8, 4], [8, 5], [8, 7], [8, 8],
            [7, 8], [5, 8], [4, 8], [3, 8], [2, 8], [1, 8], [0, 8],
        ];

        foreach ($positions as $i => [$row, $col]) {
            $candidate[$row][$col] = (int) $bits[$i];
        }

        // Copy 2: bottom-left vertical along column 8, top-right horizontal along row 8.
        $size = $this->size;
        $copy2 = [];
        for ($i = 0; $i < 7; $i++) {
            $copy2[] = [$size - 1 - $i, 8];
        }
        $copy2[] = [8, $size - 8];
        for ($i = 0; $i < 7; $i++) {
            $copy2[] = [8, $size - 7 + $i];
        }

        foreach ($copy2 as $i => [$row, $col]) {
            $candidate[$row][$col] = (int) $bits[$i];
        }
    }

    private function penalty(array $matrix): int
    {
        $penalty = 0;
        $size = $this->size;

        // Rule 1: runs of same color.
        for ($row = 0; $row < $size; $row++) {
            $run = 1;
            for ($col = 1; $col < $size; $col++) {
                if ($matrix[$row][$col] === $matrix[$row][$col - 1]) {
                    $run++;
                } else {
                    $penalty += $this->runPenalty($run);
                    $run = 1;
                }
            }
            $penalty += $this->runPenalty($run);
        }

        for ($col = 0; $col < $size; $col++) {
            $run = 1;
            for ($row = 1; $row < $size; $row++) {
                if ($matrix[$row][$col] === $matrix[$row - 1][$col]) {
                    $run++;
                } else {
                    $penalty += $this->runPenalty($run);
                    $run = 1;
                }
            }
            $penalty += $this->runPenalty($run);
        }

        // Rule 2: 2x2 blocks of same color.
        for ($row = 0; $row < $size - 1; $row++) {
            for ($col = 0; $col < $size - 1; $col++) {
                $topLeft = $matrix[$row][$col];
                if ($topLeft === $matrix[$row][$col + 1]
                    && $topLeft === $matrix[$row + 1][$col]
                    && $topLeft === $matrix[$row + 1][$col + 1]) {
                    $penalty += 3;
                }
            }
        }

        // Rule 3: finder-like patterns 1:1:3:1:1 preceded/followed by 4 light modules.
        $pattern = [1, 0, 1, 1, 1, 0, 1, 0, 0, 0, 0];

        for ($row = 0; $row < $size; $row++) {
            for ($col = 0; $col <= $size - count($pattern); $col++) {
                $matches = true;
                for ($i = 0; $i < count($pattern); $i++) {
                    if ($matrix[$row][$col + $i] !== $pattern[$i]) {
                        $matches = false;
                        break;
                    }
                }
                if ($matches) {
                    $penalty += 40;
                }
            }
        }

        for ($col = 0; $col < $size; $col++) {
            for ($row = 0; $row <= $size - count($pattern); $row++) {
                $matches = true;
                for ($i = 0; $i < count($pattern); $i++) {
                    if ($matrix[$row + $i][$col] !== $pattern[$i]) {
                        $matches = false;
                        break;
                    }
                }
                if ($matches) {
                    $penalty += 40;
                }
            }
        }

        // Rule 4: dark/light proportion.
        $dark = 0;
        foreach ($matrix as $rowData) {
            $dark += array_sum($rowData);
        }
        $percent = ($dark * 100) / ($size * $size);
        $penalty += (int) (abs($percent - 50) / 5) * 10;

        return $penalty;
    }

    private function runPenalty(int $run): int
    {
        return $run >= 5 ? $run - 2 : 0;
    }

    private function renderSvg(array $matrix, int $scale): string
    {
        $size = $this->size;
        $quiet = 4;
        $dimension = ($size + $quiet * 2) * $scale;
        $body = '';

        for ($row = 0; $row < $size; $row++) {
            for ($col = 0; $col < $size; $col++) {
                if ($matrix[$row][$col] === 1) {
                    $body .= '<rect x="' . (($quiet + $col) * $scale) . '" y="' . (($quiet + $row) * $scale)
                        . '" width="' . $scale . '" height="' . $scale . '"/>';
                }
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $dimension . ' ' . $dimension
            . '" width="' . $dimension . '" height="' . $dimension . '" shape-rendering="crispEdges">'
            . '<rect width="100%" height="100%" fill="#ffffff"/>' . $body . '</svg>';
    }
}