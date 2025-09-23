<?php
namespace App\Services;

/**
 * Fast, deterministic doc-vs-source comparer.
 * - Normalizes text
 * - Tokenizes with character offsets
 * - Builds a 7-word shingle index of YOUR doc
 * - Scans source shingles; marks matched tokens in YOUR doc
 * - Merges into char ranges; computes % coverage of YOUR doc
 */
class DocSourceComparer
{
    public function compare(string $docText, string $srcText, array $opts = []): array
    {
        $k              = max(3, (int)($opts['shingle'] ?? 7));           // shingle size
        $maxDocTokens   = max(2000, (int)($opts['max_doc_tokens'] ?? 120000));
        $maxSrcTokens   = max(2000, (int)($opts['max_src_tokens'] ?? 120000));

        $docText = $this->normalize($docText);
        $srcText = $this->normalize($srcText);

        [$docTokens, $docOffsets] = $this->tokenizeWithOffsets($docText, $maxDocTokens);
        [$srcTokens]              = $this->tokenizeWithOffsets($srcText, $maxSrcTokens);

        $docN = count($docTokens);
        $srcN = count($srcTokens);
        if ($docN < $k || $srcN < $k) {
            return [
                'matched_token_count' => 0,
                'coverage_percent'    => 0,
                'ranges'              => [],
                'top_excerpt'         => null,
            ];
        }

        $index = $this->buildShingleIndex($docTokens, $k);

        // Mark matched doc tokens
        $matched = array_fill(0, $docN, false);
        for ($i = 0; $i <= $srcN - $k; $i++) {
            $sh = $this->shingle($srcTokens, $i, $k);
            if (!isset($index[$sh])) continue;
            foreach ($index[$sh] as $pos) {
                for ($t = 0; $t < $k; $t++) {
                    $matched[$pos + $t] = true;
                }
            }
        }

        // Merge token runs -> char ranges
        $ranges = $this->tokenRunsToCharRanges($matched, $docOffsets);

        // Compute coverage %
        $matchedTokens = 0;
        foreach ($matched as $m) if ($m) $matchedTokens++;
        $coverage = (int) round(($matchedTokens * 100.0) / max(1, $docN));

        // Top excerpt (longest range)
        $topExcerpt = null;
        if ($ranges) {
            usort($ranges, fn($a,$b) => ($b['length'] <=> $a['length']));
            $r0 = $ranges[0];
            $pre  = max(0, $r0['start'] - 180);
            $post = min(strlen($docText), $r0['start'] + $r0['length'] + 180);
            $frag = substr($docText, $pre, $post - $pre);
            if ($pre  > 0) $frag = '…' . $frag;
            if ($post < strlen($docText)) $frag .= '…';
            $topExcerpt = $this->clean($frag);
        }

        return [
            'matched_token_count' => $matchedTokens,
            'coverage_percent'    => $coverage,
            'ranges'              => $ranges,      // [['start'=>int, 'length'=>int], ...] in CHAR indexes of YOUR doc
            'top_excerpt'         => $topExcerpt,
        ];
    }

    private function normalize(string $s): string
    {
        // Strip control, unify whitespace, keep letters/digits/punct that help word boundaries
        $s = preg_replace("/[^\P{C}\t\n]+/u", " ", $s) ?? $s;
        $s = str_replace(["\r\n","\r"], "\n", $s);
        $s = preg_replace("/[ \t]{2,}/u", " ", $s) ?? $s;
        $s = preg_replace("/\n{3,}/u", "\n\n", $s) ?? $s;
        return trim($s);
    }

    /**
     * Returns [tokens[], offsets[]] where offsets[i] = [charStart, charEndExclusive]
     */
    private function tokenizeWithOffsets(string $s, int $maxTokens): array
    {
        $tokens  = [];
        $offsets = [];

        // Match words (letters/numbers + interior marks) OR single punctuation that separates
        $re = '/\p{L}[\p{L}\p{Mn}\p{Nd}\']*|\d+|[^\s\p{L}\p{Mn}\p{Nd}]/u';
        if (preg_match_all($re, $s, $m, PREG_OFFSET_CAPTURE)) {
            foreach ($m[0] as [$tok, $pos]) {
                // Normalize words to lowercase; keep punctuation as-is for boundaries
                $norm = preg_match('/\p{L}|\p{Nd}/u', $tok) ? mb_strtolower($tok, 'UTF-8') : $tok;
                $tokens[]  = $norm;
                $offsets[] = [$pos, $pos + strlen($tok)];
                if (count($tokens) >= $maxTokens) break;
            }
        }
        // collapse pure punctuation tokens to avoid false shingles
        $filteredTokens = [];
        $filteredOffsets= [];
        foreach ($tokens as $i => $t) {
            if (preg_match('/^\p{L}|\p{Nd}/u', $t)) {
                $filteredTokens[]  = $t;
                $filteredOffsets[] = $offsets[$i];
            }
        }
        return [$filteredTokens, $filteredOffsets];
    }

    private function buildShingleIndex(array $tokens, int $k): array
    {
        $idx = [];
        $n = count($tokens);
        for ($i = 0; $i <= $n - $k; $i++) {
            $sh = $this->shingle($tokens, $i, $k);
            $idx[$sh][] = $i;
        }
        return $idx;
    }

    private function shingle(array $tokens, int $i, int $k): string
    {
        return implode(' ', array_slice($tokens, $i, $k));
    }

    private function tokenRunsToCharRanges(array $matched, array $offsets): array
    {
        $ranges = [];
        $n = count($matched);
        $i = 0;
        while ($i < $n) {
            if (!$matched[$i]) { $i++; continue; }
            $j = $i + 1;
            while ($j < $n && $matched[$j]) $j++;
            $start  = $offsets[$i][0];
            $end    = $offsets[$j-1][1];
            $ranges[] = ['start' => $start, 'length' => max(0, $end - $start)];
            $i = $j;
        }
        return $ranges;
    }

    private function clean(string $s): string
    {
        $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $s = preg_replace("/[ \t]{2,}/u", ' ', $s) ?? $s;
        $s = preg_replace("/\n{3,}/u", "\n\n", $s) ?? $s;
        return trim($s);
    }
}
