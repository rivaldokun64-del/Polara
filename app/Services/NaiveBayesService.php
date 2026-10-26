<?php

namespace App\Services;

class NaiveBayesService
{
    /**
     * Jalankan algoritma Naive Bayes Classifier pada dataset rekam medis
     *
     * @param array  $data      Daftar record baris data
     * @param array  $features  Daftar nama kolom fitur input
     * @param string $target    Nama kolom target klasifikasi
     * @return array Probabilitas prior, likelihood, prediksi, metrik evaluasi
     */
    public function run(array $data, array $features, string $target): array
    {
        if (empty($data)) {
            throw new \InvalidArgumentException("Dataset tidak boleh kosong.");
        }

        if (empty($features)) {
            throw new \InvalidArgumentException("Pilih setidaknya satu atribut prediktor untuk Naive Bayes.");
        }

        $totalRecords = count($data);

        // 1. Kumpulkan kelas target
        $targetCounts = [];
        $classData = [];

        foreach ($data as $row) {
            if (!isset($row[$target]) || trim($row[$target]) === '') continue;
            $class = trim($row[$target]);
            $targetCounts[$class] = ($targetCounts[$class] ?? 0) + 1;
            $classData[$class][] = $row;
        }

        $classes = array_keys($targetCounts);
        if (count($classes) < 2) {
            throw new \InvalidArgumentException("Atribut target '{$target}' harus memiliki minimal 2 kelas berbeda (Ditemukan: " . count($classes) . " kelas).");
        }

        // 2. Hitung Prior Probability: P(C)
        $priors = [];
        foreach ($targetCounts as $c => $cnt) {
            $priors[$c] = $cnt / $totalRecords;
        }

        // 3. Tentukan tipe fitur (numerik kontinu vs kategorikal)
        $featureTypes = [];
        $uniqueFeatureValues = [];

        foreach ($features as $f) {
            $isNumeric = true;
            $vals = [];
            foreach ($data as $row) {
                if (isset($row[$f]) && trim($row[$f]) !== '') {
                    $val = trim($row[$f]);
                    $vals[] = $val;
                    if (!is_numeric($val)) {
                        $isNumeric = false;
                    }
                }
            }
            $featureTypes[$f] = $isNumeric ? 'numeric' : 'categorical';
            $uniqueFeatureValues[$f] = array_unique($vals);
        }

        // 4. Hitung Likelihood Parameters
        // - Untuk numerik: mean dan variance per kelas
        // - Untuk kategorikal: frekuensi dengan Laplace smoothing (+1)
        $likelihoods = [];

        foreach ($classes as $c) {
            $recordsInClass = $classData[$c];
            $countInClass = count($recordsInClass);

            foreach ($features as $f) {
                if ($featureTypes[$f] === 'numeric') {
                    $nums = [];
                    foreach ($recordsInClass as $r) {
                        if (isset($r[$f]) && is_numeric($r[$f])) {
                            $nums[] = (float)$r[$f];
                        }
                    }
                    $mean = !empty($nums) ? array_sum($nums) / count($nums) : 0.0;
                    $variance = 0.0;
                    foreach ($nums as $n) {
                        $variance += pow($n - $mean, 2);
                    }
                    $variance = count($nums) > 1 ? $variance / (count($nums) - 1) : 0.01;
                    if ($variance <= 0) $variance = 0.01; // Cegah division by zero

                    $likelihoods[$c][$f] = [
                        'type'     => 'numeric',
                        'mean'     => round($mean, 4),
                        'variance' => round($variance, 4),
                    ];
                } else {
                    $freqs = [];
                    foreach ($recordsInClass as $r) {
                        $val = isset($r[$f]) ? trim($r[$f]) : 'N/A';
                        $freqs[$val] = ($freqs[$val] ?? 0) + 1;
                    }
                    $likelihoods[$c][$f] = [
                        'type'         => 'categorical',
                        'frequencies'  => $freqs,
                        'total_in_cls' => $countInClass,
                        'vocab_size'   => max(1, count($uniqueFeatureValues[$f])),
                    ];
                }
            }
        }

        // 5. Evaluasi & Prediksi pada Dataset
        $correct = 0;
        $matrix = [];
        foreach ($classes as $act) {
            foreach ($classes as $prd) {
                $matrix[$act][$prd] = 0;
            }
        }

        $predictions = [];

        foreach ($data as $i => $row) {
            if (!isset($row[$target])) continue;
            $actual = trim($row[$target]);

            $bestClass = null;
            $maxLogProb = -INF;
            $classProbs = [];

            foreach ($classes as $c) {
                $logProb = log($priors[$c]);

                foreach ($features as $f) {
                    $val = isset($row[$f]) ? trim($row[$f]) : '';
                    $param = $likelihoods[$c][$f];

                    if ($param['type'] === 'numeric') {
                        $numVal = is_numeric($val) ? (float)$val : $param['mean'];
                        $density = $this->gaussianDensity($numVal, $param['mean'], $param['variance']);
                        $logProb += log(max(1e-9, $density));
                    } else {
                        // Laplace smoothing
                        $freq = $param['frequencies'][$val] ?? 0;
                        $p = ($freq + 1) / ($param['total_in_cls'] + $param['vocab_size']);
                        $logProb += log($p);
                    }
                }

                $classProbs[$c] = $logProb;
                if ($logProb > $maxLogProb) {
                    $maxLogProb = $logProb;
                    $bestClass = $c;
                }
            }

            if (isset($matrix[$actual][$bestClass])) {
                $matrix[$actual][$bestClass]++;
            }

            if ($actual === $bestClass) {
                $correct++;
            }

            if ($i < 20) { // Simpan sampel 20 prediksi pertama
                $predictions[] = [
                    'actual'    => $actual,
                    'predicted' => $bestClass,
                    'match'     => ($actual === $bestClass),
                ];
            }
        }

        $accuracy = $totalRecords > 0 ? round(($correct / $totalRecords) * 100, 2) : 0.0;

        return [
            'target'           => $target,
            'features'         => $features,
            'classes'          => $classes,
            'priors'           => $priors,
            'likelihoods'      => $likelihoods,
            'accuracy'         => $accuracy,
            'correct_count'    => $correct,
            'total_tested'     => $totalRecords,
            'confusion_matrix' => $matrix,
            'sample_preds'     => $predictions,
        ];
    }

    /**
     * Hitung kerapatan probabilitas distribusi Gaussian (Normal)
     */
    protected function gaussianDensity(float $x, float $mean, float $variance): float
    {
        $coeff = 1.0 / sqrt(2.0 * M_PI * $variance);
        $exponent = -pow($x - $mean, 2) / (2.0 * $variance);
        return $coeff * exp($exponent);
    }
}
