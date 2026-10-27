<?php

namespace App\Services;

class KMeansService
{
    /**
     * Jalankan algoritma K-Means Clustering pada dataset rekam medis
     *
     * @param array $data      Daftar record baris data rekam medis
     * @param array $features  Daftar atribut fitur numerik/kategorikal terpilih
     * @param int   $k         Jumlah cluster yang diinginkan (minimal 2)
     * @param int   $maxIter   Batas maksimum iterasi (default: 100)
     * @return array Hasil clustering, centroid, ringkasan, dan metrik
     */
    public function run(array $data, array $features, int $k = 3, int $maxIter = 100): array
    {
        if (count($data) < $k) {
            throw new \InvalidArgumentException("Jumlah baris data (" . count($data) . ") harus lebih besar atau sama dengan K ({$k}).");
        }

        if (empty($features)) {
            throw new \InvalidArgumentException("Pilih setidaknya satu atribut fitur untuk klasterisasi.");
        }

        // 1. Ekstraksi vektor numerik dan normalisasi Min-Max
        $vectors = [];
        $featureStats = [];

        // Helper untuk ekstrak nilai numerik dari row (baik dari kolom database maupun raw_data JSON)
        $extractNumeric = function($row, $featureKey) {
            // 1. Cek jika key ada di baris database langsung
            if (isset($row[$featureKey]) && is_numeric($row[$featureKey])) {
                return (float)$row[$featureKey];
            }
            // 2. Cek jika ada di raw_data JSON
            if (!empty($row['raw_data'])) {
                $raw = is_array($row['raw_data']) ? $row['raw_data'] : json_decode($row['raw_data'], true);
                if (is_array($raw)) {
                    if (isset($raw[$featureKey])) {
                        $v = trim((string)$raw[$featureKey]);
                        $v = str_replace(['.', ','], ['', '.'], $v);
                        if (is_numeric($v)) return (float)$v;
                    }
                }
            }
            return 0.0;
        };

        foreach ($features as $f) {
            $vals = [];
            foreach ($data as $row) {
                $vals[] = $extractNumeric($row, $f);
            }
            $min = !empty($vals) ? min($vals) : 0.0;
            $max = !empty($vals) ? max($vals) : 1.0;
            if ($min === $max) {
                $max += 1.0;
            }
            $featureStats[$f] = ['min' => $min, 'max' => $max];
        }

        foreach ($data as $i => $row) {
            $vec = [];
            foreach ($features as $f) {
                $val = $extractNumeric($row, $f);
                // Normalisasi ke rentang [0, 1]
                $norm = ($val - $featureStats[$f]['min']) / ($featureStats[$f]['max'] - $featureStats[$f]['min']);
                $vec[] = $norm;
            }
            $vectors[$i] = $vec;
        }

        // 2. Inisialisasi Centroid Awal (K-Means++ style spread)
        $centroids = [];
        $numDim = count($features);
        $totalPoints = count($vectors);

        // Centroid pertama acak atau baris pertama
        $centroids[] = $vectors[0];

        while (count($centroids) < $k) {
            $step = (int) floor($totalPoints / $k * count($centroids));
            $step = min($step, $totalPoints - 1);
            $centroids[] = $vectors[$step];
        }

        // 3. Iterasi K-Means
        $assignments = [];
        $converged = false;
        $iteration = 0;

        while (!$converged && $iteration < $maxIter) {
            $iteration++;
            $newAssignments = [];

            // Tetapkan setiap data point ke centroid terdekat
            foreach ($vectors as $i => $vec) {
                $minDist = INF;
                $bestCluster = 0;

                foreach ($centroids as $cIdx => $centroid) {
                    $dist = $this->euclideanDistance($vec, $centroid);
                    if ($dist < $minDist) {
                        $minDist = $dist;
                        $bestCluster = $cIdx;
                    }
                }
                $newAssignments[$i] = $bestCluster;
            }

            // Hitung ulang centroid baru berdasarkan mean anggota
            $newCentroids = array_fill(0, $k, array_fill(0, $numDim, 0.0));
            $clusterCounts = array_fill(0, $k, 0);

            foreach ($vectors as $i => $vec) {
                $c = $newAssignments[$i];
                $clusterCounts[$c]++;
                for ($d = 0; $d < $numDim; $d++) {
                    $newCentroids[$c][$d] += $vec[$d];
                }
            }

            for ($c = 0; $c < $k; $c++) {
                if ($clusterCounts[$c] > 0) {
                    for ($d = 0; $d < $numDim; $d++) {
                        $newCentroids[$c][$d] /= $clusterCounts[$c];
                    }
                } else {
                    // Hindari cluster kosong dengan mempertahankan centroid lama
                    $newCentroids[$c] = $centroids[$c];
                }
            }

            // Periksa konvergensi (apakah pergeseran centroid < epsilon)
            $shift = 0.0;
            for ($c = 0; $c < $k; $c++) {
                $shift += $this->euclideanDistance($centroids[$c], $newCentroids[$c]);
            }

            if ($shift < 0.0001 || $newAssignments === $assignments) {
                $converged = true;
            }

            $centroids = $newCentroids;
            $assignments = $newAssignments;
        }

        // 4. Denormalisasi centroid ke nilai skala asli
        $denormCentroids = [];
        foreach ($centroids as $cIdx => $cVec) {
            $denorm = [];
            foreach ($features as $d => $f) {
                $actualVal = $cVec[$d] * ($featureStats[$f]['max'] - $featureStats[$f]['min']) + $featureStats[$f]['min'];
                $denorm[$f] = round($actualVal, 2);
            }
            $denormCentroids[$cIdx] = $denorm;
        }

        // 5. Hitung Metrik & Profil Karakteristik Cluster
        $clusterProfiles = [];
        for ($c = 0; $c < $k; $c++) {
            $memberIndices = array_keys(array_filter($assignments, fn($v) => $v === $c));
            $memberCount = count($memberIndices);

            // Ringkasan diagnosis dan unit pelayanan dominan jika tersedia
            $diagnoses = [];
            $units = [];
            $ages = [];

            foreach ($memberIndices as $idx) {
                $row = $data[$idx];
                if (!empty($row['nama_diagnosis'])) $diagnoses[] = $row['nama_diagnosis'];
                if (!empty($row['unit_pelayanan'])) $units[] = $row['unit_pelayanan'];
                if (isset($row['usia']) && is_numeric($row['usia'])) $ages[] = (float)$row['usia'];
            }

            $topDiag = !empty($diagnoses) ? array_search(max(array_count_values($diagnoses)), array_count_values($diagnoses)) : '-';
            $topUnit = !empty($units) ? array_search(max(array_count_values($units)), array_count_values($units)) : '-';
            $avgAge  = !empty($ages) ? round(array_sum($ages) / count($ages), 1) : null;

            $clusterProfiles[$c] = [
                'cluster_index' => $c + 1,
                'cluster_name'  => 'Klaster ' . ($c + 1),
                'member_count'  => $memberCount,
                'percentage'    => $totalPoints > 0 ? round(($memberCount / $totalPoints) * 100, 1) : 0,
                'centroid'      => $denormCentroids[$c],
                'features'      => $features,
                'avg_age'       => $avgAge,
                'top_diagnosis' => $topDiag,
                'top_unit'      => $topUnit,
            ];
        }

        // 6. Hitung Sum of Squared Errors (Inertia / WCSS)
        $inertia = 0.0;
        foreach ($vectors as $i => $vec) {
            $c = $assignments[$i];
            $inertia += pow($this->euclideanDistance($vec, $centroids[$c]), 2);
        }

        // 7. Estimasi Skor Silhouette Sederhana
        $silhouetteScore = $this->calculateSilhouette($vectors, $assignments, $k);

        return [
            'k'                 => $k,
            'features'          => $features,
            'iterations'        => $iteration,
            'converged'         => $converged,
            'inertia'           => round($inertia, 4),
            'silhouette'        => round($silhouetteScore, 4),
            'cluster_profiles'  => $clusterProfiles,
            'assignments'       => $assignments,
            'feature_stats'     => $featureStats,
        ];
    }

    /**
     * Hitung jarak Euclidean antar 2 vektor
     */
    protected function euclideanDistance(array $a, array $b): float
    {
        $sum = 0.0;
        $len = count($a);
        for ($i = 0; $i < $len; $i++) {
            $diff = $a[$i] - $b[$i];
            $sum += $diff * $diff;
        }
        return sqrt($sum);
    }

    /**
     * Estimasi Silhouette Coefficient untuk mengukur kepadatan dan keterpisahan cluster
     */
    protected function calculateSilhouette(array $vectors, array $assignments, int $k): float
    {
        if ($k < 2 || count($vectors) > 1000) {
            // Untuk dataset sangat besar, ambil subsampel 300 titik untuk efisiensi komputasi
            $sampleKeys = count($vectors) > 300 ? array_rand($vectors, 300) : array_keys($vectors);
        } else {
            $sampleKeys = array_keys($vectors);
        }

        $scores = [];

        foreach ($sampleKeys as $i) {
            $vecI = $vectors[$i];
            $clusterI = $assignments[$i];

            // 1. Jarak rata-rata ke anggota di cluster yang sama (a_i)
            $sameClusterDists = [];
            foreach ($sampleKeys as $j) {
                if ($i !== $j && $assignments[$j] === $clusterI) {
                    $sameClusterDists[] = $this->euclideanDistance($vecI, $vectors[$j]);
                }
            }
            $a_i = !empty($sameClusterDists) ? array_sum($sameClusterDists) / count($sameClusterDists) : 0.0;

            // 2. Jarak rata-rata minimum ke cluster lain (b_i)
            $b_i = INF;
            for ($c = 0; $c < $k; $c++) {
                if ($c === $clusterI) continue;
                $otherClusterDists = [];
                foreach ($sampleKeys as $j) {
                    if ($assignments[$j] === $c) {
                        $otherClusterDists[] = $this->euclideanDistance($vecI, $vectors[$j]);
                    }
                }
                if (!empty($otherClusterDists)) {
                    $avgOther = array_sum($otherClusterDists) / count($otherClusterDists);
                    if ($avgOther < $b_i) {
                        $b_i = $avgOther;
                    }
                }
            }

            if ($b_i === INF) {
                $b_i = 0.0;
            }

            $maxAB = max($a_i, $b_i);
            $scores[] = ($maxAB > 0) ? ($b_i - $a_i) / $maxAB : 0.0;
        }

        return !empty($scores) ? array_sum($scores) / count($scores) : 0.0;
    }
}
