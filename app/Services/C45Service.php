<?php

namespace App\Services;

class C45Service
{
    /**
     * Jalankan algoritma C4.5 Decision Tree
     *
     * @param array  $data      Daftar record baris data
     * @param array  $features  Daftar nama kolom fitur input
     * @param string $target    Nama kolom target klasifikasi
     * @param int    $maxDepth  Batas kedalaman pohon (default: 5)
     * @return array Struktur pohon, aturan IF-THEN, matriks evaluasi, akurasi
     */
    public function run(array $data, array $features, string $target, int $maxDepth = 5): array
    {
        if (empty($data)) {
            throw new \InvalidArgumentException("Dataset tidak boleh kosong.");
        }

        if (empty($features)) {
            throw new \InvalidArgumentException("Pilih setidaknya satu atribut prediktor untuk C4.5.");
        }

        // Validasi target kelas
        $targetValues = [];
        foreach ($data as $row) {
            if (isset($row[$target]) && trim($row[$target]) !== '') {
                $targetValues[] = trim($row[$target]);
            }
        }

        $uniqueTargets = array_unique($targetValues);
        if (count($uniqueTargets) < 2) {
            throw new \InvalidArgumentException("Atribut target '{$target}' harus memiliki minimal 2 kelas berbeda untuk klasifikasi C4.5 (Ditemukan: " . count($uniqueTargets) . " kelas).");
        }

        // Bangun Decision Tree secara rekursif
        $tree = $this->buildTree($data, $features, $target, 0, $maxDepth);

        // Ekstraksi Aturan IF-THEN dari pohon
        $rules = [];
        $this->extractRules($tree, [], $rules);

        // Evaluasi akurasi dan Confusion Matrix pada dataset
        $evaluation = $this->evaluate($data, $tree, $target, array_values($uniqueTargets));

        return [
            'target'           => $target,
            'features'         => $features,
            'classes'          => array_values($uniqueTargets),
            'tree'             => $tree,
            'rules'            => $rules,
            'total_rules'      => count($rules),
            'accuracy'         => $evaluation['accuracy'],
            'confusion_matrix' => $evaluation['confusion_matrix'],
            'correct_count'    => $evaluation['correct'],
            'total_tested'     => $evaluation['total'],
        ];
    }

    /**
     * Rekursi pembentukan pohon keputusan C4.5 menggunakan Gain Ratio
     */
    protected function buildTree(array $data, array $features, string $target, int $depth, int $maxDepth): array
    {
        // Kumpulkan kelas target pada node ini
        $classes = [];
        foreach ($data as $row) {
            if (isset($row[$target])) {
                $classes[] = trim($row[$target]);
            }
        }

        $classCounts = array_count_values($classes);
        arsort($classCounts);
        $dominantClass = array_key_first($classCounts);

        // Kondisi henti:
        // 1. Semua record memiliki kelas yang sama (pure node)
        // 2. Tidak ada fitur tersisa untuk split
        // 3. Batas kedalaman maksimum tercapai
        if (count($classCounts) <= 1 || empty($features) || $depth >= $maxDepth) {
            return [
                'type'        => 'leaf',
                'class'       => $dominantClass,
                'counts'      => $classCounts,
                'total_cases' => count($data),
            ];
        }

        $baseEntropy = $this->calculateEntropy($classCounts, count($data));

        $bestFeature = null;
        $bestGainRatio = -1.0;

        foreach ($features as $f) {
            // Partisi data berdasarkan nilai unik fitur
            $partitions = [];
            foreach ($data as $row) {
                $val = isset($row[$f]) && trim($row[$f]) !== '' ? trim($row[$f]) : 'N/A';
                $partitions[$val][] = $row;
            }

            // Hitung Entropy bersyarat dan Split Info
            $conditionalEntropy = 0.0;
            $splitInfo = 0.0;
            $totalCases = count($data);

            foreach ($partitions as $val => $subset) {
                $subClasses = [];
                foreach ($subset as $r) {
                    if (isset($r[$target])) $subClasses[] = trim($r[$target]);
                }
                $subClassCounts = array_count_values($subClasses);
                $pVal = count($subset) / $totalCases;

                $conditionalEntropy += $pVal * $this->calculateEntropy($subClassCounts, count($subset));
                $splitInfo -= $pVal * log($pVal, 2);
            }

            $infoGain = $baseEntropy - $conditionalEntropy;
            $gainRatio = ($splitInfo > 0.00001) ? ($infoGain / $splitInfo) : 0.0;

            if ($gainRatio > $bestGainRatio) {
                $bestGainRatio = $gainRatio;
                $bestFeature = $f;
            }
        }

        // Jika tidak ada gain ratio positif, jadikan daun
        if ($bestFeature === null || $bestGainRatio <= 0.0001) {
            return [
                'type'        => 'leaf',
                'class'       => $dominantClass,
                'counts'      => $classCounts,
                'total_cases' => count($data),
            ];
        }

        // Lakukan pembagian berdasarkan fitur terbaik
        $bestPartitions = [];
        foreach ($data as $row) {
            $val = isset($row[$bestFeature]) && trim($row[$bestFeature]) !== '' ? trim($row[$bestFeature]) : 'N/A';
            $bestPartitions[$val][] = $row;
        }

        $remainingFeatures = array_values(array_filter($features, fn($feat) => $feat !== $bestFeature));
        $children = [];

        foreach ($bestPartitions as $val => $subset) {
            $children[$val] = $this->buildTree($subset, $remainingFeatures, $target, $depth + 1, $maxDepth);
        }

        return [
            'type'        => 'node',
            'feature'     => $bestFeature,
            'gain_ratio'  => round($bestGainRatio, 4),
            'children'    => $children,
            'fallback'    => $dominantClass,
            'total_cases' => count($data),
        ];
    }

    /**
     * Hitung Entropy Shannon: H(S) = -sum(p * log2(p))
     */
    protected function calculateEntropy(array $classCounts, int $total): float
    {
        if ($total === 0) return 0.0;

        $entropy = 0.0;
        foreach ($classCounts as $cnt) {
            if ($cnt > 0) {
                $p = $cnt / $total;
                $entropy -= $p * log($p, 2);
            }
        }
        return $entropy;
    }

    /**
     * Ekstraksi Aturan IF-THEN dari pohon keputusan
     */
    protected function extractRules(array $node, array $currentConditions, array &$rules): void
    {
        if ($node['type'] === 'leaf') {
            $rules[] = [
                'conditions'  => $currentConditions,
                'class'       => $node['class'],
                'total_cases' => $node['total_cases'],
            ];
            return;
        }

        foreach ($node['children'] as $value => $childNode) {
            $newConditions = $currentConditions;
            $newConditions[] = "{$node['feature']} = '{$value}'";
            $this->extractRules($childNode, $newConditions, $rules);
        }
    }

    /**
     * Prediksi kelas suatu baris data menggunakan pohon C4.5
     */
    public function predict(array $tree, array $row): string
    {
        if ($tree['type'] === 'leaf') {
            return $tree['class'];
        }

        $feat = $tree['feature'];
        $val = isset($row[$feat]) && trim($row[$feat]) !== '' ? trim($row[$feat]) : 'N/A';

        if (isset($tree['children'][$val])) {
            return $this->predict($tree['children'][$val], $row);
        }

        return $tree['fallback'] ?? 'Tidak Terklasifikasi';
    }

    /**
     * Evaluasi model dan hitung matriks kebingungan (Confusion Matrix)
     */
    protected function evaluate(array $data, array $tree, string $target, array $classes): array
    {
        $matrix = [];
        foreach ($classes as $actual) {
            foreach ($classes as $pred) {
                $matrix[$actual][$pred] = 0;
            }
        }

        $correct = 0;
        $total = 0;

        foreach ($data as $row) {
            if (!isset($row[$target])) continue;
            $actual = trim($row[$target]);
            $pred   = $this->predict($tree, $row);

            if (isset($matrix[$actual][$pred])) {
                $matrix[$actual][$pred]++;
            }

            if ($actual === $pred) {
                $correct++;
            }
            $total++;
        }

        $accuracy = $total > 0 ? round(($correct / $total) * 100, 2) : 0.0;

        return [
            'total'            => $total,
            'correct'          => $correct,
            'accuracy'         => $accuracy,
            'confusion_matrix' => $matrix,
        ];
    }
}
