<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddModelToChatMessages extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('chat_messages', [
            'model' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'role',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('chat_messages', 'model');
    }
}
