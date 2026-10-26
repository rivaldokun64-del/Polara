<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDatasetsAndDynamicTables extends Migration
{
    public function up()
    {
        // 1. Datasets table (if not exists)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_dataset' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'periode_awal' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'periode_akhir' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'jumlah_baris' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'id_user' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('id_user');
        $this->forge->addForeignKey('id_user', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('datasets', true);

        // 2. Dataset Columns table (for dynamic schema & column mapping per PRD FR-06 & FR-07)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_dataset' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'nama_kolom_sumber' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'posisi_kolom' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'tipe_terdeteksi' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'pemetaan_atribut' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'aturan_transformasi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_fitur' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'is_target' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'konfirmasi_pengguna' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('id_dataset');
        $this->forge->addForeignKey('id_dataset', 'datasets', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('dataset_columns', true);
    }

    public function down()
    {
        $this->forge->dropTable('dataset_columns', true);
    }
}
