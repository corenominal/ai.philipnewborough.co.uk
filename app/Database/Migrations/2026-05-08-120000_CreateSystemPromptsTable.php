<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSystemPromptsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'content' => [
                'type' => 'TEXT',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('system_prompts');
    }

    public function down()
    {
        $this->forge->dropTable('system_prompts');
    }
}
