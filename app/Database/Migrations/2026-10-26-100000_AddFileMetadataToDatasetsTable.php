<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFileMetadataToDatasetsTable extends Migration
{
    public function up()
    {
        $fieldsToAdd = [];

        if (!$this->db->fieldExists('nama_file_asli', 'datasets')) {
            $fieldsToAdd['nama_file_asli'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'nama_dataset',
            ];
        }

        if (!$this->db->fieldExists('nama_file_internal', 'datasets')) {
            $fieldsToAdd['nama_file_internal'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'nama_file_asli',
            ];
        }

        if (!$this->db->fieldExists('ukuran_file', 'datasets')) {
            $fieldsToAdd['ukuran_file'] = [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'nama_file_internal',
            ];
        }

        if (!$this->db->fieldExists('format_file', 'datasets')) {
            $fieldsToAdd['format_file'] = [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'ukuran_file',
            ];
        }

        if (!$this->db->fieldExists('status', 'datasets')) {
            $fieldsToAdd['status'] = [
                'type'       => 'ENUM',
                'constraint' => ['uploaded', 'diproses', 'siap', 'error'],
                'default'    => 'uploaded',
                'after'      => 'jumlah_baris',
            ];
        }

        if (!empty($fieldsToAdd)) {
            $this->forge->addColumn('datasets', $fieldsToAdd);
        }
    }

    public function down()
    {
        $columnsToDrop = [
            'nama_file_asli',
            'nama_file_internal',
            'ukuran_file',
            'format_file',
            'status',
        ];

        foreach ($columnsToDrop as $col) {
            if ($this->db->fieldExists($col, 'datasets')) {
                $this->forge->dropColumn('datasets', $col);
            }
        }
    }
}
