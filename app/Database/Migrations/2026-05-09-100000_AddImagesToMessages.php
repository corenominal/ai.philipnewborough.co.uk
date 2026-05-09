<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddImagesToMessages extends Migration
{
    public function up()
    {
        $this->forge->addColumn('chat_messages', [
            'images' => ['type' => 'MEDIUMTEXT', 'null' => true, 'after' => 'content'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('chat_messages', 'images');
    }
}
